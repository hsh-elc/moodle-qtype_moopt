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
 * Plugin upgrade steps are defined here.
 *
 * @package     qtype_moopt
 * @category    upgrade
 * @copyright   2019 ZLB-ELC Hochschule Hannover <elc@hs-hannover.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/upgradelib.php');

/**
 * Execute qtype_moopt upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_qtype_moopt_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2021101401) {

        // Define field resultspecformat to be added to qtype_moopt_options.
        $table = new xmldb_table('qtype_moopt_options');
        $field = new xmldb_field('resultspecformat', XMLDB_TYPE_CHAR, '5', null, XMLDB_NOTNULL, null, "zip", 'ftsstandardlang');

        // Conditionally launch add field resultspecformat.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field resultspecstructure to be added to qtype_moopt_options.
        $field = new xmldb_field('resultspecstructure', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, "separate-test-feedback", 'resultspecformat');

        // Conditionally launch add field resultspecstructure.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field studentfeedbacklevel to be added to qtype_moopt_options.
        $field = new xmldb_field('studentfeedbacklevel', XMLDB_TYPE_CHAR, '15', null, XMLDB_NOTNULL, null, "info", 'resultspecstructure');

        // Conditionally launch add field studentfeedbacklevel.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field teacherfeedbacklevel to be added to qtype_moopt_options.
        $field = new xmldb_field('teacherfeedbacklevel', XMLDB_TYPE_CHAR, '15', null, XMLDB_NOTNULL, null, "debug", 'studentfeedbacklevel');

        // Conditionally launch add field teacherfeedbacklevel.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Moopt savepoint reached.
        upgrade_plugin_savepoint(true, 2021101401, 'qtype', 'moopt');
    }

    if ($oldversion < 2021110601) {
        $DB->set_field('quiz', 'preferredbehaviour', 'immediatefeedback', array('preferredbehaviour' => 'immediatemoopt'));
        $DB->set_field('quiz', 'preferredbehaviour', 'deferredfeedback', array('preferredbehaviour' => 'deferredmoopt'));

        // Moopt savepoint reached.
        upgrade_plugin_savepoint(true, 2021110601, 'qtype', 'moopt');
    }

    if ($oldversion < 2021112700) {

        // Define field initialdisplayrows to be added to qtype_moopt_freetexts.
        $table = new xmldb_table('qtype_moopt_freetexts');
        $field = new xmldb_field('initialdisplayrows', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '5', 'filecontent');

        // Conditionally launch add field initialdisplayrows.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Moopt savepoint reached.
        upgrade_plugin_savepoint(true, 2021112700, 'qtype', 'moopt');
    }

    if ($oldversion < 2022011200) {
        // We are going to do this slowly. We will update table mdl_files step by step and use
        // PHP functions where possible as opposed to using one big SQL statement with possibly
        // platform dependent substring functions.


        // First, fix the wrong component name: Change moopt related fileareas
        // from 'question' to 'qtype_moopt'. Otherwise, the backup and restore
        // functions will not work on moopt files.
        $filesrecords = $DB->get_records_sql("SELECT id, pathnamehash, contextid, component, filearea, itemid, filepath, filename
                           FROM {files} WHERE component = 'question' AND (
                         filearea = 'taskfile' OR
                         filearea = 'taskxmlfile' OR
                         filearea = 'attachedtaskfiles' OR
                         filearea = 'embeddedtaskfiles' OR
                         filearea = 'submissionzip' OR
                         filearea LIKE 'responsefilesresponsefile%' OR
                         filearea LIKE'responsefiles%' OR
                         filearea LIKE 'responsefilesembedded%')");
        foreach($filesrecords as $record => $file) {
            $file->timemodified = time();
            $file->component = 'qtype_moopt'; // fix component name to 'qtype_moopt'
            // Since the component value changed, we must update the pathnamehash
            // for all of these file records, otherwise we'll end up with broken
            // file links.
            $file->pathnamehash = pathnamehash($file->contextid, $file->component,
                $file->filearea, $file->itemid, $file->filepath, $file->filename);
            $DB->update_record('files', $file);
        }

        // Next, extract attempt-db-id from the filearea value and
        // write that value to itemid. Also fix filearea name
        // by removing the _<id> suffix
        $filesrecords = $DB->get_records_sql("SELECT id, pathnamehash, contextid, component, filearea, itemid, filepath, filename
               FROM {files} WHERE component = 'qtype_moopt' 
                                AND (filearea LIKE 'responsefilesresponsefile%' 
                                         OR filearea LIKE'responsefiles%' 
                                         OR filearea LIKE 'responsefilesembedded%')");
        foreach($filesrecords as $record => $file) {
            $file->timemodified = time();
            $separatorpos = strpos($file->filearea, '_');
            if($separatorpos) {
                $attemptdbid = substr($file->filearea, $separatorpos + 1);
                $fixedfilearea = substr($file->filearea, 0, $separatorpos);
                $file->filearea = $fixedfilearea;
                $file->itemid = $attemptdbid;
                // Since we made other changes to response-related files,
                // update pathnamehash once again for all affected
                $file->pathnamehash = pathnamehash($file->contextid, $file->component,
                    $file->filearea, $file->itemid, $file->filepath, $file->filename);
                $DB->update_record('files', $file);
            }
        }

        // Update the contextid of all fileareas related to response files.
        // We need to use the question's contextid instead of the
        // one that belongs to a question_usage. The backup restore process
        // only recovers files that are directly mapped to contextid belonging
        // to the question that is being restored (refer to class
        // restore_create_question_files).
        //
        // Also update the pathnamehash at last, since its value depends on
        // the columns that have been changed throughout this upgrade process.
        $filesrecords = $DB->get_records_sql(
            "SELECT f.id, f.pathnamehash, f.contextid, mqc.contextid as newctxid, f.component, f.filearea, f.itemid, f.filepath, f.filename
                FROM {files} f 
                INNER join {question_attempts} mqa ON f.itemid  = mqa.id 
                INNER join {question} mq ON mqa.questionid = mq.id 
                INNER join {question_categories} mqc ON mq.category = mqc.id
                WHERE component = 'qtype_moopt' AND (
                         filearea = 'responsefilesresponsefile' OR
                         filearea ='responsefiles' OR
                         filearea = 'responsefilesembedded')");
        foreach($filesrecords as $record => $file) {
            $file->timemodified = time();
            $file->contextid = $file->newctxid;
            $file->pathnamehash = pathnamehash($file->contextid, $file->component,
                $file->filearea, $file->itemid, $file->filepath, $file->filename);
            $DB->update_record('files', $file);
        }

        // Moopt savepoint reached.
        upgrade_plugin_savepoint(true, 2022011200, 'qtype', 'moopt');
    }

    return true;
}
