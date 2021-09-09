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
 * Code that is executed before the tables and data are dropped during the plugin uninstallation.
 *
 * @package     qtype_moopt
 * @category    upgrade
 * @copyright   2019 ZLB-ELC Hochschule Hannover <elc@hs-hannover.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Custom uninstallation procedure.
 */
function xmldb_qtype_moopt_uninstall() {

    global $DB;

    $dbman = $DB->get_manager();

    $success = true;

    $success = $success && $dbman->drop_table(new xmldb_table('qtype_moopt_options'));
    $success = $success && $dbman->drop_table(new xmldb_table('qtype_moopt_files'));
    $success = $success && $dbman->drop_table(new xmldb_table('qtype_moopt_gradeprocesses'));
    $success = $success && $dbman->drop_table(new xmldb_table('qtype_moopt_freetexts'));

    return $success;
}
