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
 * The editing form for a MooPT question type is defined here.
 *
 * @package     qtype_moopt
 * @copyright   2019 ZLB-ELC Hochschule Hannover <elc@hs-hannover.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once('locallib.php');

use qtype_moopt\utility\communicator\communicator_factory;

/**
 * moopt question editing form definition.
 *
 * You should override functions as necessary from the parent class located at
 * /question/type/edit_question_form.php.
 */
class qtype_moopt_edit_form extends question_edit_form {

    private $graderselect;
    private $gradererrorlabel;
    private $availableGraders;

    protected function definition() {
        global $COURSE, $PAGE;

        if (!has_capability("qtype/moopt:author", $this->context)) {
            redirect(new moodle_url('/question/type/moopt/missing_capability_errorpage.php'));
        } else {
            $mform = $this->_form;

            $mform->addElement('header', 'taskfile', get_string('taskfile', 'qtype_moopt'));

            $mform->addElement('filemanager', 'proformataskfileupload', get_string('proformataskfileupload', 'qtype_moopt'),
                null, array('subdirs' => 0, 'maxbytes' => $COURSE->maxbytes, 'maxfiles' => 1));
            $mform->addHelpButton('proformataskfileupload', 'proformataskfileupload', 'qtype_moopt');
            $mform->addRule('proformataskfileupload', get_string('proformataskfilerequired', 'qtype_moopt'), 'required');

            $mform->addElement('button', 'loadproformataskfilebutton', get_string('loadproformataskfile', 'qtype_moopt'),
                array('id' => 'loadproformataskfilebutton'));

            $label = $mform->addElement('static', 'ajaxerrorlabel', '', '');
            $this->set_class_attribute_of_label($label, 'errorlabel');

            $label = $mform->addElement('static', 'ajaxwarninglabel', '', '');
            $this->set_class_attribute_of_label($label, 'warninglabel');

            parent::definition();

            $PAGE->requires->js_call_amd('qtype_moopt/creation_via_drag_and_drop', 'init');
        }
    }

    protected function definition_inner($mform) {
        $mform->addElement('editor', 'internaldescription', get_string('internaldescription', 'qtype_moopt'),
                array('rows' => 10), array('maxfiles' => 0,
            'noclean' => true, 'context' => $this->context, 'subdirs' => true));
        $mform->setType('internaldescription', PARAM_RAW); // No XSS prevention here, users must be trusted.

        $graders = communicator_factory::get_instance()->get_graders()['graders'];
        $this->availableGraders = array();
        foreach ($graders as $grader) {
            $k = key($grader);
            $this->availableGraders[$k] = $grader[$k];
        }
        $this->graderselect = $mform->addElement('select', 'graderid', get_string('grader', 'qtype_moopt'),
            $this->availableGraders);

        $this->gradererrorlabel = $mform->addElement('static', 'gradernotavailableerrorlabel', '', '');
        $this->set_class_attribute_of_label($this->gradererrorlabel, 'errorlabel');

        /* Add the settings for the result specs */
        $select = $mform->addElement('select', 'resultspecformat', get_string('resultspecformat', 'qtype_moopt'), array(
            PROFORMA_RESULT_SPEC_FORMAT_ZIP => PROFORMA_RESULT_SPEC_FORMAT_ZIP,
            PROFORMA_RESULT_SPEC_FORMAT_XML => PROFORMA_RESULT_SPEC_FORMAT_XML)
        );
        $select->setSelected(PROFORMA_RESULT_SPEC_FORMAT_ZIP); // this default could be changed by a grader- or question-specific value in the near future
        $mform->setType('resultspecformat', PARAM_TEXT);

        $select = $mform->addElement('select', 'resultspecstructure', get_string('resultspecstructure', 'qtype_moopt'), array(
            PROFORMA_MERGED_FEEDBACK_TYPE => PROFORMA_MERGED_FEEDBACK_TYPE,
            PROFORMA_SEPARATE_FEEDBACK_TYPE => PROFORMA_SEPARATE_FEEDBACK_TYPE)
        );
        $select->setSelected(PROFORMA_SEPARATE_FEEDBACK_TYPE); // this default could be changed by a grader- or question-specific value in the near future
        $mform->setType('resultspecstructure', PARAM_TEXT);

        /* Add the settings for the teacher and student feedback level */
        $feedbackleveloptions = array(
            PROFORMA_FEEDBACK_LEVEL_ERROR => PROFORMA_FEEDBACK_LEVEL_ERROR,
            PROFORMA_FEEDBACK_LEVEL_WARNING => PROFORMA_FEEDBACK_LEVEL_WARNING,
            PROFORMA_FEEDBACK_LEVEL_INFO => PROFORMA_FEEDBACK_LEVEL_INFO,
            PROFORMA_FEEDBACK_LEVEL_DEBUG => PROFORMA_FEEDBACK_LEVEL_DEBUG,
            PROFORMA_FEEDBACK_LEVEL_NOTSPECIFIED => get_string('notspecified', 'qtype_moopt')
        );
        $select = $mform->addElement('select', 'studentfeedbacklevel', get_string('studentfeedbacklevel', 'qtype_moopt'), $feedbackleveloptions);
        $select->setSelected(PROFORMA_FEEDBACK_LEVEL_INFO); // this default could be changed by a grader- or question-specific value in the near future
        $mform->setType('studentfeedbacklevel', PARAM_TEXT);
        $select = $mform->addElement('select', 'teacherfeedbacklevel', get_string('teacherfeedbacklevel', 'qtype_moopt'), $feedbackleveloptions);
        $mform->setType('teacherfeedbacklevel', PARAM_TEXT);
        $select->setSelected(PROFORMA_FEEDBACK_LEVEL_DEBUG); // this default could be changed by a grader- or question-specific value in the near future

        $mform->addElement('text', 'taskuuid', get_string('taskuuid', 'qtype_moopt'), array("size" => '36'));
        $mform->setType('taskuuid', PARAM_TEXT);
        $mform->addRule('taskuuid', get_string('taskuuidrequired', 'qtype_moopt'), 'required');

        $mform->addElement('advcheckbox', 'showstudscorecalcscheme',
                get_string('showstudscorecalcscheme', 'qtype_moopt'), ' ');

        $mform->addElement('header', 'submissionsettings', get_string('submissionsettings', 'qtype_moopt'));

        $mform->addElement('advcheckbox', 'enablefilesubmissions', get_string('enablefilesubmissions', 'qtype_moopt'),
                ' ');
        $mform->setDefault('enablefilesubmissions', true);
        $mform->addElement('advcheckbox', 'enablefreetextsubmissions',
                get_string('enablefreetextsubmissions', 'qtype_moopt'), ' ');

        $mform->addElement('text', 'ftsnuminitialfields', get_string('ftsnuminitialfields', 'qtype_moopt'));
        $mform->setType('ftsnuminitialfields', PARAM_INT);
        $mform->setDefault('ftsnuminitialfields', 1);
        $mform->hideIf('ftsnuminitialfields', 'enablefreetextsubmissions');

        $mform->addElement('text', 'ftsmaxnumfields', get_string('ftsmaxnumfields', 'qtype_moopt'));
        $mform->setType('ftsmaxnumfields', PARAM_INT);
        $mform->setDefault('ftsmaxnumfields', get_config("qtype_moopt", "max_number_free_text_inputs"));
        $mform->hideIf('ftsmaxnumfields', 'enablefreetextsubmissions');

        $defaultnamesettingsarray = array();
        $defaultnamesettingsarray[] = $mform->createElement('radio', "ftsautogeneratefilenames", '',
                get_string('freetextinputautogeneratedname', 'qtype_moopt'), 1);
        $defaultnamesettingsarray[] = $mform->createElement('radio', "ftsautogeneratefilenames", '',
                get_string('freetextinputstudentname', 'qtype_moopt'), 0);
        $mform->addGroup($defaultnamesettingsarray, "ftsautogeneratefilenamesgroup",
                get_string('freetextinputnamesettingstandard', 'qtype_moopt'), array(' '), false);
        $mform->hideIf("ftsautogeneratefilenamesgroup", 'enablefreetextsubmissions');

        $mform->addElement('select', 'ftsstandardlang', get_string('ftsstandardlang', 'qtype_moopt'),
                PROFORMA_ACE_PROGLANGS);
        $mform->hideIf("ftsstandardlang", 'enablefreetextsubmissions');

        $mform->addElement('advcheckbox', "enablecustomsettingsforfreetextinputfields",
                get_string('enablecustomsettingsforfreetextinputfields', 'qtype_moopt'), ' ');
        $mform->hideIf("enablecustomsettingsforfreetextinputfields", 'enablefreetextsubmissions');

        $proglangs = ['default' => get_string('defaultlang', 'qtype_moopt')] + PROFORMA_ACE_PROGLANGS;

        for ($i = 0; $i < get_config("qtype_moopt", "max_number_free_text_inputs"); $i++) {
            $mform->addElement('advcheckbox', "enablecustomsettingsforfreetextinputfield$i",
                get_string('enablecustomsettingsforfreetextinputfield', 'qtype_moopt') . ($i + 1), ' ');
            $mform->hideIf("enablecustomsettingsforfreetextinputfield$i", 'enablefreetextsubmissions');
            $mform->hideIf("enablecustomsettingsforfreetextinputfield$i", "enablecustomsettingsforfreetextinputfields");
            $hidearr = [];
            for ($j = 0; $j <= $i; $j++) {
                $hidearr[] = $j;
            }
            $mform->hideIf("enablecustomsettingsforfreetextinputfield$i", "ftsmaxnumfields", 'in', $hidearr);

            $namesettingsarray = array();
            $namesettingsarray[] = $mform->createElement('radio', "namesettingsforfreetextinput$i", '',
                get_string('freetextinputteachername', 'qtype_moopt'), 0);
            $namesettingsarray[] = $mform->createElement('radio', "namesettingsforfreetextinput$i", '',
                get_string('freetextinputstudentname', 'qtype_moopt'), 1);
            $mform->addGroup($namesettingsarray, "namesettingsforfreetextinputgroup$i", '', array(' '), false);
            $mform->hideIf("namesettingsforfreetextinputgroup$i", "enablecustomsettingsforfreetextinputfield$i");
            $mform->hideIf("namesettingsforfreetextinputgroup$i", 'enablefreetextsubmissions');
            $mform->hideIf("namesettingsforfreetextinputgroup$i", "enablecustomsettingsforfreetextinputfields");

            $mform->addElement('text', "freetextinputfieldname$i", '');
            $mform->setType("freetextinputfieldname$i", PARAM_PATH);
            $mform->setDefault("freetextinputfieldname$i", "File" . ($i + 1) . ".txt");
            $mform->hideIf("freetextinputfieldname$i", "namesettingsforfreetextinput$i", 'neq', 0);
            $mform->hideIf("freetextinputfieldname$i", "enablecustomsettingsforfreetextinputfield$i");
            $mform->hideIf("freetextinputfieldname$i", 'enablefreetextsubmissions');
            $mform->hideIf("freetextinputfieldname$i", "enablecustomsettingsforfreetextinputfields");

            $mform->addElement('select', "ftsoverwrittenlang$i", '', $proglangs);
            $mform->hideIf("ftsoverwrittenlang$i", "enablecustomsettingsforfreetextinputfield$i");
            $mform->hideIf("ftsoverwrittenlang$i", 'enablefreetextsubmissions');
            $mform->hideIf("ftsoverwrittenlang$i", "enablecustomsettingsforfreetextinputfields");

            // static form elements don't work with hideIf
            // use workaround in https://tracker.moodle.org/browse/MDL-66251
            $lblgroup = [];
            $lblgroup[] =& $mform->createElement('static', "freetextinputfieldtemplatetitle$i", '', 'Template:');
            $mform->addGroup($lblgroup, 'lblgroup', '', ' ', false);
            $mform->hideIf('lblgroup', "enablecustomsettingsforfreetextinputfield$i");
            $mform->hideIf('lblgroup', 'enablefreetextsubmissions');
            $mform->hideIf('lblgroup', "enablecustomsettingsforfreetextinputfields");
//            $mform->addElement('static', "freetextinputfieldtemplatetitle$i", '', 'Template:');
//            $mform->hideIf("freetextinputfieldtemplatetitle$i", "enablecustomsettingsforfreetextinputfield$i");
//            $mform->hideIf("freetextinputfieldtemplatetitle$i", 'enablefreetextsubmissions');
//            $mform->hideIf("freetextinputfieldtemplatetitle$i", "enablecustomsettingsforfreetextinputfields");

            $mform->addElement('textarea', "freetextinputfieldtemplate$i", '', 'wrap="virtual" rows="3" cols="50"');
//            $mform->addElement('editor', "freetextinputfieldtemplate$i", '',
//                array('rows' => 10), array('maxfiles' => 0,
//                    'noclean' => true, 'context' => $this->context, 'subdirs' => true));
//            $mform->setType("freetextinputfieldtemplate$i", PARAM_RAW);
            $mform->hideIf("freetextinputfieldtemplate$i", "enablecustomsettingsforfreetextinputfield$i");
            $mform->hideIf("freetextinputfieldtemplate$i", 'enablefreetextsubmissions');
            $mform->hideIf("freetextinputfieldtemplate$i", "enablecustomsettingsforfreetextinputfields");
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
            if (!array_key_exists($question->graderid, $this->availableGraders)) {
                $this->gradererrorlabel->setText(get_string('previousgradernotavailable', 'qtype_moopt', ['grader' => $question->graderid]));
            }
            $this->graderselect->setSelected($question->graderid);
        }

        if (isset($question->id)) {
            $numcustomfts = $DB->count_records('qtype_moopt_freetexts', ['questionid' => $question->id]);
            if ($numcustomfts != 0) {
                $question->enablecustomsettingsforfreetextinputfields = 1;
                $customftsfields = $DB->get_records('qtype_moopt_freetexts', ['questionid' => $question->id]);
                foreach ($customftsfields as $unusedkey => $value) {
                    $indx = $value->inputindex;
                    $question->{"enablecustomsettingsforfreetextinputfield$indx"} = 1;
                    $question->{"namesettingsforfreetextinput$indx"} = !$value->presetfilename;
                    if ($value->presetfilename) {
                        $question->{"freetextinputfieldname$indx"} = $value->filename;
                    }
                    $question->{"ftsoverwrittenlang$indx"} = $value->ftslang;
                    $question->{"freetextinputfieldtemplate$indx"} = $value->filecontent;
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
        return 'moopt';
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
            $errors['taskuuid'] = get_string('taskuuidhaswronglength', 'qtype_moopt');
        }

        if (!is_null(($err = check_if_task_file_is_valid($fromform['proformataskfileupload'])))) {
            $errors['proformataskfileupload'] = $err;
        }

        if ($fromform['ftsnuminitialfields'] > $fromform['ftsmaxnumfields']) {
            $errors['ftsnuminitialfields'] = get_string('initialnumberfreetextfieldsgreaterthanmax', 'qtype_moopt');
        }

        $pluginsettingsmaxnumfields = get_config("qtype_moopt","max_number_free_text_inputs");
        if ($fromform['ftsmaxnumfields'] > $pluginsettingsmaxnumfields) {
            $errors['ftsmaxnumfields'] = get_string('ftsmaxnumfieldslegalrange','qtype_moopt',
                ['beg' => $fromform['ftsnuminitialfields'], 'end' => $pluginsettingsmaxnumfields]);
        }

        return $errors;
    }

}
