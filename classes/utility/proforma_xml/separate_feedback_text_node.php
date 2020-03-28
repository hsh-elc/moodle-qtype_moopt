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

namespace qtype_programmingtask\utility\proforma_xml;

/**
 * Description of separate_feedback_text_node
 *
 * @author robin
 */
class separate_feedback_text_node {

    private $id;
    private $heading;
    private $children;
    private $isnullified;
    private $score;
    private $accumulatorfunction;
    private $internaldescription;
    private $title;
    private $description;
    private $studentfeedback;
    private $teacherfeedback;
    private $hasinternalerror;
    private $maxscore;

    public function __construct($id, $heading = null, $content = null) {
        $this->id = $id;
        $this->heading = $heading;
        $this->content = $content;
        $this->children = [];
        $this->studentfeedback = [];
        $this->teacherfeedback = [];
        $this->filerefs = [];
    }

    public function add_child(separate_feedback_text_node $node) {
        $this->children[] = $node;
    }

    public function get_children(): array {
        return $this->children;
    }

    public function get_id() {
        return $this->id;
    }

    public function get_heading() {
        return $this->heading;
    }

    public function set_heading($heading) {
        $this->heading = $heading;
    }

    public function set_nullified($isnullified) {
        $this->isnullified = $isnullified;
    }

    public function is_nullified() {
        return $this->isnullified;
    }

    public function get_score() {
        return $this->score;
    }

    public function set_score($score) {
        $this->score = $score;
    }

    public function get_accumulator_function() {
        return $this->accumulatorfunction;
    }

    public function set_accumulator_function($accumulatorfunction) {
        $this->accumulatorfunction = $accumulatorfunction;
    }

    public function get_title() {
        return $this->title;
    }

    public function set_title($title) {
        $this->title = $title;
    }

    public function get_internal_description() {
        return $this->internaldescription;
    }

    public function set_internal_description($internaldescription) {
        $this->internaldescription = $internaldescription;
    }

    public function get_description() {
        return $this->description;
    }

    public function set_description($description) {
        $this->description = $description;
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

    public function set_max_score($score) {
        $this->maxscore = $score;
    }

    public function get_max_score() {
        return $this->maxscore;
    }

}
