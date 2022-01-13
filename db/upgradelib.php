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
 * Plugin upgrade helper functions are defined here.
 *
 * @package     qtype_moopt
 * @category    upgrade
 * @copyright   2019 ZLB-ELC Hochschule Hannover <elc@hs-hannover.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Helper function used by the upgrade.php file.
 */
function qtype_moopt_helper_function() {
    global $DB;

    /* Please note that you should always be performing any task using raw (low
      level) database access exclusively, avoiding any use of the Moodle APIs.

      For more information please read the available Moodle documentation:
      https://docs.moodle.org/dev/Upgrade_API
     */
}

function pathnamehash($contextid, $component, $filearea, $itemid, $filepath, $filename) {
    // A mdl_file record's pathnamehash value is based on multiple columns,
    // forming a complex path that looks like this:
    // "/$context->id/$component/$filearea/$itemid.$filepath.$filename"
    // See function quiz_question_pluginfile in moodle/mod/quiz/lib.php
    $fullpath = "/" . $contextid . "/" . $component . "/" . $filearea
        . "/" . $itemid . $filepath . $filename;
    return sha1($fullpath);
}

