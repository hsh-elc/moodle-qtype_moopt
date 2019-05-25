/**
 * Module that contains functions in order to extract the necessary informations from a ProFormA-task and insert into the respective form elements.
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

                    ajax.call([{
                            methodname: 'qtype_programmingtask_extract_task_infos_from_draft_file',
                            args: {itemid: itemId},
                            done: function (result) {
                                $("#id_error_ajaxerrorlabel").parent().children().first().html('');
                                $("#id_name").val(result.title);
                                editorEditor.setContents('id_questiontext', result.description);
                                editorEditor.setContents('id_internaldescription', result.internaldescription);
                            },
                            fail: function (errorObject) {
                                console.log(errorObject);
                                $("#id_error_ajaxerrorlabel").parent().children().first().
                                        html('<div>' + errorObject.debuginfo + '</div><div> For more information see browser console.</div>');
                            }
                        }]);
                }

            };
        }
);