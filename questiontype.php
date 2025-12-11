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
        return array("qtype_moopt_options", "internaldescription", "gradername", "graderversion",
            "taskuuid", 'showstudgradingscheme', 'showstudscorecalcscheme', 'enablefilesubmissions',
            'enablefreetextsubmissions', 'ftsnuminitialfields', 'ftsmaxnumfields', 'ftsautogeneratefilenames',
            'ftsstandardlang', 'resultspecformat', 'resultspecstructure', 'studentfeedbacklevel', 'teacherfeedbacklevel');
    }

    /**
     * Override the parent method and extend the structure so it matches the structure for backup and restore
     * @see backup/moodle2/restore_qtype_moopt_plugin.class.php::convert_backup_to_questiondata
     */
    #[\Override]
    public function get_question_options($question) {
        global $DB;
        parent::get_question_options($question);

        $fileRecords = $DB->get_records('qtype_moopt_files', array('questionid' => $question->id));
        if (count($fileRecords) >= 1) {
            $question->options->mooptfiles = array();
            foreach ($fileRecords as $fileRecord) {
                unset($fileRecord->questionid);
                array_push($question->options->mooptfiles, $fileRecord);
            }
        }

        $freetextRecords = $DB->get_records('qtype_moopt_freetexts', array('questionid' => $question->id));
        if (count($freetextRecords) >= 1) {
            $question->options->mooptfreetexts = array();
            foreach ($freetextRecords as $freetextRecord) {
                unset($freetextRecord->questionid);
                array_push($question->options->mooptfreetexts, $freetextRecord);
            }
        }
        return $question;
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
        // Convert the combined representation of the graderID to graderName and graderVersion
        if (property_exists($question, 'graderselect') && !is_null($question->graderselect)) {
            $separatedGraderID = get_name_and_version_from_graderid_html_representation($question->graderselect);
            $question->gradername = $separatedGraderID->gradername;
            $question->graderversion = $separatedGraderID->graderversion;
        }

        if (is_array($question->internaldescription)) {
            if (!array_key_exists('text', $question->internaldescription) || !isset($question->internaldescription['text'])) {
                $question->internaldescription = '';
            } else {
                $question->internaldescription = trim($question->internaldescription['text']);
            }
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

        if (property_exists($question, 'enablefreetextsubmissions') && $question->{'enablefreetextsubmissions'} &&
            property_exists($question, 'enablecustomsettingsforfreetextinputfields') && $question->{'enablecustomsettingsforfreetextinputfields'}) {
            $maxfts = $question->ftsmaxnumfields;
            // make sure this user-entered max num does not exceed the
            // original plugin setting from when it was installed and first-time configured
            // (necessary precautions in terms of moodle form api are already in place though)
            if($maxfts > (int)get_config("qtype_moopt","max_number_free_text_inputs"))
                throw new \coding_exception("Assertion error: user-entered max free text input fields cannot be greater than the plugin's max number setting.");

            for ($i = 0; $i < $maxfts; $i++) {
                if (property_exists($question, "enablecustomsettingsforfreetextinputfield$i") && $question->{"enablecustomsettingsforfreetextinputfield$i"}) {
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
        $this->save_hints($question);
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

    /**
     * Provide export functionality for xml format
     * @param question object the question object
     * @param format object the format object so that helper methods can be used
     * @param extra mixed any additional format specific data that may be passed by the format (see format code for info)
     * @return string the data to append to the output buffer or false if error
     */
    function export_to_xml($question, $format, $extra=null) {
        global $DB, $COURSE;

        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->setIndent(1);
        $xml->setIndentString('    ');

        /* Add an empty answer-Element because Moodle-Import function expects it */
        $xml->startElement('answer');
        $xml->writeAttribute('fraction', 0);
        $xml->writeElement('text', '');
        $xml->endElement();

        $fs = get_file_storage();
        $taskFileRecord = $DB->get_record('qtype_moopt_files', array('questionid' => $question->id, 'fileid' => 'task'));
        if (!$taskFileRecord) {
            // taskxml file without zip
            $taskFileRecord = $DB->get_record('qtype_moopt_files', array('questionid' => $question->id, 'fileid' => 'taskxml'));
        }
        $context = context_course::instance($COURSE->id);
        $taskfile = $fs->get_file($context->id,COMPONENT_NAME, $taskFileRecord->filearea, $question->id, $taskFileRecord->filepath, $taskFileRecord->filename);

        $taskfilename = $taskfile->get_filename();
        $taskfilepath = $taskfile->get_filepath();
        $taskfileencoding = 'base64';
        $taskfilecontentbase64 = base64_encode($taskfile->get_content());

        $xml->startElement('taskfile');
        $xml->writeAttribute('filearea', $taskFileRecord->filearea);
        $xml->writeAttribute('name', $taskfilename);
        $xml->writeAttribute('path', $taskfilepath);
        $xml->writeAttribute('encoding', $taskfileencoding);
        $xml->writeRaw($taskfilecontentbase64);
        $xml->endElement();

        /* Add freetext specific options to export */
        $customOptionsForAllFreetexts = $DB->get_records('qtype_moopt_freetexts', array('questionid' => $question->id));

        if (count($customOptionsForAllFreetexts) > 0) {
            $xml->startElement('customsettingsforfreetextinputfields');
            foreach ($customOptionsForAllFreetexts as $customOptionsForOneFreetext) {
                $inputindex = $customOptionsForOneFreetext->inputindex;
                $xml->startElement('field');
                $xml->writeAttribute('index', $inputindex);
                $presetfilename = $customOptionsForOneFreetext->presetfilename ? "0" : "1"; //Invert because in the form later "0" means true
                $xml->writeElement('namesettingsforfreetextinput', $presetfilename);
                $xml->writeElement('freetextinputfieldname', $customOptionsForOneFreetext->filename);
                $xml->writeElement('ftsoverwrittenlang', $customOptionsForOneFreetext->ftslang);
                $xml->writeElement('ftsinitialdisplayrows', $customOptionsForOneFreetext->initialdisplayrows);
                $xml->startElement('freetextinputfieldtemplate');
                $xml->writeCdata($customOptionsForOneFreetext->filecontent);
                $xml->endElement();
                $xml->endElement();
            }
            $xml->endElement();
        }

        $xmloutput = $xml->outputMemory();
        $xmloutput .= parent::export_to_xml($question, $format, $extra);
        return $xmloutput;
    }

    /**
     * Provide import functionality for xml format
     * @param data mixed the segment of data containing the question
     * @param question object question object processed (so far) by standard import code
     * @param format object the format object so that helper methods can be used (in particular error() )
     * @param extra mixed any additional format specific data that may be passed by the format (see format code for info)
     * @return object question object suitable for save_options() call or false if cannot handle
     */
    function import_from_xml($data, $question, $format, $extra=null) {
        global $COURSE;

        $ret = parent::import_from_xml($data, $question, $format, $extra);
        $root = $data['#'];

        /* Import Taskfile */
        if ($ret && array_key_exists('taskfile', $root)) {
            $taskfileattributes = $root['taskfile'][0]['@'];
            $taskfilearea = $taskfileattributes['filearea'];
            $taskfilename = $taskfileattributes['name'];
            $taskfilepath = $taskfileattributes['path'];
            $taskfileencoding = $taskfileattributes['encoding'];
            $taskfileencoded = $root['taskfile'][0]['#'];

            /* Check encoding of */
            $taskfilecontent = false;
            if (strtolower($taskfileencoding) == 'base64') {
                $taskfilecontent = base64_decode($taskfileencoded, true);
            }

            if (!$taskfilecontent) {
                throw new InvalidArgumentException("The taskfile could not be encoded from moodle xml.");
            }

            $context = context_course::instance($COURSE->id);

            $taskfileinfo = [
                'context' => $context,
                'component' => COMPONENT_NAME,
                'filearea' => $taskfilearea,
                'filepath' => $taskfilepath,
                'filename' => $taskfilename,
                'content' => $taskfilecontent
            ]; // Only save the taskfile information here and save the file later because question id is unknown until later
            $ret->taskfile = $taskfileinfo;
        }

        /* Import custom settings for FreetextInputFields */
        if ($ret && array_key_exists('customsettingsforfreetextinputfields', $root)) {
            $ret->{'enablecustomsettingsforfreetextinputfields'} = true;
            $customsettingsforfreetextinputfields = $root['customsettingsforfreetextinputfields'][0]['#']['field'];
            foreach ($customsettingsforfreetextinputfields AS $field) {
                $inputIndex = $field['@']['index'];
                $ret->{'enablecustomsettingsforfreetextinputfield' . $inputIndex} = true;
                $options = $field['#'];
                foreach ($options AS $option => $optionValue) {
                    $ret->{$option . $inputIndex} = $optionValue[0]['#'];
                }
            }
        }
        return $ret;
    }

    public function move_files($questionid, $oldcontextid, $newcontextid)
    {
        $fs = get_file_storage();

        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $fs->move_area_files_to_new_context($oldcontextid, $newcontextid, COMPONENT_NAME, PROFORMA_ATTACHED_TASK_FILES_FILEAREA, $questionid);
        $fs->move_area_files_to_new_context($oldcontextid, $newcontextid, COMPONENT_NAME, PROFORMA_EMBEDDED_TASK_FILES_FILEAREA, $questionid);
        $fs->move_area_files_to_new_context($oldcontextid, $newcontextid, COMPONENT_NAME, PROFORMA_RESPONSE_FILE_AREA, $questionid);
        $fs->move_area_files_to_new_context($oldcontextid, $newcontextid, COMPONENT_NAME, PROFORMA_RESPONSE_FILE_AREA_EMBEDDED, $questionid);
        $fs->move_area_files_to_new_context($oldcontextid, $newcontextid, COMPONENT_NAME, PROFORMA_RESPONSE_FILE_AREA_RESPONSEFILE, $questionid);
        $fs->move_area_files_to_new_context($oldcontextid, $newcontextid, COMPONENT_NAME, PROFORMA_SUBMISSION_ZIP_FILEAREA, $questionid);
        $fs->move_area_files_to_new_context($oldcontextid, $newcontextid, COMPONENT_NAME, PROFORMA_TASKXML_FILEAREA, $questionid);
        $fs->move_area_files_to_new_context($oldcontextid, $newcontextid, COMPONENT_NAME, PROFORMA_TASKZIP_FILEAREA, $questionid);
    }
}
