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
 * Question definition class for programmingtask.
 *
 * @package     qtype_programmingtask
 * @copyright   2019 ZLB-ELC Hochschule Hannover <elc@hs-hannover.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

// For a complete list of base question classes please examine the file
// /question/type/questionbase.php.
//
// Make sure to implement all the abstract methods of the base class.

use qtype_programmingtask\utility\grappa_communicator;
use qtype_programmingtask\utility\proforma_xml\proforma_submission_xml_creator;

/**
 * Class that represents a programmingtask question.
 */
class qtype_programmingtask_question extends question_graded_automatically {

    public $internaldescription;
    public $graderid;
    public $taskuuid;
    public $showstudscorecalcscheme;

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
        return array('answerfiles' => question_attempt::PARAM_FILES);
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

        $prefix_to_check = 'immediate';
        if (substr($preferredbehaviour, 0, strlen($prefix_to_check)) === $prefix_to_check) {
            $preferredbehaviour = 'immediateprogrammingtask';
        } else {
            // No need to check whether it starts with 'deferred' because this is also the default cause if it wouldn't start with 'deferred'
            $preferredbehaviour = 'deferredprogrammingtask';
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
        if (in_array($filearea, array(proforma_TASKZIP_FILEAREA, proforma_ATTACHED_TASK_FILES_FILEAREA, proforma_EMBEDDED_TASK_FILES_FILEAREA, proforma_TASKXML_FILEAREA))) {
            //If it is one of those files we need to check permissions because students could just guess download urls and not all files should be downloadable by students
            //From the DBs point of view this combination of fields doesn't guarantee uniqueness; however, conceptually it does
            $records = $DB->get_records_sql('SELECT visibletostudents FROM {qtype_programmingtask_files} WHERE questionid = ? and ' . $DB->sql_concat('filepath', 'filename') . ' = ? and ' . $DB->sql_compare_text('filearea') . ' = ?', array($questionid, '/' . $relativepath, $filearea));
            if (count($records) != 1) {
                return false;
            }
            //$records[0] doesn't work because $records is an associative array with the keys being the ids of the record
            $first_elem = reset($records);
            $onlyteacher = $first_elem->visibletostudents == 0 ? true : false;

            $context_record = $DB->get_record('context', ['id' => $question->contextid]);
            $context = context_course::instance($context_record->instanceid);

            if ($onlyteacher && !has_capability('mod/quiz:grade', $context)) {
                return false;
            }

            return true;
        } else if ((substr($filearea, 0, strlen(proforma_RESPONSE_FILE_AREA)) === proforma_RESPONSE_FILE_AREA) || (substr($filearea, 0, strlen(proforma_RESPONSE_FILE_AREA_EMBEDDED)) === proforma_RESPONSE_FILE_AREA_EMBEDDED)) {
            return true;
        } else if ($component == 'question' && $filearea == 'response_answerfiles') {
            return true;
        }

        //Not our thing - delegate to parent
        return parent::check_file_access($qa, $options, $component, $filearea, $args, $forcedownload);
    }

    /**
     * In situations where is_gradable_response() returns false, this method
     * should generate a description of what the problem is.
     * @return string the message.
     */
    public function get_validation_error(array $response): string {
        //Currently the only case where this might happen is that the students didn't submit any files
        return get_string('nofilessubmitted', 'qtype_programmingtask');
    }

    /**
     * This method isn't used for programming task questions, see grade_response_asynch instead.
     * Explanation: grade_response is an overriden method from its parent class and is supposed to grade the given response
     * synchronously. As we use asynchronous grading we need different parameters and have different return values. Using this method
     * and just changing the params and return values would clearly violate the substitution principle.
     */
    public function grade_response(array $response) {
        throw new coding_exception("This method isn't supported for programming tasks. See grade_response_asynch instead.");
    }

    /**
     * Sends the response to grappa for grading.
     * 
     * @param array $qa
     * @return \question_state Either question_state::$finished if it was succesfully transmitted or
     *          question_state::$needsgrading if there was an error and a teacher needs to either grade
     *          the submission manually or trigger a regrade
     */
    public function grade_response_asynch(question_attempt $qa, array $responsefiles): question_state {
        global $DB;
        $grappa_communicator = grappa_communicator::getInstance();
        $fs = get_file_storage();

        //Get response files
        $qubaid = $qa->get_usage_id();
        $files = array();   //array for all files that end up in the ZIP file
        foreach ($responsefiles as $file) {
            $files["submission/{$file->get_filename()}"] = $file;
        }

        try {
            $includeTaskFile = !$grappa_communicator->isTaskCached($this->taskuuid);
        } catch (invalid_response_exception $ex) {
            //Not good but not severe either - just assume the task isn't cached and include it
            $includeTaskFile = true;
            error_log($ex->module . '/' . $ex->errorcode . '( ' . $ex->debuginfo . ')');
        }

        //Get filename of task file if necessary but don't load it yet
        $taskfilename = '';
        if ($includeTaskFile) {
            $taskfilename = $DB->get_record('qtype_programmingtask_files', array('questionid' => $this->id, 'filearea' => proforma_TASKZIP_FILEAREA), 'filename')->filename;
        }

        //Load task.xml file because we need the grading_hints if feedback-mode is merged-test-feedback
        $taskxmlfile = $fs->get_file($this->contextid, 'question', proforma_TASKXML_FILEAREA, $this->id, '/', 'task.xml');
        $taskdoc = new DOMDocument();
        $taskdoc->loadXML($taskxmlfile->get_content());
        $taskxmlnamespace = detect_proforma_namespace($taskdoc);
        $grading_hints = $taskdoc->getElementsByTagNameNS($taskxmlnamespace, 'grading-hints')[0];

        //Create the submission.xml file
        $submission_xml_creator = new proforma_submission_xml_creator();
        $submissionXML = $submission_xml_creator->createSubmissionXML($includeTaskFile, $includeTaskFile ? $taskfilename : $this->taskuuid, $files, 'zip', proforma_MERGED_FEEDBACK_TYPE, 'info', 'debug',$grading_hints, $taskxmlnamespace, $qa->get_max_mark());

        //Load task file and add it to the files that go into the zip file
        if ($includeTaskFile) {
            $taskfile = $fs->get_file($this->contextid, 'question', proforma_TASKZIP_FILEAREA, $this->id, '/', $taskfilename);
            $files["task/$taskfilename"] = $taskfile;
        }

        //Add the submission.xml file
        $files['submission.xml'] = array($submissionXML);

        //Create submission.zip file
        $zipper = get_file_packer('application/zip');
        $zip_file = $zipper->archive_to_storage($files, $this->contextid, 'question', proforma_SUBMISSION_ZIP_FILEAREA . "_{$qa->get_slot()}", $qubaid, '/', 'submission.zip');
        if (!$zip_file) {
            throw new invalid_state_exception('Couldn\'t create submission.zip file.');
        }

        $returnState = question_state::$finished;
        try {
            $gradeProcessId = $grappa_communicator->enqueueSubmission($this->graderid, 'true', $zip_file);
            $DB->insert_record('qtype_programmingtask_grprcs', ['qubaid' => $qa->get_usage_id(), 'questionattemptdbid' => $qa->get_database_id(), 'gradeprocessid' => $gradeProcessId, 'graderid' => $this->graderid]);
            if (!$DB->record_exists('qtype_programmingtask_qaslts', ['questionattemptdbid' => $qa->get_database_id()])) {
                //This will already exist when this is a regrade
                $DB->insert_record('qtype_programmingtask_qaslts', ['questionattemptdbid' => $qa->get_database_id(), 'slot' => $qa->get_slot()]);
            }
        } catch (invalid_response_exception $ex) {
            error_log($ex->module . '/' . $ex->errorcode . '( ' . $ex->debuginfo . ')');
            $returnState = question_state::$needsgrading;
        } finally {
            $fs = get_file_storage();
            $success = $fs->delete_area_files($this->contextid, 'question', proforma_SUBMISSION_ZIP_FILEAREA . "_{$qa->get_slot()}", $qubaid);
            if (!$success) {
                throw new invalid_state_exception("Couldn't delete submission.zip after sending it to grappa. QuestionID: {$this->id}, QubaID: $qubaid, Slot: {$qa->get_slot()}");
            }
        }

        return $returnState;
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
        if (!isset($response['answerfiles']))
            return false;

        $question_file_saver = $response['answerfiles'];
        return $question_file_saver != '';
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
        if (!isset($prevresponse['answerfiles']))
            return false;
        $prevSummary = $prevresponse['answerfiles'];
        $newSummary = (string) $newresponse['answerfiles'];
        return $prevSummary == $newSummary;
    }

    /**
     * Produce a plain text summary of a response.
     * @param array $response a response, as might be passed to {@link grade_response()}.
     * @return string a plain text summary of that response, that could be used in reports.
     */
    public function summarise_response(array $response): string {
        return get_string('nosummaryavailable', 'qtype_programmingtask');
    }

}
