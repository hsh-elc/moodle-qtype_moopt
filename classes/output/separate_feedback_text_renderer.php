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

    public function __construct($root_node, $displayTeacherContent, $fileinfos) {
        $this->root_node = $root_node;
        $this->displayTeacherContent = $displayTeacherContent;
        $this->fileinfos = $fileinfos;
    }

    public function render() {
        return $this->renderInternal($this->root_node);
    }

    private function renderInternal(separate_feedback_text_node $node) {
        $accordionId = "accordion_{$node->getId()}";
        $text = "<div id='$accordionId'>";
        $text .= "<div class='card'>
                    <div class='card-header' id='{$node->getId()}'>
                      <h5 class='mb-0'>
                        <button type='button' class='btn btn-link' data-toggle='collapse' data-target='#collapse_{$node->getId()}' aria-expanded='true' aria-controls='collapse_{$node->getId()}'>
                            {$this->formatHeading($node)}
                        </button>
                      </h5>
                    </div>";

        $text .= "<div id='collapse_{$node->getId()}' class='collapse' aria-labelledby='heading_{$node->getId()}' data-parent='#accordion_$accordionId'>
                    <div class='card-body'>";

        $text .= $this->formatContent($node);

        $text .= "</div></div></div>";

        $text .= '</div>';

        return $text;
    }

    private function formatHeading(separate_feedback_text_node $node) {
        $score = round($node->getScore(), 2);
        $heading = $node->getHeading();
        if ($node->getAccumulatorFunction() != null) {
            $heading .= " , " . get_string('score', 'qtype_programmingtask') . " = $score";
        } else {
            if ($node->getTitle() != null) {
                $heading .= " '{$node->getTitle()}', " . get_string('score', 'qtype_programmingtask') . " = $score";
            }
        }
        if ($node->isNullified()) {
            $heading .= ' (' . get_string('hasbeennullified', 'qtype_programmingtask') . ')';
        }
        return $heading;
    }

    private function formatContent(separate_feedback_text_node $node) {
        if (!empty($node->getChildren())) {
            $content = '';
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

            $content .= '<div>';
            foreach ($node->getChildren() as $child) {
                $content .= $this->renderInternal($child);
            }
            $content .= '</div>';

            return $content;
        } else {
            $content = '';
            if ($node->getDescription() != null || ($node->getInternalDescription() != null && $this->displayTeacherContent)) {
                if ($node->getDescription() != null) {
                    $content .= "<div><h4>" . get_string('testdescription', 'qtype_programmingtask') . "</h4><i><p>{$node->getDescription()}</p></i></div>";
                }
                if ($this->displayTeacherContent && $node->getInternalDescription() != null) {
                    $content .= "<div><h4>" . get_string('internaldescription', 'qtype_programmingtask') . "</h4><i><p>{$node->getInternalDescription()}</p></i></div>";
                }
            }
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
