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

/**
 * Plugin strings are defined here.
 *
 * @package     qtype_moopt
 * @category    string
 * @copyright   2019 ZLB-ELC Hochschule Hannover <elc@hs-hannover.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$string['addanswertext'] = 'Add answer field';
$string['addfreetextfieldsettings'] = 'Add settings for another free text input field';
$string['chose_communicator'] = 'Service Communicator';
$string['client_polling_interval'] = 'Client polling interval';
$string['collapse_all'] = 'Collapse all feedback';
$string['combinedresult'] = 'Combined result';
$string['commonsettings'] = 'Common settings';
$string['continue'] = 'Continue';
$string['currentlybeinggraded'] = 'Your submission has been queued for automatic grading';
$string['defaultlang'] = 'Default programming language';
$string['delayeddisplayedfiles'] = 'General Feedback';
$string['description'] = 'Description';
$string['descriptionfreetextinputnames'] = 'In the following you can specify settings for each free text input field';
$string['detailedfeedback'] = 'Detailed feedback';
$string['downloadcompletefile'] = 'Download complete \'{$a}\' file';
$string['showstudscorecalcscheme'] = 'Show students score calculation scheme';
$string['enablecustomsettingsforfreetextinputfield'] = 'Enable custom settings for free text input field #';
$string['enablecustomsettingsforfreetextinputfields'] = 'Enable custom settings for free text input fields';
$string['enablefilesubmissions'] = 'Enable file submissions';
$string['enablefreetextsubmissions'] = 'Enable free text submissions';
$string['errorpage'] = 'Errorpage';
$string['expand_all'] = 'Expand all feedback';
$string['external_settings-graders'] = 'Programming task graders';
$string['feedback'] = 'Feedback';
$string['filename'] = 'Filename';
$string['files'] = 'Files';
$string['freetextinputautogeneratedname'] = 'Use auto-generated file names';
$string['freetextinputnamesettingstandard'] = 'Standard file name setting for all input fields';
$string['freetextinputteachername'] = 'Use pre-set name as file name';
$string['freetextinputstudentname'] = 'Let students set a file name';
$string['freetextsubmissions'] = 'Freetext submissions';
$string['ftsmaxnumfields'] = 'Maximum number of free text input fields';
$string['ftsmaxnumfieldslegalrange'] = 'The range must be [{$a->beg},{$a->end}].';
$string['ftsnuminitialfields'] = 'Initial number of free text input fields';
$string['ftsoverwrittenlang'] = 'Programming language';
$string['ftspathinsubmissionfile'] = 'Path in the submission zip file for free text submissions';
$string['ftsstandardlang'] = 'Programming language for freetext submissions';
$string['grader'] = 'Grader';
$string['gradercurrentlynotavailable'] = 'The grader for this task is currently unavailable. Submitting your solution will have no effect. Please contact your teacher. As soon as the grader is back online again, you will need to re-attempt the quiz and re-submit your submission.';
$string['gradersettings'] = 'Grader settings';
$string['gradeprocessfinished'] = 'At least one grade process finished. Do you want to reload the page to display the results?';
$string['hasbeennullified'] = 'has been nullified';
$string['initialnumberfreetextfieldsgreaterthanmax'] = 'Initial number of free text input fields must not be greater than maximal number of free text input fields';
$string['internaldescription'] = 'Internal description';
$string['invalidproformanamespace'] = 'Could not detect ProFormA-version. Can\'t validate task.xml file or extract data from it. Supported versions: {$a}';
$string['lmsid'] = 'LMS ID';
$string['lmspassword'] = 'LMS Password';
$string['loadproformataskfile'] = 'Extract information';
$string['maximum'] = 'Maximum';
$string['minimum'] = 'Minimum';
$string['missingauthorcapability'] = 'The MooPT question type requires special permission, which can be granted by your Moodle administrator.';
$string['moopt:author'] = 'Create and edit MooPT questions';
$string['needsgradingbyteacher'] = 'An error occurred during the grading process, your submission requires manual grading by a teacher.';
$string['nofeedback'] = 'No feedback given';
$string['nofilessubmitted'] = 'You did not submit any solution files to the task.';
$string['nograderavailable'] = 'Currently, there are no graders available. Please contact your system administrator.';
$string['noresponseyet'] = 'No response yet';
$string['nosubmissionpossible'] = 'Your teacher did not enable any way for submitting a solution.';
$string['nosummaryavailable'] = 'No summary available.';
$string['notspecified'] = 'not specified';
$string['password'] = 'Password';
$string['pluginname'] = 'MooPT';
$string['pluginname_help'] = 'MooPT (Moodle Programming Task) uses ProFormA programming tasks consisting of task descriptions for students and meta data used by grading software to automatically grade and provide scores and feedback to solutions submitted by students.';
$string['pluginnameadding'] = 'Adding a ProFormA programming task';
$string['pluginnameediting'] = 'Editing a ProFormA programming task';
$string['pluginnamesummary'] = 'MooPT (Moodle Programming Task) uses ProFormA programming tasks consisting of task descriptions for students and meta data used by grading software to automatically grade and provide scores and feedback to solutions submitted by students.';
$string['previousgradernotavailable'] = 'The previously selected grader with ID \'{$a->grader}\' is currently unavailable. Please contact your system administrator.';
$string['privacy:metadata:grader_link'] = 'In order to obtain a grade the users submission is sent to a grading software, possibly using a middleware.';
$string['proformanamespaceinvalidorunknown'] = 'Invalid or unknown proforma namespace: {$a}';
$string['proformanamespacesvalid'] = 'Valid proforma namespaces are: {$a}'; 
$string['proformataskfilerequired'] = 'You need to supply a ProFormA task file';
$string['proformataskfileupload'] = 'ProFormA task file';
$string['proformataskfileupload_help'] = 'You can add a ProFormA task file (zip file) to this file picker. If you click the according button the necessary informations will be automatically extracted and inserted into the corresponding form elements on this page.';
$string['proformavalidationerrorintro'] = 'Detected ProFormA-version {$a}. Found the following problems during validation of task.xml file:';
$string['programminglanguage'] = 'Programming language';
$string['providedfiles'] = 'Available Task Attachments';
$string['redobuttontext'] = 'Retry programming task';
$string['reloadpage'] = 'Reload page?';
$string['reload'] = 'Reload';
$string['removelastanswertext'] = 'Remove last answer field';
$string['resultspecformat'] = 'Result-Spec/Format';
$string['resultspecstructure'] = 'Result-Spec/Structure';
$string['retrievegradingresults'] = 'Retrieve grading results';
$string['remove_leftover_responsefiles'] = 'Remove leftover response files';
$string['score'] = 'Score';
$string['scorecalculationscheme'] = 'Score calculation scheme';
$string['service_url'] = 'Service URL';
$string['singleproformataskfilerequired'] = 'Only one file is allowed to be in this draft area. Currently only a ProFormA-Task as a ZIP file is accepted.';
$string['studentfeedbacklevel'] = 'Student Feedback Level';
$string['submission'] = 'Submission';
$string['submissionsettings'] = 'Submission settings';
$string['submittedfiles'] = 'Submitted files';
$string['subtests'] = 'Subtests';
$string['subtest'] = 'Subtest';
$string['summarizedfeedback'] = 'Summarized feedback';
$string['suppliedzipinvalid'] = 'Supplied zip file is not a valid zip file';
$string['taskfile'] = 'ProFormA task file';
$string['taskfilezipexpected'] = 'Supplied file must be a zip file.';
$string['taskuuid'] = 'Task-UUID';
$string['taskuuidhaswronglength'] = 'Wrong length - uuid has to have 36 characters (32 hex digits and 4 dashes)';
$string['taskuuidrequired'] = 'You need to supply a valid task uuid';
$string['taskziplackstaskxml'] = 'Supplied zip file should contain a task.xml file';
$string['teacherfeedback'] = 'Teacher feedback';
$string['teacherfeedbacklevel'] = 'Teacher Feedback Level';
$string['test'] = 'Test';
$string['testgroup'] = 'Testgroup';
$string['testresult'] = 'Test result';
$string['timeout'] = 'Service Timeout';
$string['xmlvalidationerrormsg'] = '{$a->message} (Code {$a->code}) on line {$a->line}';
$string['yourcode'] = 'Your code';
