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

    if ($oldversion < 2022130100) {

        // Define field gradername to be added to qtype_moopt_options.
        $table = new xmldb_table('qtype_moopt_options');
        $field = new xmldb_field('gradername', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL, null, null, 'graderid');

        // Conditionally launch add field gradername.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field graderversion to be added to qtype_moopt_options.
        $table = new xmldb_table('qtype_moopt_options');
        $field = new xmldb_field('graderversion', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, null, 'gradername');

        // Conditionally launch add field graderversion.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Migrate old data in the options table //TODO: implement this for all other graders
        $DB->execute("UPDATE {qtype_moopt_options} SET gradername = 'Graja', graderversion = '2.2' WHERE graderid = 'Graja2.2'");
        $DB->execute("UPDATE {qtype_moopt_options} SET gradername = 'GraFlap', graderversion = '1.0' WHERE graderid = 'GraFlap'");

        // Define field graderid to be dropped from qtype_moopt_options.
        $table = new xmldb_table('qtype_moopt_options');
        $field = new xmldb_field('graderid');

        // Conditionally launch drop field graderid.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }


        // Define field gradername to be added to qtype_moopt_gradeprocesses.
        $table = new xmldb_table('qtype_moopt_gradeprocesses');
        $field = new xmldb_field('gradername', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL, null, null, 'gradeprocessid');

        // Conditionally launch add field gradername.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field graderversion to be added to qtype_moopt_gradeprocesses.
        $table = new xmldb_table('qtype_moopt_gradeprocesses');
        $field = new xmldb_field('graderversion', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, null, 'gradername');

        // Conditionally launch add field graderversion.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Migrate old data in the gradeprocesses table //TODO: implement this for all other graders
        $DB->execute("UPDATE {qtype_moopt_gradeprocesses} SET gradername = 'Graja', graderversion = '2.2' WHERE graderid = 'Graja2.2'");
        $DB->execute("UPDATE {qtype_moopt_gradeprocesses} SET gradername = 'GraFlap', graderversion = '1.0' WHERE graderid = 'GraFlap'");

        // Define field graderid to be dropped from qtype_moopt_gradeprocesses.
        $table = new xmldb_table('qtype_moopt_gradeprocesses');
        $field = new xmldb_field('graderid');

        // Conditionally launch drop field graderid.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Moopt savepoint reached.
        upgrade_plugin_savepoint(true, 2022130100, 'qtype', 'moopt');
    }

    return true;
}
