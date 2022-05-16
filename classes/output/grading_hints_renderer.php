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
use qtype_moopt\utility\proforma_xml\separate_feedback_text_node;

/**
 * Description of gradinghints_renderer
 *
 * @author robin
 */
class grading_hints_renderer
{

    private $rootnode;
    private $displayteachercontent;
    private $fileinfos;
    private $showstudentsscorecalculationscheme;
    private $randid;
    private $showfeedbackdata;

    /**
     * @param separate_feedback_text_node|grading_hints_text_node $rootnode The type of the rootnode determines whether only the scheme should be rendered or the separate feedback tree
     * @param $displayteachercontent
     * @param $fileinfos
     * @param $showstudentsscorecalculationscheme
     * @throws \Exception
     */
    public function __construct($rootnode, $displayteachercontent, $fileinfos = null, $showstudentsscorecalculationscheme = false)
    {
        $this->rootnode = $rootnode;
        $this->displayteachercontent = $displayteachercontent;
        $this->fileinfos = $fileinfos;
        $this->showstudentsscorecalculationscheme = $showstudentsscorecalculationscheme;
        $this->randid = bin2hex(random_bytes(6));
        $this->showfeedbackdata = true;
        if (get_class($rootnode) == 'qtype_moopt\utility\proforma_xml\grading_hints_text_node') {
            $this->showfeedbackdata = false;
            $this->showstudentsscorecalculationscheme = true;
        }
    }

    public function render()
    {
        return $this->render_internal($this->rootnode);
    }

    private function render_internal($node)
    {
        $currentid = $node->get_id() . '_' . $this->randid;
        $accordionid = "accordion_$currentid";

        $additionalheaderclasses = '';
        if ($this->showfeedbackdata) {
            $additionalheaderclasses = "<div class='card-header, " . ($node->has_internal_error() ? ', internalerror' : '') . "'";
        }

        $text = "<div id='$accordionid'>";
        $text .= "<div class='card'>
                    <div $additionalheaderclasses id='$currentid'>
                      <h5 class='mb-0'>
                        <button type='button' class='btn btn-link' data-toggle='collapse' data-target='#collapse_$currentid'" .
            " aria-expanded='false' aria-controls='collapse_$currentid' style='width: 100%;'>
                            {$this->format_heading($node)}
                        </button>";
        $text .= "</h5></div>";

        $attributes = '';
        if ($this->showfeedbackdata || $node !== $this->rootnode) {
            $attributes = "id='collapse_$currentid' class='collapse' aria-labelledby='heading_$currentid' data-parent='#$accordionid'";
        }

        $text .= "<div $attributes>
                    <div class='card-body'>";

        $text .= $this->format_content($node);
        $text .= $this->format_children($node);

        $text .= "</div></div></div>";

        $text .= '</div>';

        return $text;
    }

    private function format_heading($node)
    {
        $titlewidth = 70;
        if ($this->showfeedbackdata && $node->get_type() == 'test') {
            $titlewidth = 60;
        }
        $heading = '<div style="text-align:left;width:'. $titlewidth .'%;display:inline-block;">';

        if ($this->showfeedbackdata && $node->has_internal_error()) {
            $heading .= '<div style="font-family: FontAwesome; font-size: 1.5em;margin-right:20px;".'
                . '"display:inline-block;">&#xf00d;</div>';
        }

        $heading .= '<div style="vertical-align: text-bottom;display:inline-block;">' . $node->get_heading();
        if ($node->get_title() != null) {
            $heading .= " '{$node->get_title()}'";
        }
        $heading .= '</div>';
        $heading .= '</div><div style="text-align:right;vertical-align: text-bottom; width:'. (100-$titlewidth) .'%;display:inline-block;">';
        if ($this->showfeedbackdata) {
            if (!is_null($node->get_score())) {
                $score = round($node->get_score(), 2);
                $maxscore = round($node->get_max_score(), 2);

                if (!is_null($node->get_max_score())) {
                    $heading .= "$score / $maxscore";
                } else {
                    $heading .= get_string('score', 'qtype_moopt') .": $score";
                }
                if (!is_null($node->get_rawscore()) && $node->get_type() == 'test') {
                    $heading .= ' (' . get_string("rawscore", "qtype_moopt") . ' ' . $node->get_rawscore() . ')';
                }
            }
        } else {
            if (!is_null($node->get_max_score())) {
                $heading .= get_string('maxscore', 'qtype_moopt') . ': ' . round($node->get_max_score(), 2);
            }
        }
        if ($this->showfeedbackdata && $node->is_nullified()) {
            $heading .= ' (' . get_string('hasbeennullified', 'qtype_moopt') . ')';
        }
        $heading .= '</div>';
        return $heading;
    }

    private function format_content($node)
    {

        $content = '';
        if ($node->get_type() == 'test') {
            $weightedscoreexplanation = get_string("scorecalculation", "qtype_moopt") . '<br>';
            $weightedscoreexplanation .= '<ol>
                                            <li>'. get_string('testresultrawscore', 'qtype_moopt') .'</li>
                                            <li>'. get_string('multiplicationbyaweightfactor', 'qtype_moopt') . ' ' . round($node->get_weight(), 2) .'</li>
                                          </ol>';
            $content .= $weightedscoreexplanation;
        }
        if ($node->get_description() != null || ($node->get_internal_description() != null && $this->displayteachercontent)) {
            if ($node->get_description() != null) {
                $content .= "<div class='moopt-feedback-description'><h4>" . get_string('description', 'qtype_moopt') .
                    "</h4><i><p>{$node->get_description()}</p></i></div>";
            }
            if ($this->displayteachercontent && $node->get_internal_description() != null) {
                $content .= "<div class='moopt-feedback-internal-description'><h4>" . get_string('internaldescription', 'qtype_moopt') .
                    "</h4><i><p>{$node->get_internal_description()}</p></i></div>";
            }
        }
        if (!empty($node->get_children())) {
            if ($this->displayteachercontent || $this->showstudentsscorecalculationscheme) {
                $content .= '<div align="right" style="margin-bottom: 10px">';
                $scorecalc = '<small>';
                if (!$this->showfeedbackdata && $node->get_max_score() === null) {
                    $strings = new \stdClass();
                    if ($node === $this->rootnode) {
                        $strings->type = get_string('submission', 'qtype_moopt');
                    } else {
                        $strings->type = get_string('combinedtest', 'qtype_moopt');
                    }
                    $strings->function = get_string('maxscorecalculationscheme' . $node->get_accumulator_function() . 'function', 'qtype_moopt');
                    $scorecalc .= get_string('maxscorecalculationschemedescription', 'qtype_moopt', $strings);
                } else {
                    $subscores = [];
                    foreach ($node->get_children() as $child) {
                        if ($this->showfeedbackdata) {
                            $subscores[] = round($child->get_score(), 2);
                        } else {
                            $subscores[] = round($child->get_max_score(), 2);
                        }
                    }
                    if ($this->showfeedbackdata) {
                        $scorecalcschemeprefix = get_string('scorecalculationscheme', 'qtype_moopt');
                    } else {
                        $scorecalcschemeprefix = get_string('maxscorecalculationscheme', 'qtype_moopt');
                    }
                    $scorecalc .= $scorecalcschemeprefix . ': ';
                    if ($this->showfeedbackdata) {
                        $scorecalc .= round($node->get_score(), 2);
                    } else {
                        $scorecalc .= round($node->get_max_score(), 2);
                    }
                    $scorecalc .= ' = ';
                    switch ($node->get_accumulator_function()) {
                        case 'min':
                            $scorecalc .= get_string('minimum', 'qtype_moopt') . ' {';
                            $scorecalc .= implode(', ', $subscores);
                            $scorecalc .= '}';
                            break;
                        case 'max':
                            $scorecalc .= get_string('maximum', 'qtype_moopt') . ' {';
                            $scorecalc .= implode(', ', $subscores);
                            $scorecalc .= '}';
                            break;
                        case 'sum':
                            $scorecalc .= implode(' + ', $subscores);
                            break;
                    }
                }
                $content .= "<i>$scorecalc</i></small></div>";
            }
        } else {
            if ($this->showfeedbackdata) {
                if (!empty($node->get_student_feedback())) {
                    $content .= '<div class=\'moopt-feedback-student\'><h4>' . get_string('feedback', 'qtype_moopt') . '</h4>';
                    foreach ($node->get_student_feedback() as $studfeed) {
                        if ($studfeed['title'] != null) {
                            $content .= "<p><strong>{$studfeed['title']}</strong></p>";
                        }
                        $content .= "<p>{$studfeed['content']}</p>";
                        $files = $studfeed['files'];
                        if (!empty($files['embeddedFiles'] || !empty($files['attachedFiles']))) {
                            $content .= '<p>' . get_string('files', 'qtype_moopt') . ':<br/><ul>';
                            foreach ($files['embeddedFiles'] as $file) {
                                $pathinfo = pathinfo($this->fileinfos['filepath'] . $file['id'] . '/' . $file['filename']);
                                $url = \moodle_url::make_pluginfile_url($this->fileinfos['contextid'], $this->fileinfos['component'],
                                    PROFORMA_RESPONSE_FILE_AREA_EMBEDDED, $this->fileinfos['itemid'],
                                    $pathinfo['dirname'] . '/', $pathinfo['basename'], true);
                                $content .= "<li><a href='$url'>{$file['title']}</a></li>";
                            }
                            foreach ($files['attachedFiles'] as $file) {
                                $pathinfo = pathinfo($this->fileinfos['filepath'] . $file['filename']);
                                $url = \moodle_url::make_pluginfile_url($this->fileinfos['contextid'], $this->fileinfos['component'],
                                    PROFORMA_RESPONSE_FILE_AREA, $this->fileinfos['itemid'], $pathinfo['dirname'] . '/',
                                    $pathinfo['basename'], true);
                                $content .= "<li><a href='$url'>{$file['title']}</a></li>";
                            }
                            $content .= '</ul></p>';
                        }
                    }
                    $content .= '</div>';
                }
                if (!empty($node->get_teacher_feedback()) && $this->displayteachercontent) {
                    $content .= '<div class=\'moopt-feedback-teacher\'><h4>' . get_string('teacherfeedback', 'qtype_moopt') . '</h4>';
                    foreach ($node->get_teacher_feedback() as $teacherfeed) {
                        $content .= '<p>';
                        if ($teacherfeed['title'] != null) {
                            $content .= "<p><strong>{$teacherfeed['title']}</strong></p>";
                        }
                        $content .= "<p>{$teacherfeed['content']}</p>";
                        $files = $teacherfeed['files'];
                        if (!empty($files['embeddedFiles'] || !empty($files['attachedFiles']))) {
                            $content .= '<p>' . get_string('files', 'qtype_moopt') . ':<br/><ul>';
                            foreach ($files['embeddedFiles'] as $file) {
                                $pathinfo = pathinfo($this->fileinfos['filepath'] . $file['id'] . '/' . $file['filename']);
                                $url = \moodle_url::make_pluginfile_url($this->fileinfos['contextid'], $this->fileinfos['component'],
                                    PROFORMA_RESPONSE_FILE_AREA_EMBEDDED, $this->fileinfos['itemid'],
                                    $pathinfo['dirname'] . '/', $pathinfo['basename'], true);
                                $content .= "<li><a href='$url'>{$file['title']}</a></li>";
                            }
                            foreach ($files['attachedFiles'] as $file) {
                                $pathinfo = pathinfo($this->fileinfos['filepath'] . $file['filename']);
                                $url = \moodle_url::make_pluginfile_url($this->fileinfos['contextid'], $this->fileinfos['component'],
                                    PROFORMA_RESPONSE_FILE_AREA, $this->fileinfos['itemid'], $pathinfo['dirname'] . '/',
                                    $pathinfo['basename'], true);
                                $content .= "<li><a href='$url'>{$file['title']}</a></li>";
                            }
                            $content .= '</ul></p>';
                        }
                    }
                    $content .= '</div>';
                }
                if ($content == '') {
                    $content = '<div><i>' . get_string('nofeedback', 'qtype_moopt') . '</i></div>';
                }
            }
        }
        $content .= $this->format_nullifying($node);
        return $content;
    }

    /**
     * Formats the content of all childrens of the node, will call render_internal() recursively
     * @param grading_hints_text_node|separate_feedback_text_node $node
     * @return string
     */
    private function format_children($node) {
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
     * @param grading_hints_text_node|separate_feedback_text_node $node
     * @return string the output for the nullifycondition. An empty string will be returned if the element does not have a nullifycondition or is not nullified
     * @throws \coding_exception
     */
    private function format_nullifying($node) : string {
        //Show the nullify information in feedback only if the $node has been nullified
        if (!$this->showfeedbackdata || $node->is_nullified()) {
            if ($node->get_nullifyconditionroot() !== null) {
                $o = '<div align="right" style="margin-bottom: 10px"><small><i>';

                $type = $node->get_type();
                if ($type == 'combine') {
                    if ($this->showfeedbackdata) {
                        $type = get_string("combinedresult", "qtype_moopt");
                    } else {
                        $type = get_string("combinedtest", "qtype_moopt");
                    }
                } else {
                    $type = get_string("test", "qtype_moopt");
                }

                if ($this->showfeedbackdata) {
                    $o .= get_string("scorewasnullifiedbecause", "qtype_moopt", $type);
                } else {
                    $o .= get_string("scorewillbenullifiedif", "qtype_moopt", $type);
                }

                $nullifyconditionroot = $node->get_nullifyconditionroot();
                if ($nullifyconditionroot->get_title() !== null) {
                    $o .= $nullifyconditionroot->get_title();
                }
                if ($nullifyconditionroot->get_description() !== null) {
                    $o .= '<br>' . $nullifyconditionroot->get_description();
                }
                if ($nullifyconditionroot->get_internaldescription() !== null && $this->displayteachercontent) {
                    $o .= '<br>' . $nullifyconditionroot->get_internaldescription();
                }
                $o .= '<br>(';
                switch(get_class($nullifyconditionroot)) {
                    case 'qtype_moopt\utility\proforma_xml\grading_hints_nullify_condition':
                        $o .= $this->render_nullify_single($nullifyconditionroot);
                        break;
                    case 'qtype_moopt\utility\proforma_xml\grading_hints_nullify_conditions':
                        $o .= $this->render_nullify_composite($nullifyconditionroot);
                        break;
                }

                $o .= ')';
                $o .= '</i></small></div>';
                return $o;
            } else {
                return '';
            }
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
        $operand1 = $nullifycondition->get_leftoperand();
        $operand2 = $nullifycondition->get_rightoperand();
        $o = $this->get_nullifycondition_operand_string($operand1, $operand2);
        $o .= ' ' . get_string('operator' . $nullifycondition->get_compareoperator(), "qtype_moopt") . ' ';
        $o .= $this->get_nullifycondition_operand_string($operand2, $operand1);

        return $o;
    }

    /**
     * Get the type of a nullifycondition operand
     * @param $operand
     * @return string The type as string, there are 4 possible values: 'literal', 'testref', 'combineref' and 'undefined'
     */
    private function get_nullifycondition_operand_type($operand) : string {
        if (is_float($operand)) {
            return 'literal';
        }
        switch (get_class($operand)) {
            case 'qtype_moopt\utility\proforma_xml\grading_hints_nullify_condition_testref_operand':
                return 'testref';
            case 'qtype_moopt\utility\proforma_xml\grading_hints_nullify_condition_combineref_operand':
                return 'combineref';
            default:
                return 'undefined';
        }
    }

    /**
     * Generates the string for an operand of a nullifycondition
     * @param $operand
     * @param $otheroperand
     * @return string
     * @throws \coding_exception
     */
    private function get_nullifycondition_operand_string($operand, $otheroperand) : string {
        switch ($this->get_nullifycondition_operand_type($operand)) {
            case 'literal':
                if ($this->get_nullifycondition_operand_type($otheroperand) == 'combineref') {
                    //Adjust weight of literal value
                    $operand *= $this->rootnode->get_child_by_refid($otheroperand->get_ref())->get_weight();
                }
                return $operand;
            case 'testref':
                $type = get_string("test", "qtype_moopt");

                if ($operand->get_subref() !== null && $operand->get_subref() !== '') {
                    $referencednode = $this->rootnode->get_child_by_refid($operand->get_ref(), $operand->get_subref());
                } else {
                    $referencednode = $this->rootnode->get_child_by_refid($operand->get_ref());
                }
                if ($referencednode->get_title() !== null) {
                    $reftitle = $referencednode->get_title();
                } else {
                    $reftitle = $operand->get_ref();
                    if ($operand->get_subref !== null) {
                       $reftitle .= '/' . $operand->get_subref();
                    }
                }
                if ($this->showfeedbackdata) {
                    $achievedscore = round($referencednode->get_rawscore(), 2);
                }
                $scoretype = get_string("rawscoreof", "qtype_moopt");
                break;
            case 'combineref':
                $type = get_string("combinedtest", "qtype_moopt");
                $scoretype = get_string("rawscoreof", "qtype_moopt");
                $referencednode = $this->rootnode->get_child_by_refid($operand->get_ref());
                if ($referencednode->get_title() !== null) {
                    $reftitle = $referencednode->get_title();
                } else {
                    $reftitle = $operand->get_ref();
                }
                if ($this->showfeedbackdata) {
                    $achievedscore = round($referencednode->get_rawscore(), 2);
                }
                if ($this->get_nullifycondition_operand_type($otheroperand) == 'literal') {
                    $scoretype = get_string("weightedscoreof", "qtype_moopt");
                    if ($this->showfeedbackdata) {
                        $achievedscore = round($referencednode->get_score(), 2);
                    }
                }
                break;
            default:
                return '';
        }
        if (isset($achievedscore)) {
            $achievedscore = '(' . get_string("achieved", "qtype_moopt") . ' ' . $achievedscore . ')';
        } else {
            $achievedscore = '';
        }
        return $scoretype . ' ' . $type . " '$reftitle' " . $achievedscore;
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
