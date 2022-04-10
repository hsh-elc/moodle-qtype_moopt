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


namespace qtype_moopt\output;

use qtype_moopt\utility\proforma_xml\grading_hints_nullify_condition;
use qtype_moopt\utility\proforma_xml\grading_hints_nullify_conditions;
use qtype_moopt\utility\proforma_xml\grading_hints_text_node;

class grading_hints_text_renderer
{

    private $rootnode;
    private $displayteachercontent;
    private $randid;

    public function __construct($rootnode, $displayteachercontent)
    {
        $this->rootnode = $rootnode;
        $this->displayteachercontent = $displayteachercontent;
        $this->randid = bin2hex(random_bytes(6));
    }

    public function render()
    {
        return $this->render_internal($this->rootnode);
    }

    private function render_internal(grading_hints_text_node $node)
    {
        $currentid = $node->get_id() . '_' . $this->randid;
        $accordionid = "accordion_$currentid";

        $attributes = '';
        $text = "<div id='$accordionid'>";
        $text .= "<div class='card'>
                    <div id='$currentid'>
                      <h5 class='mb-0'>";
        $text .=   "<button type='button' class='btn btn-link' data-toggle='collapse' data-target='#collapse_$currentid'" .
            " aria-expanded='false' aria-controls='collapse_$currentid' style='width: 100%;'>
                            {$this->format_heading($node)}
                        </button>";
        if ($node !== $this->rootnode) {
            $attributes = "id='collapse_$currentid' class='collapse' aria-labelledby='heading_$currentid' data-parent='#$accordionid'";
        }
        $text .= "</h5></div>";
        $text .= "<div $attributes>
                    <div class='card-body'>";

        $text .= $this->format_content($node);
        $text .= $this->format_children($node);

        $text .= "</div></div></div>";

        $text .= '</div>';

        return $text;
    }

    private function format_heading(grading_hints_text_node $node)
    {
        $heading = '<div style="text-align:left;width:70%;display:inline-block;">';

        $heading .= '<div style="vertical-align: text-bottom;display:inline-block;">' . $node->get_heading();
        if ($node->get_title() != null) {
            $heading .= " '{$node->get_title()}'";
        }
        $heading .= '</div>';
        $heading .= '</div><div style="text-align:right;vertical-align: text-bottom; width:30%;display:inline-block;">';
        if (!is_null($node->get_max_score())) {
            $heading .= get_string('maxscore', 'qtype_moopt') . ': ' . $node->get_max_score();
        }
        $heading .= '</div>';
        return $heading;
    }

    private function format_content(grading_hints_text_node $node)
    {
        $content = '';
        if ($node->get_description() != null || ($node->get_internal_description() != null && $this->displayteachercontent)) {
            if ($node->get_description() != null) {
                $content .= "<div class='moopt-gradingscheme-description'><h4>" . get_string('description', 'qtype_moopt') .
                    "</h4><i><p>{$node->get_description()}</p></i></div>";
            }
            if ($this->displayteachercontent && $node->get_internal_description() != null) {
                $content .= "<div class='moopt-gradingscheme-internal-description'><h4>" . get_string('internaldescription', 'qtype_moopt') .
                    "</h4><i><p>{$node->get_internal_description()}</p></i></div>";
            }
        }
        if (!empty($node->get_children())) {
            $content .= '<div align="right" style="margin-bottom: 10px">';
            $maxscorecalc = '<small>';
            if ($node->get_max_score() !== null) {
                $submaxscores = [];
                foreach ($node->get_children() as $child) {
                    $submaxscores[] = round($child->get_max_score(), 2);
                }
                $maxscorecalc .= get_string('maxscorecalculationscheme', 'qtype_moopt') . ': ';
                $maxscorecalc .= round($node->get_max_score(), 2) . ' = ';
                switch ($node->get_accumulator_function()) {
                    case 'min':
                        $maxscorecalc .= get_string('minimum', 'qtype_moopt') . ' {';
                        $maxscorecalc .= implode(', ', $submaxscores);
                        $maxscorecalc .= '}';
                        break;
                    case 'max':
                        $maxscorecalc .= get_string('maximum', 'qtype_moopt') . ' {';
                        $maxscorecalc .= implode(', ', $submaxscores);
                        $maxscorecalc .= '}';
                        break;
                    case 'sum':
                        $maxscorecalc .= implode(' + ', $submaxscores);
                        break;
                }
            } else {
                $strings  = new \stdClass();
                if ($node == $this->rootnode) {
                    $strings->type = get_string('submission', 'qtype_moopt');
                } else {
                    $strings->type = get_string('combinedtest', 'qtype_moopt');
                }
                $strings->function = get_string('maxscorecalculationscheme' . $node->get_accumulator_function() . 'function', 'qtype_moopt');
                $maxscorecalc .= get_string('maxscorecalculationschemedescription', 'qtype_moopt', $strings);
            }
            $content .= "<i>$maxscorecalc</i></small></div>";
        }
        $content .= $this->format_nullifying($node);
        return $content;
    }

    /**
     * Formats the content of all childrens of the node, will call render_internal() recursively
     * @param grading_hints_text_node $node
     * @return string
     */
    private function format_children(grading_hints_text_node $node) {
        $content = '';
        if (!empty($node->get_children())) {
            $content .= '<div>';
            foreach ($node->get_children() as $child) {
                $content .= '<p>' . $this->render_internal($child) . '</p>';
            }
            $content .= '</div>';

        }
        return $content;
    }

    /**
     * Generates the nullifycondition output for a given node
     * @param grading_hints_text_node $node
     * @return string the output for the nullifycondition. An empty string will be returned if the element does not have a nullifycondition
     * @throws \coding_exception
     */
    private function format_nullifying(grading_hints_text_node $node) : string {
        if ($node->get_nullifyconditionroot() !== null) {
            $o = '<div align="right" style="margin-bottom: 10px"><small><i>';

            $type = $node->get_type();
            if ($type == 'combine') {
                $type = get_string("combinedtest", "qtype_moopt");
            } else {
                $type = get_string("test", "qtype_moopt");
            }

            $o .= get_string("scorewillbenullifiedif", "qtype_moopt", $type);

            $nullifyconditionroot = $node->get_nullifyconditionroot();
            switch(get_class($nullifyconditionroot)) {
                case 'qtype_moopt\utility\proforma_xml\grading_hints_nullify_condition':
                    $o .= $this->render_nullify_single($nullifyconditionroot);
                    break;
                case 'qtype_moopt\utility\proforma_xml\grading_hints_nullify_conditions':
                    $o .= $this->render_nullify_composite($nullifyconditionroot);
                    break;
            }

            $o .= '</i></small></div>';
            return $o;
        } else {
            return '';
        }
    }

    /**
     * Generates the output for a simple nullifycondition
     * @param grading_hints_nullify_condition $nullifycondition
     * @return string
     * @throws \coding_exception
     */
    private function render_nullify_single(grading_hints_nullify_condition $nullifycondition) : string {

        $o = $this->get_nullifycondition_operand_string($nullifycondition->get_leftoperand());
        $o .= ' ' . get_string('operator' . $nullifycondition->get_compareoperator(), "qtype_moopt") . ' ';
        $o .= $this->get_nullifycondition_operand_string($nullifycondition->get_rightoperand());

        return $o;
    }

    /**
     * Generates the string for an operand of a nullifycondition
     * @param $operand
     * @return string
     * @throws \coding_exception
     */
    private function get_nullifycondition_operand_string($operand) : string {
        if (is_float($operand)) {
            //Operand is a literal
            return $operand;
        }
        switch (get_class($operand)) {
            case 'qtype_moopt\utility\proforma_xml\grading_hints_nullify_condition_testref_operand':
                $type = get_string("test", "qtype_moopt");
                if ($operand->get_subref() !== null) {
                    //Use the subref instead of the test title if the test has a subref
                    $reftitle = $operand->get_subref();
                }
                break;
            case 'qtype_moopt\utility\proforma_xml\grading_hints_nullify_condition_combineref_operand':
                $type = get_string("combinedtest", "qtype_moopt");
                break;
        }
        if (!isset($reftitle)) {
            $reftitle = $this->rootnode->get_child_by_refid($operand->get_ref())->get_title();
        }
        return get_string("scoreof", "qtype_moopt", $type) . " '$reftitle'";
    }

    /**
     * Generates the output for a composite nullifycondition
     * @param grading_hints_nullify_conditions $nullifyconditions
     * @return string
     * @throws \coding_exception
     */
    private function render_nullify_composite(grading_hints_nullify_conditions $nullifyconditions) : string {
        $o = '';

        $operands = $nullifyconditions->get_operands();
        $o .= $this->get_nullifyconditions_operand_string($operands[0]);
        for ($i = 1; $i < count($operands); $i++) {
            $o .= ' ' . get_string('operator' . $nullifyconditions->get_composeoperator(), "qtype_moopt") . ' ';
            $o .= $this->get_nullifyconditions_operand_string($operands[$i]);
        }

        return $o;
    }

    /**
     * Generates the string for an operand of a composite nullifycondition
     * @param mixed $operand The operand can be of type grading_hints_nullify_condition or grading_hints_nullify_conditions
     * @return string returns the specific string or an empty string if the type of $operand parameter is not valid
     * @throws \coding_exception
     */
    private function get_nullifyconditions_operand_string($operand) : string {
        switch (get_class($operand)) {
            case 'qtype_moopt\utility\proforma_xml\grading_hints_nullify_condition':
                return '(' . $this->render_nullify_single($operand) . ')';
            case 'qtype_moopt\utility\proforma_xml\grading_hints_nullify_conditions':
                return '(' . $this->render_nullify_composite($operand) . ')';
            default:
                return '';
        }
    }
}
