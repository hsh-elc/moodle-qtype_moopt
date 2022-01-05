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
 * Question type class for MooPT is defined here.
 *
 * @package     qtype_moopt
 * @copyright   2019 ZLB-ELC Hochschule Hannover <elc@hs-hannover.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');
require_once("$CFG->dirroot/question/type/moopt/locallib.php");

/**
 * Class that represents a Moodle Programming Task (MooPT) question type.
 *
 * The class loads, saves and deletes questions of the type moopt
 * to and from the database and provides methods to help with editing questions
 * of this type. It can also provide the implementation for import and export
 * in various formats.
 */
class qtype_moopt extends question_type {

    public function response_file_areas() {
        return array("answer");
    }

    /**
     * TODO: fix this description. The table that's being talked about is
     * actually the MooPT's edit form, with the table probably being the <table>
     * structure that the mutable fields are displayed in
     *
     * If your question type has a table that extends the question table, and
     * you want the base class to automatically save, backup and restore the extra fields,
     * override this method to return an array where the first element is the table name,
     * and the subsequent entries are the column names (apart from id and questionid).
     *
     * @return mixed array as above, or null to tell the base class to do nothing.
     */
    public function extra_question_fields() {
        return array("qtype_moopt_options", "internaldescription", "graderid", "taskuuid", 'showstudscorecalcscheme',
            'enablefilesubmissions', 'enablefreetextsubmissions', 'ftsnuminitialfields', 'ftsmaxnumfields',
            'ftsautogeneratefilenames', 'ftsstandardlang', 'resultspecformat', 'resultspecstructure',
            'studentfeedbacklevel', 'teacherfeedbacklevel');
    }

    /**
     * Saves question-type specific options
     *
     * This is called by {@link save_question()} to save the question-type specific data
     * @return object $result->error or $result->notice
     * @param object $question  This holds the information from the editing form,
     *      it is not a standard question object.
     */
    public function save_question_options($question) {
        if (!isset($question->internaldescription['text'])) {
            $question->internaldescription = '';
        } else {
            $question->internaldescription = trim($question->internaldescription['text']);
        }

        parent::save_question_options($question);

        global $DB;

        // Save the files contained in the task file
        // First remove all old files and db entries.
        $DB->delete_records('qtype_moopt_files', array('questionid' => $question->id));
        $fs = get_file_storage();
        $fs->delete_area_files($question->context->id, COMPONENT_NAME, PROFORMA_TASKZIP_FILEAREA, $question->id);
        $fs->delete_area_files($question->context->id, COMPONENT_NAME, PROFORMA_ATTACHED_TASK_FILES_FILEAREA, $question->id);
        $fs->delete_area_files($question->context->id, COMPONENT_NAME, PROFORMA_EMBEDDED_TASK_FILES_FILEAREA, $question->id);
        $fs->delete_area_files($question->context->id, COMPONENT_NAME, PROFORMA_TASKXML_FILEAREA, $question->id);
        save_task_and_according_files($question);

        // Store custom settings for free text input fields.
        $DB->delete_records('qtype_moopt_freetexts', array('questionid' => $question->id));
        if ($question->{'enablefreetextsubmissions'} && $question->{'enablecustomsettingsforfreetextinputfields'}) {
            $maxfts = $question->ftsmaxnumfields;
            // make sure this user-entered max num does not exceed the
            // original plugin setting from when it was installed and first-time configured
            // (necessary precautions in terms of moodle form api are already in place though)
            if($maxfts > (int)get_config("qtype_moopt","max_number_free_text_inputs"))
                throw new \coding_exception("Assertion error: user-entered max free text input fields cannot be greater than the plugin's max number setting.");

            for ($i = 0; $i < $maxfts; $i++) {
                if ($question->{"enablecustomsettingsforfreetextinputfield$i"}) {
                    $data = new stdClass();
                    $data->questionid = $question->id;
                    $data->inputindex = $i;
                    $data->ftslang = $question->{"ftsoverwrittenlang$i"};
                    $data->presetfilename = $question->{"namesettingsforfreetextinput$i"} == 0;
                    $data->filename = $data->presetfilename ? $question->{"freetextinputfieldname$i"} : null;
                    $data->filecontent = $question->{"freetextinputfieldtemplate$i"};
                    $data->initialdisplayrows = $question->{"ftsinitialdisplayrows$i"};
                    $DB->insert_record('qtype_moopt_freetexts', $data);
                }
            }
        }
    }

    public function delete_question($questionid, $contextid) {
        parent::delete_question($questionid, $contextid);

        global $DB;

        $DB->delete_records('qtype_moopt_files', array('questionid' => $questionid));
        $fs = get_file_storage();
        $fs->delete_area_files($contextid, COMPONENT_NAME, PROFORMA_TASKZIP_FILEAREA, $questionid);
        $fs->delete_area_files($contextid, COMPONENT_NAME, PROFORMA_ATTACHED_TASK_FILES_FILEAREA, $questionid);
        $fs->delete_area_files($contextid, COMPONENT_NAME, PROFORMA_EMBEDDED_TASK_FILES_FILEAREA, $questionid);
        $fs->delete_area_files($contextid, COMPONENT_NAME, PROFORMA_TASKXML_FILEAREA, $questionid);

        parent::delete_question($questionid, $contextid);
    }

}
