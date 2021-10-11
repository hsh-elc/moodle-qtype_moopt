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

/**
 * Question definition class for a Moodle Programming task (MooPT).
 *
 * @package     qtype_moopt
 * @copyright   2019 ZLB-ELC Hochschule Hannover <elc@hs-hannover.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

// For a complete list of base question classes please examine the file
// /question/type/questionbase.php.
//
// Make sure to implement all the abstract methods of the base class.

use qtype_moopt\utility\communicator\communicator_factory;
use qtype_moopt\utility\proforma_xml\proforma_submission_xml_creator;

/**
 * Class that represents a MooPT question.
 */
class qtype_moopt_question extends question_graded_automatically {

    public $internaldescription;
    public $graderid;
    public $taskuuid;
    public $showstudscorecalcscheme;
    public $enablefilesubmissions;
    public $enablefreetextsubmissions;
    public $ftsnuminitialfields;
    public $ftsmaxnumfields;
    public $ftsautogeneratefilenames;
    public $ftsstandardlang;
    public $resultspecformat;
    public $resultspecstructure;
    public $studentfeedbacklevel;
    public $teacherfeedbacklevel;

    /**
     * What data may be included in the form submission when a student submits
     * this question in its current state?
     *
     * This information is used in calls to optional_param. The parameter name
     * has {@link question_attempt::get_field_prefix()} automatically prepended.
     *
     * @return array|string variable name => PARAM_... constant, or, as a special case
     *      that should only be used in unavoidable, the constant question_attempt::USE_RAW_DATA
     *      meaning take all the raw submitted data belonging to this question.
     */
    public function get_expected_data() {
        $expected = ['answer' => question_attempt::PARAM_FILES];
        for ($i = 0; $i < get_config("qtype_moopt", "max_number_free_text_inputs"); $i++) {
            $expected["answertext$i"] = PARAM_RAW;
            $expected["answerfilename$i"] = PARAM_FILE;
        }
        return $expected;
    }

    /**
     * Returns the data that would need to be submitted to get a correct answer.
     *
     * @return array|null Null if it is not possible to compute a correct response.
     */
    public function get_correct_response() {
        return null;
    }

    /**
     * Creat the appropriate behaviour for an attempt at this quetsion,
     * given the desired (archetypal) behaviour.
     *
     * This default implementation will suit most normal graded questions.
     *
     * If your question is of a patricular type, then it may need to do something
     * different. For example, if your question can only be graded manually, then
     * it should probably return a manualgraded behaviour, irrespective of
     * what is asked for.
     *
     * If your question wants to do somthing especially complicated is some situations,
     * then you may wish to return a particular behaviour related to the
     * one asked for. For example, you migth want to return a
     * qbehaviour_interactive_adapted_for_myqtype.
     *
     * @param question_attempt $qa the attempt we are creating a behaviour for.
     * @param string $preferredbehaviour the requested type of behaviour.
     * @return question_behaviour the new behaviour object.
     */
    public function make_behaviour(question_attempt $qa, $preferredbehaviour) {

        $prefixtocheck = 'immediate';
        if (substr($preferredbehaviour, 0, strlen($prefixtocheck)) === $prefixtocheck) {
            $preferredbehaviour = 'immediatemoopt';
        } else {
            // No need to check whether it starts with 'deferred' because this is also the default cause if
            // it wouldn't start with 'deferred'.
            $preferredbehaviour = 'deferredmoopt';
        }

        return parent::make_behaviour($qa, $preferredbehaviour);
    }

    /**
     * Checks whether the user is allowed to be served a particular file.
     *
     * @param question_attempt $qa The question attempt being displayed.
     * @param question_display_options $options The options that control display of the question.
     * @param string $component The name of the component we are serving files for.
     * @param string $filearea The name of the file area.
     * @param array $args the Remaining bits of the file path.
     * @param bool $forcedownload Whether the user must be forced to download the file.
     * @return bool True if the user can access this file.
     */
    public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) {
        global $DB;
        $question = $qa->get_question();
        $questionid = $question->id;
        $argscopy = $args;
        unset($argscopy[0]);
        $relativepath = implode('/', $argscopy);
        if (in_array($filearea, array(PROFORMA_TASKZIP_FILEAREA, PROFORMA_ATTACHED_TASK_FILES_FILEAREA,
                    PROFORMA_EMBEDDED_TASK_FILES_FILEAREA, PROFORMA_TASKXML_FILEAREA))) {
            // If it is one of those files we need to check permissions because students could just guess download urls
            // and not all files should be downloadable by students
            // From the DBs point of view this combination of fields doesn't guarantee uniqueness; however, conceptually it does.
            $records = $DB->get_records_sql('SELECT visibletostudents FROM {qtype_moopt_files} WHERE questionid = ? and '
                    . $DB->sql_concat('filepath', 'filename') . ' = ? and ' . $DB->sql_compare_text('filearea') . ' = ?',
                    array($questionid, '/' . $relativepath, $filearea));
            if (count($records) != 1) {
                return false;
            }
            // Here $records[0] doesn't work because $records is an associative array with the keys being the ids of the record.
            $firstelem = reset($records);
            $onlyteacher = $firstelem->visibletostudents == 'no' ? true : false;

            $contextrecord = $DB->get_record('context', ['id' => $question->contextid]);
            $context = context_course::instance($contextrecord->instanceid);

            if ($onlyteacher && !has_capability('mod/quiz:grade', $context)) {
                return false;
            }

            return true;
        } else if ((substr($filearea, 0, strlen(PROFORMA_RESPONSE_FILE_AREA)) === PROFORMA_RESPONSE_FILE_AREA) ||
                (substr($filearea, 0, strlen(PROFORMA_RESPONSE_FILE_AREA_EMBEDDED)) === PROFORMA_RESPONSE_FILE_AREA_EMBEDDED) ||
                (substr($filearea, 0, strlen(PROFORMA_RESPONSE_FILE_AREA_RESPONSEFILE)) ===
                PROFORMA_RESPONSE_FILE_AREA_RESPONSEFILE)) {
            return true;
        } else if ($component == 'question' && $filearea == 'response_answer') {
            return true;
        }

        // Not our thing - delegate to parent.
        return parent::check_file_access($qa, $options, $component, $filearea, $args, $forcedownload);
    }

    /**
     * In situations where is_gradable_response() returns false, this method
     * should generate a description of what the problem is.
     * @return string the message.
     */
    public function get_validation_error(array $response): string {
        // Currently the only case where this might happen is that the students didn't submit any files.
        return get_string('nofilessubmitted', 'qtype_moopt');
    }

    /**
     * This method isn't used for MooPT questions, see grade_response_asynch instead.
     * Explanation: grade_response is an overriden method from its parent class and is supposed to grade the given response
     * synchronously. As we use asynchronous grading we need different parameters and have different return values.
     * Using this method and just changing the params and return values would clearly violate the substitution principle.
     */
    public function grade_response(array $response) {
        throw new coding_exception("This method isn't supported for MooPT. See grade_response_asynch instead.");
    }








    // move this func further down
    public function extract_submission_zip(array $submissionzip) {
        global $USER;
        $usercontext = context_user::instance($USER->id);

        $fs = get_file_storage();

        //$fs->

        // Check if there is only the file we want.
        $area = file_get_draft_area_info('answer', "/");
        if ($area['filecount'] == 0) {
            return false;
        } else if ($area['filecount'] > 1 || $area['foldercount'] != 0) {
            throw new invalid_parameter_exception(
                'Only one file is allowed to be in this draft area: A ProFormA-Task as either ZIP or XML file.');
        }
//
//        // Get name of the file.
//        $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftareaid);
//        // Get_area_files returns an associative array where the keys are some kind of hash value.
//        $keys = array_keys($files);
//        // Index 1 because index 0 is the current directory it seems.
//        $filename = $files[$keys[1]]->get_filename();
//
//        $file = $fs->get_file($usercontext->id, 'user', 'draft', $draftareaid, "/", $filename);
//
//        // Check file type (it's really only checking the file extension but that is good enough here).
//        $fileinfo = pathinfo($filename);
//        $filetype = '';
//        if (array_key_exists('extension', $fileinfo)) {
//            $filetype = strtolower($fileinfo['extension']);
//        }
//        if ($filetype != 'zip') {
//            throw new invalid_parameter_exception('Supplied file must be a zip file.');
//        }
//
//        // Unzip file - basically copied from draftfiles_ajax.php.
//        $zipper = get_file_packer('application/zip');
//
//        // Find unused name for directory to extract the archive.
//        $temppath = $fs->get_unused_dirname($usercontext->id, 'user', 'draft', $draftareaid, "/" . pathinfo($filename,
//                PATHINFO_FILENAME) . '/');
//        $donotremovedirs = array();
//        $doremovedirs = array($temppath);
//        // Extract archive and move all files from $temppath to $filepath.
//        if ($file->extract_to_storage($zipper, $usercontext->id, 'user', 'draft', $draftareaid, $temppath, $USER->id)) {
//            $extractedfiles = $fs->get_directory_files($usercontext->id, 'user', 'draft', $draftareaid, $temppath, true);
//            $xtemppath = preg_quote($temppath, '|');
//            foreach ($extractedfiles as $exfile) {
//                $realpath = preg_replace('|^' . $xtemppath . '|', '/', $exfile->get_filepath());
//                if (!$exfile->is_directory()) {
//                    // Set the source to the extracted file to indicate that it came from archive.
//                    $exfile->set_source(serialize((object) array('source' => '/')));
//                }
//                if (!$fs->file_exists($usercontext->id, 'user', 'draft', $draftareaid, $realpath, $exfile->get_filename())) {
//                    // File or directory did not exist, just move it.
//                    $exfile->rename($realpath, $exfile->get_filename());
//                } else if (!$exfile->is_directory()) {
//                    // File already existed, overwrite it.
//                    repository::overwrite_existing_draftfile($draftareaid, $realpath, $exfile->get_filename(), $exfile->get_filepath(),
//                        $exfile->get_filename());
//                } else {
//                    // Directory already existed, remove temporary dir but make sure we don't remove the existing dir.
//                    $doremovedirs[] = $exfile->get_filepath();
//                    $donotremovedirs[] = $realpath;
//                }
//            }
//        } else {
//            return null;
//        }
//        // Remove remaining temporary directories.
//        foreach (array_diff($doremovedirs, $donotremovedirs) as $filepath) {
//            $file = $fs->get_file($usercontext->id, 'user', 'draft', $draftareaid, $filepath, '.');
//            if ($file) {
//                $file->delete();
//            }
//        }
//
//        return $filename;










//        $files = array();
//        $zipper = get_file_packer('application/zip');
//        $zipfile = $zipper->archive_to_storage($files, $this->contextid, 'question', PROFORMA_SUBMISSION_ZIP_FILEAREA .
//            "_{$qa->get_slot()}", $qubaid, '/', 'submission.zip');
//        if (!$zipfile) {
//            throw new invalid_state_exception('Couldn\'t create submission.zip file.');
//        }


//        if (!isset($question->proformataskfileupload)) {
//            return;
//        }
//        $draftareaid = $question->proformataskfileupload;
//
//        $usercontext = context_user::instance($USER->id);
//
//        $filename = unzip_task_file_in_draft_area($draftareaid, $usercontext);
//        if (!$filename) {
//            // Seems like no task file was submitted.
//            return false;
//        }
//
//        // Copy all extracted files to the corresponding file area.
//        file_save_draft_area_files($draftareaid, $question->context->id, 'question', PROFORMA_ATTACHED_TASK_FILES_FILEAREA,
//            $question->id, array('subdirs' => true));
//
//        // $fs->delete_area_files($question->context->id, 'question', PROFORMA_TASKZIP_FILEAREA, $question->id);
//        //
    }

    /**
     * Sends the response to grappa for grading.
     * @param array $qa
     * @return \question_state Either question_state::$finished if it was succesfully transmitted or
     *          question_state::$needsgrading if there was an error and a teacher needs to either grade
     *          the submission manually or trigger a regrade
     */
    public function grade_response_asynch(question_attempt $qa, array $responsefiles, array $freetextanswers): question_state {
        global $DB;
        $communicator = communicator_factory::get_instance();
        $fs = get_file_storage();

        //$this->extract_submission_zip($responsefiles);

        // Get response files.
        $qubaid = $qa->get_usage_id();
        $files = array();   // Array for all files that end up in the ZIP file.

        foreach ($responsefiles as $file) {
            $files["submission/{$file->get_filename()}"] = $file;
        }

        foreach ($freetextanswers as $filename => $filecontent) {
            $mangledname = mangle_pathname($filename);
            $files["submission/$mangledname"] = [$filecontent]; // Syntax to use a string as
            // file contents.
        }

        try {
            $includetaskfile = !$communicator->is_task_cached($this->taskuuid);
            $includetaskfile = true; // TODO: remove this and test the caching mechanism
        } catch (invalid_response_exception $ex) {
            // Not good but not severe either - just assume the task isn't cached and include it.
            $includetaskfile = true;
            debugging($ex->module . '/' . $ex->errorcode . '( ' . $ex->debuginfo . ')');
        }

        // Get filename of task file if necessary but don't load it yet.
        $taskfilename = '';
        if ($includetaskfile) {
            $taskfilename = $DB->get_record('qtype_moopt_files', array('questionid' => $this->id,
                        'filearea' => PROFORMA_TASKZIP_FILEAREA), 'filename')->filename;
        }

        // Load task.xml file because we need the grading_hints if feedback-mode is merged-test-feedback.
        $taskxmlfile = $fs->get_file($this->contextid, 'question', PROFORMA_TASKXML_FILEAREA, $this->id, '/', 'task.xml');
        $taskdoc = new DOMDocument();
        $taskdoc->loadXML($taskxmlfile->get_content());
        $taskxmlnamespace = detect_proforma_namespace($taskdoc);
        $gradinghints = $taskdoc->getElementsByTagNameNS($taskxmlnamespace, 'grading-hints')[0];
        $tests = $taskdoc->getElementsByTagNameNS($taskxmlnamespace, 'tests')[0];

        /* When a question doesn't have set the grader specific options, set them in database and in this question */
        if ($this->resultspecformat === null) {
            $value = PROFORMA_RESULT_SPEC_FORMAT_ZIP; // this default could be changed by a grader- or question-specific value in the near future
            update_options_table_field("resultspecformat", $value, $this->id);
            $this->resultspecformat = $value;
        }
        if ($this->resultspecstructure === null) {
            $value = PROFORMA_SEPARATE_FEEDBACK_TYPE; // this default could be changed by a grader- or question-specific value in the near future
            update_options_table_field("resultspecstructure", $value, $this->id);
            $this->resultspecstructure = $value;
        }
        if ($this->studentfeedbacklevel === null) {
            $value = PROFORMA_FEEDBACK_LEVEL_INFO; // this default could be changed by a grader- or question-specific value in the near future
            update_options_table_field("studentfeedbacklevel", $value, $this->id);
            $this->studentfeedbacklevel = $value;
        }
        if ($this->teacherfeedbacklevel === null) {
            $value = PROFORMA_FEEDBACK_LEVEL_DEBUG; // this default could be changed by a grader- or question-specific value in the near future
            update_options_table_field("teacherfeedbacklevel", $value, $this->id);
            $this->teacherfeedbacklevel = $value;
        }

        // Create the submission.xml file.
        $submissionxmlcreator = new proforma_submission_xml_creator();
        $submissionxml = $submissionxmlcreator->create_submission_xml($includetaskfile, $includetaskfile ?
                $taskfilename : $this->taskuuid, $files, $this->resultspecformat, $this->resultspecstructure, $this->studentfeedbacklevel, $this->teacherfeedbacklevel,
                $gradinghints, $tests, $taskxmlnamespace, $qa->get_max_mark());

        // Load task file and add it to the files that go into the zip file.
        if ($includetaskfile) {
            $taskfile = $fs->get_file($this->contextid, 'question', PROFORMA_TASKZIP_FILEAREA, $this->id, '/', $taskfilename);
            $files["task/$taskfilename"] = $taskfile;
        }

        // Add the submission.xml file.
        $files['submission.xml'] = array($submissionxml);       // Syntax to use a string as file contents.
        // Create submission.zip file.
        $zipper = get_file_packer('application/zip');
        $zipfile = $zipper->archive_to_storage($files, $this->contextid, 'question', PROFORMA_SUBMISSION_ZIP_FILEAREA .
                "_{$qa->get_slot()}", $qubaid, '/', 'submission.zip');
        if (!$zipfile) {
            throw new invalid_state_exception('Couldn\'t create submission.zip file.');
        }

        $returnstate = question_state::$finished;
        try {
            $gradeprocessid = $communicator->enqueue_submission($this->graderid, 'true', $zipfile);
            $DB->insert_record('qtype_moopt_gradeprocesses', ['qubaid' => $qa->get_usage_id(),
                'questionattemptdbid' => $qa->get_database_id(), 'gradeprocessid' => $gradeprocessid,
                'graderid' => $this->graderid]);
        } catch (invalid_response_exception $ex) {
            debugging($ex->module . '/' . $ex->errorcode . '( ' . $ex->debuginfo . ')');
            $returnstate = question_state::$needsgrading;
        } finally {
            $fs = get_file_storage();
            $success = $fs->delete_area_files($this->contextid, 'question', PROFORMA_SUBMISSION_ZIP_FILEAREA .
                    "_{$qa->get_slot()}", $qubaid);
            if (!$success) {
                throw new invalid_state_exception("Couldn't delete submission.zip after sending it to grappa." .
                        " QuestionID: {$this->id}, QubaID: $qubaid, Slot: {$qa->get_slot()}");
            }
        }

        return $returnstate;
    }

    /**
     * Used by many of the behaviours, to work out whether the student's
     * response to the question is complete. That is, whether the question attempt
     * should move to the COMPLETE or INCOMPLETE state.
     *
     * @param array $response responses, as returned by
     *      {@link question_attempt_step::get_qt_data()}.
     * @return bool whether this response is a complete answer to this question.
     */
    public function is_complete_response(array $response): bool {
        if ($this->enablefilesubmissions && isset($response['answer'])) {
            $questionfilesaver = $response['answer'];
            if ($questionfilesaver != '') {
                return true;
            }
        }
        if ($this->enablefreetextsubmissions) {
            for ($i = 0; $i < $this->ftsmaxnumfields; $i++) {
                if (isset($response["answertext$i"]) && $response["answertext$i"] != '') {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Use by many of the behaviours to determine whether the student's
     * response has changed. This is normally used to determine that a new set
     * of responses can safely be discarded.
     *
     * @param array $prevresponse the responses previously recorded for this question,
     *      as returned by {@link question_attempt_step::get_qt_data()}
     * @param array $newresponse the new responses, in the same format.
     * @return bool whether the two sets of responses are the same - that is
     *      whether the new set of responses can safely be discarded.
     */
    public function is_same_response(array $prevresponse, array $newresponse): bool {
        if ($this->enablefilesubmissions) {
            // Can't really compare files here.
            return false;
        }
        if ($this->enablefreetextsubmissions) {
            for ($i = 0; $i < $this->ftsmaxnumfields; $i++) {
                if (!isset($prevresponse["answertext$i"]) || !isset($prevresponse["answerfilename$i"])) {
                    // If there wasn't an answer before it can't be the same.
                    return false;
                }
                if ($prevresponse["answertext$i"] != $newresponse["answertext$i"] ||
                        $prevresponse["answerfilename$i"] != $newresponse["answerfilename$i"]) {
                    // If filename or filecontent isn't the same it's not the same answer.
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Produce a plain text summary of a response.
     * @param array $response a response, as might be passed to {@link grade_response()}.
     * @return string a plain text summary of that response, that could be used in reports.
     */
    public function summarise_response(array $response): string {
        return get_string('nosummaryavailable', 'qtype_moopt');
    }

}
