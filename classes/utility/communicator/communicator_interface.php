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

namespace qtype_moopt\utility\communicator;

interface communicator_interface {

    public function get_graders(): array;

    public function is_task_cached($uuid): bool;

    public function enqueue_submission(string $gradername, string $graderversion, bool $asynch, \stored_file $submissionfile);

    public function get_grading_result(string $gradername, string $graderversion, string $gradeprocessid);
}
