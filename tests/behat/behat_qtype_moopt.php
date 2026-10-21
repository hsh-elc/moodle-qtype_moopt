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
 * Class holding the custom steps needed for behat tests of moopt.
 */
class behat_qtype_moopt extends behat_base {

    /** @var string|null Absolute path of a generated solution file.
     *
     * Used by i_generate_a_solution_for_the_subtask_from_the_template_and_the_solution_file and i_upload_the_generated_moopt_solution
     * to store a generated solution between steps.
     */
    private ?string $generatedsolution = null;

    /** Configures moopt for testing. This step must be called for every scenario that uses a live grading middleware.
     *
     * The following values must be set in config.php of the moodle installation for that:
     * - <code>$CFG->behat_qtype_moopt_communicator = '<which communicator to use>'; // e.g. 'grappa'</code>
     * - <code>$CFG->behat_qtype_moopt_service_url = '<service url of the grading middleware>';</code>
     * - <code>$CFG->behat_qtype_moopt_lms_id = '<lms id of the grading middleware';</code>
     * - <code>$CFG->behat_qtype_moopt_lms_password = '<lms password of the grading middleware>';</code>
     *
     * @Given /^MooPT is configured for testing$/
     */
    public function moopt_is_configured_for_testing(): void
    {
        global $CFG;

        $settingprefix = 'behat_qtype_moopt_';
        foreach (['communicator', 'service_url', 'lms_id', 'lms_password'] as $setting) {
            $key = $settingprefix . $setting;
            if (!isset($CFG->$key) || $CFG->$key === '') {
                throw new moodle_exception("\$CFG->$key is not set in config.php.");
            }
            set_config($setting, $CFG->$key, 'qtype_moopt');
        }

        // Validate communicator
        $communicator = $CFG->{$settingprefix . 'communicator'};
        if (!in_array($communicator, qtype_moopt\utility\communicator\communicator_factory::$implementations)) {
            throw new moodle_exception("There is no communicator with the name: '$communicator'");
        }

        // So the tests are as fast and responsive as possible
        set_config('service_client_polling_interval', 1, 'qtype_moopt');
    }

    /** Prevent the ACE editor from loading so the content of the underlying textarea can be set normally.
     *
     * @When /^I disable UI plugins in the MooPT question type$/
     */
    public function i_disable_ui_plugins(): void
    {
        // Pattern seen in qtype_coderunner from which the ace integration comes anyway
        $this->getSession()->executeScript("sessionStorage.setItem('disableUis', true);");
    }

    /** Fills a field located by xpath with the content of a file.
     *
     * @param string $xpath Path to the field which should be filled
     * @param string $path Path to the file which contains the content for the field
     *
     * @When /^I set the field with xpath "(?P<xpath>[^"]*)" to the contents of "(?P<path>[^"]*)"$/
     */
    public function i_set_the_field_with_xpath_to_the_contents_of(string $xpath, string $path): void
    {
        global $CFG;
        $fullpath = $CFG->dirroot . '/' . $path;
        if (!is_readable($fullpath)) {
            throw new file_exception("Fixture file '$path' not found.");
        }
        $this->execute('behat_forms::i_set_the_field_with_xpath_to', [$xpath, file_get_contents($fullpath)]);
    }

    /** Uploads a file into the MooPT submission file manager.
     * Note: This only works for the first file manager on the page.
     *
     * @param string $path Path to the file which should be uploaded.
     *
     * @When /^I upload "(?P<path>[^"]*)" as MooPT submission$/
     */
    public function i_upload_as_moopt_submission(string $path): void
    {
        // Will only find the first file manager on the page (pattern seen in: qtype_coderunner)
        $this->execute('behat_repository_upload::i_upload_file_to_filemanager', [$path, '']);
    }

    /** Generates a correct solution for a subtask from the template and solution file.
     * Note: Does only work with asqlg.
     *
     * @param string $subtask Name of the subtask to locate the correct block in the template e.g. "DBS1.7.1.b".
     * @param string $templatelink Link from which the template can be downloaded.
     * @param string $solutionfilepath File path to the solution file that contains a list of solutions for all the variants.
     *
     * @When /^I generate a solution for the subtask "(?P<subtask>[^"]*)" from the template "(?P<templatelink>[^"]*)" and the solution file "(?P<solutionfilepath>[^"]*)"$/
     */
    public function i_generate_a_solution_for_the_subtask_from_the_template_and_the_solution_file(string $subtask, string $templatelink, string $solutionfilepath): void
    {
        global $CFG;
        $templatecontent = behat_context_helper::get('behat_general')->download_file_from_link($templatelink);
        $templatedescription = "";
        $insidedescriptionblock = false;
        $solutionfromtemplate = "";
        foreach (preg_split('/\R/', $templatecontent) as $line) {
            if ($insidedescriptionblock) {
                if (!preg_match('/^--/', $line)) {
                    break; // Description ended
                } else {
                    $templatedescription .= $line; // Collect description
                }
            } else if (preg_match('/^--\s*' . preg_quote($subtask, '/') . '::\s*$/', $line)) {
                $insidedescriptionblock = true; // Next lines will be the description for the specific subtask
            }
            $solutionfromtemplate .= $line . "\n";
        }

        $solutioncontent = file_get_contents($CFG->dirroot . '/' . $solutionfilepath);
        $insidedescriptionblock = false;
        $solutiondescription = "";
        $foundsolution = false;
        foreach (preg_split('/\R/', $solutioncontent) as $line) {
            if ($insidedescriptionblock) {
                if (!preg_match('/^--/', $line)) { // Description ended
                    if ($solutiondescription === $templatedescription) {
                        $foundsolution = true;
                    }
                    $solutiondescription = "";
                    $insidedescriptionblock = false;
                } else {
                    $solutiondescription .= $line; // Collect description
                }
            } else if (preg_match('/^--\s*' . preg_quote($subtask, '/') . '(.*)::\s*$/', $line)) {
                $insidedescriptionblock = true; // Next lines will be a description for the specific subtask
            }

            if ($foundsolution) {
                if (preg_match('/^--/', $line)) break; // New block started
                $solutionfromtemplate .= $line . "\n";
            }
        }

        if (!$foundsolution) {
            throw new moodle_exception("No variant in '$solutionfilepath' matched the template '$templatelink'.");
        }

        // Write the built solution into temp area and remember its path for the upload step.
        $outputpath = make_request_directory() . '/generated_solution.sql';
        if (file_put_contents($outputpath, $solutionfromtemplate) === false) {
            throw new moodle_exception("Could not write generated solution to: $outputpath");
        }
        $this->generatedsolution = $outputpath;
    }

    /** Uploads the solution generated by the "I generate a solution for the subtask from the template and the solution file" step into the MooPT submission file manager.
     * 
     * @When /^I upload the generated MooPT solution$/
     */
    public function i_upload_the_generated_moopt_solution(): void
    {
        if ($this->generatedsolution === null) {
            throw new moodle_exception('No generated MooPT solution available: The step that generates the solution must have been run first.');
        }
        $this->i_upload_as_moopt_submission($this->generatedsolution);
    }
}
