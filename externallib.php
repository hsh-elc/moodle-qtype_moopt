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

        //Do a little bit of cleanup and remove everything from the file area we extracted
        remove_all_files_from_draft_area($draftid, $user_context, $filename);

        return $returnVal;
    }

}
