define(['jquery', 'core/ajax', 'core/str'], function ($, ajax, Strings) {

    return {
        init: function () {
            Strings.get_string('redobuttontext', 'qtype_programmingtask').then(function (value) {
                $("input[name^='redoslot']").val(value);
            });
        }
    };

});