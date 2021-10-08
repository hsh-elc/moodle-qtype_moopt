/**
 * Module that contains functions in order to extract the necessary informations from a ProFormA-task and insert into
 * the respective form elements.
 */

/*global console */

define(['jquery', 'core/ajax',
    'qtype_moopt/edit_editor_content'],
        function ($, ajax, editorEditor) {

            return {

                init: function () {
                    var self = this;
                    document.querySelector("#loadproformataskfilebutton").addEventListener('click', function(event) {
                        self.extractInformation();
                    });
                },

                extractInformation: function () {
                    var fileManager = document.querySelector("#id_proformataskfileupload").parentNode;
                    var itemId = null;
                    for (const child of fileManager.childNodes) {
                        if (child.name == 'proformataskfileupload') {
                            itemId = child.value;
                            break;
                        }
                    }

                    ajax.call([
                        {
                            methodname: 'qtype_moopt_extract_task_infos_from_draft_file',
                            args: {itemid: itemId},
                            done: function (result) {

                                if (typeof result.error !== 'undefined') {
                                    $("#id_error_ajaxerrorlabel").parent().children().first().
                                            html('<div>' + result.error + '</div>');
                                    $("#id_error_ajaxwarninglabel").parent().children().first().html('');
                                    return;
                                }

                                $("#id_error_ajaxerrorlabel").parent().children().first().html('');
                                $("#id_name").val(result.title);
                                editorEditor.setContents('id_questiontext', result.description);
                                editorEditor.setContents('id_internaldescription', result.internaldescription);
                                editorEditor.setContentsOfText('id_taskuuid', result.taskuuid);
                                editorEditor.setContentsOfText('id_defaultmark', result.maxscoregradinghints);
                                editorEditor.setContents('id_generalfeedback', result.filesdisplayedingeneralfeedback);

                                //$('#id_showstudscorecalcscheme').prop('checked', false).click();
                                $('#id_enablefilesubmissions').prop('checked', !result.enablefileinput).click();

                                var ftsmaxnumfields = result.freetextfilesettings.length;
                                $('#id_enablefreetextsubmissions').prop('checked', !(ftsmaxnumfields > 0)).click();
                                if(ftsmaxnumfields > 0) {
                                    //$('#id_enablefreetextsubmissions').prop('checked', false).click();
                                    $('#id_ftsnuminitialfields').val(ftsmaxnumfields).click();
                                    $('#id_ftsmaxnumfields').val(ftsmaxnumfields).click();
                                    $('#id_enablecustomsettingsforfreetextinputfields').prop('checked', false).click();

                                    for(var i=0; i<result.freetextfilesettings.length; i++) {
                                        $('#id_enablecustomsettingsforfreetextinputfield'+i).prop('checked',
                                            !result.freetextfilesettings[i]["enablecustomsettings"]).click();
                                        if(result.freetextfilesettings[i]["usefixedfilename"] == true) {
                                            $('#id_namesettingsforfreetextinput'+i+'_0').prop('checked', false).click();
                                        } else {
                                            $('#id_namesettingsforfreetextinput'+i+'_1').prop('checked', false).click();
                                        }
                                        $('#id_freetextinputfieldname'+i).val(result.freetextfilesettings[i]['defaultfilename']);
                                        $('#id_freetextinputfieldtemplate'+i).val(result.freetextfilesettings[i]['filecontent']);
                                        $('#id_ftsoverwrittenlang'+i).val(result.freetextfilesettings[i]['proglang']);
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
                                $("#id_error_ajaxwarninglabel").parent().children().first().html(warnings);

                                // Focus the first input field of extracted data:
                                $("#id_name").focus();
                            },
                            fail: function (errorObject) {
                                console.log(errorObject);
                                $("#id_error_ajaxerrorlabel").parent().children().first().
                                        html('<div>' + errorObject.debuginfo + '</div><div> For more information see browser '
                                                + 'console.</div>');
                                $("#id_error_ajaxwarninglabel").parent().children().first().html('');
                            }
                        }
                    ]);
                }

            };
        }
);
