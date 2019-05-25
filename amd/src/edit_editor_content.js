/**
 * Module contains functionality to change the contents of an editor regardless whether is the plain text editor, tinyMCE or atto
 */

/*global tinyMCE */

define(['jquery'], function ($) {

    return {

        setContents: function (common_id, contents) {
            if (typeof tinyMCE === 'undefined') {
                $("[id^='" + common_id + "']").html(contents);
            } else {
                tinyMCE.get(common_id).setContent(contents);
            }
        }

    };
});
