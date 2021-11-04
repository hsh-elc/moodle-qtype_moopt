## **Architecture of MooPT** ##

<pre>
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
</pre>

----------

## **AMD Modules** ##

The JavaScript files under **amd/src/** must be minifed to **amd/build/** after changes of the source files in **amd/src/**, because moodle will use the minified files rather than the normal source files. When we want to do changes at the javascript source files we should set the option "cache javascript" in moodle to off so moodle will use the normal source files under **amd/src/** and the not the minified files under **amd/build/**.
To do this minification of the AMD Modules moodle is using grunt.


### **HowTo Grunt** ###
This section is based on the following two pages:   
https://docs.moodle.org/dev/Grunt  
https://docs.moodle.org/dev/Javascript_Modules#Install_grunt  
This section does also only explain the minification based on a windows system, so on other systems it could be different.

#### **Installation of Grunt** ####
At first you need to install Node.js on your system (https://nodejs.org/). The Node.js version that is supported by moodle is [documented here](https://docs.moodle.org/dev/Javascript_Modules#Install_NVM_and_Node). In the Windows installer check "Automatically install the necessary tools".

If you missed the automatic installation of necessary tools, after installation you can run "Install additional tools for Node.js" from the start menu.

Then as administrator open CMD and navigate to the directory in which moodle is installed.
From there execute the following two commands:  
```npm install```  
```npm install -g grunt-cli```  
It can happen that vulnerabilities are mentioned, you can ignore that.


#### **Running Grunt** ####

Use CMD and move into the AMD directory of the plugin and run the following command:   
```grunt amd```  
It can happen that this fails because this command also checks the code with the ESLint Code Analysis Tool and when it finds "problematic patterns" it will return some errors. 
When these "problematic patterns" in the code are no real problems you can also run:   
```grunt amd --force```   
instead to minify the files even when the Code Analysis Tool finds problematic code.

After that the minified files should be under amd/build/.


----------

## **Ace WebEditor** ##

MooPT is currently using the Ace WebEditor Version 1.4.8 (https://ace.c9.io/).  
The files of the Ace Editor are located under **ace/**  
The integration of ace is done by 3 javascript AMD Modules that are copied from Coderunner Version 3.7.5 (https://moodle.org/plugins/qtype_coderunner).   
The AMD Modules for the intergration are: ***textareas.js***, ***ui_ace.js*** and ***userinterfacewrapper.js*** in amd/src/ and the corresponding minified files in: amd/build/