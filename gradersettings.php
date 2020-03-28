<?php

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
        $DB->execute('update {qtype_programmingtask_gradrs} set lmsid = ?, lmspw = ? where graderid = ?', [$lmsid, $pw, $grader->graderid]);
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
