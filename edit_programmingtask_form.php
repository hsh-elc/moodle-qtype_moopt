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

require_once('locallib.php');

defined('MOODLE_INTERNAL') || die();

/**
 * programmingtask question editing form defition.
 *
 * You should override functions as necessary from the parent class located at
 * /question/type/edit_question_form.php.
 */
class qtype_programmingtask_edit_form extends question_edit_form {

    private $grader_select;
    private $grader_options;
    private $grader_errorlabel;

    protected function definition() {
        global $COURSE, $PAGE;

        $mform = $this->_form;

        $mform->addElement('header', 'taskfile', get_string('taskfile', 'qtype_programmingtask'));

        $mform->addElement('filemanager', 'proformataskfileupload', get_string('proformataskfileupload', 'qtype_programmingtask'), null, array('subdirs' => 0, 'maxbytes' => $COURSE->maxbytes, 'maxfiles' => 1));
        $mform->addHelpButton('proformataskfileupload', 'proformataskfileupload', 'qtype_programmingtask');
        $mform->addRule('proformataskfileupload', get_string('proformataskfilerequired', 'qtype_programmingtask'), 'required');

        $mform->addElement('button', 'loadproformataskfilebutton', get_string('loadproformataskfile', 'qtype_programmingtask'), array('id' => 'loadproformataskfilebutton'));

        $label = $mform->addElement('static', 'ajaxerrorlabel', '', '');
        $this->setClassAttributeOfLabel($label, 'errorlabel');

        parent::definition();

        $PAGE->requires->js_call_amd('qtype_programmingtask/creation_via_drag_and_drop', 'init');
    }

    protected function definition_inner($mform) {
        global $DB;

        $mform->addElement('editor', 'internaldescription', get_string('internaldescription', 'qtype_programmingtask'), array('rows' => 10), array('maxfiles' => 0,
            'noclean' => true, 'context' => $this->context, 'subdirs' => true));
        $mform->setType('internaldescription', PARAM_RAW); // no XSS prevention here, users must be trusted

        $graders = retrieve_graders_and_update_local_list();
        $this->grader_options = array();
        foreach ($graders['graders'] as $name => $id) {
            $this->grader_options[$id] = $name;
        }
        $this->grader_select = $mform->addElement('select', 'graderid', get_string('grader', 'qtype_programmingtask'), $this->grader_options);

        $this->grader_errorlabel = $mform->addElement('static', 'gradernotavailableerrorlabel', '', '');
        $this->setClassAttributeOfLabel($this->grader_errorlabel, 'errorlabel');

        $mform->addElement('text', 'taskuuid', get_string('taskuuid', 'qtype_programmingtask'), array("size" => '36'));
        $mform->setType('taskuuid', PARAM_TEXT);
        $mform->addRule('taskuuid', get_string('taskuuidrequired', 'qtype_programmingtask'), 'required');
    }

    protected function data_preprocessing($question) {
        global $DB;

        $question = parent::data_preprocessing($question);

        if (isset($question->id)) {
            $draftitemid = file_get_submitted_draft_itemid('proformataskfileupload');
            file_prepare_draft_area($draftitemid, $this->context->id, 'question', proforma_TASKZIP_FILEAREA, $question->id, array('subdirs' => 0));
            $question->proformataskfileupload = $draftitemid;
        }

        if (isset($question->internaldescription)) {
            $question->internaldescription = array('text' => $question->internaldescription);
        }

        if (isset($question->graderid)) {
            $is_current_grader_available = false;
            foreach ($this->grader_options as $id => $name) {
                if ($id === $question->graderid) {
                    $is_current_grader_available = true;
                    break;
                }
            }
            if (!$is_current_grader_available) {
                $gradername = $DB->get_field('qtype_programmingtask_gradrs', 'gradername', array("graderid" => $question->graderid));
                $this->grader_select->addOption($gradername, $question->graderid);
                $this->grader_errorlabel->setText(get_string('previousgradernotavailable', 'qtype_programmingtask'));
            }
            $this->grader_select->setSelected($question->graderid);
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

    private function setClassAttributeOfLabel(MoodleQuickForm_static $label, $classes) {
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

        return $errors;
    }

}
