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

/**
 * programmingtask question editing form defition.
 *
 * You should override functions as necessary from the parent class located at
 * /question/type/edit_question_form.php.
 */
class qtype_programmingtask_edit_form extends question_edit_form {

    protected function definition() {
        global $COURSE, $PAGE;

        $mform = $this->_form;

        $mform->addElement('header', 'taskfile', get_string('taskfile', 'proforma'));

        $mform->addElement('filemanager', 'proformataskfileupload', get_string('proformataskfileupload', 'proforma'), null, array('subdirs' => 0, 'maxbytes' => $COURSE->maxbytes, 'maxfiles' => 1));
        $mform->addHelpButton('proformataskfileupload', 'proformataskfileupload', 'proforma');

        $mform->addElement('button', 'loadproformataskfilebutton', get_string('loadproformataskfile', 'proforma'), array('id' => 'loadproformataskfilebutton'));

        $mform->addElement('static', 'ajaxerrorlabel', '', '');

        parent::definition();

        $PAGE->requires->js_call_amd('qtype_programmingtask/creation_via_drag_and_drop', 'init');
    }

    protected function definition_inner($mform) {
        $mform->addElement('editor', 'internaldescription', get_string('internaldescription', 'proforma'), array('rows' => 10), array('maxfiles' => 0,
            'noclean' => true, 'context' => $this->context, 'subdirs' => true));
        $mform->setType('internaldescription', PARAM_RAW); // no XSS prevention here, users must be trusted
    }

    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);

        if (isset($question->id)) {
            $draftitemid = file_get_submitted_draft_itemid('proformataskfileupload');
            file_prepare_draft_area($draftitemid, $this->context->id, 'question', proforma_TASKZIP_FILEAREA, $question->id, array('subdirs' => 0));
            $question->proformataskfileupload = $draftitemid;
        }

        if (isset($question->internaldescription)) {
            $question->internaldescription = array('text' => $question->internaldescription);
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

}
