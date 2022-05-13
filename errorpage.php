<?php

/* Setup the Page */
require_once('../../../config.php');


// Get params.
$courseid = required_param('courseid', PARAM_INT);
$error = required_param('error', PARAM_TEXT);
$error = $error ?? 'serviceunavailable';
$errormessage = get_string($error, 'qtype_moopt') ;

// Validate them and get the corresponding objects.
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

$coursecontext = context_course::instance($course->id);

$PAGE->set_url('/question/type/moopt/errorpage.php');

require_login($course);
$PAGE->set_context($coursecontext);

$PAGE->set_title(get_string('errorpage', 'qtype_moopt'));
$PAGE->set_heading(get_string('errorpage', 'qtype_moopt'));
$PAGE->set_pagelayout('standard');

/* Output of the page's content */
$output = $PAGE->get_renderer('qtype_moopt');
echo $output->header();
echo $output->render_error_msg($errormessage,
    new moodle_url('/course/view.php'), $courseid);
echo $output->footer();
