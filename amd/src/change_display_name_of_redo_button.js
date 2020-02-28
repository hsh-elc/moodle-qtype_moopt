define(['jquery', 'core/ajax', 'core/str'], function ($, ajax, Strings) {

    return {
        init: function () {
            Strings.get_string('redobuttontext', 'qtype_programmingtask').then(function (value) {
                let button = $("input[name^='redoslot']");
                if (button.length) {
                    button.val(value);
                }
            });
        }
    };

});