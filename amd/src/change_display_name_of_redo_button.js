define(['core/ajax', 'core/str'], function (ajax, Strings) {

    return {
        init: function (slot) {
            Strings.get_string('redobuttontext', 'qtype_moopt').then(function (value) {
                let button = document.querySelector("input[name='redoslot" + slot + "'");
                if (button != null) {
                    button.value = value;
                }
            });
        }
    };

});