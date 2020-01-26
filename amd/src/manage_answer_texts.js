define(['jquery'], function ($) {

    var maxNumberFields;
    var currentNumberFields;
    return {

        init: function (maxFields, initialNumberFields) {
            maxNumberFields = maxFields;
            currentNumberFields = initialNumberFields;
            this.adjustVisibility();
            var self = this;
            $("#addAnswertextButton").click(function (event) {
                if (currentNumberFields < maxNumberFields) {
                    currentNumberFields++;
                    self.adjustVisibility();
                }
                event.preventDefault();
            });
            $("#removeLastAnswertextButton").click(function (event) {
                if (currentNumberFields > 1) {
                    currentNumberFields--;
                    self.adjustVisibility();
                }
                event.preventDefault();
            });
        },

        adjustVisibility: function () {
            for (var i = 0; i < maxNumberFields; i++) {
                var id = "#qtype_programmingtask_answertext_" + i;
                if (i < currentNumberFields) {
                    $(id).show();
                } else {
                    $(id).hide();
                }
            }
            if (currentNumberFields == maxNumberFields) {
                $("#addAnswertextButton").prop('disabled', true);
            } else {
                $("#addAnswertextButton").prop('disabled', false);
            }

            if (currentNumberFields <= 1) {
                $("#removeLastAnswertextButton").prop('disabled', true);
            } else {
                $("#removeLastAnswertextButton").prop('disabled', false);
            }

        }
    };
});