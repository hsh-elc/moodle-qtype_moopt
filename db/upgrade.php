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
 * @package     qtype_programmingtask
 * @category    upgrade
 * @copyright   2019 ZLB-ELC Hochschule Hannover <elc@hs-hannover.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/upgradelib.php');

/**
 * Execute qtype_programmingtask upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_qtype_programmingtask_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2019031700) {

        $optionsTable = new xmldb_table('qtype_programmingtask_optns');

        $optionsTable->addField(new xmldb_field('id', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null));
        $optionsTable->addField(new xmldb_field('questionid', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null));
        $optionsTable->addField(new xmldb_field('internaldescription', XMLDB_TYPE_TEXT, 'medium', null, null, null, null));

        $optionsTable->addKey(new xmldb_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null));
        $optionsTable->addKey(new xmldb_key('questionid', XMLDB_KEY_FOREIGN, array('questionid'), 'question', 'id'));

        $dbman->create_table($optionsTable);


        $filesTable = new xmldb_table('qtype_programmingtask_files');

        $filesTable->addField(new xmldb_field('id', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null));
        $filesTable->addField(new xmldb_field('questionid', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null));
        $filesTable->addField(new xmldb_field('fileid', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL, null, null));
        $filesTable->addField(new xmldb_field('usedbygrader', XMLDB_TYPE_INTEGER, 2, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null));
        $filesTable->addField(new xmldb_field('visibletostudents', XMLDB_TYPE_INTEGER, 2, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null));
        $filesTable->addField(new xmldb_field('usagebylms', XMLDB_TYPE_CHAR, 64, null, XMLDB_NOTNULL, null, 'download'));
        $filesTable->addField(new xmldb_field('filepath', XMLDB_TYPE_TEXT, 'medium', null, XMLDB_NOTNULL, null, null));
        $filesTable->addField(new xmldb_field('filename', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL, null, null));
        $filesTable->addField(new xmldb_field('filearea', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL, null, null));

        $filesTable->addKey(new xmldb_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null));
        $filesTable->addKey(new xmldb_key('questionid', XMLDB_KEY_FOREIGN, array('questionid'), 'question', 'id'));

        $filesTable->addIndex(new xmldb_index('fileid', XMLDB_INDEX_NOTUNIQUE, array('fileid')));

        $dbman->create_table($filesTable);

        // ProForma savepoint reached.
        upgrade_plugin_savepoint(true, 2019031700, 'qtype', 'programmingtask');
    }

    if ($oldversion < 2019052601) {

        $graderTable = new xmldb_table('qtype_programmingtask_gradrs');
        $graderTable->addField(new xmldb_field('graderid', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL));
        $graderTable->addField(new xmldb_field('gradername', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL));
        $graderTable->addKey(new xmldb_key('primary', XMLDB_KEY_PRIMARY, array('graderid'), null, null));
        $dbman->create_table($graderTable);

        $table = new xmldb_table('qtype_programmingtask_optns');
        $dbman->add_field($table, new xmldb_field('graderid', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL));
        $dbman->add_key($table, new xmldb_key('graderid', XMLDB_KEY_FOREIGN, array('graderid'), 'qtype_programmingtask_gradrs', 'graderid'));

        // ProForma savepoint reached.
        upgrade_plugin_savepoint(true, 2019052601, 'qtype', 'programmingtask');
    }

    if ($oldversion < 2019061601) {
        $table = new xmldb_table('qtype_programmingtask_optns');
        $dbman->add_field($table, new xmldb_field('taskuuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL));

        // ProForma savepoint reached.
        upgrade_plugin_savepoint(true, 2019061601, 'qtype', 'programmingtask');
    }

    if ($oldversion < 2019080301) {
        $graderTable = new xmldb_table('qtype_programmingtask_grprcs');
        $graderTable->addField(new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, true));
        $graderTable->addField(new xmldb_field('qubaid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL));
        $graderTable->addField(new xmldb_field('questionattemptdbid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL));
        $graderTable->addField(new xmldb_field('gradeprocessid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL));
        $graderTable->addField(new xmldb_field('graderid', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL));
        $graderTable->addKey(new xmldb_key('primary', XMLDB_KEY_PRIMARY, array('id')));
        $graderTable->addKey(new xmldb_key('graderid', XMLDB_KEY_FOREIGN, array('graderid'), 'qtype_programmingtask_gradrs', 'graderid'));
        $dbman->create_table($graderTable);

        // ProForma savepoint reached.
        upgrade_plugin_savepoint(true, 2019080301, 'qtype', 'programmingtask');
    }

    if ($oldversion < 2019091800) {
        $table = new xmldb_table('qtype_programmingtask_gradrs');
        $dbman->add_field($table, new xmldb_field('lmsid', XMLDB_TYPE_CHAR, '64', null));
        $dbman->add_field($table, new xmldb_field('lmspw', XMLDB_TYPE_CHAR, '64', null));

        // ProForma savepoint reached.
        upgrade_plugin_savepoint(true, 2019091800, 'qtype', 'programmingtask');
    }

    return true;
}
