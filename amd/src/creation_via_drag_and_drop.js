/**
 * Module that contains functions in order to extract the necessary informations from a ProFormA-task and insert into
 * the respective form elements.
 */

/*global console */

define(['core/ajax',
    'qtype_moopt/edit_editor_content'],
        function (ajax, editorEditor) {

            return {

                init: function () {
                    var self = this;
                    document.querySelector("#loadproformataskfilebutton").addEventListener('click', function() {
                        self.extractInformation();
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
                                editorEditor.setContentsOfText('id_taskuuid', result.taskuuid);
                                editorEditor.setContentsOfText('id_defaultmark', result.maxscoregradinghints);
                                editorEditor.setContents('id_generalfeedback', result.filesdisplayedingeneralfeedback);

                                var elem = document.querySelector("#id_enablefilesubmissions");
                                elem.checked = !result.enablefileinput;
                                elem.click();

                                var ftsmaxnumfields = result.freetextfilesettings.length;
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
                                    elem = document.querySelector("#id_enablecustomsettingsforfreetextinputfields");
                                    elem.checked = false;
                                    elem.click();

                                    for(var i=0; i<result.freetextfilesettings.length; i++) {
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

                                var warnings = '';
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

                                document.querySelector("#id_name").focus();
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
