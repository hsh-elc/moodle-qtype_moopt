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

/*
 * Web service for qtype MooPT
 */
$functions = array(
    'qtype_moopt_extract_task_infos_from_draft_file' => array(
        'classname' => 'qtype_moopt_external',
        'methodname' => 'extract_task_infos_from_draft_file',
        'classpath' => 'question/type/moopt/externallib.php',
        'description' => 'Extracts relevant data from the file currently in the draft area.',
        'type' => 'read',
        'capabilities' => 'moodle/question:add',
        'ajax' => true,
        'services' => array('mooptwebservice')
    ),
    'qtype_moopt_retrieve_grading_results' => array(
        'classname' => 'qtype_moopt_external',
        'methodname' => 'retrieve_grading_results',
        'classpath' => 'question/type/moopt/externallib.php',
        'description' => 'Check if any grade process with the given qubaid finished, retrieves those results,'
        . ' inserts them into the database and returns whether any grade process finished.',
        'type' => 'write',
        'ajax' => true,
        'services' => array('mooptwebservice')
    )
);

$services = array(
    'mooptwebservice' => array(
        'functions' => array('qtype_moopt_extract_task_infos_from_draft_file',
            'qtype_moopt_check_if_any_gradeprocess_finished'),
        'requiredcapability' => '',
        'restrictedusers' => 0,
        'enabled' => 1
    )
);
