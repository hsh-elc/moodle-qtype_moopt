/**
 * Module that contains functions in order to extract the necessary informations from a ProFormA-task and insert into
 * the respective form elements.
 */

/*global console */

define(['core/ajax',
    'qtype_moopt/edit_editor_content'],
        function (ajax, editorEditor) {

            return {

                init: function (availableGraders) {
                    var self = this;
                    document.querySelector("#loadproformataskfilebutton").addEventListener('click', function() {
                        self.extractInformation(availableGraders);
                    });
                },

                extractInformation: function () {
                    var fileManager = document.querySelector("#id_proformataskfileupload").parentNode;
                    var itemId = null;
                    fileManager.childNodes.forEach( function (child) {
                       if (child.name == 'proformataskfileupload' && itemId === null) {
                           itemId = child.value;
                       }
                    });

                    ajax.call([
                        {
                            methodname: 'qtype_moopt_extract_task_infos_from_draft_file',
                            args: {itemid: itemId},
                            done: function (result) {

                                if (typeof result.error !== 'undefined') {

                                    document.querySelector("#id_error_ajaxerrorlabel")
                                        .parentNode.children.item(0).innerHTML = '<div>' + result.error + '</div>';

                                    document.querySelector("#id_error_ajaxwarnlabel")
                                        .parentNode.children.item(0).innerHTML = '';

                                    return;
                                }

                                document.querySelector("#id_error_ajaxerrorlabel").parentNode.children.item(0).innerHTML = '';
                                document.querySelector("#id_name").value = result.title;
                                editorEditor.setContents('id_questiontext', result.description);
                                editorEditor.setContents('id_internaldescription', result.internaldescription);
                                editorEditor.setContents('id_taskuuid', result.taskuuid);
                                editorEditor.setContents('id_defaultmark', result.maxscoregradinghints);
                                editorEditor.setContents('id_generalfeedback', result.filesdisplayedingeneralfeedback);

                                ajax.call([
                                    {
                                        methodname: 'qtype_moopt_get_grader_data',
                                        args: {},
                                        done: function (availableGraders) {
                                            /*
                                             * selects a grader that supports the proglang of the task.
                                             * selects the grader with the higher version number if two or more graders with the same name exist
                                             * if several graders with different names are supporting the proglang of the task,
                                             * the first one will be used
                                             */
                                            if (availableGraders.length > 0) {
                                                let selectedGrader = availableGraders[0];
                                                //use the already selected grader in case no grader is found that supports the proglang
                                                let e = document.querySelector("#id_graderselect");
                                                let alreadySelectedGraderIDHtmlRepresentation = e.options[e.selectedIndex].value;
                                                for (let i = 0; i < availableGraders.length; i++) {
                                                    let graderIDHtmlRepresentation = availableGraders[i]['html_representation'];
                                                    if (graderIDHtmlRepresentation === alreadySelectedGraderIDHtmlRepresentation) {
                                                        selectedGrader = availableGraders[i];
                                                        break;
                                                    }
                                                }

                                                const supportedGraders = [];
                                                availableGraders.forEach(function (grader) {
                                                    if ('proglangs' in grader) {
                                                        for (let i = 0; i < grader['proglangs'].length; i++) {
                                                            if (grader['proglangs'][i].toLowerCase() === result.proglang.toLowerCase()) {
                                                                supportedGraders.push(grader);
                                                                break;
                                                            }
                                                        }
                                                    }
                                                });

                                                if (supportedGraders.length > 0) {
                                                    const supportedGradersWithSameName = [];
                                                    const firstName = supportedGraders[0]['name'];
                                                    supportedGraders.forEach(function (grader) {
                                                        if (grader['name'] === firstName) {
                                                            supportedGradersWithSameName.push(grader);
                                                        }
                                                    });

                                                    if (supportedGradersWithSameName.length > 0) {
                                                        selectedGrader = supportedGradersWithSameName[0];
                                                        //Algorithm to find the highest graderversion of the grader
                                                        for (let k = 1; k < supportedGradersWithSameName.length; k++) {
                                                            const versionNumDigits = supportedGradersWithSameName[k]['version'].split(".");
                                                            const maxVersionNumDigits = selectedGrader['version'].split(".");
                                                            for (let i = 0; i < 2; i++) {
                                                                if (parseInt(versionNumDigits[i]) > parseInt(maxVersionNumDigits[i])) {
                                                                    selectedGrader = supportedGradersWithSameName[k];
                                                                    break;
                                                                } else if (parseInt(versionNumDigits[i]) < parseInt(maxVersionNumDigits[i])) {
                                                                    break;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }

                                                let searchVal = selectedGrader["html_representation"];
                                                setSelectionSafely("#id_graderselect option[value='" + searchVal + "']");

                                                if ('result_spec' in selectedGrader) {
                                                    if ('format' in selectedGrader['result_spec']) {
                                                        searchVal = selectedGrader['result_spec']['format'];
                                                        setSelectionSafely("#id_resultspecformat option[value='" + searchVal + "']");
                                                    }
                                                    if ('structure' in selectedGrader['result_spec']) {
                                                        searchVal = selectedGrader['result_spec']['structure'];
                                                        setSelectionSafely("#id_resultspecstructure option[value='" + searchVal + "']");
                                                    }
                                                    if ('teacher_feedback_level' in selectedGrader['result_spec']) {
                                                        searchVal = selectedGrader['result_spec']['teacher_feedback_level'];
                                                        setSelectionSafely("#id_teacherfeedbacklevel option[value='" + searchVal + "']");
                                                    }
                                                    if ('student_feedback_level' in selectedGrader['result_spec']) {
                                                        searchVal = selectedGrader['result_spec']['student_feedback_level'];
                                                        setSelectionSafely("#id_studentfeedbacklevel option[value='" + searchVal + "']");
                                                    }
                                                }
                                            }
                                        }
                                    }
                                ]);

                                let elem = document.querySelector("#id_enablefilesubmissions");
                                elem.checked = !result.enablefileinput;
                                elem.click();

                                let ftsmaxnumfields = result.freetextfilesettings.length;
                                elem = document.querySelector("#id_enablefreetextsubmissions");
                                elem.checked = !(ftsmaxnumfields > 0);
                                elem.click();

                                if(ftsmaxnumfields > 0) {
                                    elem = document.querySelector("#id_ftsnuminitialfields");
                                    elem.value = ftsmaxnumfields;
                                    elem.click();
                                    elem = document.querySelector("#id_ftsmaxnumfields");
                                    elem.value = ftsmaxnumfields;
                                    elem.click();
                                    elem.dispatchEvent(new Event('change')); // trigger hideIf rules in edit_moopt_form
                                    elem = document.querySelector("#id_ftsstandardlang");
                                    let options = elem.options;
                                    for(let i = 0; i < options.length; i++) {
                                        if(options[i].text.toLowerCase() === result.proglang.toLowerCase()) {
                                            elem.selectedIndex = i;
                                        }
                                    }
                                    elem = document.querySelector("#id_enablecustomsettingsforfreetextinputfields");
                                    elem.checked = false;
                                    elem.click();

                                    for(let i=0; i<result.freetextfilesettings.length; i++) {
                                        elem = document.querySelector("#id_enablecustomsettingsforfreetextinputfield"+i);
                                        elem.checked = !result.freetextfilesettings[i]["enablecustomsettings"];
                                        elem.click();
                                        if(result.freetextfilesettings[i]["usefixedfilename"] == true) {
                                            elem = document.querySelector("#id_namesettingsforfreetextinput"+i+"_0");
                                            elem.checked = false;
                                            elem.click();
                                        } else {
                                            elem = document.querySelector("#id_namesettingsforfreetextinput"+i+"_1");
                                            elem.checked = false;
                                            elem.click();
                                        }
                                        document.querySelector("#id_freetextinputfieldname"+i)
                                            .value = result.freetextfilesettings[i]['defaultfilename'];

                                        document.querySelector("#id_freetextinputfieldtemplate"+i)
                                            .value = result.freetextfilesettings[i]['filecontent'];

                                        document.querySelector("#id_ftsoverwrittenlang"+i)
                                            .value = result.freetextfilesettings[i]['proglang'];

                                        document.querySelector("#id_ftsinitialdisplayrows"+i)
                                            .value = result.freetextfilesettings[i]['initialdisplayrows'];
                                    }
                                }

                                let warnings = '';
                                if (typeof result.moodleValidationProformaNamespace !== 'undefined') {
                                    warnings += '<p>Detected ProFormA-version ' + result.moodleValidationProformaNamespace + '</p>';
                                }
                                if (typeof result.moodleValidationWarningInvalidNamespace !== 'undefined') {
                                    warnings += '<p>' + result.moodleValidationWarningInvalidNamespace + '</p>';
                                }
                                if (typeof result.moodleValidationWarnings !== 'undefined') {
                                    warnings += '<p>Found the following problems during validation but still continued:</p><ul>';
                                    result.moodleValidationWarnings.forEach(function (e) {
                                        warnings += '<li>' + e.msg + '</li>';
                                    });
                                    warnings += '</ul>';
                                }

                                document.querySelector("#id_error_ajaxwarninglabel")
                                    .parentNode.children.item(0).innerHTML = warnings;

                                document.querySelector("#loadproformataskfilebutton").focus();
                            },
                            fail: function (errorObject) {
                                console.log(errorObject);
                                document.querySelector("#id_error_ajaxerrorlabel").parentNode.children.item(0)
                                    .innerHTML = '<div>' + errorObject.debuginfo + '</div><div> For more information see browser '
                                    + 'console.</div>';
                                document.querySelector("#id_error_ajaxwarninglabel").parentNode.children.item(0).innerHTML = '';

                            }
                        }
                    ]);
                }

            };
        }
);

function setSelectionSafely(query) {
    select = document.querySelector(query);
    if(null !== select)
        select.selected = true;
}
