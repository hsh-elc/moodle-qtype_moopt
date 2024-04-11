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

define('COMPONENT_NAME', 'qtype_moopt');

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
// - component: qtype
// - context: a context of level 50 (question related)
// - filearea: the labels responsefilesresponsefile, responsefiles or responsefilesembedded
// - itemid: question attempt db id
// - filepath: the path inside a response.zip file  (or / in case of responsefilesresponsefile)
// - filename: the filename inside the response.zip  (or the name of the zip file in case of responsefilesresponsefile)


define('PROFORMA_RETRIEVE_GRADING_RESULTS_LOCK_MAXLIFETIME', 10);

// ProFormA task xml namespaces.
define('PROFORMA_TASK_XML_NAMESPACES', [/* First namespace is default namespace. */'urn:proforma:v2.1']);

define('PROFORMA_MERGED_FEEDBACK_TYPE', 'merged-test-feedback');
define('PROFORMA_SEPARATE_FEEDBACK_TYPE', 'separate-test-feedback');
define('PROFORMA_RESULT_SPEC_FORMAT_ZIP', 'zip');
define('PROFORMA_RESULT_SPEC_FORMAT_XML', 'xml');
define('PROFORMA_FEEDBACK_LEVEL_ERROR', 'error');
define('PROFORMA_FEEDBACK_LEVEL_WARNING', 'warn');
define('PROFORMA_FEEDBACK_LEVEL_INFO', 'info');
define('PROFORMA_FEEDBACK_LEVEL_DEBUG', 'debug');
define('PROFORMA_FEEDBACK_LEVEL_NOTSPECIFIED', 'notspecified');

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

// Free text input fields will show 5 rows by default:
define('DEFAULT_INITIAL_DISPLAY_ROWS', 5);

// The character that separates the gradername and the graderversion in the options of the grader selection dropdown
define('GRADERID_SEPARATOR', '$');

require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/mod/quiz/attemptlib.php');
require_once($CFG->dirroot . '/mod/quiz/accessmanager.php');

use qtype_moopt\exceptions\resource_not_found_exception;
use qtype_moopt\utility\communicator\communicator_factory;
use qtype_moopt\utility\proforma_xml\separate_feedback_handler;

/*
 * Unzips the task zip file in the given draft area into the area
 *
 * @param type $draftareaid
 * @param type $usercontext
 * @return array the name of the task zip file and the task xml file.
 *      [
 *        'zip' => (string) the name of the zip file, if any (the key 'zip' is optional)
 *        'xml' => (string) the name of the xml file (mandatory)
 *      ]
 *      Returns false, if there is no file in the given draft area.
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
    if ($filetype == 'xml') {
        return array('xml' => $filename);
    }
    if ($filetype != 'zip') {
        throw new invalid_parameter_exception('Supplied file must be a xml or zip file.');
    }
    $zipfilename = $filename;
    $result = array('zip' => $zipfilename);

    // Unzip file - basically copied from draftfiles_ajax.php.
    $zipper = get_file_packer('application/zip');

    // Find unused name for directory to extract the archive.
    $temppath = $fs->get_unused_dirname($usercontext->id, 'user', 'draft', $draftareaid, "/" . pathinfo($zipfilename,
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
                $exfile->set_source(serialize((object)array('source' => '/')));
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
            if (!$exfile->is_directory() && $realpath == '/' && $exfile->get_filename() == 'task.xml') {
                $result['xml'] = $exfile->get_filename();
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

    if (!array_key_exists('xml', $result)) {
        throw new invalid_parameter_exception('Supplied zip file must contain the file task.xml.');
    }

    return $result;
}

/**
 * Removes all files and directories from the given draft area except a file with the given file name
 *
 * @param type $draftareaid
 * @param type $user_context
 * @param type $excluded_file_name
 */
function remove_all_files_from_draft_area($draftareaid, $usercontext, $excludedfilename)
{
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
 * @param type $xmlfilename
 * @param type $zipfilename (optional, only if user uploaded a zip)
 * @return \DOMDocument
 * @throws invalid_parameter_exception
 */

function create_domdocument_from_task_xml($usercontext, $draftareaid, $xmlfilename, $zipfilename)
{
    $fs = get_file_storage();
    $file = $fs->get_file($usercontext->id, 'user', 'draft', $draftareaid, "/", $xmlfilename);
    if (!$file) {
        remove_all_files_from_draft_area($draftareaid, $usercontext, $zipfilename);
        throw new invalid_parameter_exception('Supplied zip file doesn\'t contain task.xml file.');
    }

    $doc = new DOMDocument();
    if (!$doc->loadXML($file->get_content())) {
        remove_all_files_from_draft_area($draftareaid, $usercontext, $zipfilename);
        throw new invalid_parameter_exception('Error parsing the supplied ' . $xmlfilename . ' file. See server log for details.');
    }

    return $doc;
}

/**
 * Get the text content of a file -- attached or embedded, from the task.zip
 *
 * @param $usercontext
 * @param $draftareaid
 * @param $keepfilename
 * @param $filepath
 * @param $filename
 * @return string
 * @throws invalid_parameter_exception
 */
function get_text_content_from_file($usercontext, $draftareaid, $keepfilename, $filepath, $filename, $attached, $encoding)
{
    $fs = get_file_storage();
    $file = $fs->get_file($usercontext->id, 'user', 'draft', $draftareaid, $filepath, $filename);
    if (!$file) {
        remove_all_files_from_draft_area($draftareaid, $usercontext, $keepfilename);
        throw new invalid_parameter_exception('Supplied file doesn\'t contain file ' . $filepath . $filename . '.');
    }

    // TODO: make sure the mimetype is plain text
    // even task.xmls may contain mistakes (eg PDF )

    //check if encoding of attached file is utf-8 else convert
    $content = $file->get_content();
    if($attached){
        if($encoding!=null){
            $enc=$encoding;
        } else {
            $enc = mb_detect_encoding($content, null, true);
            if($enc===false){
                throw new invalid_parameter_exception('Encoding of attached file ' . $filepath . $filename . ' could\'nt be detectet.');
            }
        }
        if($enc!=='UTF-8'){
            $content = mb_convert_encoding($content, 'UTF-8', $enc);        
        }
    }
    
    return $content;
}


/**
 * Get the stored_file from the file area PROFORMA_TASKXML_FILEAREA, if any.
 * @return stored_file|bool the file or false, if not found.
 * @throws Exception if there is more than one file in that area.
 */
function get_task_xml_file_from_filearea($question)
{
    $fs = get_file_storage();
    $files = $fs->get_area_files($question->contextid, COMPONENT_NAME, PROFORMA_TASKXML_FILEAREA, $question->id, "", false);
    if (empty($files)) return false;
    if (count($files) > 1) {
        throw new Exception('Internal error. Unexpected ' + count($files) + ' in file area ' + PROFORMA_TASKXML_FILEAREA + ' of question ' + $question->id);
    }
    return reset($files); // first value
}


function save_task_and_according_files($question)
{
    global $USER, $DB;

    if (!isset($question->proformataskfileupload)) {
        return;
    }
    $draftareaid = $question->proformataskfileupload;

    $usercontext = context_user::instance($USER->id);

    $unzipinfo = unzip_task_file_in_draft_area($draftareaid, $usercontext);
    if (!$unzipinfo) {
        // Seems like no task file was submitted.
        return false;
    }
    $taskxmlfilename = $unzipinfo['xml'];
    $taskzipfilename = $unzipinfo['zip'] ?? null;
    $keepfilename = $taskzipfilename != null ? $taskzipfilename : $taskxmlfilename;

    // Copy all extracted files to the corresponding file area.
    file_save_draft_area_files($draftareaid, $question->context->id, COMPONENT_NAME, PROFORMA_ATTACHED_TASK_FILES_FILEAREA,
        $question->id, array('subdirs' => true));

    $doc = create_domdocument_from_task_xml($usercontext, $draftareaid, $taskxmlfilename, $taskzipfilename);
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
                    'component' => COMPONENT_NAME,
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
                $record->filepath = $pathinfo['basename'] == $child->nodeValue ? '/' : $pathinfo['dirname'] . '/';
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
    $file = $fs->get_file($question->context->id, COMPONENT_NAME, PROFORMA_ATTACHED_TASK_FILES_FILEAREA, $question->id,
        '/', $taskxmlfilename);
    $newfilerecord = array(
        'component' => COMPONENT_NAME,
        'filearea' => PROFORMA_TASKXML_FILEAREA,
        'itemid' => $question->id,
        'contextid' => $question->context->id,
        'filepath' => '/',
        'filename' => $taskxmlfilename);
    $fs->create_file_from_storedfile($newfilerecord, $file);
    $file->delete();

    $record = new stdClass();
    $record->questionid = $question->id;
    $record->fileid = 'taskxml';
    $record->usedbygrader = 0;
    $record->visibletostudents = 'no';
    $record->usagebylms = 'download';
    $record->filepath = '/';
    $record->filename = $taskxmlfilename;
    $record->filearea = PROFORMA_TASKXML_FILEAREA;
    $filesfordb[] = $record;

    if ($taskzipfilename != null) {
        // Now move the task zip file to the designated area.
        $file = $fs->get_file($question->context->id, COMPONENT_NAME, PROFORMA_ATTACHED_TASK_FILES_FILEAREA, $question->id, '/', $taskzipfilename);
        $newfilerecord = array(
            'component' => COMPONENT_NAME,
            'filearea' => PROFORMA_TASKZIP_FILEAREA,
            'itemid' => $question->id,
            'contextid' => $question->context->id,
            'filepath' => '/',
            'filename' => $taskzipfilename);
        $fs->create_file_from_storedfile($newfilerecord, $file);
        $file->delete();

        $record = new stdClass();
        $record->questionid = $question->id;
        $record->fileid = 'task';
        $record->usedbygrader = 0;
        $record->visibletostudents = 'no';
        $record->usagebylms = 'download';
        $record->filepath = '/';
        $record->filename = $taskzipfilename;
        $record->filearea = PROFORMA_TASKZIP_FILEAREA;
        $filesfordb[] = $record;
    }

    // Save all records in database.
    $DB->insert_records('qtype_moopt_files', $filesfordb);

    // Do a little bit of cleanup and remove everything from the file area we extracted.
    remove_all_files_from_draft_area($draftareaid, $usercontext, $keepfilename);
}

/**
 * Checks if any of the grade processes belonging to the given $qubaid finished. If so retrieves the grading results and
 * writes them to the system.
 * @param type $qubaid
 * @return array an array with two elements with the following keys:
 *      'finished' - a boolean value that indicates whether any grade process finished,
 *      'estimatedSecondsRemainingForEachQuestion' - another array that contains several arrays as elements,
 *          each of these array's contains two elements with the following keys:
 *              'questionId',
 *              'estimatedSecondsRemaining'
 */
function retrieve_grading_results($qubaid) : array
{

    /*
     * This function is called from both the webservice and the task api therefore it might happen that both calls happen at
     * the same time
     * and try to access the same qubaid which can lead to unwanted behaviour. Hence use the locking api.
     */
    $locktype = "qtype_moopt_retrieve_grading_results";
    $resource = "qubaid:$qubaid";
    $lockfactory = \core\lock\lock_config::get_lock_factory($locktype);
    $lock = $lockfactory->get_lock($resource, 0, PROFORMA_RETRIEVE_GRADING_RESULTS_LOCK_MAXLIFETIME);
    if (!$lock) {
        return array(
            'estimatedSecondsRemainingForEachQuestion' => array(),
            'finished' => false
        );
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
 * @return array an array with two elements with the following keys:
 *      'finished' - a boolean value that indicates whether any grade process finished,
 *      'estimatedSecondsRemainingForEachQuestion' - another array that contains several arrays as elements,
 *          each of these array's contains two elements with the following keys:
 *              'questionId',
 *              'estimatedSecondsRemaining'
 */
function internal_retrieve_grading_results($qubaid) : array
{
    global $DB, $USER;
    $communicator = communicator_factory::get_instance();
    $fs = get_file_storage();

    $ret = array();
    $ret['estimatedSecondsRemainingForEachQuestion'] = array();

    $finishedgradingprocesses = [];
    $qubarecord = $DB->get_record('question_usages', ['id' => $qubaid]);
    $gradeprocessrecords = $DB->get_records('qtype_moopt_gradeprocesses', ['qubaid' => $qubaid]);
    foreach ($gradeprocessrecords as $gradeprocrecord) {
        if (!$qubarecord) {
            // The quba got deleted for whatever reason. There's no need to keep this particular gradeprocess
            // record anymore because it has no purpose without its quba, so we mark it for deletion.
            $finishedgradingprocesses[] = $gradeprocrecord->id;
            continue;
        }

        $quba = question_engine::load_questions_usage_by_activity($qubaid);

        $slot = $DB->get_record('question_attempts', ['id' => $gradeprocrecord->questionattemptdbid], 'slot')->slot;
        try {
            $response = $communicator->get_grading_result($gradeprocrecord->gradername, $gradeprocrecord->graderversion, $gradeprocrecord->gradeprocessid);
        }
        //catch (service_communicator_exception $ex) {
           // Do not handle service_communicator_exceptions (service unavailable) here, since this function is
           // not called by users but rather by tasks and intervals. No need to indicate a service unavailable
           // exception to automated callers.
           // Let this function fail silently with the catch(Throwable) below.
        //}
        catch (resource_not_found_exception $e) {
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
            $quba->process_action($slot, ['-graderunavailable' => 1, 'gradeprocessdbid' => $gradeprocrecord->id]);
            question_engine::save_questions_usage_by_activity($quba);
            $finishedgradingprocesses[] = $gradeprocrecord->id;
        } catch (Throwable $e) {
            // Treat network errors, authorization errors etc differently, do not abbandon the automatic
            // polling for a grading result, since these types of errors are easily fixable.
            debugging($e->getMessage());
        }

        if ($response->finished) {
            $internalerror = false;
            $hasdisplayablefeedback = false;
            $couldsaveresponsetodisk = false;
            try {
                // We take care of this grade process therefore we will delete it afterwards.
                $finishedgradingprocesses[] = $gradeprocrecord->id;

                // Remove old extracted files in case this is a regrade.
                $oldexractedfiles = array_merge(
                    $fs->get_directory_files($quba->get_question_attempt($slot)->get_question()->contextid,
                        COMPONENT_NAME, PROFORMA_RESPONSE_FILE_AREA,
                        $gradeprocrecord->questionattemptdbid, "/", true, true),
                    $fs->get_directory_files($quba->get_question_attempt($slot)->get_question()->contextid, COMPONENT_NAME, PROFORMA_RESPONSE_FILE_AREA_RESPONSEFILE,
                        $gradeprocrecord->questionattemptdbid, "/", true, true),
                    $fs->get_directory_files($quba->get_question_attempt($slot)->get_question()->contextid, COMPONENT_NAME, PROFORMA_RESPONSE_FILE_AREA_EMBEDDED, $gradeprocrecord->questionattemptdbid, "/", true, true)
                );
                foreach ($oldexractedfiles as $f) {
                    $f->delete();
                }

                // Check if response is zip file or xml.
                if (substr($response->response, 0, 2) == 'PK') {
                    // ZIP file.
                    // Write response to file system.
                    $filerecord = array(
                        'component' => COMPONENT_NAME,
                        'filearea' => PROFORMA_RESPONSE_FILE_AREA_RESPONSEFILE,
                        'itemid' => $gradeprocrecord->questionattemptdbid,
                        'contextid' => $quba->get_question($slot)->contextid,
                        'filepath' => "/",
                        'filename' => 'response.zip');

                    $file = $fs->create_file_from_string($filerecord, $response->response);
                    $zipper = get_file_packer('application/zip');

                    $couldsaveresponsetodisk = $file->extract_to_storage($zipper, $quba->get_question($slot)->contextid,
                        COMPONENT_NAME, PROFORMA_RESPONSE_FILE_AREA,
                        $quba->get_question_attempt($slot)->get_database_id(), "/");
                } else {
                    // XML file.
                    $filerecord = array(
                        'component' => COMPONENT_NAME,
                        'filearea' => PROFORMA_RESPONSE_FILE_AREA,
                        'itemid' => $gradeprocrecord->questionattemptdbid,
                        'contextid' => $quba->get_question($slot)->contextid,
                        'filepath' => "/",
                        'filename' => 'response.xml');

                    $couldsaveresponsetodisk = $fs->create_file_from_string($filerecord, $response->response);
                }


                // Apply the grade from the response.
                if ($couldsaveresponsetodisk) {
                    $doc = new DOMDocument();
                    $responsexmlfile = $fs->get_file($quba->get_question($slot)->contextid,
                        COMPONENT_NAME, PROFORMA_RESPONSE_FILE_AREA,
                        $gradeprocrecord->questionattemptdbid, "/", 'response.xml');
                    if ($responsexmlfile) {

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
                                    'component' => COMPONENT_NAME,
                                    'filearea' => PROFORMA_RESPONSE_FILE_AREA_EMBEDDED,
                                    'itemid' => $gradeprocrecord->questionattemptdbid,
                                    'contextid' => $quba->get_question($slot)->contextid,
                                    'filepath' => $pathinfo['dirname'] . '/',
                                    'filename' => $pathinfo['basename']);
                                $fs->create_file_from_string($fileinfo, $content);
                            }

                            $mtf = $doc->getElementsByTagNameNS($namespace, 'merged-test-feedback');
                            if ($mtf->length == 1) {
                                // Merged test feedback.
                                $overallresult = $mtf[0]->getElementsByTagNameNS($namespace, 'overall-result')[0];

                                $internalerror = $overallresult->hasAttribute('is-internal-error') &&
                                    $overallresult->getAttribute('is-internal-error') == 'true';
                                // get the score despite any is-internal-error flag, we will use the score regardless
                                $score = $overallresult->getElementsByTagNameNS($namespace, 'score')[0]->nodeValue;
                                $hasdisplayablefeedback = true;
                            } else {
                                // Separate test feedback.
                                $question = $quba->get_question($slot);

                                $separatetestfeedback = $doc->getElementsByTagNameNS($namespace, 'separate-test-feedback')[0];

                                // Load task.xml to get grading hints and tests.
                                $taskxmlfile = get_task_xml_file_from_filearea($question);
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

                                $internalerror = $separatefeedbackhelper->get_detailed_feedback()->getSeparateFeedbackData()->has_internal_error();
                                // retrieve the score and apply it (later on) regardless of whether there
                                // was an error or not
                                $score = $separatefeedbackhelper->get_calculated_score();
                                $hasdisplayablefeedback = true;
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

            $qubagradingresultdata = array('gradeprocessdbid' => $gradeprocrecord->id);
            if (!$couldsaveresponsetodisk || !$hasdisplayablefeedback) {
                // Change the state to the question needing manual grading because something went wrong
                $qubagradingresultdata['-graderunavailable'] = 1;
            } else  {
                // Even when the grader is unavailable or failing because of internal errors, there might
                // be displayable feedback. We record this in order to display that feedback when rendering.
                // We don't have a score.
                $qubagradingresultdata['-gradingresult'] = 1;
            }
            if (isset($score)) {
                // At this point, we explicitly ignore the Proforma Whitepaper stating that student submissions
                // should be invalidated if a response was returned with the is-internal-error flag set to true.
                // This is because this plugin does not support limitations on submissions yet, and because
                // even if one or some of the many sub-tests failed, displaying the scores achieved
                // for other sub-tests is more helpful to the student than showing them none at all.
                $qubagradingresultdata['score'] = $score;
            }
            // Apply the grading result to the question attempt
            $quba->process_action($slot, $qubagradingresultdata);
            question_engine::save_questions_usage_by_activity($quba);

            // make sure we are in the context of a quiz and not a preview before proceeding with updating the quizze's
            // attempt data with a total mark
            $attemptrecord = $DB->get_record('quiz_attempts', ['uniqueid' => $qubaid]);
            if (!$attemptrecord)
                continue;
            $attempt = quiz_attempt::create_from_usage_id($qubaid)->get_attempt();
            $attempt->timemodified = time();
            $attempt->sumgrades = $quba->get_total_mark();
            $DB->update_record('quiz_attempts', $attempt);

            // Update gradebook.
            $quiz = $DB->get_record('quiz', array('id' => $attempt->quiz), '*', MUST_EXIST);
            quiz_save_best_grade($quiz, $USER->id);


        } else {
            if ($response->response != null) {
                $estimatedSecondsRemainingForCurrentQuestion = array();
                $estimatedSecondsRemainingForCurrentQuestion['questionId'] = $quba->get_question_attempt($slot)->get_question_id();
                $estimatedSecondsRemainingForCurrentQuestion['estimatedSecondsRemaining'] = $response->response;
                $ret['estimatedSecondsRemainingForEachQuestion'][] = $estimatedSecondsRemainingForCurrentQuestion;
            }
        }
    }

    foreach ($finishedgradingprocesses as $doneid) {
        $DB->delete_records('qtype_moopt_gradeprocesses', ['id' => $doneid]);
    }

    $ret['finished'] = !empty($finishedgradingprocesses);
    return $ret;
}

function detect_proforma_namespace(DOMDocument $doc)
{
    foreach (PROFORMA_TASK_XML_NAMESPACES as $namespace) {
        if ($doc->getElementsByTagNameNS($namespace, "task")->length != 0 ||
            $doc->getElementsByTagNameNS($namespace, "submission")->length != 0 ||
            $doc->getElementsByTagNameNS($namespace, "response")->length != 0) {
            return $namespace;
        }
    }
    return null;
}

function validate_proforma_file_against_schema(DOMDocument $doc, $namespace): array
{
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
            $msgs[] = get_string('xmlvalidationerrormsg', 'qtype_moopt', ['message' => $error->message, 'code' => $error->code, 'line' => $error->line]);
        }
        libxml_clear_errors();
    }
    libxml_use_internal_errors(false);

    return $msgs;
}

/**
 *  Checks whether supplied zip file is valid.
 * @param type $draftareaid
 * @return string null if there are no errors. The error message otherwise
 * @global type $USER
 */
function check_if_task_file_is_valid($draftareaid)
{
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

    if ($filetype == 'xml') {
        $taskfilecontents = $file->get_content();
    } else {
        if ($filetype != 'zip') {
            return get_string('taskfileziporxmlexpected', 'qtype_moopt');
        }
        $zipfilename = $filename;

        // Unzip file - basically copied from draftfiles_ajax.php.
        $zipper = get_file_packer('application/zip');

        // Find unused name for directory to extract the archive.
        $temppath = $fs->get_unused_dirname($usercontext->id, 'user', 'draft', $draftareaid, "/" . pathinfo($zipfilename,
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
            $fs->get_file($usercontext->id, 'user', 'draft', $draftareaid, "/" . pathinfo($zipfilename, PATHINFO_FILENAME) .
                '/', '.')->delete();

            if ($taskfilecontents == null) {
                return get_string('taskziplackstaskxml', 'qtype_moopt');
            }

        } else {
            return get_string('suppliedzipinvalid', 'qtype_moopt');
        }
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

    return null;
}

function readLmsInputFieldSettingsFromTaskXml(\DOMDocument $doc)
{
    // prepare return values:
    $includeenablefileinput = false;
    $enablefileinput = false;
    $lmsinputfieldsettings = array();

    $lmsinputfieldsV01 = $doc->getElementsByTagNameNS('urn:proforma:lmsinputfields:v0.1', 'lms-input-fields');
    $lmsinputfieldsV02 = $doc->getElementsByTagNameNS('urn:proforma:lmsinputfields:v0.2', 'lms-input-fields');
    if (1 <= $lmsinputfieldsV01->length && 1 <= $lmsinputfieldsV02->length) {
        throw new Exception('Task meta-data contains more than one lms-input-fields element.');
    }
    if (1 == $lmsinputfieldsV01->length) {
        $lmsinputfields = $lmsinputfieldsV01;
        $version = "0.1";
    } else if (1 == $lmsinputfieldsV02->length) {
        $lmsinputfields = $lmsinputfieldsV02;
        $version = "0.2";
    }
    if (1 < $lmsinputfields->length) {
        throw new Exception('Task meta-data contains more than one lms-input-fields element.');
    }
    if (1 == $lmsinputfields->length) {
        $includeenablefileinput = true;
        foreach ($lmsinputfields[0]->childNodes as $child) {
            if ($child->localName == 'fileinput') {
                // Moopt doesn't support multiple fileinputs, meaning multiple draft areas for file upload.
                // If at least one fileinput is configured in lms-input-fields, use that as an indicator to
                // use one draft area and that's it. Moopt also doesn't  support the fixedfilename and
                // proglang attributes of the fileinput element.
                $enablefileinput = true;
            } else if ($child->localName == 'textfield') {
                // fixedfilename is true by default if not set via lmsinputfields
                $fixedfilename = true;
                if ($child->hasAttribute('fixedfilename')) {
                    $fixedfilename = filter_var($child->attributes->getNamedItem('fixedfilename')->nodeValue,
                        FILTER_VALIDATE_BOOLEAN);
                }
                $proglang = 'txt';
                if ($child->hasAttribute('proglang')) {
                    $lang = $child->attributes->getNamedItem('proglang')->nodeValue;
                    // make sure the task's lmsinputfields contains a valid proglang value
                    if (array_key_exists($lang, PROFORMA_ACE_PROGLANGS))
                        $proglang = $lang;
                }
                $initialdisplayrows = DEFAULT_INITIAL_DISPLAY_ROWS;
                if (strcmp("0.2", $version) <= 0) {
                    if ($child->hasAttribute('initial-display-rows')) {
                        $initialdisplayrows = $child->attributes->getNamedItem('initial-display-rows')->nodeValue;
                    }
                }
                $settings = array('fixedfilename' => $fixedfilename, 'proglang' => $proglang, 'initialdisplayrows' => $initialdisplayrows);
                $lmsinputfieldsettings[$child->attributes->getNamedItem('file-ref')->nodeValue] = $settings;
            }
        }
    }

    return array(
        $includeenablefileinput,
        $enablefileinput,
        $lmsinputfieldsettings);
}

/**
 * Converts the graderid (gradername and graderversion) of a grader into the html_representation of the grader.
 * This is needed so that the grader can be identified in the HTML forms
 * The html representation of a graderid is: {gradername}{GRADERID_SEPARATOR}{graderversion}
 * @param $gradername
 * @param $graderversion
 * @return string the html representation of the gradername and graderversion
 */
function get_html_representation_of_graderid($gradername, $graderversion) : string {
    //if this code is changed, the code of "get_name_and_version_from_graderid_html_representation()" must probably be changed too
    return $gradername . GRADERID_SEPARATOR . $graderversion;
}

/**
 * Converts the html representation of a graderid back into the gradername and graderversion
 * The html representation of a graderid is: {gradername}{GRADERID_SEPARATOR}{graderversion}
 * @param $html_representation
 * @return stdClass an object with the properties: gradername, graderversion
 */
function get_name_and_version_from_graderid_html_representation($html_representation) : stdClass {
    //if this code is changed, the code of "get_html_representation_of_graderid()" must probably be changed too
    $graderid = new stdClass();
    $arr = explode(GRADERID_SEPARATOR, $html_representation);
    $graderid->gradername = $arr[0];
    $graderid->graderversion = $arr[1];
    return $graderid;
}

/**
 * @return array with one entry for every available grader. Every entry contains further information about the grader
 */
function get_available_graders_form_data(): array {
    global $COURSE;
    $availableGraders = array();
    $graders = communicator_factory::get_instance()->get_graders()['graders'];
    foreach ($graders as $grader) {
        $key = array_push($availableGraders, $grader) - 1;
        $graderid_html_representation = get_html_representation_of_graderid($grader['name'], $grader['version']);
        //Add this field so creation_via_drag_and_drop.js can select the grader
        $availableGraders[$key]['html_representation'] = $graderid_html_representation;
    }
    return $availableGraders;
}

// Copied from zip_archive::mangle_pathname.
function mangle_pathname($filename) {
    $filename = trim($filename, '/');
    $filename = str_replace('\\', '/', $filename);   // No MS \ separators.
    $filename = preg_replace('/\.\.+/', '', $filename); // Prevent /.../   .
    $filename = ltrim($filename, '/');                  // No leading slash.
    return $filename;
}
