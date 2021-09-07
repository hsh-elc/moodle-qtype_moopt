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

    if ($oldversion < 2019031700) {

        $optionstable = new xmldb_table('qtype_moopt_options');

        $optionstable->addField(new xmldb_field('id', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null));
        $optionstable->addField(new xmldb_field('questionid', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null));
        $optionstable->addField(new xmldb_field('internaldescription', XMLDB_TYPE_TEXT, 'medium', null, null, null, null));

        $optionstable->addKey(new xmldb_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null));
        $optionstable->addKey(new xmldb_key('questionid', XMLDB_KEY_FOREIGN, array('questionid'), 'question', 'id'));

        $dbman->create_table($optionstable);

        $filestable = new xmldb_table('qtype_moopt_files');

        $filestable->addField(new xmldb_field('id', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null));
        $filestable->addField(new xmldb_field('questionid', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null));
        $filestable->addField(new xmldb_field('fileid', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL, null, null));
        $filestable->addField(new xmldb_field('usedbygrader', XMLDB_TYPE_INTEGER, 2, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null));
        $filestable->addField(new xmldb_field('visibletostudents', XMLDB_TYPE_CHAR, 64, null, XMLDB_NOTNULL, null, 'no'));
        $filestable->addField(new xmldb_field('usagebylms', XMLDB_TYPE_CHAR, 64, null, XMLDB_NOTNULL, null, 'download'));
        $filestable->addField(new xmldb_field('filepath', XMLDB_TYPE_TEXT, 'medium', null, XMLDB_NOTNULL, null, null));
        $filestable->addField(new xmldb_field('filename', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL, null, null));
        $filestable->addField(new xmldb_field('filearea', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL, null, null));

        $filestable->addKey(new xmldb_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null));
        $filestable->addKey(new xmldb_key('questionid', XMLDB_KEY_FOREIGN, array('questionid'), 'question', 'id'));

        $filestable->addIndex(new xmldb_index('fileid', XMLDB_INDEX_NOTUNIQUE, array('fileid')));

        $dbman->create_table($filestable);

        // ProForma savepoint reached.
        upgrade_plugin_savepoint(true, 2019031700, 'qtype', 'moopt');
    }

    if ($oldversion < 2019052601) {

        $table = new xmldb_table('qtype_moopt_options');
        $dbman->add_field($table, new xmldb_field('graderid', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL));

        // ProForma savepoint reached.
        upgrade_plugin_savepoint(true, 2019052601, 'qtype', 'moopt');
    }

    if ($oldversion < 2019061601) {
        $table = new xmldb_table('qtype_moopt_options');
        $dbman->add_field($table, new xmldb_field('taskuuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL));

        // ProForma savepoint reached.
        upgrade_plugin_savepoint(true, 2019061601, 'qtype', 'moopt');
    }

    if ($oldversion < 2019080301) {
        $gradertable = new xmldb_table('qtype_moopt_gradeprocesses');
        $gradertable->addField(new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, true));
        $gradertable->addField(new xmldb_field('qubaid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL));
        $gradertable->addField(new xmldb_field('questionattemptdbid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL));
        $gradertable->addField(new xmldb_field('gradeprocessid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL));
        $gradertable->addField(new xmldb_field('graderid', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL));
        $gradertable->addKey(new xmldb_key('primary', XMLDB_KEY_PRIMARY, array('id')));
        $dbman->create_table($gradertable);

        // ProForma savepoint reached.
        upgrade_plugin_savepoint(true, 2019080301, 'qtype', 'moopt');
    }

    if ($oldversion < 2019110400) {
        $table = new xmldb_table('qtype_moopt_options');
        $dbman->add_field($table, new xmldb_field('showstudscorecalcscheme', XMLDB_TYPE_INTEGER, '1', null,
                        XMLDB_NOTNULL, false, 0));

        // ProForma savepoint reached.
        upgrade_plugin_savepoint(true, 2019110400, 'qtype', 'moopt');
    }

    if ($oldversion < 2020012300) {

        $table = new xmldb_table('qtype_moopt_options');
        $dbman->add_field($table, new xmldb_field('enablefilesubmissions', XMLDB_TYPE_INTEGER, '1', null, null, false, 0));
        $dbman->add_field($table, new xmldb_field('enablefreetextsubmissions', XMLDB_TYPE_INTEGER, '1', null, null, false, 0));
        $dbman->add_field($table, new xmldb_field('ftsnuminitialfields', XMLDB_TYPE_INTEGER, '8', null, null, false, 0));
        $dbman->add_field($table, new xmldb_field('ftsmaxnumfields', XMLDB_TYPE_INTEGER, '8', null, null, false, 0));
        $dbman->add_field($table, new xmldb_field('ftsautogeneratefilenames', XMLDB_TYPE_INTEGER, '1', null, null, false, 1));

        // ProForma savepoint reached.
        upgrade_plugin_savepoint(true, 2020012300, 'qtype', 'moopt');
    }

    if ($oldversion < 2020012500) {

        $ftstable = new xmldb_table('qtype_moopt_freetexts');
        $ftstable->addField(new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, true));
        $ftstable->addField(new xmldb_field('questionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL));
        $ftstable->addField(new xmldb_field('inputindex', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL));
        $ftstable->addField(new xmldb_field('presetfilename', XMLDB_TYPE_INTEGER, '1', null, null, false, 1));
        $ftstable->addField(new xmldb_field('filename', XMLDB_TYPE_CHAR, '256'));

        $ftstable->addKey(new xmldb_key('primary', XMLDB_KEY_PRIMARY, array('id')));
        $ftstable->addKey(new xmldb_key('questionid', XMLDB_KEY_FOREIGN, array('questionid'), 'question', 'id'));
        $dbman->create_table($ftstable);

        // ProForma savepoint reached.
        upgrade_plugin_savepoint(true, 2020012500, 'qtype', 'moopt');
    }

    if ($oldversion < 2020020800) {

        $tableoptns = new xmldb_table('qtype_moopt_options');
        $dbman->add_field($tableoptns, new xmldb_field('ftsstandardlang', XMLDB_TYPE_CHAR, '64', null, true, null, 'txt'));

        $tablefts = new xmldb_table('qtype_moopt_freetexts');
        $dbman->add_field($tablefts, new xmldb_field('ftslang', XMLDB_TYPE_CHAR, '64', null, true, null, 'default'));

        // ProForma savepoint reached.
        upgrade_plugin_savepoint(true, 2020020800, 'qtype', 'moopt');
    }

    return true;
}
