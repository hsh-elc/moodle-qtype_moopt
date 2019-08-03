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

/**
 * The programmingtask question renderer class is defined here.
 *
 * @package     qtype_programmingtask
 * @copyright   2019 ZLB-ELC Hochschule Hannover <elc@hs-hannover.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Generates the output for programmingtask questions.
 *
 * You should override functions as necessary from the parent class located at
 * /question/type/rendererbase.php.
 */
class qtype_programmingtask_renderer extends qtype_renderer {

    /**
     * Generates the display of the formulation part of the question. This is the
     * area that contains the quetsion text, and the controls for students to
     * input their answers. Some question types also embed bits of feedback, for
     * example ticks and crosses, in this area.
     *
     * @param question_attempt $qa the question attempt to display.
     * @param question_display_options $options controls what should and should not be displayed.
     * @return string HTML fragment.
     */
    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {
        global $DB;

        $o = parent::formulation_and_controls($qa, $options);

        $question = $qa->get_question();
        $qubaid = $qa->get_usage_id();
        $slot = $qa->get_slot();
        $questionid = $question->id;

        if (empty($options->readonly)) {
            $submissionfilearea = $this->renderSubmissionFileArea($qa, $options);
        } else {
            $submissionfilearea = $this->renderFilesReadOnly($qa, $options);
        }
        $o .= $this->output->heading(get_string('submissionfiles', 'qtype_programmingtask'), 3);
        $o .= html_writer::tag('div', $submissionfilearea, array('class' => 'submissionfilearea'));

        if (has_capability('mod/quiz:grade', $options->context)) {
            $internalDescription = $this->renderInternalDescription($question);
            $o .= html_writer::tag('div', $internalDescription, array('class' => 'internaldescription'));
        }

        $download_links = $this->renderDownloadLinks($qa, $options);
        $o .= html_writer::tag('div', $download_links, array('class' => 'downloadlinks'));


        return $o;
    }

    private function renderInternalDescription($question) {
        $o = '';
        $o .= $this->output->heading(get_string('internaldescription', 'qtype_programmingtask'), 3);
        $o .= $question->internaldescription;
        return $o;
    }

    private function renderDownloadLinks(question_attempt $qa, question_display_options $options) {
        global $DB;

        $question = $qa->get_question();
        $qubaid = $qa->get_usage_id();
        $slot = $qa->get_slot();
        $questionid = $question->id;
        $o = '';

        $files = $DB->get_records('qtype_programmingtask_files', array('questionid' => $questionid));
        $anythingtodisplay = false;
        if (count($files) != 0) {
            $downloadurls = '';
            $downloadurls .= $this->output->heading(get_string('providedfiles', 'qtype_programmingtask'), 3);
            $downloadurls .= $this->output->box_start('generalbox boxaligncenter', 'providedfiles');
            $downloadurls .= '<ul>';
            foreach ($files as $file) {
                if ($file->visibletostudents == 0 && !has_capability('mod/quiz:grade', $options->context)) {
                    continue;
                }
                $anythingtodisplay = true;
                $url = moodle_url::make_pluginfile_url($question->contextid, 'question', $file->filearea, "$qubaid/$slot/$questionid", $file->filepath, $file->filename, in_array($file->usagebylms, array('download', 'edit')));
                $linkdisplay = ($file->filearea == proforma_ATTACHED_TASK_FILES_FILEAREA ? $file->filepath : '') . $file->filename;
                $downloadurls .= '<li><a href="' . $url . '">' . $linkdisplay . '</a></li>';
            }
            $downloadurls .= '</ul>';
            $downloadurls .= $this->output->box_end();

            if ($anythingtodisplay) {
                $o .= $downloadurls;
            }
        }
        return $o;
    }

    private function renderFilesReadOnly(question_attempt $qa, question_display_options $options) {
        $files = $qa->get_last_qt_files('answerfiles', $options->context->id);
        $output = array();

        foreach ($files as $file) {
            $output[] = html_writer::tag('p', html_writer::link($qa->get_response_file_url($file), $this->output->pix_icon(file_file_icon($file), get_mimetype_description($file), 'moodle', array('class' => 'icon')) . ' ' . s($file->get_filename())));
        }
        return implode($output);
    }

    private function renderSubmissionFileArea(question_attempt $qa, question_display_options $options) {
        global $CFG;
        require_once($CFG->dirroot . '/lib/form/filemanager.php');

        $pickeroptions = new stdClass();
        $pickeroptions->itemid = $qa->prepare_response_files_draft_itemid(
                'answerfiles', $options->context->id);
        $pickeroptions->context = $options->context;

        $fm = new form_filemanager($pickeroptions);
        $filesrenderer = $this->page->get_renderer('core', 'files');

        //This is moodles weird way to express which file manager is responsible for which response variable
        $hidden = html_writer::empty_tag(
                        'input', array('type' => 'hidden', 'name' => $qa->get_qt_field_name('answerfiles'),
                    'value' => $pickeroptions->itemid));

        return $filesrenderer->render($fm) . $hidden;
    }

    /**
     * Generate the specific feedback. This is feedback that varies according to
     * the response the student gave. This method is only called if the display options
     * allow this to be shown.
     *
     * @param question_attempt $qa the question attempt to display.
     * @return string HTML fragment.
     */
    protected function specific_feedback(question_attempt $qa) {
        global $PAGE;
        if ($qa->get_state() == question_state::$finished) {

            $PAGE->requires->js_call_amd('qtype_programmingtask/pull_grading_status', 'init', [$qa->get_usage_id()]);
            $loader = '<div class="loader"></div>';
            return html_writer::div(get_string('currentlybeeinggraded', 'qtype_programmingtask') . $loader, 'gradingstatus');
        } else if ($qa->get_state() == question_state::$needsgrading) {
            return html_writer::div(get_string('needsgradingbyteacher', 'qtype_programmingtask'), 'gradingstatus');
        }
        return '';
    }

    /**
     * Generates an automatic description of the correct response to this question.
     * Not all question types can do this. If it is not possible, this method
     * should just return an empty string.
     *
     * @param question_attempt $qa the question attempt to display.
     * @return string HTML fragment.
     */
    protected function correct_response(question_attempt $qa) {
        return parent::correct_response($qa);
    }

}
