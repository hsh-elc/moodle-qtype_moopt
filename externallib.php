<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

use qtype_moopt\utility\proforma_xml\grading_hints_helper;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . "/externallib.php");
require_once("$CFG->dirroot/question/type/moopt/locallib.php");

class qtype_moopt_external extends external_api {

    public static function extract_task_infos_from_draft_file_parameters() {
        return new external_function_parameters(
                array(
            'itemid' => new external_value(PARAM_INT, 'id of the draft area')
                )
        );
    }

    public static function extract_task_infos_from_draft_file_returns() {
        return new external_single_structure(
                array(
            'error' => new external_value(PARAM_TEXT, 'error message', VALUE_OPTIONAL),
            'title' => new external_value(PARAM_TEXT, 'title of the task', VALUE_OPTIONAL),
            'description' => new external_value(PARAM_RAW, 'description of the task', VALUE_OPTIONAL),
            'internaldescription' => new external_value(PARAM_RAW, 'internal description of the task', VALUE_OPTIONAL),
            'proglang' => new external_value(PARAM_RAW, 'programming language of the task', VALUE_OPTIONAL),
            'taskuuid' => new external_value(PARAM_RAW, 'task\'s uuid', VALUE_OPTIONAL),
            'maxscoregradinghints' => new external_value(PARAM_FLOAT, 'maximum score', VALUE_OPTIONAL),
            'filesdisplayedingeneralfeedback' => new external_value(PARAM_RAW, 'general feedback', VALUE_OPTIONAL),
            'enablefileinput' => new external_value(PARAM_BOOL, 'Enable file submissions', VALUE_OPTIONAL),
            'freetextfilesettings' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'enablecustomsettings'    => new external_value(PARAM_BOOL, 'Enable custom settings'),
                                'usefixedfilename'    => new external_value(PARAM_BOOL, 'Use fixed file name'),
                                'defaultfilename'    => new external_value(PARAM_TEXT, 'Default file name'),
                                'proglang'    => new external_value(PARAM_TEXT, 'Programming language for syntax highlighting'),
                                'filecontent'    => new external_value(PARAM_RAW, 'File content to use as a template'),
                                'initialdisplayrows'    => new external_value(PARAM_INT, 'The initial display rows of a textfield')
                            )
                        )
                    ,'Free text settings', VALUE_OPTIONAL),
            'moodleValidationProformaNamespace' => new external_value(PARAM_TEXT, 'detected namespace', VALUE_OPTIONAL),
            'moodleValidationWarnings' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'msg' => new external_value(PARAM_TEXT, 'warning message')
                        )
                    ), 'XML validation warning messages', VALUE_OPTIONAL)
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

        // Do some validation.
        $params = self::validate_parameters(self::extract_task_infos_from_draft_file_parameters(), array('itemid' => $itemid));
        $draftid = $params['itemid'];

        $usercontext = context_user::instance($USER->id);
        self::validate_context($usercontext);

        $unzipinfo = unzip_task_file_in_file_area($usercontext, 'user', 'draft', $draftid);
        if ($unzipinfo == null) {
            return ['error' => 'Error extracting zip file'];
        } else if (isset($unzipinfo['error'])) {
            return $unzipinfo;
        }
        $taskxmlfilename = $unzipinfo['xml'];
        $taskzipfilename = $unzipinfo['zip'] ?? null;
        $keepfilename = $taskzipfilename != null ? $taskzipfilename : $taskxmlfilename;

        $doc = create_domdocument_from_task_xml($usercontext, 'user', 'draft', $draftid, $taskxmlfilename, $taskzipfilename);
        $namespace = detect_proforma_namespace($doc);
        $returnval = array();

        if ($namespace == null) {
            $returnval['error'] = get_string('invalidproformanamespace', 'qtype_moopt',
                    implode(", ", PROFORMA_TASK_XML_NAMESPACES));
        } else {

            $validationerrors = validate_proforma_file_against_schema($doc, $namespace);
            if (!empty($validationerrors)) {
                $returnval['moodleValidationProformaNamespace'] = $namespace;
                $msgarr = array();
                foreach ($validationerrors as $msg) {
                    array_push($msgarr, array('msg' => $msg));
                }
                $returnval['moodleValidationWarnings'] = $msgarr;
            }

            foreach ($doc->getElementsByTagNameNS($namespace, 'description') as $des) {
                if ($des->parentNode->localName == 'task') {
                    $returnval['description'] = $des->nodeValue;
                    break;
                }
            }

            foreach ($doc->getElementsByTagNameNS($namespace, 'title') as $des) {
                if ($des->parentNode->localName == 'task') {
                    $returnval['title'] = $des->nodeValue;
                    // prepend title as heading to description:
                    $heading = html_writer::tag('h3', $des->nodeValue);
                    $returnval['description'] = $heading . $returnval['description'];
                    break;
                }
            }

            foreach ($doc->getElementsByTagNameNS($namespace, 'internal-description') as $des) {
                if ($des->parentNode->localName == 'task') {
                    $returnval['internaldescription'] = $des->nodeValue;
                    break;
                }
            }

            foreach ($doc->getElementsByTagNameNS($namespace, 'proglang') as $des) {
                if ($des->parentNode->localName == 'task') {
                    $returnval['proglang'] = $des->nodeValue;
                    break;
                }
            }

            // Currently only supports tns:task-type; neither tns:external-task-type nor tns:included-task-file-type
            // TODO: Implement the other two.
            foreach ($doc->getElementsByTagNameNS($namespace, 'task') as $task) {
                $returnval['taskuuid'] = $task->getAttribute('uuid');
                break;
            }

            // Pre-calculate the max score in the grading hints to use as the default/max mark
            $gradinghints = $doc->getElementsByTagNameNS($namespace, 'grading-hints')[0];
            $tests = $doc->getElementsByTagNameNS($namespace, 'tests')[0];
            $gradinghintshelper = new grading_hints_helper($gradinghints, $tests, $namespace);
            $maxscoregradinghints = $gradinghintshelper->calculate_max_score();
            $returnval['maxscoregradinghints'] = $maxscoregradinghints;

            // Process lms-input-fields
            list($includeenablefileinput, $enablefileinput, $lmsinputfieldsettings) = readLmsInputFieldSettingsFromTaskXml($doc);

            $allinputfieldfilerefs = array();
            if (is_array($lmsinputfieldsettings)) {
                $allinputfieldfilerefs = array_keys($lmsinputfieldsettings);
            }
            // If not all $allinputfieldfilerefs elements are found among the files,
            // we will print a warning message.

            $returnval["enablefileinput"] = $includeenablefileinput ? $enablefileinput : true;
            $returnval["freetextfilesettings"] = array();
            foreach ($doc->getElementsByTagNameNS($namespace, 'file') as $file) {
                // enable free text fields only if there's one or more files that are visible to and editable by
                // students
                $enablefreetext = false;

                $enablecustomsettings = true;
                $usefixedfilename = true;
                $defaultfilename = '';
                $proglang = ''; // TODO: should be the task's proglang initially
                $initialdisplayrows = DEFAULT_INITIAL_DISPLAY_ROWS;
                $fileid = '';
                foreach ($file->childNodes as $child) {
                    if($file->attributes->getNamedItem('visible')->nodeValue == 'yes' &&
                        $file->attributes->getNamedItem('usage-by-lms') != null &&
                        $file->attributes->getNamedItem('usage-by-lms')->nodeValue == 'edit') {

                        if($child->localName == 'embedded-txt-file') {
                            $filecontent = $child->nodeValue;
                            $defaultfilename = $child->attributes->getNamedItem('filename')->nodeValue;
                            $fileid = $file->attributes->getNamedItem('id')->nodeValue;
                            $enablefreetext = true;
                            break;
                        } else if ($child->localName == 'attached-txt-file') {
                            $pathinfo = pathinfo('/' . $child->nodeValue);
                            $encoding = $child->attributes->getNamedItem('encoding')->nodeValue;
                            $filecontent = get_text_content_from_file($usercontext, $draftid, $keepfilename,
                                $pathinfo['dirname'] . '/', $pathinfo['basename'], true, $encoding);
                            if($filecontent === null){
                                $returnval['error'] = "Encoding of file ".$pathinfo['basename']." couldn't be detected.";
                            }
                            $defaultfilename = basename($child->nodeValue);
                            $fileid = $file->attributes->getNamedItem('id')->nodeValue;
                            $enablefreetext = true;
                            break;
                        }
                    }
                }

                if($enablefreetext) {
                    if(array_key_exists($fileid, $lmsinputfieldsettings)) {
                        $usefixedfilename = $lmsinputfieldsettings[$fileid]['fixedfilename'];
                        $proglang = $lmsinputfieldsettings[$fileid]['proglang'];
                        $initialdisplayrows = $lmsinputfieldsettings[$fileid]['initialdisplayrows'];
                    }
                    $freetextfilesettings = array("enablecustomsettings" => $enablecustomsettings,
                        "usefixedfilename" => $usefixedfilename,
                        "defaultfilename" => $defaultfilename,
                        "proglang" => $proglang,
                        "filecontent" => $filecontent,
                        "initialdisplayrows" => $initialdisplayrows);

                    array_push($returnval["freetextfilesettings"], $freetextfilesettings);

                    // remove the fileid
                    $index = array_search($fileid, $allinputfieldfilerefs);
                    if ($index !== false) {
                        array_splice($allinputfieldfilerefs, $index, 1);
                    }
                }
            }

            // Print warning, if there are textfields referencing unsuited files:
            if (!empty($allinputfieldfilerefs)) {
                if (!array_key_exists('moodleValidationWarnings', $returnval) || !is_array($returnval['moodleValidationWarnings'])) {
                    $returnval['moodleValidationWarnings'] = array();
                }
                $returnval['moodleValidationWarnings'][] =
                    array('msg' =>
                        htmlspecialchars('There are <textfield> elements below <lms-input-fields> ' .
                            'referencing either unknown or invisible or non-editable files: "' .
                            implode('", "', $allinputfieldfilerefs) . '"'));
            }

            // Fill in the question's general feedback
            // Look for files that have their 'visible' attribute set to 'delayed' and the
            // 'usage-by-lms' attribute set to 'display'. Display these files in the
            // question's general feedback.
            $filesdisplayedingeneralfeedback = '';
            $displayedfilesxml = $doc->getElementsByTagNameNS($namespace, 'file');
            $generalfeedbackfiles = array();
            foreach ($displayedfilesxml as $file) {
                foreach ($file->childNodes as $child) {
                    if($file->attributes->getNamedItem('visible')->nodeValue == 'delayed' &&
                        $file->attributes->getNamedItem('usage-by-lms') != null &&
                        $file->attributes->getNamedItem('usage-by-lms')->nodeValue == 'display') {

                        $filename = '';
                        $filecontent = '';

                        if($child->localName == 'embedded-txt-file') {
                            $filename = $child->attributes->getNamedItem('filename')->nodeValue;
                            $filecontent = $child->nodeValue;
                        } else if ($child->localName == 'attached-txt-file') {
                            $pathinfo = pathinfo('/' . $child->nodeValue);
                            $filename = basename($child->nodeValue);
                            $encoding = $child->attributes->getNamedItem('encoding')->nodeValue;
                            $filecontent = get_text_content_from_file($usercontext, $draftid, $keepfilename,
                                $pathinfo['dirname'] . '/', $pathinfo['basename'], true, $encoding);
                        }

                        if(!empty($filecontent))
                            $generalfeedbackfiles[] = array('filename' => $filename, 'content' => $filecontent);
                    }
                }
            }

            if (0 < count($generalfeedbackfiles)) {
                $filesdisplayedingeneralfeedback = html_writer::start_div('delayeddisplayedfile', ['style' => 'overflow: auto;']);
                for ($i = 0; $i < count($generalfeedbackfiles); $i++) {
                    if (1 < count($generalfeedbackfiles)) {
                        // include file names if there's multiple files
                        $filesdisplayedingeneralfeedback .= "<hr/><h5>{$generalfeedbackfiles[$i]['filename']}</h5><br/>";
                    }
                    $filesdisplayedingeneralfeedback .= $generalfeedbackfiles[$i]['content'];
                }
                $filesdisplayedingeneralfeedback .= html_writer::end_div('delayeddisplayedfile');
            }

            $returnval['filesdisplayedingeneralfeedback'] = $filesdisplayedingeneralfeedback;
        }

        // Do a little bit of cleanup and remove everything from the file area we extracted.
        remove_all_files_from_file_area($usercontext, 'user', 'draft', $draftid, $keepfilename);

        return $returnval;
    }

    public static function service_retrieve_grading_results_parameters() {
        return new external_function_parameters(
                ['qubaid' => new external_value(PARAM_INT, 'id of the question usage')]
        );
    }

    public static function service_retrieve_grading_results_returns() {
        return new external_single_structure(
            array(
                'estimatedSecondsRemainingForEachQuestion' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'questionId' => new external_value(PARAM_INT, "the question id of the question to which the estimated seconds remaining belong"),
                            'estimatedSecondsRemaining' => new external_value(PARAM_INT, "the estimated seconds remaining for this gradeprocess")
                        )
                    ), "the list of all estimated seconds remaining of an unfinished gradingprocess"
                ),
                'finished' => new external_value(PARAM_BOOL, "whether any grade process finished")
            )
        );
    }

    public static function service_retrieve_grading_results($qubaid) {
        global $USER, $SESSION, $DB;

        // Do some param validation.
        $params = self::validate_parameters(self::service_retrieve_grading_results_parameters(), array('qubaid' => $qubaid));
        $qubaid = $params['qubaid'];

        // Check if calling user is teacher.
        $qubarecord = $DB->get_record('question_usages', ['id' => $qubaid]);
        $contextrecord = $DB->get_record('context', ['id' => $qubarecord->contextid]);
        $context = context_module::instance($contextrecord->instanceid, IGNORE_MISSING);
        if($context) {
            self::validate_context($context);
            // do not make us wait wait through the polling interval if we're a teacher
            $isteacher = has_capability('mod/quiz:grade', $context);
        } else {
            // no context module for this quba, we're probably in a question preview
            $isteacher = true;
        }

        $lastaccess = $SESSION->last_retrieve_grading_results_by_service ?? microtime(true);
        $SESSION->last_retrieve_grading_results_by_service = microtime(true);
        if (microtime(true) - $lastaccess < get_config("qtype_moopt", "service_client_polling_interval") *
                0.9 && !$isteacher) {
            // Only allow a request every n seconds from the same user.
            return false;
        }

        // Check if the question usage given by the qubaid belongs to the requesting user.
        if (!$isteacher) {
            $record = $DB->get_record('quiz_attempts', ['uniqueid' => $qubaid], 'userid');
            if (!$record) {
                return false;
            }
            $userid = $record->userid;
            if ($userid != $USER->id) {
                return false;
            }
        }
        return retrieve_grading_results($qubaid);
    }

    public static function check_if_filearea_is_empty_parameters() {
        return new external_function_parameters(
            array(
                'itemid' => new external_value(PARAM_INT, 'id of the draft area')
            )
        );
    }

    public static function check_if_filearea_is_empty_returns() {
        return new external_value(PARAM_BOOL, 'Whether a filearea is empty or not');
    }

    public static function check_if_filearea_is_empty($itemid)
    {
        global $USER;

        // Do some validation.
        $params = self::validate_parameters(self::check_if_filearea_is_empty_parameters(), array('itemid' => $itemid));
        $draftid = $params['itemid'];

        $usercontext = context_user::instance($USER->id);
        self::validate_context($usercontext);

        $area = file_get_draft_area_info($draftid, "/");
        return ($area['filecount'] == 0 && $area['foldercount'] == 0);
    }

    public static function get_grader_data_parameters() {
        return new external_function_parameters([]);
    }

    public static function get_grader_data_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'name' => new external_value(PARAM_TEXT, 'grader name', VALUE_REQUIRED),
                    'version' => new external_value(PARAM_TEXT, 'grader version', VALUE_REQUIRED),
                    'display_name' => new external_value(PARAM_TEXT, 'grader display name', VALUE_REQUIRED),
                    'proglangs' => new external_multiple_structure(
                        new external_value(PARAM_TEXT, 'the programming language that is supported', VALUE_OPTIONAL)
                        , 'programming languages that are supported by the grader', VALUE_OPTIONAL),
                    'result_spec' => new external_single_structure(
                        array(
                            'format' => new external_value(PARAM_TEXT, 'The Result Specifications Format', VALUE_OPTIONAL),
                            'structure' => new external_value(PARAM_TEXT, 'The Result Specifications Structure', VALUE_OPTIONAL),
                            'teacher_feedback_level' => new external_value(PARAM_TEXT, 'The feedback level for teachers', VALUE_OPTIONAL),
                            'student_feedback_level' => new external_value(PARAM_TEXT, 'The feedback level for students', VALUE_OPTIONAL)
                        )
                        , 'Default Values for the Result Specifications for a grader', VALUE_OPTIONAL),
                    'html_representation' => new external_value(PARAM_TEXT, 'html representation of the grader', VALUE_REQUIRED)
                )
            ), "Available Graders", VALUE_REQUIRED);
    }

    public static function get_grader_data() : array {
        // Do some param validation.
        self::validate_parameters(self::get_grader_data_parameters(), array());

        return array_values(get_available_graders_form_data());
    }

}
