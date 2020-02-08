<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace qtype_programmingtask\output;

use qtype_programmingtask\utility\proforma_xml\separate_feedback_text_node;

/**
 * Description of separate_feedback_text_renderer
 *
 * @author robin
 */
class separate_feedback_text_renderer {

    private $root_node;
    private $displayTeacherContent;
    private $embeddedfileinfos;
    private $fileinfos;
    private $showStudentsScoreCalculationScheme;

    public function __construct($root_node, $displayTeacherContent, $fileinfos, $showStudentsScoreCalculationScheme) {
        $this->root_node = $root_node;
        $this->displayTeacherContent = $displayTeacherContent;
        $this->fileinfos = $fileinfos;
        $this->showStudentsScoreCalculationScheme = $showStudentsScoreCalculationScheme;
    }

    public function render() {
        return $this->renderInternal($this->root_node);
    }

    private function renderInternal(separate_feedback_text_node $node) {
        $accordionId = "accordion_{$node->getId()}";

        $additionalHeaderClasses = $node->hasInternalError() ? ', internalerror' : '';

        $text = "<div id='$accordionId'>";
        $text .= "<div class='card'>
                    <div class='card-header, {$additionalHeaderClasses}' id='{$node->getId()}'>
                      <h5 class='mb-0'>
                        <button type='button' class='btn btn-link' data-toggle='collapse' data-target='#collapse_{$node->getId()}' aria-expanded='true' aria-controls='collapse_{$node->getId()}' style='width: 100%;'>
                            {$this->formatHeading($node)}
                        </button>
                      </h5>
                    </div>";

        $text .= "<div id='collapse_{$node->getId()}' class='collapse' aria-labelledby='heading_{$node->getId()}' data-parent='#$accordionId'>
                    <div class='card-body'>";

        $text .= $this->formatContent($node);

        $text .= "</div></div></div>";

        $text .= '</div>';

        return $text;
    }

    private function formatHeading(separate_feedback_text_node $node) {
        $heading = '<div style="text-align:left;width:70%;display:inline-block;">';

        if ($node->hasInternalError()) {
            $heading .= '<div style="font-family: FontAwesome; font-size: 1.5em;margin-right:20px;display:inline-block;">&#xf00d;</div>';
        }

        $heading .= '<div style="vertical-align: text-bottom;display:inline-block;">' . $node->getHeading();
        if ($node->getTitle() != null) {
            $heading .= " '{$node->getTitle()}'";
        }
        $heading .= '</div>';
        $heading .= '</div><div style="text-align:right;vertical-align: text-bottom; width:30%;display:inline-block;">';
        if (!is_null($node->getScore())) {
            $score = round($node->getScore(), 2);
            $maxScore = round($node->getMaxScore(), 2);

            if (!is_null($node->getMaxScore())) {
                $heading .= "$score / $maxScore";
            } else {
                $heading .= "Score: $score";
            }
        }
        if ($node->isNullified()) {
            $heading .= ' (' . get_string('hasbeennullified', 'qtype_programmingtask') . ')';
        }
        $heading .= '</div>';
        return $heading;
    }

    private function formatContent(separate_feedback_text_node $node) {

        $content = '';
        if ($node->getDescription() != null || ($node->getInternalDescription() != null && $this->displayTeacherContent)) {
            if ($node->getDescription() != null) {
                $content .= "<div><h4>" . get_string('testdescription', 'qtype_programmingtask') . "</h4><i><p>{$node->getDescription()}</p></i></div>";
            }
            if ($this->displayTeacherContent && $node->getInternalDescription() != null) {
                $content .= "<div><h4>" . get_string('internaldescription', 'qtype_programmingtask') . "</h4><i><p>{$node->getInternalDescription()}</p></i></div>";
            }
        }

        if (!empty($node->getChildren())) {
            if ($this->displayTeacherContent || $this->showStudentsScoreCalculationScheme) {
                $content .= '<div align="right" style="margin-bottom: 10px">';
                $subScores = [];
                foreach ($node->getChildren() as $child) {
                    $subScores[] = round($child->getScore(), 2);
                }
                $scoreCalc = '<small>' . get_string('scorecalculationscheme', 'qtype_programmingtask') . ': ' . round($node->getScore(), 2) . ' = ';
                switch ($node->getAccumulatorFunction()) {
                    case 'min':
                        $scoreCalc .= get_string('minimum', 'qtype_programmingtask') . ' {';
                        $scoreCalc .= implode(', ', $subScores);
                        $scoreCalc .= '}';
                        break;
                    case 'max':
                        $scoreCalc .= get_string('maximum', 'qtype_programmingtask') . ' {';
                        $scoreCalc .= implode(', ', $subScores);
                        $scoreCalc .= '}';
                        break;
                    case 'sum':
                        $scoreCalc .= implode(' + ', $subScores);
                        break;
                }
                $content .= "<i>$scoreCalc</i></small></div>";
            }


            $content .= '<div>';
            foreach ($node->getChildren() as $child) {
                $content .= '<p>' . $this->renderInternal($child) . '</p>';
            }
            $content .= '</div>';

            return $content;
        } else {
            if (!empty($node->getStudentFeedback())) {
                $content .= '<div><h4>' . get_string('feedback', 'qtype_programmingtask') . '</h4>';
                foreach ($node->getStudentFeedback() as $studFeed) {
                    if ($studFeed['title'] != null) {
                        $content .= "<p><strong>{$studFeed['title']}</strong></p>";
                    }
                    $content .= "<p>{$studFeed['content']}</p>";
                    $files = $studFeed['files'];
                    if (!empty($files['embeddedFiles'] || !empty($files['attachedFiles']))) {
                        $content .= '<p>' . get_string('files', 'qtype_programmingtask') . ':<br/><ul>';
                        foreach ($files['embeddedFiles'] as $file) {
                            $pathinfo = pathinfo($this->fileinfos['filepath'] . $file['id'] . '/' . $file['filename']);
                            $url = \moodle_url::make_pluginfile_url($this->fileinfos['contextid'], 'question', proforma_RESPONSE_FILE_AREA_EMBEDDED . $this->fileinfos['fileareasuffix'], $this->fileinfos['itemid'], $pathinfo['dirname'] . '/', $pathinfo['basename'], true);
                            $content .= "<li><a href='$url'>{$file['title']}</a></li>";
                        }
                        foreach ($files['attachedFiles'] as $file) {
                            $pathinfo = pathinfo($this->fileinfos['filepath'] . $file['filename']);
                            $url = \moodle_url::make_pluginfile_url($this->fileinfos['contextid'], 'question', proforma_RESPONSE_FILE_AREA . $this->fileinfos['fileareasuffix'], $this->fileinfos['itemid'], $pathinfo['dirname'] . '/', $pathinfo['basename'], true);
                            $content .= "<li><a href='$url'>{$file['title']}</a></li>";
                        }
                        $content .= '</ul></p>';
                    }
                }
                $content .= '</div>';
            }
            if (!empty($node->getTeacherFeedback()) && $this->displayTeacherContent) {
                $content .= '<div><h4>' . get_string('teacherfeedback', 'qtype_programmingtask') . '</h4>';
                foreach ($node->getTeacherFeedback() as $teacherFeed) {
                    $content .= '<p>';
                    if ($teacherFeed['title'] != null) {
                        $content .= "<p><strong>{$teacherFeed['title']}</strong></p>";
                    }
                    $content .= "<p>{$teacherFeed['content']}</p>";
                    $files = $teacherFeed['files'];
                    if (!empty($files['embeddedFiles'] || !empty($files['attachedFiles']))) {
                        $content .= '<p>' . get_string('files', 'qtype_programmingtask') . ':<br/><ul>';
                        foreach ($files['embeddedFiles'] as $file) {
                            $pathinfo = pathinfo($this->fileinfos['filepath'] . $file['id'] . '/' . $file['filename']);
                            $url = \moodle_url::make_pluginfile_url($this->fileinfos['contextid'], 'question', proforma_RESPONSE_FILE_AREA_EMBEDDED . $this->fileinfos['fileareasuffix'], $this->fileinfos['itemid'], $pathinfo['dirname'] . '/', $pathinfo['basename'], true);
                            $content .= "<li><a href='$url'>{$file['title']}</a></li>";
                        }
                        foreach ($files['attachedFiles'] as $file) {
                            $pathinfo = pathinfo($this->fileinfos['filepath'] . $file['filename']);
                            $url = \moodle_url::make_pluginfile_url($this->fileinfos['contextid'], 'question', proforma_RESPONSE_FILE_AREA . $this->fileinfos['fileareasuffix'], $this->fileinfos['itemid'], $pathinfo['dirname'] . '/', $pathinfo['basename'], true);
                            $content .= "<li><a href='$url'>{$file['title']}</a></li>";
                        }
                        $content .= '</ul></p>';
                    }
                }
                $content .= '</div>';
            }

            if ($content == '') {
                $content = '<div><i>' . get_string('nofeedback', 'qtype_programmingtask') . '</i></div>';
            }

            return $content;
        }
    }

}
