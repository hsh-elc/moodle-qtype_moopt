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
 * The editing form for programmingtask question type is defined here.
 *
 * @package     qtype_programmingtask
 * @copyright   2019 ZLB-ELC Hochschule Hannover <elc@hs-hannover.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once('locallib.php');

/**
 * programmingtask question editing form defition.
 *
 * You should override functions as necessary from the parent class located at
 * /question/type/edit_question_form.php.
 */
class qtype_programmingtask_edit_form extends question_edit_form {

    private $graderselect;
    private $graderoptions;
    private $gradererrorlabel;
    private $availabeGraders;

    protected function definition() {
        global $COURSE, $PAGE;

        $mform = $this->_form;

        $mform->addElement('header', 'taskfile', get_string('taskfile', 'qtype_programmingtask'));

        $mform->addElement('filemanager', 'proformataskfileupload', get_string('proformataskfileupload', 'qtype_programmingtask'),
                null, array('subdirs' => 0, 'maxbytes' => $COURSE->maxbytes, 'maxfiles' => 1));
        $mform->addHelpButton('proformataskfileupload', 'proformataskfileupload', 'qtype_programmingtask');
        $mform->addRule('proformataskfileupload', get_string('proformataskfilerequired', 'qtype_programmingtask'), 'required');

        $mform->addElement('button', 'loadproformataskfilebutton', get_string('loadproformataskfile', 'qtype_programmingtask'),
                array('id' => 'loadproformataskfilebutton'));

        $label = $mform->addElement('static', 'ajaxerrorlabel', '', '');
        $this->set_class_attribute_of_label($label, 'errorlabel');

        $label = $mform->addElement('static', 'ajaxwarninglabel', '', '');
        $this->set_class_attribute_of_label($label, 'warninglabel');

        parent::definition();

        $PAGE->requires->js_call_amd('qtype_programmingtask/creation_via_drag_and_drop', 'init');
    }

    protected function definition_inner($mform) {
        global $DB;

        $mform->addElement('editor', 'internaldescription', get_string('internaldescription', 'qtype_programmingtask'),
                array('rows' => 10), array('maxfiles' => 0,
            'noclean' => true, 'context' => $this->context, 'subdirs' => true));
        $mform->setType('internaldescription', PARAM_RAW); // No XSS prevention here, users must be trusted.

        list($graders, $this->availabeGraders) = retrieve_graders_and_update_local_list();
        $this->graderoptions = array();
        foreach ($graders as $id => $name) {
            $this->graderoptions[$id] = $name;
        }
        $this->graderselect = $mform->addElement('select', 'graderid', get_string('grader', 'qtype_programmingtask'),
                $this->graderoptions);

        $this->gradererrorlabel = $mform->addElement('static', 'gradernotavailableerrorlabel', '', '');
        $this->set_class_attribute_of_label($this->gradererrorlabel, 'errorlabel');

        $mform->addElement('text', 'taskuuid', get_string('taskuuid', 'qtype_programmingtask'), array("size" => '36'));
        $mform->setType('taskuuid', PARAM_TEXT);
        $mform->addRule('taskuuid', get_string('taskuuidrequired', 'qtype_programmingtask'), 'required');

        $mform->addElement('advcheckbox', 'showstudscorecalcscheme',
                get_string('showstudscorecalcscheme', 'qtype_programmingtask'), ' ');

        $mform->addElement('header', 'submissionsettings', get_string('submissionsettings', 'qtype_programmingtask'));

        $mform->addElement('advcheckbox', 'enablefilesubmissions', get_string('enablefilesubmissions', 'qtype_programmingtask'),
                ' ');
        $mform->setDefault('enablefilesubmissions', true);
        $mform->addElement('advcheckbox', 'enablefreetextsubmissions',
                get_string('enablefreetextsubmissions', 'qtype_programmingtask'), ' ');

        $mform->addElement('text', 'ftsnuminitialfields', get_string('ftsnuminitialfields', 'qtype_programmingtask'));
        $mform->setType('ftsnuminitialfields', PARAM_INT);
        $mform->setDefault('ftsnuminitialfields', 1);
        $mform->hideIf('ftsnuminitialfields', 'enablefreetextsubmissions');

        $mform->addElement('text', 'ftsmaxnumfields', get_string('ftsmaxnumfields', 'qtype_programmingtask'));
        $mform->setType('ftsmaxnumfields', PARAM_INT);
        $mform->setDefault('ftsmaxnumfields', get_config("qtype_programmingtask", "max_number_free_text_inputs"));
        $mform->hideIf('ftsmaxnumfields', 'enablefreetextsubmissions');

        $defaultnamesettingsarray = array();
        $defaultnamesettingsarray[] = $mform->createElement('radio', "ftsautogeneratefilenames", '',
                get_string('freetextinputautogeneratedname', 'qtype_programmingtask'), 1);
        $defaultnamesettingsarray[] = $mform->createElement('radio', "ftsautogeneratefilenames", '',
                get_string('freetextinputstudentname', 'qtype_programmingtask'), 0);
        $mform->addGroup($defaultnamesettingsarray, "ftsautogeneratefilenamesgroup",
                get_string('freetextinputnamesettingstandard', 'qtype_programmingtask'), array(' '), false);
        $mform->hideIf("ftsautogeneratefilenamesgroup", 'enablefreetextsubmissions');

        $mform->addElement('select', 'ftsstandardlang', get_string('ftsstandardlang', 'qtype_programmingtask'),
                PROFORMA_ACE_PROGLANGS);
        $mform->hideIf("ftsstandardlang", 'enablefreetextsubmissions');

        $mform->addElement('advcheckbox', "enablecustomsettingsforfreetextinputfields",
                get_string('enablecustomsettingsforfreetextinputfields', 'qtype_programmingtask'), ' ');
        $mform->hideIf("enablecustomsettingsforfreetextinputfields", 'enablefreetextsubmissions');

        $proglangs = ['default' => get_string('defaultlang', 'qtype_programmingtask')] + PROFORMA_ACE_PROGLANGS;

        for ($i = 0; $i < get_config("qtype_programmingtask", "max_number_free_text_inputs"); $i++) {
            $mform->addElement('advcheckbox', "enablecustomsettingsforfreetextinputfield$i",
                    get_string('enablecustomsettingsforfreetextinputfield', 'qtype_programmingtask') . ($i + 1), ' ');
            $mform->hideIf("enablecustomsettingsforfreetextinputfield$i", 'enablefreetextsubmissions');
            $mform->hideIf("enablecustomsettingsforfreetextinputfield$i", "enablecustomsettingsforfreetextinputfields");
            $hidearr = [];
            for ($j = 0; $j <= $i; $j++) {
                $hidearr[] = $j;
            }
            $mform->hideIf("enablecustomsettingsforfreetextinputfield$i", "ftsmaxnumfields", 'in', $hidearr);

            $namesettingsarray = array();
            $namesettingsarray[] = $mform->createElement('radio', "namesettingsforfreetextinput$i", '',
                    get_string('freetextinputteachername', 'qtype_programmingtask'), 0);
            $namesettingsarray[] = $mform->createElement('radio', "namesettingsforfreetextinput$i", '',
                    get_string('freetextinputstudentname', 'qtype_programmingtask'), 1);
            $mform->addGroup($namesettingsarray, "namesettingsforfreetextinputgroup$i", '', array(' '), false);
            $mform->hideIf("namesettingsforfreetextinputgroup$i", "enablecustomsettingsforfreetextinputfield$i");
            $mform->hideIf("namesettingsforfreetextinputgroup$i", 'enablefreetextsubmissions');
            $mform->hideIf("namesettingsforfreetextinputgroup$i", "enablecustomsettingsforfreetextinputfields");

            $mform->addElement('text', "freetextinputfieldname$i", '');
            $mform->setType("freetextinputfieldname$i", PARAM_FILE);
            $mform->setDefault("freetextinputfieldname$i", "File" . ($i + 1) . ".txt");
            $mform->hideIf("freetextinputfieldname$i", "namesettingsforfreetextinput$i", 'neq', 0);
            $mform->hideIf("freetextinputfieldname$i", "enablecustomsettingsforfreetextinputfield$i");
            $mform->hideIf("freetextinputfieldname$i", 'enablefreetextsubmissions');
            $mform->hideIf("freetextinputfieldname$i", "enablecustomsettingsforfreetextinputfields");

            $mform->addElement('select', "ftsoverwrittenlang$i", '', $proglangs);
            $mform->hideIf("ftsoverwrittenlang$i", "enablecustomsettingsforfreetextinputfield$i");
            $mform->hideIf("ftsoverwrittenlang$i", 'enablefreetextsubmissions');
            $mform->hideIf("ftsoverwrittenlang$i", "enablecustomsettingsforfreetextinputfields");
        }
    }

    protected function data_preprocessing($question) {
        global $DB;

        $question = parent::data_preprocessing($question);

        if (isset($question->id)) {
            $draftitemid = file_get_submitted_draft_itemid('proformataskfileupload');
            file_prepare_draft_area($draftitemid, $this->context->id, 'question', PROFORMA_TASKZIP_FILEAREA,
                    $question->id, array('subdirs' => 0));
            $question->proformataskfileupload = $draftitemid;
        }

        if (isset($question->internaldescription)) {
            $question->internaldescription = array('text' => $question->internaldescription);
        }

        if (isset($question->graderid)) {
            if (!in_array($question->graderid, $this->availabeGraders)) {
                $gradername = $DB->get_field('qtype_programmingtask_gradrs', 'gradername',
                        array("graderid" => $question->graderid));
                $this->gradererrorlabel->setText(get_string('previousgradernotavailable', 'qtype_programmingtask'));
            }
            $this->graderselect->setSelected($question->graderid);
        }

        if (isset($question->id)) {
            $numcustomfts = $DB->count_records('qtype_programmingtask_fts', ['questionid' => $question->id]);
            if ($numcustomfts != 0) {
                $question->enablecustomsettingsforfreetextinputfields = 1;
                $customftsfields = $DB->get_records('qtype_programmingtask_fts', ['questionid' => $question->id]);
                foreach ($customftsfields as $unusedkey => $value) {
                    $indx = $value->inputindex;
                    $question->{"enablecustomsettingsforfreetextinputfield$indx"} = 1;
                    $question->{"namesettingsforfreetextinput$indx"} = !$value->presetfilename;
                    if ($value->presetfilename) {
                        $question->{"freetextinputfieldname$indx"} = $value->filename;
                    }
                    $question->{"ftsoverwrittenlang$indx"} = $value->ftslang;
                }
            }
        }

        return $question;
    }

    /**
     * Returns the question type name.
     *
     * @return string The question type name.
     */
    public function qtype() {
        return 'programmingtask';
    }

    private function set_class_attribute_of_label(MoodleQuickForm_static $label, $classes) {
        $attribs = $label->getAttributes();
        if (!isset($attribs['class'])) {
            $attribs['class'] = $classes;
        } else {
            $attribs['class'] = $attribs['class'] . " " . $classes;
        }

        $label->setAttributes($attribs);
    }

    public function validation($fromform, $files) {
        $errors = parent::validation($fromform, $files);

        if (strlen($fromform['taskuuid']) != 36) {
            $errors['taskuuid'] = get_string('taskuuidhaswronglength', 'qtype_programmingtask');
        }

        if (!is_null(($err = check_if_task_file_is_valid($fromform['proformataskfileupload'])))) {
            $errors['proformataskfileupload'] = $err;
        }

        if ($fromform['ftsnuminitialfields'] > $fromform['ftsmaxnumfields']) {
            $errors['ftsnuminitialfields'] = get_string('initialnumberfreetextfieldsgreaterthanmax', 'qtype_programmingtask');
        }

        return $errors;
    }

}
