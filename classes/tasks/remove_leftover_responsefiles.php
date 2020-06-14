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

namespace qtype_programmingtask\tasks;

defined('MOODLE_INTERNAL') || die();


class remove_leftover_responsefiles extends \core\task\scheduled_task {

    public function get_name(): string {
        return get_string('remove_leftover_responsefiles', 'qtype_programmingtask');
    }

    public function execute() {
        global $DB;

        $fileids = $DB->get_records_sql("SELECT file.id FROM {files} file WHERE filearea like '%responsefiles%' AND NOT EXISTS "
                . "(SELECT quba.id FROM {question_usages} quba WHERE quba.id = file.itemid)");

        $fs = get_file_storage();
        foreach ($fileids as $record) {
            $fs->get_file_by_id($record->id)->delete();
        }

    }

}
