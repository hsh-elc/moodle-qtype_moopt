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


/**
 * Checks file access for matching questions.
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @return bool
 */
function qtype_moopt_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
    global $DB, $CFG;
    require_once($CFG->libdir . '/questionlib.php');

    // Note: do not use the constant COMPONENT_NAME, the interpreter will not be able to resolve it
    // due to the way this function is called by moodle
    question_pluginfile($course, $context, 'qtype_moopt', $filearea, $args, $forcedownload);
}
