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

class grading_hints_node {

    private $id;
    private $heading;
    private $children;
    private $accumulatorfunction;
    private $internaldescription;
    private $title;
    private $description;
    private $maxscore;
    private $weight;
    private $type;
    private $refid;
    private $subref;
    private $nullifyconditionroot;
    private separate_feedback_text_node $separate_feedback_data;

    public function __construct($id, $heading = null) {
        $this->id = $id;
        $this->heading = $heading;
        $this->children = [];
    }

    public function addSeparateFeedbackData() {
        if (!isset($this->separate_feedback_data)) {
            $this->separate_feedback_data = new separate_feedback_text_node();
        }
    }

    public function add_child(grading_hints_node $node) {
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

    public function set_max_score($score) {
        $this->maxscore = $score;
    }

    public function get_max_score() {
        return $this->maxscore;
    }

    public function set_type($type) {
        $this->type = $type;
    }

    public function get_type() {
        return $this->type;
    }

    public function set_weight($weight) {
        $this->weight = $weight;
    }

    public function get_weight() {
        return $this->weight;
    }

    public function set_refid($refid) {
        $this->refid = $refid;
    }

    public function get_refid() {
        return $this->refid;
    }

    public function set_subref($subref) {
        $this->subref = $subref;
    }

    public function get_subref() {
        return $this->subref;
    }

    public function set_nullifyconditionroot($nullifyconditionroot) {
        $this->nullifyconditionroot = $nullifyconditionroot;
    }

    public function get_nullifyconditionroot() {
        return $this->nullifyconditionroot;
    }

    public function has_feedback_data() : bool {
        return isset($this->separate_feedback_data);
    }

    /**
     * @return separate_feedback_text_node All the feedback related data
     */
    public function getSeparateFeedbackData(): separate_feedback_text_node
    {
        return $this->separate_feedback_data;
    }

    public function get_child_by_refid($refid, $subref = null) : ?grading_hints_node {
        if ($this->ref_equals($refid, $subref)) {
            return $this;
        } else {
            foreach ($this->get_children() as $child) {
                $ret = $child->get_child_by_refid($refid, $subref);
                if ($ret !== null) {
                    return $ret;
                }
            }
            return null;
        }
    }

    private function ref_equals($refid, $subref = null) : bool {
        $condition = true;
        if ($subref !== null) {
            $condition = $this->get_subref() === $subref;
        }
        return $this->get_refid() === $refid && $condition;
    }
}
