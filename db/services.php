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
 * Web service for qtype programmingtask
 */
$functions = array(
    'qtype_programmingtask_extract_task_infos_from_draft_file' => array(
        'classname' => 'qtype_programmingtask_external',
        'methodname' => 'extract_task_infos_from_draft_file',
        'classpath' => 'question/type/programmingtask/externallib.php',
        'description' => 'Extracts the relevant information from the file currently in the draft area.',
        'type' => 'read',
        'capabilities' => 'moodle/question:add',
        'ajax' => true,
        'services' => array('programmingtaskwebservice')
    )
);

$services = array(
    'programmingtaskwebservice' => array(
        'functions' => array('qtype_programmingtask_extract_task_infos_from_draft_file'),
        'requiredcapability' => 'moodle/question:add',
        'restrictedusers' => 0,
        'enabled' => 1
    )
);
