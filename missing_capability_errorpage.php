<?php

/* Setup the Page */
require_once('../../../config.php');

// Get params.
$courseid = required_param('courseid', PARAM_INT);

// Validate them and get the corresponding objects.
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

$coursecontext = context_course::instance($course->id);

$PAGE->set_url('/question/type/moopt/missing_capability_errorpage.php');

require_login($course);
$PAGE->set_context($coursecontext);

$PAGE->set_title(get_string('errorpage', 'qtype_moopt'));
$PAGE->set_heading(get_string('errorpage', 'qtype_moopt'));
$PAGE->set_pagelayout('standard');



/* Output of the page's content */
$output = $PAGE->get_renderer('qtype_moopt');
echo $output->header();
echo $output->render_error_msg(get_string('missingauthorcapability', 'qtype_moopt'),
    new moodle_url('/course/view.php'), $courseid); // redirect to the startpage of the course
echo $output->footer();
