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

        if (has_capability('mod/quiz:grade', $options->context)) {
            $o .= $this->output->heading(get_string('internaldescription', 'proforma'), 3);
            $o .= $this->output->box_start('generalbox boxaligncenter', 'internaldescription');
            $o .= $question->internaldescription;
            $o .= $this->output->box_end();
        }

        $files = $DB->get_records('qtype_programmingtask_files', array('questionid' => $questionid));
        $anythingtodisplay = false;
        if (count($files) != 0) {
            $downloadurls = '';
            $downloadurls .= $this->output->heading(get_string('providedfiles', 'proforma'), 3);
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

    /**
     * Generate the specific feedback. This is feedback that varies according to
     * the response the student gave. This method is only called if the display options
     * allow this to be shown.
     *
     * @param question_attempt $qa the question attempt to display.
     * @return string HTML fragment.
     */
    protected function specific_feedback(question_attempt $qa) {
        return parent::specific_feedback($qa);
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
