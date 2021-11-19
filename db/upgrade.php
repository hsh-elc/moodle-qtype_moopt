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

    if ($oldversion < 2021110600) {
        $DB->set_field('quiz', 'preferredbehaviour', 'immediatefeedback', array('preferredbehaviour' => 'immediatemoopt'));
        $DB->set_field('quiz', 'preferredbehaviour', 'deferredfeedback', array('preferredbehaviour' => 'deferredmoopt'));

        // Moopt savepoint reached.
        upgrade_plugin_savepoint(true, 2021110600, 'qtype', 'moopt');
    }

    return true;
}
