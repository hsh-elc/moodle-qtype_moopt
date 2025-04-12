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

require_once($CFG->dirroot . '/question/behaviour/immediatemoopt/behaviour.php');
require_once($CFG->dirroot . '/question/behaviour/deferredmoopt/behaviour.php');
require_once($CFG->dirroot . '/question/behaviour/adaptivemoopt/behaviour.php');
require_once($CFG->dirroot . '/question/behaviour/adaptivemooptnopenalty/behaviour.php');
require_once($CFG->dirroot . '/question/behaviour/manualgraded/behaviour.php');
require_once($CFG->dirroot . '/question/behaviour/deferredmooptcbm/behaviour.php');
require_once($CFG->dirroot . '/question/behaviour/immediatemooptcbm/behaviour.php');
require_once($CFG->dirroot . '/question/behaviour/interactivemoopt/behaviour.php');

use qtype_moopt\utility\communicator\communicator_factory;
use qtype_moopt\utility\proforma_xml\proforma_submission_xml_creator;

/**
 * Class that represents a MooPT question.
 */
class qtype_moopt_question extends question_graded_automatically {

    public $internaldescription;
    public $gradername; // A grader is uniquely identified by the grader name and the grader version
    public $graderversion;
    public $taskuuid;
    public $showstudgradingscheme;
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

    public $submission_proforma_restrictions_message;

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
        $mappings['immediate'] = 'immediatemoopt';
        $mappings['deferred'] = 'deferredmoopt';
        $mappings['adaptive'] = 'adaptivemoopt';
        $mappings['manualgraded'] = 'manualgraded';
        $mappings['interactive'] = 'interactivemoopt';
        $mappings['deferredcbm'] = 'deferredmooptcbm';
        $mappings['immediatecbm'] = 'immediatemooptcbm';
        $mappings['adaptivenopenalty'] = 'adaptivemooptnopenalty';

        // Sort mappings in descending order to ensure deferredcbm will be checked before deferred, etc.
        krsort($mappings);

        $found = false;
        foreach ($mappings as $old => $new){
            if(substr($preferredbehaviour, 0, strlen($old)) == $old){
                $preferredbehaviour = $new;
                $found = true;
                break;
            }
        }

        if (!$found){
            $preferredbehaviour = $mappings['immediate'];
        }

        $class = 'qbehaviour_' . $preferredbehaviour;
        return new $class($qa, $preferredbehaviour);
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
        } else if ($filearea == PROFORMA_RESPONSE_FILE_AREA ||
                 $filearea == PROFORMA_RESPONSE_FILE_AREA_EMBEDDED ||
                 $filearea == PROFORMA_RESPONSE_FILE_AREA_RESPONSEFILE)  {
            return true;
        } else if ($component == 'question' && $filearea == 'response_answer') {
            // the component is hard-wired to 'question' for filearea 'response_answer'
            // do not change it to 'qtype_moopt'
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

    /**
     * Sends the response to grappa for grading.
     * @param array $qa
     * @return \question_state Either question_state::$finished if it was succesfully transmitted or
     *          question_state::$needsgrading if there was an error and a teacher needs to either grade
     *          the submission manually or trigger a regrade
     */
    public function grade_response_asynch(question_attempt $qa, array $responsefiles, array $freetextanswers): question_state {
        global $DB, $USER, $COURSE;
        $communicator = communicator_factory::get_instance();
        $fs = get_file_storage();

        // Get response files.
        $qubaid = $qa->get_usage_id();
        $files = array();   // Array for all files that end up in the ZIP file.
        $submissionfiles = array(); // Array for all submission files, is needed for to check the proforma_submission_restrictions

        foreach ($responsefiles as $file) {
            $files["submission/{$file->get_filename()}"] = $file;
            $submissionfiles[$file->get_filepath() . $file->get_filename()] = $file;
        }

        foreach ($freetextanswers as $filename => $filecontent) {
            $mangledname = mangle_pathname($filename);
            $files["submission/$mangledname"] = [$filecontent]; // Syntax to use a string as file contents.
            $submissionfiles[$mangledname] = [$filecontent];
        }

        global $PAGE;
        try {
            $includetaskfile = !$communicator->is_task_cached($this->taskuuid);
            $includetaskfile = true; // TODO: remove this and test the caching mechanism
        } catch (\qtype_moopt\exceptions\service_communicator_exception $ex) {
            debugging($ex->module . '/' . $ex->errorcode . '( ' . $ex->debuginfo . ')');
            if (!has_capability('mod/quiz:grade', $PAGE->context))
                redirect(new moodle_url('/question/type/moopt/errorpage.php', array('courseid' =>
                    $COURSE->id, 'error' => 'serviceunavailable'))); // show a generic error to students
            // let anyone with quiz:grade capabilities see the full details of the error, displayed in
            // moodle's own detailed way
            throw $ex;
        }
        catch (invalid_response_exception $ex) {
            // Not good but not severe either - just assume the task isn't cached and include it.
            $includetaskfile = true;
            debugging($ex->module . '/' . $ex->errorcode . '( ' . $ex->debuginfo . ')');
        }

        // Get filename of task file if necessary but don't load it yet.
        $taskfilename = '';
        $sourcearea = '';
        $taskreftype = '';
        if ($includetaskfile) {
            // Try getting the task.zip, then task.xml filename from the DB, whatever is available at this point
            $sourcearea = PROFORMA_TASKZIP_FILEAREA;
            $taskreftype = 'zip';
            $rec = $DB->get_record('qtype_moopt_files', array('questionid' => $this->id,
                        'filearea' => $sourcearea), 'filename');
            if (!$rec) {
                $sourcearea = PROFORMA_TASKXML_FILEAREA;
                $taskreftype = 'xml';
                $rec = $DB->get_record('qtype_moopt_files', array('questionid' => $this->id,
                        'filearea' => $sourcearea), 'filename');
            }
            $taskfilename = $rec->filename;
        } else {
            $taskreftype = 'uuid';
        }

        // Load task.xml file because we need the grading_hints if feedback-mode is merged-test-feedback.
        $taskxmlfile = get_task_xml_file_from_filearea($this);
        $taskdoc = new DOMDocument();
        $taskdoc->loadXML($taskxmlfile->get_content());
        $taskxmlnamespace = detect_proforma_namespace($taskdoc);
        $gradinghints = $taskdoc->getElementsByTagNameNS($taskxmlnamespace, 'grading-hints')[0];
        $tests = $taskdoc->getElementsByTagNameNS($taskxmlnamespace, 'tests')[0];


        /* Check if the submitted files of the students violates the proforma submission restrictions */
        $msgarr = check_proforma_submission_restrictions($taskdoc, $submissionfiles, $qa);
        if (!empty($msgarr)) {
            //Submission Restrictions violated, the submission is invalid, don't grade it

            //the use of this public attribute could go wrong when several students do the same question at the same time
            //because it belongs to the question in general not to the question attempt, is needed for the summarise_response function
            $this->submission_proforma_restrictions_message = render_proforma_submission_restrictions($msgarr);

            write_proforma_submission_restrictions_msg_to_db($msgarr, $qa);
            return question_state::$gradedwrong;
        }

        // Create the submission.xml file.
        $submissionxmlcreator = new proforma_submission_xml_creator();

        $submissionxml = $submissionxmlcreator->create_submission_xml($taskreftype,
        ($includetaskfile ? $taskfilename : $this->taskuuid), $files, $this->resultspecformat,
        $this->resultspecstructure, $this->studentfeedbacklevel, $this->teacherfeedbacklevel,
        $gradinghints, $tests, $taskxmlnamespace, $qa->get_max_mark(), $USER->id, $COURSE->id);

        // Load task file and add it to the files that go into the zip file.
        if ($includetaskfile) {
            $taskfile = $fs->get_file($this->contextid, COMPONENT_NAME, $sourcearea, $this->id, '/', $taskfilename);
            $files["task/$taskfilename"] = $taskfile;
        }

        // Add the submission.xml file.
        $files['submission.xml'] = array($submissionxml);       // Syntax to use a string as file contents.
        // Create submission.zip file.
        $zipper = get_file_packer('application/zip');
        $zipfile = $zipper->archive_to_storage($files, $this->contextid, COMPONENT_NAME, PROFORMA_SUBMISSION_ZIP_FILEAREA,
            $qa->get_database_id(), '/', 'submission.zip');
        if (!$zipfile) {
            throw new invalid_state_exception('Couldn\'t create submission.zip file.');
        }

        $returnstate = question_state::$finished;
        try {
            $gradeprocessid = $communicator->enqueue_submission($this->gradername, $this->graderversion, 'true', $zipfile);
            $DB->insert_record('qtype_moopt_gradeprocesses', ['qubaid' => $qa->get_usage_id(),
                'questionattemptdbid' => $qa->get_database_id(), 'gradeprocessid' => $gradeprocessid,
                'gradername' => $this->gradername, 'graderversion' => $this->graderversion]);
        } catch (\qtype_moopt\exceptions\service_communicator_exception $ex) {
            debugging($ex->module . '/' . $ex->errorcode . '( ' . $ex->debuginfo . ')');
            if (!has_capability('mod/quiz:grade', $PAGE->context))
                redirect(new moodle_url('/question/type/moopt/errorpage.php', array('courseid' =>
                    $COURSE->id, 'error' => 'serviceunavailable')));
            throw $ex;
        } catch (invalid_response_exception $ex) {
            debugging($ex->module . '/' . $ex->errorcode . '( ' . $ex->debuginfo . ')');
            $returnstate = question_state::$needsgrading;
        } finally {
            $fs = get_file_storage();
            $success = $fs->delete_area_files($this->contextid, COMPONENT_NAME,
                PROFORMA_SUBMISSION_ZIP_FILEAREA, $qa->get_database_id());
            if (!$success) {
                throw new invalid_state_exception("Could not delete submission.zip after sending it to the grader." .
                        " QuestionID: {$this->id}, QubaID: $qubaid, AttemptID: {$qa->get_database_id()}, SlotID: {$qa->get_slot()}");
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
        //This is added to ensure that "nusummaryavailable" does not override the restriction message
        if($this->submission_proforma_restrictions_message != null) {
            return $this->submission_proforma_restrictions_message;
        } else {
            return get_string('nosummaryavailable', 'qtype_moopt');
        }
    }

}
