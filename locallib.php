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

// The following four file areas store files that are attached to a question.
// All these four file areas store files with the following key values:
// - component: question
// - context: a context of level 50 (=course)
// - filearea: <key>, 
//             where <key> is one of the four labels
// - itemid: <q-id>, i. e. the database id of the question
// - filepath: for PROFORMA_TASKZIP_FILEAREA and PROFORMA_TASKXML_FILEAREA this is "/".
//             for PROFORMA_ATTACHED_TASK_FILES_FILEAREA this is the path inside the zip file
//             for PROFORMA_EMBEDDED_TASK_FILES_FILEAREA this is "/<file-id>/", where <file-id> denotes the ProFormA file id of the embedded file
// - filename: the filename inside the task.zip  (or the name of the zip file in case of PROFORMA_TASKZIP_FILEAREA)
define('PROFORMA_TASKZIP_FILEAREA', 'taskfile'); // the task.zip file
define('PROFORMA_TASKXML_FILEAREA', 'taskxmlfile'); // the task.xml file
define('PROFORMA_ATTACHED_TASK_FILES_FILEAREA', 'attachedtaskfiles'); // files attached in a task.zip file
define('PROFORMA_EMBEDDED_TASK_FILES_FILEAREA', 'embeddedtaskfiles'); // files embedded in a task.xml file


define('PROFORMA_SUBMISSION_ZIP_FILEAREA', 'submissionzip');

// The following file area is used for a response.zip file only. Contents of the zip file will
// go to PROFORMA_RESPONSE_FILE_AREA and PROFORMA_RESPONSE_FILE_AREA_EMBEDDED.
define('PROFORMA_RESPONSE_FILE_AREA_RESPONSEFILE', 'responsefilesresponsefile');

// The following two file areas store files that are attached to a response.
define('PROFORMA_RESPONSE_FILE_AREA', 'responsefiles');
define('PROFORMA_RESPONSE_FILE_AREA_EMBEDDED', 'responsefilesembedded');

// All of the above PROFORMA_RESPONSE_FILE_AREA* file areas store files
// with the following key values:
// - component: question
// - context: a context of level 70 (=module)
// - filearea: <key>_<qa-id>, 
//             where <key> is one of the labels responsefilesresponsefile, responsefiles or responsefilesembedded
//             and <qa-id> is the database id of a mdl_question_attempt
// - itemid: <quba-id>, i. e. the database id of a mdl_question_usages
// - filepath: the path inside a response.zip file  (or / in case of responsefilesresponsefile)
// - filename: the filename inside the response.zip  (or the name of the zip file in case of responsefilesresponsefile)


define('PROFORMA_RETRIEVE_GRADING_RESULTS_LOCK_MAXLIFETIME', 10);

// ProFormA task xml namespaces.
define('PROFORMA_TASK_XML_NAMESPACES', [/* First namespace is default namespace. */'urn:proforma:v2.1']);

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

use qtype_moopt\utility\communicator\communicator_factory;
use qtype_moopt\utility\proforma_xml\separate_feedback_handler;
use qtype_moopt\exceptions\resource_not_found_exception;


/*
 * Unzips the task zip file in the given draft area into the area
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

/*
 * Removes all files and directories from the given draft area except a file with the given file name
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

/**
 * Creates a DOMDocument object from the task.xml file in the given file area and returns it.
 *
 * @param type $user_context
 * @param type $draftareaid
 * @param type $zipfilename
 * @return \DOMDocument
 * @throws invalid_parameter_exception
 */

function create_domdocument_from_task_xml($usercontext, $draftareaid, $zipfilename) {
    $fs = get_file_storage();
    $file = $fs->get_file($usercontext->id, 'user', 'draft', $draftareaid, "/", 'task.xml');
    if (!$file) {
        remove_all_files_from_draft_area($draftareaid, $usercontext, $zipfilename);
        throw new invalid_parameter_exception('Supplied zip file doesn\'t contain task.xml file.');
    }

    $doc = new DOMDocument();
    if (!$doc->loadXML($file->get_content())) {
        remove_all_files_from_draft_area($draftareaid, $usercontext, $zipfilename);
        throw new invalid_parameter_exception('Error parsing the supplied task.xml file. See server log for details.');
    }

    return $doc;
}

/**
 * Get the text content of a file -- attached or embedded(), from the task.zip
 *
 * @param $usercontext
 * @param $draftareaid
 * @param $zipfilename
 * @param $filepath
 * @param $filename
 * @return string
 * @throws invalid_parameter_exception
 */
function get_text_content_from_file($usercontext, $draftareaid, $zipfilename, $filepath, $filename) {
    $fs = get_file_storage();
    $file = $fs->get_file($usercontext->id, 'user', 'draft', $draftareaid, $filepath, $filename);
    if (!$file) {
        remove_all_files_from_draft_area($draftareaid, $usercontext, $zipfilename);
        throw new invalid_parameter_exception('Supplied zip file doesn\'t contain file '. $filepath . $filename . '.');
    }

    // TODO: make sure the mimetype is plain text
    // even task.xmls may contain mistakes (eg PDF )

    return $file->get_content();
}

function save_task_and_according_files($question) {
    global $USER, $DB;

    if (!isset($question->proformataskfileupload)) {
        return;
    }
    $draftareaid = $question->proformataskfileupload;

    $usercontext = context_user::instance($USER->id);

    $taskfilename = unzip_task_file_in_draft_area($draftareaid, $usercontext);
    if (!$taskfilename) {
        // Seems like no task file was submitted.
        return false;
    }

    // Copy all extracted files to the corresponding file area.
    file_save_draft_area_files($draftareaid, $question->context->id, 'question', PROFORMA_ATTACHED_TASK_FILES_FILEAREA,
            $question->id, array('subdirs' => true));

    $doc = create_domdocument_from_task_xml($usercontext, $draftareaid, $taskfilename);
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
                $record->visibletostudents = $file->attributes->getNamedItem('visible')->nodeValue;
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
                $record->visibletostudents = $file->attributes->getNamedItem('visible')->nodeValue;
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
    $record->visibletostudents = 'no';
    $record->usagebylms = 'download';
    $record->filepath = '/';
    $record->filename = 'task.xml';
    $record->filearea = PROFORMA_TASKXML_FILEAREA;
    $filesfordb[] = $record;

    // Now move the task zip file to the designated area.
    $file = $fs->get_file($question->context->id, 'question', PROFORMA_ATTACHED_TASK_FILES_FILEAREA, $question->id, '/', $taskfilename);
    $newfilerecord = array(
        'component' => 'question',
        'filearea' => PROFORMA_TASKZIP_FILEAREA,
        'itemid' => $question->id,
        'contextid' => $question->context->id,
        'filepath' => '/',
        'filename' => $taskfilename);
    $fs->create_file_from_storedfile($newfilerecord, $file);
    $file->delete();

    $record = new stdClass();
    $record->questionid = $question->id;
    $record->fileid = 'task';
    $record->usedbygrader = 0;
    $record->visibletostudents = 'no';
    $record->usagebylms = 'download';
    $record->filepath = '/';
    $record->filename = $taskfilename;
    $record->filearea = PROFORMA_TASKZIP_FILEAREA;
    $filesfordb[] = $record;

    // Save all records in database.
    $DB->insert_records('qtype_moopt_files', $filesfordb);

    // Do a little bit of cleanup and remove everything from the file area we extracted.
    remove_all_files_from_draft_area($draftareaid, $usercontext, $taskfilename);
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
    $locktype = "qtype_moopt_retrieve_grading_results";
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
    $records = $DB->get_records('qtype_moopt_gradeprocesses', ['qubaid' => $qubaid]);

    if (empty($records)) {
        // Most likely the systems cron job retrieved the result a couple of seconds ago.
        return true;
    }

    foreach ($records as $record) {

        $quba = question_engine::load_questions_usage_by_activity($qubaid);
        $qubarecord = $DB->get_record('question_usages', ['id' => $qubaid]);
        if (!$qubarecord) {
            // The quba got deleted for whatever reason - just ignore this entry.
            // It can be safely deleted at the end of this function because the result has already been fetched.
            continue;
        }
        $slot = $DB->get_record('question_attempts', ['id' => $record->questionattemptdbid], 'slot')->slot;
        try {
            $response = $communicator->get_grading_result($record->graderid, $record->gradeprocessid);
        } catch (resource_not_found_exception $e) {
            // A grading result does not exist and won't ever exist for this grade process id.
            // The middleware or grader returned a HTTP 404 NotFound when polling for a
            // grading result. This case is different from a queued submission for which a grading result does
            // not yet exist (in which case the middleware/grader would just return a HTTP 202 Accepted).
            // With 404, something went wrong with either the middleware or grader.
            // Print this error message. However, printing it will not be visible to anybody
            // if debug output is disabled (which is usually the case for productive usage).
            // Another place too look for the cause of error is the middleware's log files.
            debugging($e->getMessage());

            // In any case, we cannot ignore this particular question attempt anymore, because
            // the polling will be stuck in this state indefinitely (i.e. polling for a result that does
            // not exist (HTTP 404) over and over agian).
            // That's why we set this question attempt's state to needing manual grading and delete the grade
            // process record from the database, thus ending the automatic polling.
            // Once the error has been resolved, a teacher may either start a re-grade, or manually
            // delete the question attempt.
            $quba->process_action($slot, ['-graderunavailable' => 1, 'gradeprocessdbid' => $record->id]);
            question_engine::save_questions_usage_by_activity($quba);
            $finishedgradingprocesses[] = $record->id;
        } catch (Throwable $e) {
            // Treat network errors, authorization errors etc differently, do not abbandon the automatic
            // polling for a grading result, since these types of errors are easily fixable.
            debugging($e->getMessage());
        }

        if($response) {
            $internalerror = false;
            try {
                // We take care of this grade process therefore we will delete it afterwards.
                $finishedgradingprocesses[] = $record->id;

                // Remove old extracted files in case this is a regrade.
                $oldexractedfiles = array_merge(
                        $fs->get_directory_files($qubarecord->contextid, 'question', PROFORMA_RESPONSE_FILE_AREA .
                                "_{$record->questionattemptdbid}", $qubaid, "/", true, true),
                        $fs->get_directory_files($qubarecord->contextid, 'question', PROFORMA_RESPONSE_FILE_AREA_RESPONSEFILE .
                                "_{$record->questionattemptdbid}", $qubaid, "/", true, true),
                        $fs->get_directory_files($qubarecord->contextid, 'question', PROFORMA_RESPONSE_FILE_AREA_EMBEDDED .
                                "_{$record->questionattemptdbid}", $qubaid, "/", true, true)
                );
                foreach ($oldexractedfiles as $f) {
                    $f->delete();
                }

                // Check if response is zip file or xml.
                if (substr($response, 0, 2) == 'PK') {
                    // ZIP file.
                    // Write response to file system.
                    $filerecord = array(
                        'component' => 'question',
                        'filearea' => PROFORMA_RESPONSE_FILE_AREA_RESPONSEFILE . "_{$record->questionattemptdbid}",
                        'itemid' => $qubaid,
                        'contextid' => $qubarecord->contextid,
                        'filepath' => "/",
                        'filename' => 'response.zip');

                    $file = $fs->create_file_from_string($filerecord, $response);
                    $zipper = get_file_packer('application/zip');

                    $couldsaveresponsetodisk = $file->extract_to_storage($zipper, $qubarecord->contextid, 'question', PROFORMA_RESPONSE_FILE_AREA .
                            "_{$record->questionattemptdbid}", $qubaid, "/");
                } else {
                    // XML file.
                    $filerecord = array(
                        'component' => 'question',
                        'filearea' => PROFORMA_RESPONSE_FILE_AREA . "_{$record->questionattemptdbid}",
                        'itemid' => $qubaid,
                        'contextid' => $qubarecord->contextid,
                        'filepath' => "/",
                        'filename' => 'response.xml');

                    $couldsaveresponsetodisk = $fs->create_file_from_string($filerecord, $response);
                }


                // Apply the grade from the response.
                if ($couldsaveresponsetodisk) {
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

                                $overallresult = $mtf[0]->getElementsByTagNameNS($namespace, 'overall-result')[0];

                                if ($overallresult->hasAttribute('is-internal-error') &&
                                        $overallresult->getAttribute('is-internal-error') == 'true') {
                                    $internalerror = true;
                                } else {
                                    $score = $overallresult->getElementsByTagNameNS($namespace, 'score')[0]->nodeValue;
                                }
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
            } catch (\qtype_moopt\exceptions\service_communicator_exception $ex) {
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

            if (!$couldsaveresponsetodisk || !isset($score) || $internalerror) {
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
        $DB->delete_records('qtype_moopt_gradeprocesses', ['id' => $doneid]);
    }

    return !empty($finishedgradingprocesses);
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
    $namespace = str_replace(":", "_", $namespace);
    $schema = file_get_contents(__DIR__ . "/res/proforma/xsd/$namespace.xsd");
    if (!$schema) {
        $msgs[] = get_string('proformanamespaceinvalidorunknown', 'qtype_moopt', $namespace) . '<br/>' 
                . get_string('proformanamespacesvalid', 'qtype_moopt', implode(", ", PROFORMA_TASK_XML_NAMESPACES));
        return $msgs;
    }

    libxml_use_internal_errors(true);
    if (!$doc->schemaValidateSource($schema)) {
        $errors = libxml_get_errors();
        foreach ($errors as $error) {
            $msgs[] = get_string('xmlvalidationerrormsg', 'qtype_moopt', ['message' => $error->message, 'code' => $error->code, 'line' => $error->line ]);
        }
        libxml_clear_errors();
    }
    libxml_use_internal_errors(false);

    return $msgs;
}

/**
 * Checks whether supplied zip file is valid.
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
        return get_string('proformataskfilerequired', 'qtype_moopt');
    } else if ($area['filecount'] > 1 || $area['foldercount'] != 0) {
        return get_string('singleproformataskfilerequired', 'qtype_moopt');
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
        return get_string('taskfilezipexpected', 'qtype_moopt');
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
            return get_string('taskziplackstaskxml', 'qtype_moopt');
        }

        // TODO: Uncomment this to enable validation of task.xml file itself. Currently commented out because most task files
        // don't validate against the schema.
        /*
        $doc = new DOMDocument();
        $doc->loadXML($taskfilecontents);
        $namespace = detect_proforma_namespace($doc);
        if ($namespace == null) {
            return "<p>" . get_string('invalidproformanamespace', 'qtype_moopt',
                    implode(", ", PROFORMA_TASK_XML_NAMESPACES)) . "</p>";
        }
        if (!empty(($errors = validate_proforma_file_against_schema($doc, $namespace)))) {
            $ret = "<p>" . get_string('proformavalidationerrorintro', 'qtype_moopt', $namespace) . "</p><ul>";
            foreach ($errors as $er) {
                $ret .= '<li>' . $er . '</li>';
            }
            $ret .= '</ul>';
            return $ret;
        }
        */
    } else {
        return get_string('suppliedzipinvalid', 'qtype_moopt');
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

/**
 * TODO: check how this works when working with zip files in a submission
 * Checks if the file-restrictions of the specific task.xml file are violated by the submission of a student
 *
 * @param DOMDocument $taskdoc the content of the task.xml file as a DOMDocument
 * @param array $submissionfiles the files that belong to the submission as an array
 * @return array an array that contains the messages with the following fields:
 * generaldescription,
 * maxfilesizeforthistask,
 * filesizesubmitted,
 * requiredfilemissing,
 * prohibitedfileexists
 */
function check_proforma_submission_restrictions(DOMDocument $taskdoc, array $submissionfiles, $qa) : array {
    global $DB;
    $returnval = array();

    $firstfile = "";
    foreach($submissionfiles as $file) {
        $firstfile = $file;
        break;
    }
    if(count($submissionfiles) == 1 && ($firstfile->get_mimetype() === 'application/zip' /* || TODO: check other formats too */)) {
        //unzip it and do the other stuff...
        //$submissionfiles = get_files_inside_archive_file($firstfile);
    }

    $taskxmlnamespace = detect_proforma_namespace($taskdoc);
    $submissionrestrictions = $taskdoc->getElementsByTagNameNS($taskxmlnamespace, 'submission-restrictions')[0];
    if ($submissionrestrictions->hasAttribute('max-size')){
        $sum = 0;
        foreach($submissionfiles as $file) {
            $sum += $file->get_filesize();//$DB->get_record(files, ['id' => $file->get_id()], 'filesize')->filesize;
        }
        if($sum > $submissionrestrictions->getAttribute('max-size')) {
            $returnval['maxfilesizeforthistask'] = $submissionrestrictions->getAttribute('max-size') . " bytes";
            $returnval['filesizesubmitted'] = $sum ." bytes";
        }
    }
    foreach($taskdoc->getElementsByTagNameNS($taskxmlnamespace, 'file-restriction') as $filerestriction) {
        $format = "none";  //The default pattern-format
        if ($filerestriction->hasAttribute('pattern-format')) {
            $format = $filerestriction->getAttribute('pattern-format');
        }
        switch($filerestriction->getAttribute('use')){
            case 'required':
                $nodeValue = add_slash_to_filename($filerestriction->nodeValue, $format);
                if (!does_key_exist_in_array($submissionfiles ,$nodeValue, $format)) {
                    if (empty($returnval["requiredfilemissing"])) {
                        $returnval["requiredfilemissing"] = array();
                    }
                    $returnval["requiredfilemissing"][] = $nodeValue;
                }
                break;
            case 'optional':
                // No actions required here
                break;
            case 'prohibited':
                $nodeValue = add_slash_to_filename($filerestriction->nodeValue, $format);
                if (does_key_exist_in_array($submissionfiles ,$nodeValue, $format)) {
                    if (empty($returnval["prohibitedfileexists"])) {
                        $returnval["prohibitedfileexists"] = array();
                    }
                    $returnval["prohibitedfileexists"][] = $nodeValue;
                }
                break;
            default:
                $use_value = $filerestriction->getAttribute('use');
                throw new InvalidArgumentException("the use-attribute value: '$use_value' is unknown");
                break;
        }
    }
    /* find the description in the child nodes of the 'submission-restrictions' node */
    $description = "";
    foreach($submissionrestrictions->childNodes as $child) {
        if ($child->localName === 'description') {
            $description = $child->nodeValue;
            break;
        }
    }
    //This is done indirectly because we want that the generaldescription field is always set (""), even when it is empty
    //so the following if statement will work
    $returnval["generaldescription"] = $description;
    if(count($returnval) > 1) {
        return $returnval;
    } else {
        return array(); //Just return an empty array when no restrictions are violated
    }
}

/**
 * Checks if a given key exists in a given array
 *
 * @param $array The array in which to search for the key
 * @param $key The key to search for in the given array
 * @param $format The pattern format in which this should be checked: "none" for standard string comparison
 * and "posix-ere" for standard regular expression comparison
 * @return bool whether the key exists in the array or not
 */
function does_key_exist_in_array($array, $key, $format) {
    foreach($array as $arrkey => $element) {
        switch($format) {
            case 'none':
                if ($arrkey === $key) {
                    return true;
                }
                break;
            case 'posix-ere':
                if (preg_match("#$key#", "$arrkey")) {
                    return true;
                }
                break;
            default:
                throw new InvalidArgumentException("pattern-format: '$format' is unknown");
                break;
        }
    }
    return false;
}

/**
 * TODO: check what happens when something of the array contains ' this could mess up the sql query
 * Writes the proforma submission restrictions message to the moodle database
 *
 * @param $msg The array with the proforma submission restrictions message
 * @param $qa The question attempt of the question
 */
function write_proforma_submission_restrictions_msg_to_db($msg, $qa) {
    global $DB;
    /* this is really not the best solution to abuse the responsesummary field of the question_attempt table */
    $responsesummary = render_proforma_submission_restrictions($msg);
    $qaid = $qa->get_database_id();
    $sql = "UPDATE mdl_question_attempts SET responsesummary = '$responsesummary' WHERE id = $qaid";
    $DB->execute($sql);
}

/**
 * Converts the proforma_submission_restrictions_message array into html text
 *
 * @param $msg the array that contains the proforma_submission_restrictions_message
 * @return string the proforma_submission_restrictions_messagem as html text
 */
function render_proforma_submission_restrictions($msg) {
    $o = "<div><h3>Submission Restrictions</h3>";
    $o .= "<p>Your submission violated some restrictions, because of this, your submission will not be graded.</p>";
    $o .= "<br><br>";
    if($msg['generaldescription'] != "") {
        $o .= "<div><h4>General Description</h4>";
        $o .= "<p>{$msg['generaldescription']}</p></div>";
        $o .= "<br>";
    }
    if(!empty($msg['maxfilesizeforthistask'])) {
        $o .= "<div><h4>Filesize</h4>";
        $o .= "<p>The sum of your submitted files exceeded the maximum filesize a submission could have. </p>";
        $o .= "<p>The maximum filesize that is allowed for this task: {$msg['maxfilesizeforthistask']}</p>";
        $o .= "<p>The size of your files in total: {$msg['filesizesubmitted']}</p></div>";
        $o .= "<br>";
    }
    if (!empty($msg['requiredfilemissing'])) {
        $o .= "<div><h4>Files missing</h4>";
        $o .= "<p>There are file(s) missing that are expected to be submitted:</p><ul>";
        foreach($msg['requiredfilemissing'] as $missingfilename) {
            $o .= "<li>$missingfilename</li>";
        }
        $o .= "</ul></div>";
        $o .= "<br>";
    }
    if(!empty($msg['prohibitedfileexists'])) {
        $o .= "<div><h4>Prohibited files</h4>";
        $o .= "<p>You submitted file(s) that are not allowed to be submitted:</p><ul>";
        foreach($msg['prohibitedfileexists'] as $prohibitedfilename) {
            $o .= "<li>$prohibitedfilename</li>";
        }
        $o .= "</ul></div>";
        $o .= "<br>";
    }
    $o .= "</div>";
    return $o;
}

/**
 * @param $filename The filename to check as a String
 * @param $format The pattern-format that is used in the proforma submission restriction
 * @return string returns the filename with an added "/" at the beginning when the proforma pattern-format is "none",
 * if there were a "/" before at the beginning of the filename it will be returned as it was before
 */
function add_slash_to_filename($filename, $format) {
    if ($format == "none") {
        if ("/" != substr($filename, 0, 1)) {
            return "/" . $filename;
        } else {
            return $filename;
        }
    } else {
        return $filename;
    }
}

//TODO: finish this function
function get_files_inside_archive_file($archivefile) {
    global $USER;
    $files = array();
    extract_file_in_area($archivefile, $archivefile->get_filearea(), $archivefile->get_mimetype());
    /* Build the array of the files...



    */
    //Remove all the files again afterwards
    remove_all_files_from_draft_area($archivefile->get_filearea(), context_user::instance($USER->id), $archivefile->get_filename());
    return $files;
}

//TODO: finish this function
function extract_file_in_area($archivefile) {
    //TODO: find out what the other mimetypes are and programm the extracting
    switch($archivefile->get_mimetype()) {
        case 'application/zip':

            break;
        default:
            break;
    }
}
