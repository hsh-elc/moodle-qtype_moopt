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

/**
 * Class that represents a programmingtask question.
 */
class qtype_programmingtask_question extends question_graded_automatically_with_countback {

    public $internaldescription;

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
        return array();
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

        $context = context_module::instance($question->contextid);

        $argscopy = $args;
        unset($argscopy[0]);
        $relativepath = implode('/', $argscopy);
        if (in_array($filearea, array(proforma_TASKZIP_FILEAREA, proforma_ATTACHED_TASK_FILES_FILEAREA, proforma_EMBEDDED_TASK_FILES_FILEAREA))) {
            //If it is one of those files we need to check permissions because students could just guess download urls and not all files should be downloadable by students
            //From the DBs point of view this combination of fields doesn't guarantee uniqueness; however, conceptually it does
            $records = $DB->get_records_sql('SELECT visibletostudents FROM {qtype_programmingtask_files} WHERE questionid = ? and ' . $DB->sql_concat('filepath', 'filename') . ' = ? and ' . $DB->sql_compare_text('filearea') . ' = ?', array($questionid, '/' . $relativepath, $filearea));
            if (count($records) != 1) {
                return false;
            }
            //$records[0] doesn't work because $records is an associative array with the keys being the ids of the record
            $first_elem = reset($records);
            $onlyteacher = $first_elem->visibletostudents == 0 ? true : false;
            if ($onlyteacher && !has_capability('mod/quiz:grade', $context)) {
                return false;
            }

            return true;
        }

        //Not our thing - delegate to parent
        return parent::check_file_access($qa, $options, $component, $filearea, $args, $forcedownload);
    }

    public function compute_final_grade($responses, $totaltries): \numeric {
        //TODO, auto implemented
    }

    public function get_validation_error(array $response): string {
        //TODO, auto implemented
    }

    public function grade_response(array $response): array {
        //TODO, auto implemented
        return array(1, question_state::$gradedright);
    }

    public function is_complete_response(array $response): bool {
        return true;
        //TODO, auto implemented
    }

    public function is_same_response(array $prevresponse, array $newresponse): bool {
        //TODO, auto implemented
        return false;
    }

    public function summarise_response(array $response): string {
        //TODO, auto implemented
        return "No response yet";
    }

}
