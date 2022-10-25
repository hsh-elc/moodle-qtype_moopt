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
 * The moopt question renderer class is defined here.
 *
 * @package     qtype_moopt
 * @copyright   2019 ZLB-ELC Hochschule Hannover <elc@hs-hannover.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../locallib.php');

use qtype_moopt\utility\proforma_xml\grading_scheme_handler;
use qtype_moopt\output\grading_hints_renderer;
use qtype_moopt\utility\communicator\communicator_factory;
use qtype_moopt\utility\proforma_xml\separate_feedback_handler;

/**
 * Generates the output for MooPT questions.
 *
 * You should override functions as necessary from the parent class located at
 * /question/type/rendererbase.php.
 */
class qtype_moopt_renderer extends qtype_renderer {

    public $generalfeedbacktemp;

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
        global $DB, $PAGE;

        // Temporarly hide the generalfeedback until the grader returns a response file
        // Since the general feedback may contain the solution to the question,
        // hiding the general feedback is neccessary for the fellowing reason:
        // A grader may return a Proforma response file with the is-internal-error attribute set to true, allowing
        // the student to redo their attempt -- by then the student will already have seen the correct solution in the
        // feedback.
        // Do this unless the question has the manual graded behaviour.
        // Since the manual grading can not fail displaying the solution at this point
        // don´t give the student an advantage
        if (!$qa->get_behaviour() instanceof qbehaviour_manualgraded){
            $this->generalfeedbacktemp = $qa->get_question()->generalfeedback;
            $qa->get_question()->generalfeedback = '<p />';
        }

        $o = "";


        $laststep = $qa->get_last_step();

        // Question is queued for grading, print the message that the question have been queued for grading
        if ($qa->get_state() == question_state::$finished || $laststep->has_behaviour_var('_completeForGrading')) {
            $o .= "<div class='specificfeedback queuedforgrading'>";
            $loader = '<div class="loader"></div>';
            $o .= html_writer::div(get_string('currentlybeinggraded', 'qtype_moopt') . $loader, 'gradingstatus');
            $o .= '<div style="display: none;" class="estimatedSecondsRemaining_' . $qa->get_question_id() . '">' . '(<span class="estimatedSecondsRemainingValue_' . $qa->get_question_id() . '"></span> '. get_string('estimatedSecondsRemaining', 'qtype_moopt') . ')</div>';
            $o .= "</div><br>";
        }

        try {
            $onlinegraders = communicator_factory::get_instance()->get_graders();
            $record = $DB->get_record('qtype_moopt_options', ['questionid' => $qa->get_question()->id], 'gradername, graderversion');
            $found = false;
            foreach($onlinegraders['graders'] as $grader) {
                if ($grader['name'] === $record->gradername &&
                    $grader['version'] === $record->graderversion) {
                    $found = true;
                    break;
                }
            }
        } catch (Exception $ex) {
            $found= false;
        }

        if(!$found) {
            $o = '<div class="alertlabel">' . get_string('gradercurrentlynotavailable', 'qtype_moopt') . '</div>';
        }


        $o .= parent::formulation_and_controls($qa, $options);

        $question = $qa->get_question();
        $qubaid = $qa->get_usage_id();
        $slot = $qa->get_slot();
        $questionid = $question->id;

        // Load ace scripts.
        $plugindirrel = '/question/type/moopt';
        $PAGE->requires->js($plugindirrel . '/ace/ace.js');
        $PAGE->requires->js($plugindirrel . '/ace/ext-language_tools.js');
        $PAGE->requires->js($plugindirrel . '/ace/ext-modelist.js');

//        // Display score calculation scheme on the task submission page.
//        // Do so for teachers and if students are allowed to see the scheme
//        if(has_capability('mod/quiz:grade', $options->context) ||
//            $qa->get_question()->showstudscorecalcscheme) {
//            $schema = $this->render_score_calculation_scheme($question, $qa);
//            $o .= html_writer::tag('div', $schema, array('class' => 'scorecalculationschema'));
//        }

        $downloadlinks = $this->render_downloadable_files($qa, $options);
        $o .= html_writer::tag('div', $downloadlinks, array('class' => 'downloadlinks'));

        if (empty($options->readonly)) {
            $submissionarea = $this->render_submission_area($qa, $options);
        } else {
            $submissionarea = $this->render_files_read_only($qa, $options);
        }
        $o .= $this->output->heading(get_string('submission', 'qtype_moopt'), 3);
        $o .= html_writer::tag('div', $submissionarea, array('class' => 'submissionfilearea'));

        $PAGE->requires->js_call_amd('qtype_moopt/textareas', 'setupAllTAs');

        if (has_capability('mod/quiz:grade', $options->context) && $question->internaldescription != '') {
            $internaldescription = $this->render_internal_description($question);
            $o .= html_writer::tag('div', $internaldescription, array('class' => 'internaldescription'));
        }
        if (!$qa->get_state()->is_graded() && ($qa->get_question()->showstudgradingscheme || has_capability('mod/quiz:grade', $PAGE->context))) {
            $gradingscheme = $this->render_grading_scheme($qa);
            $o .= html_writer::tag('div', $gradingscheme);
        }
        if ($qa->get_state()->is_finished() || $laststep->has_behaviour_var('_completeForGrading')) {
            // state->is_finished() implies that a question attempt has been finished by the student,
            // i.e. the student was sure enough to click the button to submit their solution.
            // The question attempt's state is not 'todo', 'invalid', or anything like that anymore
            // (for which case the is_finished() would return a 'false' value).
            if ($qa->get_state() == question_state::$finished || $laststep->has_behaviour_var('_completeForGrading')) {
                // Check if the question attempt's state is set to a particular state: question_state::$finished.
                // This is not the same as checking whether the state finished via is_finished() (as mentioned above,
                // a few other states return is_finished()==true (e.g. needsgrading, gaveup, and finished)).
                // We only care about pulling for a grading result in this particular state since that's
                // the only one that has a potential grading result lined up for polling.
                // If the state is anything other than $finished (such as 'gradedright' or 'gradedwrong'), we do
                // not pull for a grading result, since it's likely been already pulled in the past.
                $PAGE->requires->js_call_amd('qtype_moopt/pull_grading_status', 'init', [$qa->get_usage_id(), $qa->get_slot(),
                    get_config("qtype_moopt","service_client_polling_interval") * 1000 /* to milliseconds */]);
            }
        }
        return $o;
    }

    private function render_internal_description($question) {
        $o = '';
        $o .= $this->output->heading(get_string('internaldescription', 'qtype_moopt'), 3);
        $o .= $question->internaldescription;
        return $o;
    }

    /**
     * @throws coding_exception
     */
    private function render_grading_scheme(question_attempt $qa) {
        global $PAGE;

        $o = '<br>';
        $o .= $this->output->heading(get_string('gradingscheme', 'qtype_moopt'), 3);#

        $blockid = "moopt-gradingscheme-" . $qa->get_usage_id() . "-" . $qa->get_slot();
        $PAGE->requires->js_call_amd('qtype_moopt/toggle_all_grading_scheme_buttons', 'init', [$blockid]);
        $o .= "<p class='expandcollapselink'><a href='#' id='" . $blockid . "-expand-all-button'>"
            . get_string('expand_all', 'qtype_moopt') . "</a> ";
        $o .= "<a href='#' id='" . $blockid . "-collapse-all-button'>"
            . get_string('collapse_all', 'qtype_moopt') . "</a></p>";

        $taskxmlfile = get_task_xml_file_from_filearea($qa->get_question());
        $taskdoc = new DOMDocument();
        $taskdoc->loadXML($taskxmlfile->get_content());
        $taskxmlnamespace = detect_proforma_namespace($taskdoc);
        $gradinghints = $taskdoc->getElementsByTagNameNS($taskxmlnamespace, 'grading-hints')[0];
        $tests = $taskdoc->getElementsByTagNameNS($taskxmlnamespace, 'tests')[0];

        $xpathtask = new DOMXPath($taskdoc);
        $xpathtask->registerNamespace('p', $taskxmlnamespace);

        $gradingschemehelper = new grading_scheme_handler($gradinghints, $tests, $taskxmlnamespace, $qa->get_max_mark(), $xpathtask);
        $gradingschemehelper->build_grading_scheme();

        $gradinghintsrenderer = new grading_hints_renderer($gradingschemehelper->get_grading_scheme(),
            has_capability('mod/quiz:grade', $PAGE->context));

        $o .= "<div id='$blockid'>";
        $o .= $gradinghintsrenderer->render();
        $o .= "</div>";

        return $o;
    }


    private function render_downloadable_files(question_attempt $qa, question_display_options $options) {
        global $DB;

        $question = $qa->get_question();
        $qubaid = $qa->get_usage_id();
        $slot = $qa->get_slot();
        $questionid = $question->id;
        $o = '';
        $isteacher = has_capability('mod/quiz:grade', $options->context);

        $files = $DB->get_records('qtype_moopt_files', array('questionid' => $questionid));
        $anythingtodisplay = false;
        if (count($files) != 0) { // TODO: this check should happen before these render methods are called
            $downloadurls = '';
            $downloadurls .= $this->output->heading(get_string('providedfiles', 'qtype_moopt'), 3);
            $downloadurls .= html_writer::start_div('providedfiles');
            $downloadurls .= '<ul>';
            foreach ($files as $file) {
                // skip files that are
                // - not configured to be downloadable (usagebylms)
                // - not visible to students
                if ($file->usagebylms == 'display'
                    || ($file->visibletostudents == 'no' && !$isteacher)
                    || ($file->usagebylms == 'edit' && !$isteacher)) {
                    continue;
                }

                $anythingtodisplay = true;
                $url = moodle_url::make_pluginfile_url($question->contextid, COMPONENT_NAME, $file->filearea,
                                "$qubaid/$slot/$questionid", $file->filepath, $file->filename, true);
                if ($file->filearea == PROFORMA_ATTACHED_TASK_FILES_FILEAREA) {
                    $folderdisplay = $file->filepath;
                    // remove leading slash:
                    if (strlen($folderdisplay) > 0 && $folderdisplay[0] == '/') $folderdisplay = substr($folderdisplay, 1);
                } else {
                    $folderdisplay = '';
                }
                $linkdisplay = $folderdisplay . $file->filename;
                $downloadurls .= '<li><a href="' . $url . '">' . $linkdisplay . '</a></li>';
            }
            $downloadurls .= '</ul>';
            $downloadurls .= html_writer::end_div('providedfiles');

            if ($anythingtodisplay) {
                $o .= $downloadurls;
            }
        }
        return $o;
    }

    /**
     * Renders student-submitted files for download.
     *
     * @param question_attempt $qa
     * @param question_display_options $options
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    private function render_files_read_only(question_attempt $qa, question_display_options $options) {
        global $DB, $PAGE;

        $rendered = '';

        if ($qa->get_question()->enablefilesubmissions) {

            $files = $qa->get_last_qt_files('answer', $options->context->id);
            if (!empty($files)) {
                $output = array();

                foreach ($files as $file) {
                    $output[] = html_writer::tag('p', html_writer::link($qa->get_response_file_url($file),
                                            $this->output->pix_icon(file_file_icon($file), get_mimetype_description($file),
                                                    'moodle', array('class' => 'icon')) . ' ' . s($file->get_filename())));
                }
                $rendered .= $this->output->heading(get_string('files', 'qtype_moopt'), 4);
                $rendered .= html_writer::div(implode($output), 'readonlysubmittedfiles');
            }
        }

        if ($qa->get_question()->enablefreetextsubmissions) {
            $renderedfreetext = '';

            $questionoptions = $DB->get_record('qtype_moopt_options', ['questionid' => $qa->get_question()->id]);
            $defaultproglang = $questionoptions->ftsstandardlang;

            for ($i = 0; $i < $qa->get_question()->ftsmaxnumfields; $i++) {
                $text = $qa->get_last_qt_var("answertext$i");

                if(is_null($text)) { // TODO: test this code block
                    $customoptions = $DB->get_record('qtype_moopt_freetexts', ['questionid' => $qa->get_question()->id,
                        'inputindex' => $i]);
                    if ($customoptions && !is_null($customoptions->filecontent)) {
                        $text = $customoptions->filecontent;
                    }
                }

                if ($text) {
                    list($filename, ) = $this->get_filename($qa->get_question()->id, $i, $qa->get_last_qt_var("answerfilename$i"),
                            $qa->get_question()->ftsautogeneratefilenames);

                    $customoptions = $DB->get_record('qtype_moopt_freetexts', ['questionid' => $qa->get_question()->id,
                        'inputindex' => $i]);

                    $proglang = $defaultproglang;
                    if ($customoptions && $customoptions->ftslang != 'default') {
                        $proglang = $customoptions->ftslang;
                    }

                    // Adjust the height of the textarea based on the content of the textarea.
                    // The 'rows' attribute will be interpreted by the javascript userinterfacewrapper.js
                    // to adapt the CSS height of the editor.
                    $textarearows = $this->calc_rows(($customoptions ? $customoptions->initialdisplayrows : DEFAULT_INITIAL_DISPLAY_ROWS), $text);

                    $textarea_id = "qtype_moopt_answertext_" . $qa->get_question_id() . "_" . $i;
                    $renderedfreetext .= html_writer::start_div('answertextreadonly');
                    $renderedfreetext .= html_writer::tag('div', mangle_pathname($filename) . ' (' .
                                    PROFORMA_ACE_PROGLANGS[$proglang] . ')' . ':');
                    $renderedfreetext .= html_writer::tag('div', html_writer::tag('textarea', $text, array('id' => $textarea_id,
                                        'style' => 'width: 100%;padding-left: 10px;height: 14px;', 'class' => 'edit_code',
                                        'data-lang' => $proglang, 'readonly' => '', 'rows' => $textarearows)));
                    $renderedfreetext .= html_writer::end_div();

                    $PAGE->requires->js_call_amd('qtype_moopt/userinterfacewrapper', 'newUiWrapper',
                            ['ace', $textarea_id]);
                }
            }

            if ($renderedfreetext != '') {
                $rendered .= $this->output->heading(get_string('freetextsubmissions', 'qtype_moopt'), 4);
                $rendered .= html_writer::div($renderedfreetext, 'readonlysubmittedfreetext');
            }
        }

        return $rendered;
    }

    private function render_submission_area(question_attempt $qa, question_display_options $options) {
        global $CFG, $DB, $PAGE;
        require_once($CFG->dirroot . '/lib/form/filemanager.php');
        require_once($CFG->dirroot . '/repository/lib.php');

        $questionoptions = $DB->get_record('qtype_moopt_options', ['questionid' => $qa->get_question()->id]);

        $renderedarea = '';

        $itemid = null;
        $filemanagerid = null;
        $answertextids = array();

        if ($questionoptions->enablefilesubmissions) {
            $pickeroptions = new stdClass();
            $pickeroptions->itemid = $qa->prepare_response_files_draft_itemid(
                    'answer', $options->context->id);
            $pickeroptions->context = $options->context;

            $fm = new form_filemanager($pickeroptions);
            $filesrenderer = $this->page->get_renderer('core', 'files');

            // This is moodles weird way to express which file manager is responsible for which response variable.
            $hidden = html_writer::empty_tag(
                            'input', array('type' => 'hidden', 'name' => $qa->get_qt_field_name('answer'),
                        'value' => $pickeroptions->itemid));

            $renderedarea .= $filesrenderer->render($fm) . $hidden;

            $filemanagerid = 'filemanager-'.$fm->options->client_id;
            $itemid = $pickeroptions->itemid;
        }
        if ($questionoptions->enablefreetextsubmissions) {

            if ($renderedarea != '') {
                $renderedarea .= html_writer::tag('hr', '');
            }

            $autogeneratefilenames = $questionoptions->ftsautogeneratefilenames;
            $maxindexoffieldwithcontent = 0;

            $defaultproglang = $questionoptions->ftsstandardlang;

            for ($i = 0; $i < (int)$questionoptions->ftsmaxnumfields; $i++) {
                $customoptions = $DB->get_record('qtype_moopt_freetexts', ['questionid' => $qa->get_question()->id,
                    'inputindex' => $i]);

                $answertextname = "answertext$i";
                $answertextinputname = $qa->get_qt_field_name($answertextname);
                $answertextid = $answertextinputname . '_id';

                $answertextids[$i] = 'qtype_moopt_answertext_' . $qa->get_question_id() . "_$i";

                $answertextresponse = $qa->get_last_step_with_qt_var($answertextname)->get_qt_var($answertextname);
                if(!isset($answertextresponse)) {
                    if ($customoptions && !is_null($customoptions->filecontent))
                        $answertextresponse = $customoptions->filecontent;
                    else
                        $answertextresponse = '';
                }

                $filenamename = "answerfilename$i";
                $filenameinputname = $qa->get_qt_field_name($filenamename);
                $filenameid = $filenameinputname . '_id';

                $proglang = $defaultproglang;
                if ($customoptions && $customoptions->ftslang != 'default') {
                    $proglang = $customoptions->ftslang;
                }

                list($filenameresponse, $disablefilenameinput) = $this->get_filename($qa->get_question()->id, $i,
                        $qa->get_last_step_with_qt_var($filenamename)->get_qt_var($filenamename), $autogeneratefilenames);

                $output = '';
                $output .= html_writer::start_tag('div', array('class' => "qtype_moopt_answertext",
                        'id' =>  $answertextids[$i]));
                $output .= html_writer::start_div('answertextfilename');
                $output .= html_writer::label(get_string('filename', 'qtype_moopt') . ":", $filenameid);
                $inputoptions = ['id' => $filenameid, 'name' => $filenameinputname, 'style' => 'width: 100%;padding-left: 10px;',
                    'value' => $filenameresponse];
                if ($disablefilenameinput) {
                    // readonly is used instead of disabled because the data in this field would not be submitted if it is disabled
                    $inputoptions['readonly'] = true;
                    $inputoptions['style'] .= "color: grey;";
                }

                // Adjust the height of the textarea based on the content of the textarea
                // The 'rows' attribute will be interpreted by the javascript userinterfacewrapper.js
                // to adapt the CSS height of the editor.
                $textarearows = $this->calc_rows(($customoptions ? $customoptions->initialdisplayrows : DEFAULT_INITIAL_DISPLAY_ROWS), $answertextresponse);

                $output .= html_writer::tag('input', '', $inputoptions);
                $output .= html_writer::end_div();
                $output .= html_writer::div(get_string('yourcode', 'qtype_moopt') . ' (' .
                                get_string('programminglanguage', 'qtype_moopt') . ': ' .
                                PROFORMA_ACE_PROGLANGS[$proglang] . '):');
                $output .= html_writer::tag('div', html_writer::tag('textarea', $answertextresponse, array('id' => $answertextid,
                                    'name' => $answertextinputname, 'style' => 'width: 100%;padding-left: 10px;height: 14px;',
                                    'class' => 'edit_code', 'data-lang' => $proglang, 'rows' => $textarearows)));
                $output .= html_writer::end_tag('div');

                $renderedarea .= $output;

                if ($answertextresponse != '') {
                    $maxindexoffieldwithcontent = $i + 1;
                }

                $PAGE->requires->js_call_amd('qtype_moopt/userinterfacewrapper', 'newUiWrapper', ['ace', $answertextid]);
            }
            $renderedarea .= html_writer::start_div('', ['style' => 'display:flex;justify-content:flex-end;']);
            $renderedarea .= html_writer::tag('button', get_string('addanswertext', 'qtype_moopt'),
                            ['id' => 'addAnswertextButton_' . $qa->get_question_id()]);
            $renderedarea .= html_writer::tag('button', get_string('removelastanswertext', 'qtype_moopt'),
                            ['id' => 'removeLastAnswertextButton_' . $qa->get_question_id(), 'style' => 'margin-left: 10px']);
            $renderedarea .= html_writer::end_div();

            $PAGE->requires->js_call_amd('qtype_moopt/manage_answer_texts', 'init',
                    [(int)$questionoptions->ftsmaxnumfields, max($maxindexoffieldwithcontent, (int)$questionoptions->ftsnuminitialfields), $qa->get_question_id()]);
        }

        if ($qa->get_behaviour_name() == 'immediatemoopt') {
            if ($questionoptions->enablefilesubmissions && $questionoptions->enablefreetextsubmissions) {
                $PAGE->requires->js_call_amd('qtype_moopt/disable_check_button_for_incomplete_submissions',
                    'initForFileAndFreetextSubmissions', [$qa->get_behaviour_field_name('submit'), $filemanagerid, $itemid, $answertextids]);
            } elseif ($questionoptions->enablefilesubmissions) {
                $PAGE->requires->js_call_amd('qtype_moopt/disable_check_button_for_incomplete_submissions',
                    'initForFileSubmissions', [$qa->get_behaviour_field_name('submit'), $filemanagerid, $itemid]);
            } elseif ($questionoptions->enablefreetextsubmissions) {
                $PAGE->requires->js_call_amd('qtype_moopt/disable_check_button_for_incomplete_submissions',
                    'initForFreetextSubmissions', [$qa->get_behaviour_field_name('submit'), $answertextids]);
            }
        }

        if ($renderedarea == '') {
            $nosubmissionpossible = get_string('nosubmissionpossible', 'qtype_moopt');
            $renderedarea = "<div>$nosubmissionpossible</div>";
        }

        return $renderedarea;
    }

    private function get_filename($questionid, $index, $usersuppliedname, $autogeneratefilenames) {
        global $DB;

        $customoptions = $DB->get_record('qtype_moopt_freetexts', ['questionid' => $questionid, 'inputindex' => $index]);
        $filenameresponse = $usersuppliedname ?? '';         // Init with previous value.
        if ($filenameresponse == '') {
            $temp = $index + 1;
            $filenameresponse = "File$temp.txt";
        }
        $disablefilenameinput = false;
        if ($customoptions) {
            if ($customoptions->presetfilename) {
                $filenameresponse = $customoptions->filename;
                $disablefilenameinput = true;
            }
            // Else use already set previous value.
        } else {
            if ($autogeneratefilenames) {
                $temp = $index + 1;
                $filenameresponse = "File$temp.txt";
                $disablefilenameinput = true;
            }
            // Else use already set previous value.
        }
        return [$filenameresponse, $disablefilenameinput];
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
        global $PAGE, $DB;
        $laststep = $qa->get_last_step();
        if ($qa->get_state()->is_finished() || $laststep->has_behaviour_var('_showGradedFeedback') ||
            $laststep->has_behaviour_var('_completeForGrading')) {

            $PAGE->requires->js_call_amd('qtype_moopt/change_display_name_of_redo_button', 'init', [$qa->get_slot()]);

            if ($qa->get_state() == question_state::$finished || $laststep->has_behaviour_var('_completeForGrading')) {
                $loader = '<div class="loader"></div>';
                $o = html_writer::div(get_string('currentlybeinggraded', 'qtype_moopt') . $loader, 'gradingstatus');
                $o .= '<div style="display: none;" class="estimatedSecondsRemaining_' . $qa->get_question_id() . '">' . '(<span class="estimatedSecondsRemainingValue_' . $qa->get_question_id() . '"></span> '. get_string('estimatedSecondsRemaining', 'qtype_moopt') . ')</div>';
                return $o;
            } else if ($qa->get_state() == question_state::$gaveup) {
                return get_string('gaveup', 'qtype_moopt');
            } else if ($qa->get_state() == question_state::$needsgrading && !has_capability('mod/quiz:grade', $PAGE->context)) {
                // If a teacher is looking at this feedback and we did receive a valid response but it has an
                // internal-error-attribute we still want to display this result.
                return html_writer::div(get_string('needsgradingbyteacher', 'qtype_moopt'), 'gradingstatus');
            } else if ($qa->get_state()->is_graded() || $laststep->has_behaviour_var('_showGradedFeedback') ||
                (has_capability('mod/quiz:grade', $PAGE->context) && $qa->get_state() == question_state::$needsgrading)) {

                $qubarecord = $DB->get_record('question_usages', ['id' => $qa->get_usage_id()]);

                $fs = get_file_storage();

                $html = '';

                $responsexmlfile = $fs->get_file($qa->get_question()->contextid, COMPONENT_NAME,
                    PROFORMA_RESPONSE_FILE_AREA,
                    $qa->get_database_id(), "/", 'response.xml');
                if ($responsexmlfile) {
                    $doc = new DOMDocument();

                    set_error_handler(function ($number, $error) {
                        if (preg_match('/^DOMDocument::loadXML\(\): (.+)$/', $error, $m) === 1) {
                            throw new Exception($m[1]);
                        }
                    });
                    try {
                        $doc->loadXML($responsexmlfile->get_content());
                    } catch (Exception $ex) {
                        $doc = false;
                    } finally {
                        restore_error_handler();
                    }

                    try {
                        if ($doc) {
                            $namespace = detect_proforma_namespace($doc);

                            $separatetestfeedbacklist = $doc->getElementsByTagNameNS($namespace, "separate-test-feedback");

                            if ($separatetestfeedbacklist->length == 1) {
                                // Separate test feedback.
                                $separatetestfeedbackelem = $separatetestfeedbacklist[0];

                                // Load task.xml to get grading hints and tests.
                                $fs = get_file_storage();

                                $taskxmlfile = get_task_xml_file_from_filearea($qa->get_question());
                                $taskdoc = new DOMDocument();
                                $taskdoc->loadXML($taskxmlfile->get_content());
                                $taskxmlnamespace = detect_proforma_namespace($taskdoc);
                                $gradinghints = $taskdoc->getElementsByTagNameNS($taskxmlnamespace, 'grading-hints')[0];
                                $tests = $taskdoc->getElementsByTagNameNS($taskxmlnamespace, 'tests')[0];
                                $feedbackfiles = $doc->getElementsByTagNameNS($namespace, "files")[0];

                                $xpathtask = new DOMXPath($taskdoc);
                                $xpathtask->registerNamespace('p', $taskxmlnamespace);

                                $separatefeedbackhelper = new separate_feedback_handler($gradinghints, $tests,
                                        $separatetestfeedbackelem, $feedbackfiles, $taskxmlnamespace, $namespace,
                                        $qa->get_max_mark(), $xpathtask);
                                $separatefeedbackhelper->process_result();

                                // File download URLs of files that are attached to the response
                                // are rendered for the question component as follows:
                                // protocol://host:port/moodle/pluginfile.php/<context-id>/question/<filearea>_<qa-id>/<quba-id>/<slot>/<quba-id>/<filepath>/<filename>
                                // where context-id denotes a context of context level "module"
                                // and qa-id denotes the database id of the question attempt
                                // and quba-id denotes the database id of the question usage by activity object.
                                //
                                // The URL gets processed as a download request by the following call chain:
                                //  1. lib/filelib.php: function file_pluginfile
                                //  2. lib/questionlib.php: function question_pluginfile
                                //  3. the module using the question
                                //     e. g. mod/quiz/lib.php: function quiz_question_pluginfile
                                // Step 1. detects the context and the component and delegates to the question component.
                                // Step 2. strips of the <quba-id>/<slot> part of the URL and leaves <quba-id>/<filepath>/<filename> for the module
                                //         resulting in a key record of the file table as:
                                //         - component: question
                                //         - context: a context of level 70 (=module)
                                //         - filearea: <filearea>_<qa-id>,
                                //             where <filearea> is one of the labels responsefilesresponsefile, responsefiles or responsefilesembedded
                                //             and <qa-id> is the database id of a question attempt
                                //         - itemid: <quba-id>, i. e. the database id of a question usage by activity object
                                //         - filepath: the path inside a response.zip file  (or / in case of filearea=responsefilesresponsefile)
                                //         - filename: the filename inside the response.zip  (or the name of the zip file in case of filearea=responsefilesresponsefile)

                                $fileinfos = [
                                    'component' => COMPONENT_NAME,
                                    'itemid' => "{$qa->get_usage_id()}/{$qa->get_slot()}/{$qa->get_database_id()}",
                                    'contextid' => $qa->get_question()->contextid,
                                    'filepath' => "/"
                                ];
                                $feedbackblockid = "moopt-feedbackblock-" . $qa->get_usage_id() . "-" . $qa->get_slot();
                                $PAGE->requires->js_call_amd('qtype_moopt/toggle_all_grading_scheme_buttons', 'init', [$feedbackblockid]);

                                $separatefeedbackrenderersummarised = new grading_hints_renderer(
                                    $separatefeedbackhelper->get_summarised_feedback(),
                                    has_capability('mod/quiz:grade', $PAGE->context), $fileinfos,
                                    $qa->get_question()->showstudscorecalcscheme);
                                $html .= "<p class='expandcollapselink'><a href='#' id='" . $feedbackblockid . "-expand-all-button'>"
                                    . get_string('expand_all', 'qtype_moopt') . "</a> ";
                                $html .= "<a href='#' id='" . $feedbackblockid . "-collapse-all-button'>"
                                    . get_string('collapse_all', 'qtype_moopt') . "</a></p>";

                                $html .= "<div id='" . $feedbackblockid . "'>";
                                $html .= $separatefeedbackrenderersummarised->render();
                                $html .= '<p/>'; // vertical space between summarized and detailed feedback buttons
                                $separatefeedbackrendererdetailed = new grading_hints_renderer(
                                    $separatefeedbackhelper->get_detailed_feedback(), has_capability('mod/quiz:grade',
                                    $PAGE->context), $fileinfos, $qa->get_question()->showstudscorecalcscheme);
                                $html .= $separatefeedbackrendererdetailed->render();
                                $html .= '</div>';
                            } else {
                                // Merged test feedback.
                                $studentfb = $doc->getElementsByTagNameNS($namespace, "student-feedback")[0];
                                if ($studentfb) {
                                    $html .= html_writer::div($studentfb->nodeValue, 'studentfeedback');
                                }
                                $teacherfb = $doc->getElementsByTagNameNS($namespace, "teacher-feedback")[0];
                                if (has_capability('mod/quiz:grade', $PAGE->context) && $teacherfb) {
                                    $html .= '<hr/>';
                                    $html .= html_writer::div($teacherfb->nodeValue, 'teacherfeedback');
                                }
                            }

                            // Restore the (hidden) general feedback now that the grader has returned a
                            // response file (is-internal-error=false) indicating that everything is fine
                            $qa->get_question()->generalfeedback = $this->generalfeedbacktemp;
                        } else {
                            $html = html_writer::div('The response contains an invalid response.xml file', 'gradingstatus');
                        }
                    } catch (\qtype_moopt\exceptions\service_communicator_exception $ex) {
                        // We did get a xml-valid response but something was still wrong. Display that message.
                        $html = html_writer::div($ex->getMessage(), 'gradingstatus');
                    } catch (\exception $ex) {
                        // Catch anything weird that might happend during processing of the response.
                        $html = html_writer::div($ex->getMessage(), 'gradingstatus') . html_writer::div($ex->getTraceAsString(),
                                'gradingstatus');
                    } catch (\Error $er) {
                        // Catch anything weird that might happend during processing of the response.
                        $html = html_writer::div('Error code: ' . $er->getCode() . ". Message: " .
                                $er->getMessage(), 'gradingstatus') .
                            html_writer::div('Stack trace:<br/>' . $er->getTraceAsString(), 'gradingstatus');
                    }
                } else {
                    $html = html_writer::div('Response.zip doesn\'t contain a response.xml file', 'gradingstatus');
                }
                // If teacher, display response.zip for download.
                if (has_capability('mod/quiz:grade', $PAGE->context)) {
                    $slot = $qa->get_slot();

                    // check, if we have a response.zip file
                    $zipfileinfos = array(
                        'component' => COMPONENT_NAME,
                        'filearea' => PROFORMA_RESPONSE_FILE_AREA_RESPONSEFILE,
                        'itemid' => $qa->get_database_id(),
                        'contextid' => $qa->get_question()->contextid,
                        'filepath' => "/",
                        'filename' => 'response.zip');

                    $checkfile = $fs->get_file($zipfileinfos['contextid'], $zipfileinfos['component'], $zipfileinfos['filearea'],
                        $zipfileinfos['itemid'], $zipfileinfos['filepath'], $zipfileinfos['filename']);
                    if ($checkfile) {
                        // response.zip
                        $responsefileinfos = $zipfileinfos;
                    } else {
                        // response.xml
                        $responsefileinfos = array(
                            'component' => COMPONENT_NAME,
                            'filearea' => PROFORMA_RESPONSE_FILE_AREA,
                            'contextid' => $qa->get_question()->contextid,
                            'filepath' => "/",
                            'filename' => 'response.xml');
                    }
                    $responsefileinfos['itemid'] = "{$qa->get_usage_id()}/$slot/{$qa->get_database_id()}"; // see questionlib.php\question_pluginfile(...)

                    $url = moodle_url::make_pluginfile_url($responsefileinfos['contextid'], $responsefileinfos['component'],
                        $responsefileinfos['filearea'], $responsefileinfos['itemid'], $responsefileinfos['filepath'],
                        $responsefileinfos['filename'], true);
                    $downloadable_responsefilename = $responsefileinfos['filename'];
                    $html .= "<a href='$url' style='display:block;text-align:right;'>" .
                        " <span style='font-family: FontAwesome; display:inline-block;" .
                        "margin-right: 5px'>&#xf019;</span> " .
                        get_string('downloadcompletefile', 'qtype_moopt', $downloadable_responsefilename) . "</a>";
                }
                return $html;
            }
        }
        return '';
    }

    /**
     * Generates the output of an errormessage and also adds a button that will redirect to a specific url
     * @param string $err_msg The message that should be shown
     * @param string $redirect_url The url to which the button should redirect the user
     * @return string The html output of the errormessage
     * @throws coding_exception
     */
    public function render_error_msg(string $err_msg, string $redirect_url, int $courseid): string {
        $output = "<div class='box py-3 errorbox alert alert-danger'>" . $err_msg . "</div>";
        $output .= "<form method='get' action='$redirect_url'>";
        $output .= "<input type='hidden' id='id' name='id' value='$courseid'>";
        $output .= "<button class='btn btn-primary' type='submit'>" . get_string('continue', 'qtype_moopt') . "</button>";
        $output .= "</form></div>";
        return $output;
    }

    /**
     * Calculates the editor rows when displaying a given content
     * @param {int} minrows
     * @param {string} content
     * @return {int} the number of rows
     */
    public function calc_rows(int $minrows, string $content): int {
        return max($minrows, count(explode(PHP_EOL, $content)));
    }
}
