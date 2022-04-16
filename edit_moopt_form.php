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
            redirect(new moodle_url('/question/type/moopt/missing_capability_errorpage.php', array('courseid' => $COURSE->id)));
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
        $mform->addHelpButton('internaldescription', 'internaldescription', 'qtype_moopt');

        $this->availableGraders = get_available_graders_form_data();
        $graderSelectOptions = array();
        foreach ($this->availableGraders as $grader) {
            $graderSelectOptions[$grader['html_representation']] = $grader['display_name'];
        }

        $this->graderselect = $mform->addElement('select', 'graderselect', get_string('grader', 'qtype_moopt'),
            $graderSelectOptions);
        $mform->addHelpButton('graderselect', 'grader', 'qtype_moopt');

        $this->gradererrorlabel = $mform->addElement('static', 'gradernotavailableerrorlabel', '', '');
        $this->set_class_attribute_of_label($this->gradererrorlabel, 'errorlabel');
        if (empty($this->availableGraders)) {
            $this->gradererrorlabel->setText(get_string('nograderavailable', 'qtype_moopt'));
            // This message might get overwritten in the function data_preprocessing
            // by a more specific message, if we are editing an existing question
            // whose given grader is currently unavailable.
        }

        /* Add the settings for the result specs */
        $select = $mform->addElement('select', 'resultspecformat', get_string('resultspecformat', 'qtype_moopt'), array(
            PROFORMA_RESULT_SPEC_FORMAT_ZIP => PROFORMA_RESULT_SPEC_FORMAT_ZIP,
            PROFORMA_RESULT_SPEC_FORMAT_XML => PROFORMA_RESULT_SPEC_FORMAT_XML)
        );
        $mform->addHelpButton('resultspecformat', 'resultspecformat', 'qtype_moopt');
        $select->setSelected(PROFORMA_RESULT_SPEC_FORMAT_ZIP); // this default could be changed by a grader- or question-specific value in the near future
        $mform->setType('resultspecformat', PARAM_TEXT);

        $select = $mform->addElement('select', 'resultspecstructure', get_string('resultspecstructure', 'qtype_moopt'), array(
            PROFORMA_MERGED_FEEDBACK_TYPE => PROFORMA_MERGED_FEEDBACK_TYPE,
            PROFORMA_SEPARATE_FEEDBACK_TYPE => PROFORMA_SEPARATE_FEEDBACK_TYPE)
        );
        $mform->addHelpButton('resultspecstructure', 'resultspecstructure', 'qtype_moopt');
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
        $mform->addHelpButton('studentfeedbacklevel', 'studentfeedbacklevel', 'qtype_moopt');
        $select->setSelected(PROFORMA_FEEDBACK_LEVEL_INFO); // this default could be changed by a grader- or question-specific value in the near future
        $mform->setType('studentfeedbacklevel', PARAM_TEXT);
        $select = $mform->addElement('select', 'teacherfeedbacklevel', get_string('teacherfeedbacklevel', 'qtype_moopt'), $feedbackleveloptions);
        $mform->addHelpButton('teacherfeedbacklevel', 'teacherfeedbacklevel', 'qtype_moopt');
        $mform->setType('teacherfeedbacklevel', PARAM_TEXT);
        $select->setSelected(PROFORMA_FEEDBACK_LEVEL_DEBUG); // this default could be changed by a grader- or question-specific value in the near future

        $mform->addElement('text', 'taskuuid', get_string('taskuuid', 'qtype_moopt'), array("size" => '36'));
        $mform->addHelpButton('taskuuid', 'taskuuid', 'qtype_moopt');
        $mform->setType('taskuuid', PARAM_TEXT);
        $mform->addRule('taskuuid', get_string('taskuuidrequired', 'qtype_moopt'), 'required');

        $mform->addElement('advcheckbox', 'showstudgradingscheme',
            get_string('showstudgradingscheme', 'qtype_moopt'), ' ');
        $mform->addHelpButton('showstudgradingscheme', 'showstudgradingscheme', 'qtype_moopt');
        $mform->setDefault('showstudgradingscheme', true);

        $mform->addElement('advcheckbox', 'showstudscorecalcscheme',
                get_string('showstudscorecalcscheme', 'qtype_moopt'), ' ');
        $mform->addHelpButton('showstudscorecalcscheme', 'showstudscorecalcscheme', 'qtype_moopt');
        $mform->setDefault('showstudscorecalcscheme', true);

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
                get_string('enablecustomsettingsforfreetextinputfield', 'qtype_moopt', $i + 1), ' ');
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
            $this->hide_custom_fts_conditionally($mform, "namesettingsforfreetextinputgroup", $i);

            $mform->addElement('text', "freetextinputfieldname$i", '');
            $mform->setType("freetextinputfieldname$i", PARAM_PATH);
            $mform->setDefault("freetextinputfieldname$i", "File" . ($i + 1) . ".txt");
            $mform->hideIf("freetextinputfieldname$i", "namesettingsforfreetextinput$i", 'neq', 0);
            $this->hide_custom_fts_conditionally($mform, "freetextinputfieldname", $i);

            $plgroup = [];
            $plgroup[] =& $mform->createElement('static', "ftsproglangtitle$i", '', get_string('ftsprogramminglanguage_i', 'qtype_moopt', $i + 1));
            $mform->addGroup($plgroup, "ftsproglanglblgroup$i", '', ' ', false);
            $this->hide_custom_fts_conditionally($mform, "ftsproglanglblgroup", $i);

            $mform->addElement('select', "ftsoverwrittenlang$i", '', $proglangs);
            $this->hide_custom_fts_conditionally($mform, "ftsoverwrittenlang", $i);

            $idrgroup = [];
            $idrgroup[] =& $mform->createElement('static', "ftsinitialdisplayrowstitle$i", '', get_string('ftsinitialdisplayrows_i', 'qtype_moopt', $i + 1));
            $mform->addGroup($idrgroup, "ftsinitialdisplayrowslblgroup$i", '', ' ', false);
            $this->hide_custom_fts_conditionally($mform, "ftsinitialdisplayrowslblgroup", $i);

            $mform->addElement('text', "ftsinitialdisplayrows$i", '');
            $mform->setType("ftsinitialdisplayrows$i", PARAM_INT);
            $mform->setDefault("ftsinitialdisplayrows$i", DEFAULT_INITIAL_DISPLAY_ROWS);
            $this->hide_custom_fts_conditionally($mform, "ftsinitialdisplayrows", $i);

            // static form elements don't work with hideIf
            // use workaround in https://tracker.moodle.org/browse/MDL-66251
            $lblgroup = [];
            $lblgroup[] =& $mform->createElement('static', "freetextinputfieldtemplatetitle$i", '', get_string('ftstemplate_i', 'qtype_moopt', $i + 1));
            $mform->addGroup($lblgroup, "ftstemplatelblgroup$i", '', ' ', false);
            $this->hide_custom_fts_conditionally($mform, "ftstemplatelblgroup", $i);
//            $mform->addElement('static', "freetextinputfieldtemplatetitle$i", '', get_string('ftstemplate_i', 'qtype_moopt', $i + 1));
//            $this->hide_custom_fts_conditionally($mform, "freetextinputfieldtemplatetitle", $i);

            $mform->addElement('textarea', "freetextinputfieldtemplate$i", '', 'wrap="virtual" rows="3" cols="50"');
//            $mform->addElement('editor', "freetextinputfieldtemplate$i", '',
//                array('rows' => 10), array('maxfiles' => 0,
//                    'noclean' => true, 'context' => $this->context, 'subdirs' => true));
//            $mform->setType("freetextinputfieldtemplate$i", PARAM_RAW);
            $this->hide_custom_fts_conditionally($mform, "freetextinputfieldtemplate", $i);
        }
    }

    protected function data_preprocessing($question) {
        global $DB;

        $question = parent::data_preprocessing($question);

        if (isset($question->id)) {
            $draftitemid = file_get_submitted_draft_itemid('proformataskfileupload');

            $fs = get_file_storage();
            $sourcefilearea = PROFORMA_TASKZIP_FILEAREA;
            if (empty($fs->get_area_files($this->context->id, COMPONENT_NAME, $sourcefilearea, $question->id))) {
                $sourcefilearea = PROFORMA_TASKXML_FILEAREA;
            }
            file_prepare_draft_area($draftitemid, $this->context->id, COMPONENT_NAME, $sourcefilearea,
                    $question->id, array('subdirs' => 0));
            $question->proformataskfileupload = $draftitemid;
        }

        if (isset($question->internaldescription)) {
            $question->internaldescription = array('text' => $question->internaldescription);
        }

        if (isset($question->gradername) && isset($question->graderversion)) {
            $graderfound = false;
            foreach ($this->availableGraders as $grader) {
                if ($question->gradername === $grader['name'] && $question->graderversion === $grader['version']) {
                    $graderfound = true;
                    break;
                }
            }
            if (!$graderfound) {
                $this->gradererrorlabel->setText(get_string('previousgradernotavailable', 'qtype_moopt', ['gradername' => $question->gradername, 'graderversion' => $question->graderversion]));
            }
            $this->graderselect->setSelected(get_html_representation_of_graderid($question->gradername, $question->graderversion));
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
                    $question->{"ftsinitialdisplayrows$indx"} = $value->initialdisplayrows;
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

    private function hide_custom_fts_conditionally($mform, string $name, int $i) {
        $mform->hideIf($name . $i, "enablecustomsettingsforfreetextinputfield$i");
        $mform->hideIf($name . $i, 'enablefreetextsubmissions');
        $mform->hideIf($name . $i, "enablecustomsettingsforfreetextinputfields");
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
