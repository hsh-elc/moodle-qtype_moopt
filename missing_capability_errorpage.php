<?php

/* Setup the Page */
require_once('../../../config.php');
$PAGE->set_url('/question/type/moopt/missing_capability_errorpage.php');
require_login();
$PAGE->set_title(get_string('errorpage', 'qtype_moopt'));
$PAGE->set_heading(get_string('errorpage', 'qtype_moopt'));
$PAGE->set_pagelayout('standard');

/* Output of the page's content */
$output = $PAGE->get_renderer('qtype_moopt');
echo $output->header();
echo $output->render_error_msg(get_string('missingauthorcapability', 'qtype_moopt'),
    new moodle_url('/')); //Just redirect to the startpage of moodle
echo $output->footer();
