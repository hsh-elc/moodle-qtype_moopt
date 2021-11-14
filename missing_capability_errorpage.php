<?php

/* Setup the Page */
require_once('../../../config.php');
$PAGE->set_url('/question/type/moopt/missing_capability_errorpage.php');
require_login();
$PAGE->set_title(get_string('errorpage', 'qtype_moopt'));
$PAGE->set_heading(get_string('errorpage', 'qtype_moopt'));
$PAGE->set_pagelayout('standard');

/* Output the content of the Page */
$output = $PAGE->get_renderer('qtype_moopt');
echo $output->header();
echo $output->render(new error_renderable(get_string('missingcapability', 'qtype_moopt'), new moodle_url('/'))); //Just use the moodle startpage as redirection
echo $output->footer();
