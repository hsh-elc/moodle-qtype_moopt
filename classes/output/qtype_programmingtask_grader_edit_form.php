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

namespace qtype_programmingtask\output;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

class qtype_programmingtask_grader_edit_form extends \moodleform {

    private $graders;

    public function __construct($graderlist, $action = null, $customdata = null, $method = 'post', $target = '', $attributes = null,
            $editable = true, $ajaxformdata = null) {
        $this->graders = $graderlist;

        parent::__construct($action, $customdata, $method, $target, $attributes, $editable, $ajaxformdata);
    }

    protected function definition() {
        $mform = $this->_form;

        foreach ($this->graders as $grader) {
            $mform->addElement('header', 'gradername_' . $grader->graderid, $grader->gradername, '');
            $mform->addElement('text', 'lmsid_' . $grader->graderid, get_string('lmsid', 'qtype_programmingtask'));
            $mform->setType('lmsid_' . $grader->graderid, PARAM_TEXT);
            $mform->addElement('passwordunmask', 'lmspw_' . $grader->graderid, get_string('password', 'qtype_programmingtask'));
        }

        $this->add_action_buttons();
    }

    public function get_graders(): array {
        return $this->graders;
    }

}
