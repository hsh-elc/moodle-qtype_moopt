## HowTo MooPT and Moodle Plugin Development in general

[Plugintypes](https://moodledev.io/docs/4.5/apis/plugintypes) should be read first, as an introduction to the different kinds of Moodle-Plugins. 
To get a general idea how Moodle-Plugins are structured in general, [Common Files](https://moodledev.io/docs/4.5/apis/commonfiles) should be read.
MooPT is a [Questiontype](https://moodledev.io/docs/4.5/apis/plugintypes/qtype) Plugin. 
Besides MooPT there are also several [Questionbehaviour](https://moodledev.io/docs/4.5/apis/subsystems/question#question-behaviours-qbehaviour_) Plugins, specifically created to work with MooPT-Questions.

To understand the different parts of MooPT in Detail, the docs listed below could be relevant. Not all the details on all the pages are necessarily relevant. It is probably best to go through the corresponding MooPT code in parallel and look up the appropriate parts of the documentation accordingly.

1. MooPT uses some [JavaScript Modules](https://moodledev.io/docs/4.5/guides/javascript/modules). For more Information about this, see: MooPT Architecture below
2. [Upgrade API](https://moodledev.io/docs/4.4/guides/upgrade) and the [XMLDB Editor](https://moodledev.io/general/development/tools/xmldb) to understand how to upgrade the MooPT plugin and change the database
3. [File Handling](https://moodledev.io/docs/4.5/apis/subsystems/files) is very important to understand how the different files that are used by MooPT are saved in Moodle
4. [Webservice API](https://moodledev.io/docs/4.5/apis/subsystems/external/writing-a-service) to understand the Webservice written in db/services.php and externallib.php
5. [Multi Language Support](https://docs.moodle.org/dev/String_API#Basic_concepts)
6. [Automatic Class Loading](https://docs.moodle.org/dev/Automatic_class_loading) to understand what the purpose of the classes/ folder is
7. [Output API](https://moodledev.io/docs/4.5/apis/subsystems/output) to understand the renderer that MooPT uses in classes/output/
8. [Forms API](https://moodledev.io/docs/4.5/apis/subsystems/form) to understand how the Question-Creation-Form works in edit_moopt_form.php
9. [Capabilities](https://moodledev.io/docs/4.4/apis/subsystems/access)
10. [Import/Export](https://docs.moodle.org/dev/Import/export_for_questiontype_plugins) to understand the Import/Export to Moodle XML implementation in questiontype.php
11. [Backup](https://moodledev.io/docs/4.4/apis/subsystems/backup) and [Restore](https://moodledev.io/docs/4.4/apis/subsystems/backup/restore) to understand how the Backup and Restore works in backup/moodle2/

For general reference when developing: [APIs](https://moodledev.io/docs/4.5/apis) could be also helpful.


## MooPT architecture

```
+----------------------------------------------------------------------------+
|                                                                            |
|                               +------------------------------------------+ |
|  qtype_moopt                  |                                          | |
|                               |   Code to integrate ACE into MooPT       | |
|                               |   (taken from Coderunner Moodle Plugin)  | |
|                               |                                          | |
|                               |   Source files:                          | |
|                               |   amd/src/textareas.js                   | |
|                               |   amd/src/ui_ace.js                      | |
|                               |   amd/src/userinterfacewrapper.js        | |
|                               |                                          | |
|                               +------------------------------------------+ |
|                                                                            |
|                               +------------------------+                   |
|                               |                        |                   |
|                               |   ACE Web-Editor       |                   |
|                               |   (https://ace.c9.io/) |                   |
|                               |                        |                   |
|                               |   Source files:        |                   |
|                               |   ace/*                |                   |
|                               |                        |                   |
|                               +------------------------+                   |
|                                                                            |
+----------------------------------------------------------------------------+
```

----------

## AMD Modules

The JavaScript files below **amd/src/** must be minifed to **amd/build/** after changes of the source files in **amd/src/**, because Moodle will use the minified files rather than the normal source files. During development of javascript source files we should set the option "cache javascript" in Moodle to off so the browser interprets additional source map files for mapping the minified source to the original one. 

[Since version 3.8 Moodle](https://docs.moodle.org/dev/Javascript_Modules#Development_mode_.28Moodle_v3.8_and_above.29) won't deliver the original source files below **amd/src/** to the browser, so minification to **amd/build/** is a must.

For minification of the AMD Modules, Moodle is using grunt.


### HowTo Grunt
This section is based on the following two pages:   
 - https://docs.moodle.org/dev/Grunt  
 - https://docs.moodle.org/dev/Javascript_Modules#Install_grunt  

This section does also only explain the minification based on a Windows system, so on other systems it could be different.

#### Installation of Grunt
At first you need to install Node.js on your system (https://nodejs.org/). The Node.js version that is supported by Moodle is [documented here](https://docs.moodle.org/dev/Javascript_Modules#Install_NVM_and_Node). In the Windows installer check "Automatically install the necessary tools".

If you missed the automatic installation of necessary tools, after installation you can run "Install additional tools for Node.js" from the Windows start menu.

Then as administrator open CMD and navigate to the directory in which Moodle is installed.
From there execute the following two commands:  
```npm install```  
```npm install -g grunt-cli```  
It may happen that vulnerabilities are mentioned, you can ignore that.


#### Running Grunt

Open CMD and navigate to the directory in which moodle is installed and run the following command:   
```grunt amd --root=question/type/moopt/amd --verbose```  

It can happen that this fails because this command also checks the code with the ESLint Code Analysis Tool and when it finds "problematic patterns" it will return some errors. 

When these "problematic patterns" in the code are no real problems you can also run:   
```grunt amd --root=question/type/moopt/amd --verbose --force```   
instead to minify the files even when the Code Analysis Tool finds problematic code.

After that the minified files should be under amd/build/.

#### Running Grunt automatically on changes

You might have to install watchman first. Therefore as an administrator start the Node.js command prompt via the Windows start menu and type:
```choco install watchman```

Then at a CMD prompt move into the directory in which moodle is installed and run the following command:   
```grunt watch --root=question/type/moopt/amd --verbose```  

In order to have the force option on when running the task eslint via watchman, which could be helpful during development, you can modify the `Gruntfile.js` inside the moodle main directory as follows:

```
// Register JS tasks.
grunt.registerTask('shifter', 'Run Shifter against the current directory', tasks.shifter);
grunt.registerTask('gherkinlint', 'Run gherkinlint against the current directory', tasks.gherkinlint);
grunt.registerTask('ignorefiles', 'Generate ignore files for linters', tasks.ignorefiles);
grunt.registerTask('watch', 'Run tasks on file changes', tasks.watch);
grunt.registerTask('yui', ['eslint:yui', 'shifter']);
// BEGIN mod
grunt.loadNpmTasks('grunt-force-task');
grunt.registerTask('amd', ['force:eslint:amd', 'babel']);
// END mod
grunt.registerTask('js', ['amd', 'yui']);
```

For this to work you might have to install this first:
```npm install grunt-force-task --save-dev```

Then restart `grunt watch --root=question/type/moopt/amd --verbose`.

----------

## Ace WebEditor

MooPT is currently using the Ace WebEditor Version 1.4.8 (https://ace.c9.io/).  

- The files of the Ace Editor are located under **ace/**  
- The integration of Ace is done by three javascript AMD Modules that have been copied from Coderunner Version 3.7.5 (https://moodle.org/plugins/qtype_coderunner) and slightly tweaked to fit MooPT:
  * The three javascript files are: **textareas.js**, **ui_ace.js** and **userinterfacewrapper.js** in **amd/src/**.
  * The corresponding minified files are in **amd/build/**
  
## Testing with Behat
MooPT uses Behat for testing: https://moodledev.io/general/development/tools/behat

### File structure
- `tests/behat/*.feature`: The feature files which contain the tests.
- `tests/behat/behat_qtype_moopt.php` is the place where custom steps reside.
- `tests/helper.php` contains the automatic MooPT question generation.
- The directory `tests/behat/fixtures/` is used for tasks and solutions.

### Setup
This section is based on: https://moodledev.io/general/development/tools/behat/running

It has only been tested on a Linux system, so on other systems it might be different.

#### Overall picture
##### Moodle and database instances
The Behat tests run on a completely separate Moodle instance. This instance uses the same database as the normal Moodle installation but different tables, so it doesn't influence the main Moodle instance.
This test site must be provided by a webserver. Here, a separate simple PHP server is used for that, which must be running before running tests with Behat.

##### Browser
The Behat tests run on a specific browser (which must be installed) and also need the specific driver for that browser, which must also be running before running any Behat tests.

#### Prerequisites
This section assumes that a functioning Moodle installation and a grading middleware are already configured and running properly.

#### Installation
Clone the moodle-browser-config repository:<br>
```git clone https://github.com/andrewnicols/moodle-browser-config <moodle_browser_config_dir>```

After that, include it by adding the following line into `<moodle_root>/config.php` of your Moodle installation:<br>
```require_once('<moodle_browser_config_dir>/init.php');```<br>

Create a new folder for Behat tests:<br>
```mkdir <behat_dataroot>```

Add the following to `<moodle_root>/config.php` of your Moodle installation:<br>
```php
$CFG->behat_dataroot = '<behat_dataroot>';
$CFG->behat_wwwroot = 'http://localhost:<behat_port>'; // e.g. with port: 8001
$CFG->behat_prefix = 'beh_';
```
Note: Make sure that `$CFG->behat_wwwroot` and `$CFG->wwwroot` are different.

Make sure the database that is used for your Moodle installation is running before initializing Behat with the following command:<br>
```php <moodle_root>/public/admin/tool/behat/cli/init.php```

Composer doesn't need to be installed manually, it happens automatically on init. Sometimes init might fail because composer tries to update itself. `--no-composer-self-update` can help here.

Note: Make sure to use a PHP version that your current Moodle version supports (https://moodledev.io/general/development/policies/php). 

The init was successful if a command that looks something like this gets printed at the end:<br>
```vendor/bin/behat --config <behat_dataroot>/behatrun/behat/behat.yml```

This will be used later to run the Behat tests. 

Note: The init might print some deprecation warnings: These are from Moodle's code so there is nothing to do about them.

##### Browser setup
In these instructions Chromium is used as an example, but other browsers could be used too (see: https://moodledev.io/general/development/tools/behat/running#setting-up-your-browsers).

Install Chromium and the Chromedriver:
```bash
sudo apt update
sudo apt install chromium chromium-driver
```

#### Grading middleware configuration
The tests need an active grading middleware, which needs to be configured by adding these lines into `<moodle_root>/config.php`:
```php
$CFG->behat_qtype_moopt_communicator = '<which communicator to use>'; // e.g. 'grappa'
$CFG->behat_qtype_moopt_service_url = '<service url of the grading middleware>';
$CFG->behat_qtype_moopt_lms_id = '<lms id of the grading middleware>';
$CFG->behat_qtype_moopt_lms_password = '<lms password of the grading middleware>';
```

#### Grader configuration
The different questions use different graders which must also be configured. 
The graders that need to be configured are visible in `tests/helper.php`: 
Every method with the name `get_moopt_question_form_data_<some name>()` generates a single moopt question. Inside these methods, the required graders are named.

For each of these grader names (`<expectedgrader>`), add two lines in `<moodle_root>/config.php`: 
```php
$CFG->behat_qtype_moopt_grader_name_<expectedgrader> = '<name of the grader configured in the grading middleware>';
$CFG->behat_qtype_moopt_grader_version_<expectedgrader> = '<version of the grader configured in the grading middleware>';
```
Note: Some of the generated questions have specific requirements for the grader used, which are documented inside these methods. 

In an example it would look like this: 
```php
tests/helper.php:

public function get_moopt_question_form_data_asqlg_quicktest(): stdClass
{
    // The asqlg grader used must provide the uri: de.hsh.inform.hochschule.postgres
    return $this->extract_form_data_from_task("fixtures/tasks/asqlg_quicktest.zip", 'asqlg');
}
```
where `asqlg` (the second parameter of the method call) is the expected grader that now needs the following two example lines in `<moodle_root>/config.php`: 
```php
$CFG->behat_qtype_moopt_grader_name_asqlg = 'asqlg_with_uri_de_hsh_inform_hochschule_postgres';
$CFG->behat_qtype_moopt_grader_version_asqlg = '2.0';
```

When a grader isn't configured in `<moodle_root>/config.php`, the test will fail and throw an exception stating which config values are missing. 

### Running the tests

#### Starting the server and browser

First, the Moodle test instance must be provided by a webserver. An easy option is to use a simple PHP webserver listening on <behat_port> (the one configured above inside `<moodle_root>/config.php`). This can be done via the following command in the background: 
```bash 
php -S localhost:<behat_port> -t <moodle_root>/public/ > /tmp/behat_php_server.log 2>&1 &
```

To stop the server after testing, you could use: `pkill -f 'php -S localhost:<behat_port>'`

After that, start the Chromedriver in the background: 
```bash
chromedriver --port=9515 > /tmp/behat_chromedriver.log 2>&1 &
```

Port 9515 is exactly the port the Moodle browser config profiles `chromedriver` and `headlesschromedriver` use.

To stop the Chromedriver after testing, you may use: `pkill -f 'chromedriver --port=9515'`

Make sure that the grading middleware is running before starting the tests. 

To run the Behat tests for MooPT, use the command that got printed by the init earlier with the following parameters:<br>
```--tags=@qtype_moopt --profile=headlesschromedriver --format=pretty```, so it looks something like this:<br>
```bash
<moodle_root>/vendor/bin/behat --config <behat_dataroot>/behatrun/behat/behat.yml --tags=@qtype_moopt --profile=headlesschromedriver --format=pretty
```

The profile can be changed to whatever driver you want to use (see: https://moodledev.io/general/development/tools/behat/running for more details).
To run only specific scenarios of the Behat tests, you can specify different tags, which are defined inside the feature files (`tests/behat/*.feature`). 

#### Debugging
It can help to use a non-headless profile to actually see what is happening while running the tests (e.g. `--profile=chromedriver`).
If the browser closes on failure, it might be helpful to temporarily add a step inside the currently tested scenario:<br>
```gherkin
And I pause
```
If you add the following to `<moodle_root>/config.php`: `$CFG->behat_faildump_path = '<behat_faildump_dir>';`, Behat will store snapshots on failure for debugging in `<behat_faildump_dir>`. Make sure that the directory `<behat_faildump_dir>` exists in that case.

Use the `-vv` parameter for more verbose output if a failure error message doesn't suffice to find the problem. 

If a test fails because of wrong grading results, make sure to check if the configured grader for that question in `<moodle_root>/config.php` is one that can actually grade the specific task correctly.

### Writing behat tests
Behat is a BDD framework, so the tests are written in Gherkin. 

Some additional info can be found here for writing Behat tests: https://moodledev.io/general/development/tools/behat/writing

It might be necessary to run: `php <moodle_root>/public/admin/tool/behat/cli/util.php --enable` after adding a step definition or a new test. 

#### Grading middleware configuration
Whenever a test needs an active grading middleware, make sure to run the following step in that test before the first call to the grading middleware: 
```gherkin
Given MooPT is configured for testing
```
This step reads the config values from `<moodle_root>/config.php` and sets them for the plugin. 

#### Adding new generated questions
To add a new question to the generator, add the following to `tests/helper.php`:
```php
public function get_moopt_question_form_data_<Question Name>(): stdClass
{
    return $this->extract_form_data_from_task("fixtures/tasks/<The task file to use>", '<expectedgrader>');
}
```

Add `<Question Name>` to the array in the `get_test_questions()` method in `tests/helper.php`. 

To use a question inside a scenario, the following step may be used:
```gherkin
Given the following "questions" exist:
      | qtype | name                    | questioncategory     | template        |
      | moopt | <Display Question Name> | <Question Category>  | <Question Name> |
```

#### Custom steps
Sometimes custom behat steps are necessary to do specific things. These can be added in `tests/behat/behat_qtype_moopt.php`. 
As examples there are several custom steps already defined like
```gherkin
When I disable UI plugins in the MooPT question type
And I set the field with xpath "..." to the contents of "..."
```
which are used to fill a textarea with the content of a solution file or
```gherkin
When I upload "..." as MooPT submission
```
to upload a specific file as a solution.