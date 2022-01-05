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

use qtype_moopt\utility\proforma_xml\separate_feedback_text_node;

/**
 * Description of separate_feedback_text_renderer
 *
 * @author robin
 */
class separate_feedback_text_renderer {

    private $rootnode;
    private $displayteachercontent;
    private $fileinfos;
    private $showstudentsscorecalculationscheme;
    private $randid;

    public function __construct($rootnode, $displayteachercontent, $fileinfos, $showstudentsscorecalculationscheme) {
        $this->rootnode = $rootnode;
        $this->displayteachercontent = $displayteachercontent;
        $this->fileinfos = $fileinfos;
        $this->showstudentsscorecalculationscheme = $showstudentsscorecalculationscheme;
        $this->randid = bin2hex(random_bytes(6));
    }

    public function render() {
        return $this->render_internal($this->rootnode);
    }

    private function render_internal(separate_feedback_text_node $node) {
        $currentid = $node->get_id() . '_' . $this->randid;
        $accordionid = "accordion_$currentid";

        $additionalheaderclasses = $node->has_internal_error() ? ', internalerror' : '';

        $text = "<div id='$accordionid'>";
        $text .= "<div class='card'>
                    <div class='card-header, {$additionalheaderclasses}' id='$currentid'>
                      <h5 class='mb-0'>
                        <button type='button' class='btn btn-link' data-toggle='collapse' data-target='#collapse_$currentid'" .
                " aria-expanded='false' aria-controls='collapse_$currentid' style='width: 100%;'>
                            {$this->format_heading($node)}
                        </button>
                      </h5>
                    </div>";

        $text .= "<div id='collapse_$currentid' class='collapse' aria-labelledby='heading_$currentid' data-parent='#$accordionid'>
                    <div class='card-body'>";

        $text .= $this->format_content($node);

        $text .= "</div></div></div>";

        $text .= '</div>';

        return $text;
    }

    private function format_heading(separate_feedback_text_node $node) {
        $heading = '<div style="text-align:left;width:70%;display:inline-block;">';

        if ($node->has_internal_error()) {
            $heading .= '<div style="font-family: FontAwesome; font-size: 1.5em;margin-right:20px;".'
                    . '"display:inline-block;">&#xf00d;</div>';
        }

        $heading .= '<div style="vertical-align: text-bottom;display:inline-block;">' . $node->get_heading();
        if ($node->get_title() != null) {
            $heading .= " '{$node->get_title()}'";
        }
        $heading .= '</div>';
        $heading .= '</div><div style="text-align:right;vertical-align: text-bottom; width:30%;display:inline-block;">';
        if (!is_null($node->get_score())) {
            $score = round($node->get_score(), 2);
            $maxscore = round($node->get_max_score(), 2);

            if (!is_null($node->get_max_score())) {
                $heading .= "$score / $maxscore";
            } else {
                $heading .= "Score: $score";
            }
        }
        if ($node->is_nullified()) {
            $heading .= ' (' . get_string('hasbeennullified', 'qtype_moopt') . ')';
        }
        $heading .= '</div>';
        return $heading;
    }

    private function format_content(separate_feedback_text_node $node) {

        $content = '';
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
                $subscores = [];
                foreach ($node->get_children() as $child) {
                    $subscores[] = round($child->get_score(), 2);
                }
                $scorecalc = '<small>' . get_string('scorecalculationscheme', 'qtype_moopt') . ': ' .
                        round($node->get_score(), 2) . ' = ';
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
                $content .= "<i>$scorecalc</i></small></div>";
            }

            $content .= '<div>';
            foreach ($node->get_children() as $child) {
                $content .= '<p>' . $this->render_internal($child) . '</p>';
            }
            $content .= '</div>';

            return $content;
        } else {
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
                            $url = \moodle_url::make_pluginfile_url($this->fileinfos['contextid'], COMPONENT_NAME,
                                            PROFORMA_RESPONSE_FILE_AREA_EMBEDDED . $this->fileinfos['fileareasuffix'],
                                            $this->fileinfos['itemid'], $pathinfo['dirname'] . '/', $pathinfo['basename'], true);
                            $content .= "<li><a href='$url'>{$file['title']}</a></li>";
                        }
                        foreach ($files['attachedFiles'] as $file) {
                            $pathinfo = pathinfo($this->fileinfos['filepath'] . $file['filename']);
                            $url = \moodle_url::make_pluginfile_url($this->fileinfos['contextid'], COMPONENT_NAME,
                                            PROFORMA_RESPONSE_FILE_AREA . $this->fileinfos['fileareasuffix'],
                                            $this->fileinfos['itemid'], $pathinfo['dirname'] . '/', $pathinfo['basename'], true);
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
                            $url = \moodle_url::make_pluginfile_url($this->fileinfos['contextid'], COMPONENT_NAME,
                                            PROFORMA_RESPONSE_FILE_AREA_EMBEDDED . $this->fileinfos['fileareasuffix'],
                                            $this->fileinfos['itemid'], $pathinfo['dirname'] . '/', $pathinfo['basename'], true);
                            $content .= "<li><a href='$url'>{$file['title']}</a></li>";
                        }
                        foreach ($files['attachedFiles'] as $file) {
                            $pathinfo = pathinfo($this->fileinfos['filepath'] . $file['filename']);
                            $url = \moodle_url::make_pluginfile_url($this->fileinfos['contextid'], COMPONENT_NAME,
                                            PROFORMA_RESPONSE_FILE_AREA . $this->fileinfos['fileareasuffix'],
                                            $this->fileinfos['itemid'], $pathinfo['dirname'] . '/', $pathinfo['basename'], true);
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

            return $content;
        }
    }

}
