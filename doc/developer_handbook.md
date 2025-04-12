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