/**
 * Module that contains functions in order to extract the necessary informations from a ProFormA-task and insert into
 * the respective form elements.
 */

/*global console */

define(['jquery', 'core/ajax',
    'qtype_programmingtask/edit_editor_content'],
        function ($, ajax, editorEditor) {

            return {

                init: function () {
                    var self = this;
                    $("#loadproformataskfilebutton").click(function () {
                        self.extractInformation();
                    });
                },

                extractInformation: function () {
                    var fileManager = $("#id_proformataskfileupload").parent();
                    var itemId = fileManager.find("[name='proformataskfileupload']")[0].value;
                    fileManager.find

                    ajax.call([
                        {
                            methodname: 'qtype_programmingtask_extract_task_infos_from_draft_file',
                            args: {itemid: itemId},
                            done: function (result) {

                                if (typeof result.error !== 'undefined') {
                                    $("#id_error_ajaxerrorlabel").parent().children().first().
                                            html('<div>' + result.error + '</div>');
                                    return;
                                }

                                $("#id_error_ajaxerrorlabel").parent().children().first().html('');
                                $("#id_name").val(result.title);
                                editorEditor.setContents('id_questiontext', result.description);
                                editorEditor.setContents('id_internaldescription', result.internaldescription);
                                editorEditor.setContentsOfText('id_taskuuid', result.taskuuid);
                                editorEditor.setContentsOfText('id_defaultmark', result.maxscoregradinghints);
                                editorEditor.setContents('id_generalfeedback', result.filesdisplayedingeneralfeedback);

                                var warnings = '';
                                if (typeof result.moodleValidationWarnings !== 'undefined') {
                                    if (typeof result.moodleValidationProformaNamespace !== 'undefined') {
                                        warnings = '<p>Detected ProFormA-version ' + result.moodleValidationProformaNamespace +
                                                '. Found the following problems during validation but still continued:</p><ul>';
                                        result.moodleValidationWarnings.forEach(function (e) {
                                            warnings += '<li>' + e + '</li>';
                                        });
                                        warnings += '</ul>';
                                    } else {
                                        warnings = '<p>' + result.moodleValidationWarnings + '</p>';
                                    }

                                }
                                $("#id_error_ajaxwarninglabel").parent().children().first().html(warnings);

                                // kind of a weird bug fix: extremely large texts require to have their
                                // text field focused/clicked upon for the text value to be written after
                                // the texts are extracted from the task.zip.
                                // the fix is to automatically do that focusing
                                $("#id_questiontexteditable").focus();
                                $("#id_internaldescriptioneditable").focus();
                                $("#id_generalfeedbackeditable").focus();
                                $("#id_name").focus();
                            },
                            fail: function (errorObject) {
                                console.log(errorObject);
                                $("#id_error_ajaxerrorlabel").parent().children().first().
                                        html('<div>' + errorObject.debuginfo + '</div><div> For more information see browser '
                                                + 'console.</div>');
                            }
                        }
                    ]);
                }

            };
        }
);
