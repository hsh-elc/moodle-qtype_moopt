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

/**
 * interface that represents a communicator to communicate with a specific grading middleware
 */
interface communicator_interface {

    /**
     * Get the available graders from the grading middleware
     *
     * @return array Array of the available graders
     */
    public function get_graders(): array;

    /**
     * Checks if the task with the specific uuid has already been cached by the grading middleware
     *
     * @param $uuid - The uuid of the task to be checked
     * @return bool
     */
    public function is_task_cached($uuid): bool;

    /**
     * Sends a submission to the grading middleware
     *
     * @param string $gradername The name of the grader that is used for this grading
     * @param string $graderversion The version of the grader that is used for this grading
     * @param bool $asynch Whether the gradeprocess is asynchronous or not
     * @param \stored_file $submissionfile The file that contains the submission
     * @return int The ID of the enqueued grading process
     */
    public function enqueue_submission(string $gradername, string $graderversion, bool $asynch, \stored_file $submissionfile);

    /**
     * Gets the grading results of a specific gradeprocess
     *
     * @param string $gradername The name of the grader that is used for this grading
     * @param string $graderversion The version of the grader that is used for this grading
     * @param string $gradeprocessid The id of the gradeprocess to get the results of
     * @return false|mixed false when the processing of this gradingprocess is not finished yet,
     * else the grading result
     */
    public function get_grading_result(string $gradername, string $graderversion, string $gradeprocessid);
}
