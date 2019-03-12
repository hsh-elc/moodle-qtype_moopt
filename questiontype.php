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
 * Question type class for programmingtask is defined here.
 *
 * @package     qtype_programmingtask
 * @copyright   2019 ZLB-ELC Hochschule Hannover <elc@hs-hannover.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/questionlib.php');

/**
 * Class that represents a programmingtask question type.
 *
 * The class loads, saves and deletes questions of the type programmingtask
 * to and from the database and provides methods to help with editing questions
 * of this type. It can also provide the implementation for import and export
 * in various formats.
 */
class qtype_programmingtask extends question_type {

    // Override functions as necessary from the parent class located at
    // /question/type/questiontype.php.

}
