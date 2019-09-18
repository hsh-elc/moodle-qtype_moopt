<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace qtype_programmingtask\output;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

class qtype_programmingtask_grader_edit_form extends \moodleform {

    private $graders;

    function __construct($graderlist, $action = null, $customdata = null, $method = 'post', $target = '', $attributes = null, $editable = true, $ajaxformdata = null) {
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

    public function getGraders(): array {
        return $this->graders;
    }

}
