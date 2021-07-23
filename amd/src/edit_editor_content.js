/**
 * Module contains functionality to change the contents of an editor regardless whether is the plain text editor, tinyMCE or atto
 */

/*global tinyMCE */

define(['jquery'], function ($) {

    return {

        setContents: function (common_id, contents) {
            if (typeof tinyMCE === 'undefined') {
                var elem= document.getElementById(common_id + "editable");
                if (elem === null) {
                    // probably we are facing the plain text editor:
                    elem= document.getElementById(common_id);
                    elem.value = contents;
                } else {
                    // Atto HTML editor uses a div containing inner html:
                    elem.innerHTML = contents;
                    // kind of a weird bug fix: extremely large texts require to have their
                    // text field focused/clicked upon for the text value to be written after
                    // the texts are extracted from the task.zip.
                    // the fix is to automatically do that focusing:
                    elem.focus();
                }
            } else {
                tinyMCE.get(common_id).setContent(contents);
            }
        },

        setContentsOfText: function (common_id, contents) {
            $("[id^='" + common_id + "']").val(contents);
        }

    };
});
