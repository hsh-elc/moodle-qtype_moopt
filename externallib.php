<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . "/externallib.php");
require_once("$CFG->dirroot/question/type/programmingtask/locallib.php");

class qtype_programmingtask_external extends external_api {

    public static function extract_task_infos_from_draft_file_parameters() {
        return new external_function_parameters(
                array(
            'itemid' => new external_value(PARAM_INT, 'id of the draft area')
                )
        );
    }

    public static function extract_task_infos_from_draft_file_returns() {
        new external_single_structure(
                array(
            'title' => new external_value(PARAM_TEXT, 'title of the task'),
            'description' => new external_value(PARAM_RAW, 'description of the task'),
            'internaldescription' => new external_value(PARAM_RAW, 'internal description of the task')
                )
        );
    }

    /**
     * Extracts the relevant information from the file in the draft area with the supplied id.
     * Relevant information are for example: Title, description, internal description.
     *
     * @param type $itemid
     */
    public static function extract_task_infos_from_draft_file($itemid) {
        global $USER;

        //Do some validation
        $params = self::validate_parameters(self::extract_task_infos_from_draft_file_parameters(), array('itemid' => $itemid));
        $draftid = $params['itemid'];

        $user_context = context_user::instance($USER->id);
        self::validate_context($user_context);

        $filename = unzip_task_file_in_draft_area($draftid, $user_context);

        $doc = create_domdocument_from_task_xml($user_context, $draftid, $filename);

        $returnVal = array();

        foreach ($doc->getElementsByTagNameNS(proforma_TASK_XML_NAMESPACE, 'description') as $des) {
            if ($des->parentNode->localName == 'task') {
                $returnVal['description'] = $des->nodeValue;
                break;
            }
        }

        foreach ($doc->getElementsByTagNameNS(proforma_TASK_XML_NAMESPACE, 'title') as $des) {
            if ($des->parentNode->localName == 'task') {
                $returnVal['title'] = $des->nodeValue;
                break;
            }
        }

        foreach ($doc->getElementsByTagNameNS(proforma_TASK_XML_NAMESPACE, 'internal-description') as $des) {
            if ($des->parentNode->localName == 'task') {
                $returnVal['internaldescription'] = $des->nodeValue;
                break;
            }
        }

        //Currently only supports tns:task-type; neither tns:external-task-type nor tns:included-task-file-type
        //TODO: Implement the other two
        foreach ($doc->getElementsByTagNameNS(proforma_TASK_XML_NAMESPACE, 'task') as $task) {
            $returnVal['taskuuid'] = $task->getAttribute('uuid');
            break;
        }

        //Do a little bit of cleanup and remove everything from the file area we extracted
        remove_all_files_from_draft_area($draftid, $user_context, $filename);

        return $returnVal;
    }

    public static function retrieve_grading_results_parameters() {
        return new external_function_parameters(
                ['qubaid' => new external_value(PARAM_INT, 'id of the question usage')]
        );
    }

    public static function retrieve_grading_results_returns() {
        return new external_value(PARAM_BOOL, "whether any grade process finished");
    }

    public static function retrieve_grading_results($qubaid) {
        global $USER, $SESSION, $DB;

        //Check if calling user is teacher
        $quba_record = $DB->get_record('question_usages', ['id' => $qubaid]);
        $context_record = $DB->get_record('context', ['id' => $quba_record->contextid]);
        $context = context_module::instance($context_record->instanceid);
        self::validate_context($context);
        $isTeacher = has_capability('mod/quiz:grade', $context);;

        $lastAccess = $SESSION->last_retrieve_grading_results ?? microtime(true);
        $SESSION->last_retrieve_grading_results = microtime(true);
        if (microtime(true) - $lastAccess < get_config("qtype_programmingtask", "grappa_client_polling_interval") * 0.9 /* grace interval */ && !$isTeacher) {
            //Only allow a request every n seconds from the same user
            return false;
        }

        //Do some param validation
        $params = self::validate_parameters(self::retrieve_grading_results_parameters(), array('qubaid' => $qubaid));
        $qubaid = $params['qubaid'];

        //Check if the question usage given by the qubaid belongs to the requesting user
        if (!$isTeacher) {
            $record = $DB->get_record('quiz_attempts', ['uniqueid' => $qubaid], 'userid');
            if (!$record)
                return false;
            $userid = $record->userid;
            if ($userid != $USER->id)
                return false;
        }

        return retrieve_grading_results($qubaid);
    }

}
