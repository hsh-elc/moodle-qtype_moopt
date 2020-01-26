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

require_once(__DIR__ . '/../../locallib.php');

use qtype_programmingtask\utility\proforma_xml\separate_feedback_handler;

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
            $submissionarea = $this->renderSubmissionArea($qa, $options);
        } else {
            $submissionarea = $this->renderFilesReadOnly($qa, $options);
        }
        $o .= $this->output->heading(get_string('submission', 'qtype_programmingtask'), 3);
        $o .= html_writer::tag('div', $submissionarea, array('class' => 'submissionfilearea'));

        if (has_capability('mod/quiz:grade', $options->context) && $question->internaldescription != '') {
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
            $downloadurls .= html_writer::start_div('providedfiles');
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
            $downloadurls .= html_writer::end_div('providedfiles');

            if ($anythingtodisplay) {
                $o .= $downloadurls;
            }
        }
        return $o;
    }

    private function renderFilesReadOnly(question_attempt $qa, question_display_options $options) {
        global $DB;

        $rendered = '';

        if ($qa->get_question()->enablefilesubmissions) {

            $files = $qa->get_last_qt_files('answerfiles', $options->context->id);
            if (!empty($files)) {
                $output = array();

                foreach ($files as $file) {
                    $output[] = html_writer::tag('p', html_writer::link($qa->get_response_file_url($file), $this->output->pix_icon(file_file_icon($file), get_mimetype_description($file), 'moodle', array('class' => 'icon')) . ' ' . s($file->get_filename())));
                }
                $rendered .= $this->output->heading(get_string('files', 'qtype_programmingtask'), 4);
                $rendered .= html_writer::div(implode($output), 'readonlysubmittedfiles');
            }
        }

        if ($qa->get_question()->enablefreetextsubmissions) {
            $renderedfreetext = '';
            for ($i = 0; $i < $qa->get_question()->ftsmaxnumfields; $i++) {
                $text = $qa->get_last_qt_var("answertext$i");
                if ($text) {
                    list($filename, ) = $this->get_filename($qa->get_question()->id, $i, $qa->get_last_qt_var("answerfilename$i"), $qa->get_question()->ftsautogeneratefilenames);
                    $renderedfreetext .= html_writer::start_div('answertextreadonly');
                    $renderedfreetext .= html_writer::tag('div', mangle_pathname($filename) . ':');
                    $renderedfreetext .= html_writer::start_div('answertextcode');
                    $renderedfreetext .= html_writer::tag('pre', html_writer::tag('code', $text));
                    $renderedfreetext .= html_writer::end_div();
                    $renderedfreetext .= html_writer::end_div();
                }
            }

            if ($renderedfreetext != '') {
                $rendered .= $this->output->heading(get_string('freetextsubmissions', 'qtype_programmingtask'), 4);
                $rendered .= html_writer::div($renderedfreetext, 'readonlysubmittedfreetext');
            }
        }

        return $rendered;
    }

    private function renderSubmissionArea(question_attempt $qa, question_display_options $options) {
        global $CFG, $DB, $PAGE;
        require_once($CFG->dirroot . '/lib/form/filemanager.php');
        require_once($CFG->dirroot . '/repository/lib.php');

        $questionoptions = $DB->get_record('qtype_programmingtask_optns', ['questionid' => $qa->get_question()->id]);

        $renderedArea = '';

        if ($questionoptions->enablefilesubmissions) {
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

            $renderedArea .= $filesrenderer->render($fm) . $hidden;
        }
        if ($questionoptions->enablefreetextsubmissions) {

            if ($renderedArea != '') {
                $renderedArea .= html_writer::tag('hr', '');
            }

            $autogeneratefilenames = $questionoptions->ftsautogeneratefilenames;
            $maxIndexOfFieldWithContent = 0;

            for ($i = 0; $i < $questionoptions->ftsmaxnumfields; $i++) {
                $answertextname = "answertext$i";
                $answertextinputname = $qa->get_qt_field_name($answertextname);
                $answertextid = $answertextinputname . '_id';

                //$editor = editors_get_preferred_editor();
                $answertextresponse = $qa->get_last_step_with_qt_var($answertextname)->get_qt_var($answertextname) ?? '';
                //$editor->set_text($answertextresponse);
                //$editor->use_editor($answertextid, ['context' => $options->context, 'autosave' => false]);

                $filenamename = "answerfilename$i";
                $filenameinputname = $qa->get_qt_field_name($filenamename);
                $filenameid = $filenameinputname . '_id';

                list($filenameresponse, $disablefilenameinput) = $this->get_filename($qa->get_question()->id, $i, $qa->get_last_step_with_qt_var($filenamename)->get_qt_var($filenamename), $autogeneratefilenames);

                $output = '';
                $output .= html_writer::start_tag('div', array('class' => "qtype_programmingtask_answertext", 'id' => "qtype_programmingtask_answertext_$i", 'style' => 'display:none;'));
                $output .= html_writer::start_div('answertextfilename');
                $output .= html_writer::label(get_string('filename', 'qtype_programmingtask') . ":", $filenameid);
                $inputoptions = ['id' => $filenameid, 'name' => $filenameinputname, 'style' => 'width: 100%;padding-left: 10px;', 'value' => $filenameresponse];
                if ($disablefilenameinput) {
                    $inputoptions['disabled'] = true;
                }
                $output .= html_writer::tag('input', '', $inputoptions);
                $output .= html_writer::end_div();
                $output .= html_writer::tag('div', html_writer::tag('textarea', $answertextresponse, array('id' => $answertextid, 'name' => $answertextinputname, 'style' => 'width: 100%;padding-left: 10px;', 'rows' => 15)));
                $output .= html_writer::end_tag('div');

                $renderedArea .= $output;

                if ($answertextresponse != '') {
                    $maxIndexOfFieldWithContent = $i + 1;
                }
            }
            $renderedArea .= html_writer::start_div('', ['style' => 'display:flex;justify-content:flex-end;']);
            $renderedArea .= html_writer::tag('button', get_string('addanswertext', 'qtype_programmingtask'), ['id' => 'addAnswertextButton']);
            $renderedArea .= html_writer::tag('button', get_string('removelastanswertext', 'qtype_programmingtask'), ['id' => 'removeLastAnswertextButton', 'style' => 'margin-left: 10px']);
            $renderedArea .= html_writer::end_div();

            $PAGE->requires->js_call_amd('qtype_programmingtask/manage_answer_texts', 'init', [$questionoptions->ftsmaxnumfields, max($maxIndexOfFieldWithContent, $questionoptions->ftsnuminitialfields)]);
        }

        if ($renderedArea == '') {
            $noSubmissionPossible = get_string('nosubmissionpossible', 'qtype_programmingtask');
            $renderedArea = "<div>$noSubmissionPossible</div>";
        }

        return $renderedArea;
    }

    private function get_filename($questionid, $index, $usersuppliedname, $autogeneratefilenames) {
        global $DB;

        $customOptions = $DB->get_record('qtype_programmingtask_fts', ['questionid' => $questionid, 'inputindex' => $index]);
        $filenameresponse = $usersuppliedname ?? '';         //Init with previous value
        if ($filenameresponse == '') {
            $temp = $index + 1;
            $filenameresponse = "File$temp.txt";
        }
        $disablefilenameinput = false;
        if ($customOptions) {
            if ($customOptions->presetfilename) {
                $filenameresponse = $customOptions->filename;
                $disablefilenameinput = true;
            }
            //else use already set previous value
        } else {
            if ($autogeneratefilenames) {
                $temp = $index + 1;
                $filenameresponse = "File$temp.txt";
                $disablefilenameinput = true;
            }
            //else use already set previous value
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
        if ($qa->get_state()->is_finished()) {
            if ($qa->get_state() == question_state::$finished) {
                $PAGE->requires->js_call_amd('qtype_programmingtask/pull_grading_status', 'init', [$qa->get_usage_id(), get_config("qtype_programmingtask", "grappa_client_polling_interval") * 1000 /* to milliseconds */]);
                $loader = '<div class="loader"></div>';
                return html_writer::div(get_string('currentlybeeinggraded', 'qtype_programmingtask') . $loader, 'gradingstatus');
            } else if ($qa->get_state() == question_state::$needsgrading && !has_capability('mod/quiz:grade', $PAGE->context)) {
                //If a teacher is looking at this feedback and we did receive a valid response but it has an internal-error-attribute we still want to display this result
                return html_writer::div(get_string('needsgradingbyteacher', 'qtype_programmingtask'), 'gradingstatus');
            } else if ($qa->get_state()->is_graded() || (has_capability('mod/quiz:grade', $PAGE->context) && $qa->get_state() == question_state::$needsgrading)) {

                $PAGE->requires->js_call_amd('qtype_programmingtask/change_display_name_of_redo_button', 'init');

                $quba_record = $DB->get_record('question_usages', ['id' => $qa->get_usage_id()]);
                $initial_slot = $DB->get_record('qtype_programmingtask_qaslts', ['questionattemptdbid' => $qa->get_database_id()], 'slot')->slot;

                $fs = get_file_storage();

                $html = '';

                $responseXmlFile = $fs->get_file($quba_record->contextid, 'question', proforma_RESPONSE_FILE_AREA . "_{$qa->get_database_id()}", $qa->get_usage_id(), "/", 'response.xml');
                if ($responseXmlFile) {
                    $doc = new DOMDocument();

                    set_error_handler(function($number, $error) {
                        if (preg_match('/^DOMDocument::loadXML\(\): (.+)$/', $error, $m) === 1) {
                            throw new Exception($m[1]);
                        }
                    });
                    try {
                        $doc->loadXML($responseXmlFile->get_content());
                    } catch (Exception $ex) {
                        $doc = false;
                    } finally {
                        restore_error_handler();
                    }

                    try {
                        if ($doc) {
                            $namespace = detect_proforma_namespace($doc);

                            $separate_test_feedback_list = $doc->getElementsByTagNameNS($namespace, "separate-test-feedback");

                            if ($separate_test_feedback_list->length == 1) {
                                //Separate test feedback
                                $separate_test_feedback_elem = $separate_test_feedback_list[0];

                                //Load task.xml to get grading hints and tests
                                $fs = get_file_storage();
                                $taskxmlfile = $fs->get_file($qa->get_question()->contextid, 'question', proforma_TASKXML_FILEAREA,
                                        $qa->get_question()->id, '/', 'task.xml');
                                $taskdoc = new DOMDocument();
                                $taskdoc->loadXML($taskxmlfile->get_content());
                                $taskxmlnamespace = detect_proforma_namespace($taskdoc);
                                $grading_hints = $taskdoc->getElementsByTagNameNS($taskxmlnamespace, 'grading-hints')[0];
                                $tests = $taskdoc->getElementsByTagNameNS($taskxmlnamespace, 'tests')[0];
                                $feedbackfiles = $doc->getElementsByTagNameNS($namespace, "files")[0];

                                $xpathTask = new DOMXPath($taskdoc);
                                $xpathTask->registerNamespace('p', $taskxmlnamespace);
                                $xpathResponse = new DOMXPath($doc);
                                $xpathResponse->registerNamespace('p', $namespace);

                                $separate_feedback_helper = new separate_feedback_handler($grading_hints, $tests, $separate_test_feedback_elem, $feedbackfiles, $taskxmlnamespace, $namespace, $qa->get_max_mark(), $xpathTask, $xpathResponse);
                                $separate_feedback_helper->processResult();

                                $fileinfos = [
                                    'component' => 'question',
                                    'itemid' => $qa->get_usage_id(),
                                    'fileareasuffix' => "_{$qa->get_database_id()}",
                                    'contextid' => $quba_record->contextid,
                                    'filepath' => "/$initial_slot/{$qa->get_usage_id()}/"
                                ];

                                if (!$separate_feedback_helper->getDetailedFeedback()->hasInternalError() || has_capability('mod/quiz:grade', $PAGE->context)) {
                                    $separate_feedback_renderer_summarised = new qtype_programmingtask\output\separate_feedback_text_renderer($separate_feedback_helper->getSummarisedFeedback(), has_capability('mod/quiz:grade', $PAGE->context), $fileinfos, $qa->get_question()->showstudscorecalcscheme);
                                    $html .= '<p>' . $separate_feedback_renderer_summarised->render() . '</p>';

                                    $separate_feedback_renderer_detailed = new qtype_programmingtask\output\separate_feedback_text_renderer($separate_feedback_helper->getDetailedFeedback(), has_capability('mod/quiz:grade', $PAGE->context), $fileinfos, $qa->get_question()->showstudscorecalcscheme);
                                    $html .= '<p>' . $separate_feedback_renderer_detailed->render() . '</p>';
                                } else {
                                    $html .= '<p>' . get_string('needsgradingbyteacher', 'qtype_programmingtask') . '</p>';
                                }
                            } else {
                                //Merged test feedback
                                $html .= html_writer::div($doc->getElementsByTagNameNS($namespace, "student-feedback")[0]->nodeValue, 'studentfeedback');
                                if (has_capability('mod/quiz:grade', $PAGE->context)) {
                                    $html .= '<hr/>';
                                    $html .= html_writer::div($doc->getElementsByTagNameNS($namespace, "teacher-feedback")[0]->nodeValue, 'teacherfeedback');
                                }
                            }
                        } else {
                            $html = html_writer::div('The response contains an invalid response.xml file', 'gradingstatus');
                        }
                    } catch (\qtype_programmingtask\exceptions\grappa_exception $ex) {
                        //We did get a xml-valid response but something was still wrong. Display that message
                        $html = html_writer::div($ex->getMessage(), 'gradingstatus');
                    } catch (\exception $ex) {
                        //Catch anything weird that might happend during processing of the response
                        $html = html_writer::div($ex->getMessage(), 'gradingstatus') . html_writer::div($ex->getTraceAsString(), 'gradingstatus');
                    } catch (\Error $er) {
                        //Catch anything weird that might happend during processing of the response
                        $html = html_writer::div('Error code: ' . $er->getCode() . ". Message: " . $er->getMessage(), 'gradingstatus') . html_writer::div('Stack trace:<br/>' . $er->getTraceAsString(), 'gradingstatus');
                    }
                } else {
                    $html = html_writer::div('Response didn\'t contain response.xml file', 'gradingstatus');
                }
                //If teacher, display response.zip for download
                if (has_capability('mod/quiz:grade', $PAGE->context)) {
                    $slot = $qa->get_slot();
                    $responsefileinfos = array(
                        'component' => 'question',
                        'filearea' => proforma_RESPONSE_FILE_AREA . "_{$qa->get_database_id()}",
                        'itemid' => "{$qa->get_usage_id()}/$slot/{$qa->get_usage_id()}",
                        'contextid' => $quba_record->contextid,
                        'filepath' => "/",
                        'filename' => 'response.zip');
                    $url = moodle_url::make_pluginfile_url($responsefileinfos['contextid'], $responsefileinfos['component'], $responsefileinfos['filearea'], $responsefileinfos['itemid'], $responsefileinfos['filepath'], $responsefileinfos['filename'], true);
                    $html .= "<a href='$url' style='display:block;text-align:right;'> <span style='font-family: FontAwesome; display:inline-block;margin-right: 5px'>&#xf019;</span> Download complete 'response.zip' file</a>";
                }
                return $html;
            }
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
