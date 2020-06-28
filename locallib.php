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

defined('MOODLE_INTERNAL') || die();

// Name of file areas.
define('PROFORMA_TASKZIP_FILEAREA', 'taskfile');
define('PROFORMA_TASKXML_FILEAREA', 'taskxmlfile');
define('PROFORMA_ATTACHED_TASK_FILES_FILEAREA', 'attachedtaskfiles');
define('PROFORMA_EMBEDDED_TASK_FILES_FILEAREA', 'embeddedtaskfiles');
define('PROFORMA_SUBMISSION_ZIP_FILEAREA', 'submissionzip');
define('PROFORMA_RESPONSE_FILE_AREA_RESPONSEFILE', 'responsefilesresponsefile');
define('PROFORMA_RESPONSE_FILE_AREA', 'responsefiles');
define('PROFORMA_RESPONSE_FILE_AREA_EMBEDDED', 'responsefilesembedded');

define('PROFORMA_RETRIEVE_GRADING_RESULTS_LOCK_MAXLIFETIME', 10);

// ProFormA task xml namespaces.
define('PROFORMA_TASK_XML_NAMESPACES', [/* First namespace is default namespace. */'urn:proforma:v2.0.1', 'urn:proforma:v2.0']);

define('PROFORMA_MERGED_FEEDBACK_TYPE', 'merged-test-feedback');
define('PROFORMA_SEPARATE_FEEDBACK_TYPE', 'separate-test-feedback');

define('PROFORMA_ACE_PROGLANGS', [
    'txt' => 'Plain text',
    'java' => 'Java',
    'sql' => 'SQL',
    'cpp' => 'C/C++',
    'javascript' => 'Javascript',
    'php' => 'PHP',
    'py' => 'Python',
    'cs' => 'C#',
    'scala' => 'Scala',
    'bat' => 'Batchfile',
    'css' => 'CSS',
    'dart' => 'Dart',
    'glsl' => 'GLSL',
    'go' => 'Go',
    'html' => 'HTML',
    'json' => 'JSON',
    'kt' => 'Kotlin',
    'latex' => 'LaTeX',
    'lua' => 'Lua',
    'matlab' => 'MATLAB',
    'mm' => 'Objective-C',
    'pas' => 'Pascal',
    'pl' => 'Perl',
    'prolog' => 'Prolog',
    'r' => 'R',
    'rb' => 'Ruby',
    'rs' => 'Rust',
    'swift' => 'Swift',
    'typescript' => 'Typescript',
    'xml' => 'XML',
    'yaml' => 'YAML'
]);

require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/mod/quiz/attemptlib.php');
require_once($CFG->dirroot . '/mod/quiz/accessmanager.php');

use qtype_programmingtask\utility\communicator\communicator_factory;
use qtype_programmingtask\utility\proforma_xml\separate_feedback_handler;

/** Unzips the task zip file in the given draft area into the area
 *
 * @param type $draftareaid
 * @param type $usercontext
 * @return string the name of the task file
 * @throws invalid_parameter_exception
 */
function unzip_task_file_in_draft_area($draftareaid, $usercontext) {
    global $USER;

    $fs = get_file_storage();

    // Check if there is only the file we want.
    $area = file_get_draft_area_info($draftareaid, "/");
    if ($area['filecount'] == 0) {
        return false;
    } else if ($area['filecount'] > 1 || $area['foldercount'] != 0) {
        throw new invalid_parameter_exception(
                'Only one file is allowed to be in this draft area: A ProFormA-Task as either ZIP or XML file.');
    }

    // Get name of the file.
    $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftareaid);
    // Get_area_files returns an associative array where the keys are some kind of hash value.
    $keys = array_keys($files);
    // Index 1 because index 0 is the current directory it seems.
    $filename = $files[$keys[1]]->get_filename();

    $file = $fs->get_file($usercontext->id, 'user', 'draft', $draftareaid, "/", $filename);

    // Check file type (it's really only checking the file extension but that is good enough here).
    $fileinfo = pathinfo($filename);
    $filetype = '';
    if (array_key_exists('extension', $fileinfo)) {
        $filetype = strtolower($fileinfo['extension']);
    }
    if ($filetype != 'zip') {
        throw new invalid_parameter_exception('Supplied file must be a zip file.');
    }

    // Unzip file - basically copied from draftfiles_ajax.php.
    $zipper = get_file_packer('application/zip');

    // Find unused name for directory to extract the archive.
    $temppath = $fs->get_unused_dirname($usercontext->id, 'user', 'draft', $draftareaid, "/" . pathinfo($filename,
                    PATHINFO_FILENAME) . '/');
    $donotremovedirs = array();
    $doremovedirs = array($temppath);
    // Extract archive and move all files from $temppath to $filepath.
    if ($file->extract_to_storage($zipper, $usercontext->id, 'user', 'draft', $draftareaid, $temppath, $USER->id)) {
        $extractedfiles = $fs->get_directory_files($usercontext->id, 'user', 'draft', $draftareaid, $temppath, true);
        $xtemppath = preg_quote($temppath, '|');
        foreach ($extractedfiles as $exfile) {
            $realpath = preg_replace('|^' . $xtemppath . '|', '/', $exfile->get_filepath());
            if (!$exfile->is_directory()) {
                // Set the source to the extracted file to indicate that it came from archive.
                $exfile->set_source(serialize((object) array('source' => '/')));
            }
            if (!$fs->file_exists($usercontext->id, 'user', 'draft', $draftareaid, $realpath, $exfile->get_filename())) {
                // File or directory did not exist, just move it.
                $exfile->rename($realpath, $exfile->get_filename());
            } else if (!$exfile->is_directory()) {
                // File already existed, overwrite it.
                repository::overwrite_existing_draftfile($draftareaid, $realpath, $exfile->get_filename(), $exfile->get_filepath(),
                        $exfile->get_filename());
            } else {
                // Directory already existed, remove temporary dir but make sure we don't remove the existing dir.
                $doremovedirs[] = $exfile->get_filepath();
                $donotremovedirs[] = $realpath;
            }
        }
    } else {
        return null;
    }
    // Remove remaining temporary directories.
    foreach (array_diff($doremovedirs, $donotremovedirs) as $filepath) {
        $file = $fs->get_file($usercontext->id, 'user', 'draft', $draftareaid, $filepath, '.');
        if ($file) {
            $file->delete();
        }
    }

    return $filename;
}

/* * Removes all files and directories from the given draft area except a file with the given file name
 *
 * @param type $draftareaid
 * @param type $user_context
 * @param type $excluded_file_name
 */

function remove_all_files_from_draft_area($draftareaid, $usercontext, $excludedfilename) {
    $fs = get_file_storage();
    $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftareaid);
    foreach ($files as $fi) {
        if (($fi->is_directory() && $fi->get_filepath() != '/') || ($fi->get_filename() != $excludedfilename &&
                $fi->get_filename() != '.')) {
            $fi->delete();
        }
    }
}

/* * Creates a DOMDocument object from the task.xml file in the given file area and returns it.
 *
 * @param type $user_context
 * @param type $draftareid
 * @param type $zipfilename
 * @return \DOMDocument
 * @throws invalid_parameter_exception
 */

function create_domdocument_from_task_xml($usercontext, $draftareid, $zipfilename) {
    $fs = get_file_storage();
    $file = $fs->get_file($usercontext->id, 'user', 'draft', $draftareid, "/", 'task.xml');
    if (!$file) {
        remove_all_files_from_draft_area($draftareid, $usercontext, $zipfilename);
        throw new invalid_parameter_exception('Supplied zip file doesn\'t contain task.xml file.');
    }

    $doc = new DOMDocument();
    if (!$doc->loadXML($file->get_content())) {
        remove_all_files_from_draft_area($draftareid, $usercontext, $zipfilename);
        throw new invalid_parameter_exception('Error parsing the supplied task.xml file. See server log for details.');
    }

    return $doc;
}

function save_task_and_according_files($question) {
    global $USER, $DB;

    if (!isset($question->proformataskfileupload)) {
        return;
    }
    $draftareaid = $question->proformataskfileupload;

    $usercontext = context_user::instance($USER->id);

    $filename = unzip_task_file_in_draft_area($draftareaid, $usercontext);
    if (!$filename) {
        // Seems like no task file was submitted.
        return false;
    }

    // Copy all extracted files to the corresponding file area.
    file_save_draft_area_files($draftareaid, $question->context->id, 'question', PROFORMA_ATTACHED_TASK_FILES_FILEAREA,
            $question->id, array('subdirs' => true));

    $doc = create_domdocument_from_task_xml($usercontext, $draftareaid, $filename);
    $namespace = detect_proforma_namespace($doc);

    $filesfordb = array();
    $fs = get_file_storage();
    $embeddedelems = array("embedded-bin-file", "embedded-txt-file");
    $attachedelems = array("attached-bin-file", "attached-txt-file");
    foreach ($doc->getElementsByTagNameNS($namespace, 'file') as $file) {
        foreach ($file->childNodes as $child) {
            $break = false;
            if (in_array($child->localName, $embeddedelems)) {
                $content = '';
                if ($child->localName == 'embedded-bin-file') {
                    $content = base64_decode($child->nodeValue);
                } else {
                    $content = $child->nodeValue;
                }

                // Compensate the fact that the filename might contain a relative path.
                $pathinfo = pathinfo('/' . $file->attributes->getNamedItem('id')->nodeValue . '/' .
                        $child->attributes->getNamedItem('filename')->nodeValue);

                $fileinfo = array(
                    'component' => 'question',
                    'filearea' => PROFORMA_EMBEDDED_TASK_FILES_FILEAREA,
                    'itemid' => $question->id,
                    'contextid' => $question->context->id,
                    'filepath' => $pathinfo['dirname'] . '/',
                    'filename' => $pathinfo['basename']);
                $fs->create_file_from_string($fileinfo, $content);

                $record = new stdClass();
                $record->questionid = $question->id;
                $record->fileid = $file->attributes->getNamedItem('id')->nodeValue;
                $record->usedbygrader = $file->attributes->getNamedItem('used-by-grader')->nodeValue == 'true' ? 1 : 0;
                $record->visibletostudents = $file->attributes->getNamedItem('visible')->nodeValue == 'true' ? 1 : 0;
                $record->usagebylms = $file->attributes->getNamedItem('usage-by-lms') != null ?
                        $file->attributes->getNamedItem('usage-by-lms')->nodeValue : 'download';
                $record->filepath = '/' . $file->attributes->getNamedItem('id')->nodeValue . '/';
                $record->filename = $child->attributes->getNamedItem('filename')->nodeValue;
                $record->filearea = PROFORMA_EMBEDDED_TASK_FILES_FILEAREA;
                $filesfordb[] = $record;

                $break = true;
            } else if (in_array($child->localName, $attachedelems)) {

                // The file itself has already been copied - now only add the database entry.

                $pathinfo = pathinfo('/' . $child->nodeValue);
                $record = new stdClass();
                $record->questionid = $question->id;
                $record->fileid = $file->attributes->getNamedItem('id')->nodeValue;
                $record->usedbygrader = $file->attributes->getNamedItem('used-by-grader')->nodeValue == 'true' ? 1 : 0;
                $record->visibletostudents = $file->attributes->getNamedItem('visible')->nodeValue == 'true' ? 1 : 0;
                $record->usagebylms = $file->attributes->getNamedItem('usage-by-lms') != null ?
                        $file->attributes->getNamedItem('usage-by-lms')->nodeValue : 'download';
                $record->filepath = $pathinfo['dirname'] . '/';
                $record->filename = $pathinfo['basename'];
                $record->filearea = PROFORMA_ATTACHED_TASK_FILES_FILEAREA;
                $filesfordb[] = $record;

                $break = true;
            }
            if ($break) {
                break;
            }
        }
    }

    // Now move the task xml file to the designated area.
    $file = $fs->get_file($question->context->id, 'question', PROFORMA_ATTACHED_TASK_FILES_FILEAREA, $question->id,
            '/', 'task.xml');
    $newfilerecord = array(
        'component' => 'question',
        'filearea' => PROFORMA_TASKXML_FILEAREA,
        'itemid' => $question->id,
        'contextid' => $question->context->id,
        'filepath' => '/',
        'filename' => 'task.xml');
    $fs->create_file_from_storedfile($newfilerecord, $file);
    $file->delete();

    $record = new stdClass();
    $record->questionid = $question->id;
    $record->fileid = 'taskxml';
    $record->usedbygrader = 0;
    $record->visibletostudents = 0;
    $record->usagebylms = 'download';
    $record->filepath = '/';
    $record->filename = 'task.xml';
    $record->filearea = PROFORMA_TASKXML_FILEAREA;
    $filesfordb[] = $record;

    // Now move the task zip file to the designated area.
    $file = $fs->get_file($question->context->id, 'question', PROFORMA_ATTACHED_TASK_FILES_FILEAREA, $question->id, '/', $filename);
    $newfilerecord = array(
        'component' => 'question',
        'filearea' => PROFORMA_TASKZIP_FILEAREA,
        'itemid' => $question->id,
        'contextid' => $question->context->id,
        'filepath' => '/',
        'filename' => $filename);
    $fs->create_file_from_storedfile($newfilerecord, $file);
    $file->delete();

    $record = new stdClass();
    $record->questionid = $question->id;
    $record->fileid = 'task';
    $record->usedbygrader = 0;
    $record->visibletostudents = 0;
    $record->usagebylms = 'download';
    $record->filepath = '/';
    $record->filename = $filename;
    $record->filearea = PROFORMA_TASKZIP_FILEAREA;
    $filesfordb[] = $record;

    // Save all records in database.
    $DB->insert_records('qtype_programmingtask_files', $filesfordb);

    // Do a little bit of cleanup and remove everything from the file area we extracted.
    remove_all_files_from_draft_area($draftareaid, $usercontext, $filename);
}

/**
 * Checks if any of the grade processes belonging to the given $qubaid finished. If so retrieves the grading results and
 * writes them to the system.
 * @return bool whether any of grade process finished
 * @param type $qubaid
 */
function retrieve_grading_results($qubaid) {

    /*
     * This function is called from both the webservice and the task api therefore it might happen that both calls happen at
     * the same time
     * and try to access the same qubaid which can lead to unwanted behaviour. Hence use the locking api.
     */
    $locktype = "qtype_programmingtask_retrieve_grading_results";
    $ressource = "qubaid:$qubaid";
    $lockfactory = \core\lock\lock_config::get_lock_factory($locktype);
    $lock = $lockfactory->get_lock($ressource, 0, PROFORMA_RETRIEVE_GRADING_RESULTS_LOCK_MAXLIFETIME);
    if (!$lock) {
        return false;
    }

    try {
        $result = internal_retrieve_grading_results($qubaid);
    } catch (Exception $ex) {
        throw $ex;
    } finally {
        $lock->release();
    }

    return $result;
}

/**
 * Do not call this function unless you know what you are doing. Use retrieve_grading_results instead
 */
function internal_retrieve_grading_results($qubaid) {
    global $DB, $USER;
    $communicator = communicator_factory::get_instance();
    $fs = get_file_storage();

    $finishedgradingprocesses = [];
    $records = $DB->get_records('qtype_programmingtask_grprcs', ['qubaid' => $qubaid]);

    if (empty($records)) {
        // Most likely the systems cron job retrieved the result a couple of seconds ago.
        return true;
    }

    foreach ($records as $record) {

        $quba = question_engine::load_questions_usage_by_activity($qubaid);
        $slot = $DB->get_record('question_attempts', ['id' => $record->questionattemptdbid], 'slot')->slot;
        $initialslot = $DB->get_record('qtype_programmingtask_qaslts',
                        ['questionattemptdbid' => $record->questionattemptdbid], 'slot')->slot;

        try {
            $response = $communicator->get_grading_result($record->graderid, $record->gradeprocessid);
        } catch (invalid_response_exception $ex) {
            // Here was a network error.
            debugging($ex->module . '/' . $ex->errorcode . '( ' . $ex->debuginfo . ')');

            continue;
        } catch (Exception $e) {
            debugging($e->getMessage());
            continue;
        }
        if ($response) {
            $internalerror = false;
            try {
                $qubarecord = $DB->get_record('question_usages', ['id' => $qubaid]);
                if (!$qubarecord) {
                    // The quba got deleted for whatever reason - just ignore this entry.
                    // It can be safely deleted at the end of this function because the result has already been fetched.
                    continue;
                }

                // Write response to file system.
                $filerecord = array(
                    'component' => 'question',
                    'filearea' => PROFORMA_RESPONSE_FILE_AREA_RESPONSEFILE . "_{$record->questionattemptdbid}",
                    'itemid' => $qubaid,
                    'contextid' => $qubarecord->contextid,
                    'filepath' => "/",
                    'filename' => 'response.zip');

                $file = $fs->get_file($filerecord['contextid'], $filerecord['component'], $filerecord['filearea'],
                        $filerecord['itemid'], $filerecord['filepath'], $filerecord['filename']);
                if ($file) {
                    // Delete old result.
                    $file->delete();
                }
                // We take care of this grade process therefore we will delete it afterwards.
                $finishedgradingprocesses[] = $record->id;
                $file = $fs->create_file_from_string($filerecord, $response);
                $zipper = get_file_packer('application/zip');
                // Remove old extracted files in case this is a regrade.
                $oldexractedfiles = array_merge(
                        $fs->get_directory_files($qubarecord->contextid, 'question', PROFORMA_RESPONSE_FILE_AREA .
                                "_{$record->questionattemptdbid}", $qubaid, "/", true, true),
                        $fs->get_directory_files($qubarecord->contextid, 'question', PROFORMA_RESPONSE_FILE_AREA_EMBEDDED .
                                "_{$record->questionattemptdbid}", $qubaid, "/", true, true)
                );
                foreach ($oldexractedfiles as $f) {
                    $f->delete();
                }

                // Apply the grade from the response.
                $result = $file->extract_to_storage($zipper, $qubarecord->contextid, 'question', PROFORMA_RESPONSE_FILE_AREA .
                        "_{$record->questionattemptdbid}", $qubaid, "/");
                if ($result) {
                    $doc = new DOMDocument();
                    $responsexmlfile = $fs->get_file($qubarecord->contextid, 'question', PROFORMA_RESPONSE_FILE_AREA .
                            "_{$record->questionattemptdbid}", $qubaid, "/", 'response.xml');
                    if ($responsexmlfile) {

                        set_error_handler(function($number, $error) {
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

                        if ($doc) {
                            $namespace = detect_proforma_namespace($doc);

                            // Save embedded files to disk.
                            $files = $doc->getElementsByTagNameNS($namespace, "files")[0];
                            foreach ($files->getElementsByTagNameNS($namespace, "file") as $responsefile) {
                                $elem = null;
                                if (($tmp = $responsefile->getElementsByTagNameNS($namespace, 'embedded-bin-file'))->length == 1) {
                                    $elem = $tmp[0];
                                    $content = base64_decode($elem->nodeValue);
                                } else if (($tmp = $responsefile->getElementsByTagNameNS($namespace,
                                        'embedded-txt-file'))->length == 1) {
                                    $elem = $tmp[0];
                                    $content = $elem->nodeValue;
                                }

                                if ($elem == null) {
                                    continue;
                                }

                                // Compensate the fact that the filename might contain a relative path.
                                $pathinfo = pathinfo('/' . $responsefile->getAttribute('id') . '/' .
                                        $elem->getAttribute('filename'));
                                $fileinfo = array(
                                    'component' => 'question',
                                    'filearea' => PROFORMA_RESPONSE_FILE_AREA_EMBEDDED . "_{$record->questionattemptdbid}",
                                    'itemid' => $qubaid,
                                    'contextid' => $qubarecord->contextid,
                                    'filepath' => $pathinfo['dirname'] . '/',
                                    'filename' => $pathinfo['basename']);
                                $fs->create_file_from_string($fileinfo, $content);
                            }

                            $mtf = $doc->getElementsByTagNameNS($namespace, 'merged-test-feedback');
                            if ($mtf->length == 1) {
                                // Merged test feedback.

                                $score = $mtf[0]->getElementsByTagNameNS($namespace, 'overall-result')[0]->getElementsByTagNameNS(
                                                $namespace, 'score')[0]->nodeValue;
                            } else {
                                // Separate test feedback.
                                $question = $quba->get_question($slot);

                                $separatetestfeedback = $doc->getElementsByTagNameNS($namespace, 'separate-test-feedback')[0];

                                // Load task.xml to get grading hints and tests.
                                $fs = get_file_storage();
                                $taskxmlfile = $fs->get_file($question->contextid, 'question', PROFORMA_TASKXML_FILEAREA,
                                        $question->id, '/', 'task.xml');
                                $taskdoc = new DOMDocument();
                                $taskdoc->loadXML($taskxmlfile->get_content());
                                $taskxmlnamespace = detect_proforma_namespace($taskdoc);
                                $gradinghints = $taskdoc->getElementsByTagNameNS($taskxmlnamespace, 'grading-hints')[0];
                                $tests = $taskdoc->getElementsByTagNameNS($taskxmlnamespace, 'tests')[0];
                                $feedbackfiles = $doc->getElementsByTagNameNS($namespace, 'files')[0];

                                $xpathtask = new DOMXPath($taskdoc);
                                $xpathtask->registerNamespace('p', $taskxmlnamespace);
                                $xpathresponse = new DOMXPath($doc);
                                $xpathresponse->registerNamespace('p', $namespace);

                                $separatefeedbackhelper = new separate_feedback_handler($gradinghints, $tests,
                                        $separatetestfeedback, $feedbackfiles, $taskxmlnamespace, $namespace,
                                        $quba->get_question_max_mark($slot), $xpathtask, $xpathresponse);

                                $separatefeedbackhelper->process_result();
                                if (!$separatefeedbackhelper->get_detailed_feedback()->has_internal_error()) {
                                    $score = $separatefeedbackhelper->get_calculated_score();
                                } else {
                                    $internalerror = true;
                                }
                            }
                        }
                    } else {
                        debugging("Response didn't contain a response.xml file");
                    }
                }
            } catch (\qtype_programmingtask\exceptions\grappa_exception $ex) {
                // Something with the response we got was wrong - log it and set that the question needs manual grading.
                $internalerror = true;
                debugging($ex->module . '/' . $ex->errorcode . '( ' . $ex->debuginfo . ')');
            } catch (\Exception $ex) {
                // Catch anything weird that might happend during processing of the response.
                $internalerror = true;
                debugging($ex->module . '/' . $ex->errorcode . '( ' . $ex->debuginfo . ')');
            } catch (\Error $er) {
                // Catch anything weird that might happend during processing of the response.
                $internalerror = true;
                debugging('Error while processing response from grader. Error code: ' . $er->getCode() . '. Message: ' .
                        $er->getMessage() . ')');
            }

            if (!$result || !isset($score) || $internalerror) {
                if (!$internalerror) {
                    debugging("Received invalid response from grader");
                }

                // Change the state to the question needing manual grading because automatic grading failed.
                $quba->process_action($slot, ['-graderunavailable' => 1, 'gradeprocessdbid' => $record->id]);
                question_engine::save_questions_usage_by_activity($quba);

                continue;
            }

            // Apply the grading result.
            $quba->process_action($slot, ['-gradingresult' => 1, 'score' => $score, 'gradeprocessdbid' => $record->id]);
            question_engine::save_questions_usage_by_activity($quba);

            // Update total mark.
            $attempt = quiz_attempt::create_from_usage_id($qubaid)->get_attempt();
            $attempt->timemodified = time();
            $attempt->sumgrades = $quba->get_total_mark();
            $DB->update_record('quiz_attempts', $attempt);

            // Update gradebook.
            $quiz = $DB->get_record('quiz', array('id' => $attempt->quiz), '*', MUST_EXIST);
            quiz_save_best_grade($quiz, $USER->id);
        }
    }

    foreach ($finishedgradingprocesses as $doneid) {
        $DB->delete_records('qtype_programmingtask_grprcs', ['id' => $doneid]);
    }

    return !empty($finishedgradingprocesses);
}

function retrieve_graders_and_update_local_list() {
    global $DB;

    $graders = communicator_factory::get_instance()->get_graders();
    $records = array();
    $availableGraders = [];
    foreach ($graders['graders'] as $id => $name) {
        if (!$DB->record_exists('qtype_programmingtask_gradrs', array("graderid" => $id))) {
            array_push($records, array("graderid" => $id, "gradername" => $name));
        }
        $availableGraders[] = $id;
    }
    $DB->insert_records('qtype_programmingtask_gradrs', $records);
    
    $allgradersrecords = $DB->get_records('qtype_programmingtask_gradrs');
    $allgraders = [];
    foreach($allgradersrecords as $graderrecord){
        $allgraders[$graderrecord->graderid] = $graderrecord->gradername;
    }
    
    return [$allgraders, $availableGraders];
}

function detect_proforma_namespace(DOMDocument $doc) {
    foreach (PROFORMA_TASK_XML_NAMESPACES as $namespace) {
        if ($doc->getElementsByTagNameNS($namespace, "task")->length != 0 ||
                $doc->getElementsByTagNameNS($namespace, "submission")->length != 0 ||
                $doc->getElementsByTagNameNS($namespace, "response")->length != 0) {
            return $namespace;
        }
    }
    return null;
}

function validate_proforma_file_against_schema(DOMDocument $doc, $namespace): array {
    $msgs = [];
    $schema = file_get_contents(__DIR__ . "/res/proforma/xsd/$namespace.xsd");
    if (!$schema) {
        $msgs[] = "Invalid or unknown proforma namespace: $namespace<br/>Valid proforma namespaces are: " . implode(", ",
                        PROFORMA_TASK_XML_NAMESPACES);
    }

    libxml_use_internal_errors(true);
    if (!$doc->schemaValidateSource($schema)) {
        $errors = libxml_get_errors();
        foreach ($errors as $error) {
            $msgs[] = "{$error->message} (Code {$error->code}) on line {$error->line}";
        }
        libxml_clear_errors();
    }
    libxml_use_internal_errors(false);

    return $msgs;
}

/**
 *  Checks whether supplied zip file is valid.
 * @global type $USER
 * @param type $draftareaid
 * @return string null if there are no errors. The error message otherwise
 */
function check_if_task_file_is_valid($draftareaid) {
    global $USER;

    $usercontext = context_user::instance($USER->id);

    $fs = get_file_storage();

    // Check if there is only the file we want.
    $area = file_get_draft_area_info($draftareaid, "/");
    if ($area['filecount'] == 0) {
        return 'You need to supply a valid ProFormA task file.';
    } else if ($area['filecount'] > 1 || $area['foldercount'] != 0) {
        return 'Only one file is allowed to be in this draft area: A ProFormA-Task as either ZIP or XML file.';
    }

    // Get name of the file.
    $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftareaid);
    // Get_area_files returns an associative array where the keys are some kind of hash value.
    $keys = array_keys($files);
    // Index 1 because index 0 is the current directory it seems.
    $filename = $files[$keys[1]]->get_filename();

    $file = $fs->get_file($usercontext->id, 'user', 'draft', $draftareaid, "/", $filename);

    // Check file type (it's really only checking the file extension but that is good enough here).
    $fileinfo = pathinfo($filename);
    $filetype = '';
    if (array_key_exists('extension', $fileinfo)) {
        $filetype = strtolower($fileinfo['extension']);
    }
    if ($filetype != 'zip') {
        return 'Supplied file must be a zip file.';
    }

    // Unzip file - basically copied from draftfiles_ajax.php.
    $zipper = get_file_packer('application/zip');

    // Find unused name for directory to extract the archive.
    $temppath = $fs->get_unused_dirname($usercontext->id, 'user', 'draft', $draftareaid, "/" . pathinfo($filename,
                    PATHINFO_FILENAME) . '/');

    // Extract archive and move all files from $temppath to $filepath.
    if ($file->extract_to_storage($zipper, $usercontext->id, 'user', 'draft', $draftareaid, $temppath, $USER->id)) {
        $extractedfiles = $fs->get_directory_files($usercontext->id, 'user', 'draft', $draftareaid, $temppath, true);
        $taskfilecontents = null;
        foreach ($extractedfiles as $exfile) {
            if ($exfile->get_filename() == 'task.xml') {
                $taskfilecontents = $exfile->get_content();
                break;
            }
        }

        foreach ($extractedfiles as $exfile) {
            $exfile->delete();
        }
        $fs->get_file($usercontext->id, 'user', 'draft', $draftareaid, "/" . pathinfo($filename, PATHINFO_FILENAME) .
                '/', '.')->delete();

        if ($taskfilecontents == null) {
            return "Supplied zip file doesn't contain a task.xml file";
        }

        // TODO: Uncomment this to enable validation of task.xml file itself. Currently commented out because most task files
        // don't validate against the schema.
        /*
          $doc = new DOMDocument();
          $doc->loadXML($taskFileContents);
          $namespace = detect_proforma_namespace($doc);
          if (!empty(($errors = validate_proforma_file_against_schema($doc, $namespace)))) {
          $ret = "<p>Detected ProFormA-version $namespace. Found the following problems during validation".
          " of task.xml file:</p><ul>";
          foreach ($errors as $er) {
          $ret .= '<li>' . $er . '</li>';
          }
          $ret .= '</ul>';
          return $ret;
          } */
    } else {
        return 'Supplied zip file is not a valid zip file';
    }

    return null;
}

// Copied from zip_archive::mangle_pathname.
function mangle_pathname($filename) {
    $filename = trim($filename, '/');
    $filename = str_replace('\\', '/', $filename);   // No MS \ separators.
    $filename = preg_replace('/\.\.+/', '', $filename); // Prevent /.../   .
    $filename = ltrim($filename, '/');                  // No leading slash.
    return $filename;
}
