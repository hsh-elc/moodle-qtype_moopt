define(['core/ajax', 'core/str'], function (ajax, Strings) {

    return {
        init: function () {
            Strings.get_string('redobuttontext', 'qtype_moopt').then(function (value) {
                let button = document.querySelector("input[name^='redoslot']").item(0);
                if (button != null) {
                    button.value = value;
                }
            });
        }
    };

});