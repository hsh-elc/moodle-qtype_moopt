<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace qtype_programmingtask\tasks;

require_once($CFG->dirroot . '/question/type/programmingtask/locallib.php');

class retrieve_grading_results extends \core\task\scheduled_task {

    public function get_name(): string {
        return get_string('retrievegradingresults', 'qtype_programmingtask');
    }

    public function execute() {
        global $DB;

        $records = $DB->get_records_sql('select distinct qubaid from {qtype_programmingtask_grprcs}');
        foreach($records as $record){
            retrieve_grading_results($record->qubaid);
        }
       
    }

}
