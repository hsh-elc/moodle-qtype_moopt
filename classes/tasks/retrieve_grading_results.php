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

namespace qtype_programmingtask\tasks;

require_once($CFG->dirroot . '/question/type/programmingtask/locallib.php');

class retrieve_grading_results extends \core\task\scheduled_task {

    public function get_name(): string {
        return get_string('retrievegradingresults', 'qtype_programmingtask');
    }

    public function execute() {
        global $DB;

        $records = $DB->get_records_sql('select distinct qubaid from {qtype_programmingtask_grprcs}');
        foreach ($records as $record) {
            retrieve_grading_results($record->qubaid);
        }
    }

}
