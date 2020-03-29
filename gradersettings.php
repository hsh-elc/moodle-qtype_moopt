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

defined('MOODLE_INTERNAL') || die;

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('locallib.php');

use qtype_programmingtask\output\qtype_programmingtask_grader_edit_form;

admin_externalpage_setup('qtypeprogrammingtaskgradersettings');

retrieve_graders_and_update_local_list();

$graderlist = $DB->get_records('qtype_programmingtask_gradrs');
$mform = new qtype_programmingtask_grader_edit_form($graderlist);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/admin/category.php?category=qtypeprogrammingtaskfolder'));
} else if ($data = $mform->get_data()) {

    $graders = $mform->get_graders();

    foreach ($graders as $grader) {
        $lmsid = $data->{'lmsid_' . $grader->graderid};
        $pw = $data->{'lmspw_' . $grader->graderid};
        $DB->execute('update {qtype_programmingtask_gradrs} set lmsid = ?, lmspw = ? where graderid = ?',
                [$lmsid, $pw, $grader->graderid]);
    }

    redirect(new moodle_url('/admin/category.php?category=qtypeprogrammingtaskfolder'));
}

$formdata = new stdClass();
foreach ($graderlist as $grader) {
    $formdata->{'lmsid_' . $grader->graderid} = $grader->lmsid;
    $formdata->{'lmspw_' . $grader->graderid} = $grader->lmspw;
}
$mform->set_data($formdata);

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
