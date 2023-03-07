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

namespace qtype_moopt\utility\proforma_xml;

/**
 * Contains all the feedback related data
 *
 * @author robin
 */
class separate_feedback_text_node {

    private $isnullified = false;
    private $rawscore;
    private $score;
    private $studentfeedback;
    private $teacherfeedback;
    private $hasinternalerror = false;

    public function __construct() {
        $this->studentfeedback = [];
        $this->teacherfeedback = [];
    }

    public function set_nullified($isnullified) {
        $this->isnullified = $isnullified;
    }

    public function is_nullified() {
        return $this->isnullified;
    }

    public function get_rawscore() {
        return $this->rawscore;
    }

    public function set_rawscore($rawscore) {
        $this->rawscore = $rawscore;
    }

    public function get_score() {
        return $this->score;
    }

    public function set_score($score) {
        $this->score = $score;
    }

    public function get_student_feedback() {
        return $this->studentfeedback;
    }

    public function get_teacher_feedback() {
        return $this->teacherfeedback;
    }

    public function add_student_feedback($feedback) {
        if ($feedback['content'] == null && $feedback['title'] == null) {
            return;
        }
        $this->studentfeedback[] = $feedback;
    }

    public function add_teacher_feedback($feedback) {
        if ($feedback['content'] == null && $feedback['title'] == null) {
            return;
        }
        $this->teacherfeedback[] = $feedback;
    }

    public function has_internal_error() {
        return $this->hasinternalerror;
    }

    public function set_has_internal_error($err) {
        $this->hasinternalerror = $err;
    }
}
