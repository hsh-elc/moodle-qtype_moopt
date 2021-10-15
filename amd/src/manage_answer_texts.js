define([], function () {

    var maxNumberFields;
    var currentNumberFields;
    return {

        init: function (maxFields, initialNumberFields) {

            //these parameters are strings, convert them to numbers to prevent some problems later
            maxNumberFields = Number(maxFields);
            currentNumberFields = Number(initialNumberFields);

            this.adjustVisibility();
            var self = this;
            document.querySelector("#addAnswertextButton").addEventListener("click", function (event) {
                if (currentNumberFields < maxNumberFields) {
                    currentNumberFields++;
                    self.adjustVisibility();
                }
                event.preventDefault();
            });
            document.querySelector("#removeLastAnswertextButton").addEventListener("click", function (event) {
                if (currentNumberFields > 1) {
                    currentNumberFields--;
                    self.adjustVisibility();
                }
                event.preventDefault();
            });
        },

        adjustVisibility: function () {
            for (var i = 0; i < maxNumberFields; i++) {
                var id = "#qtype_moopt_answertext_" + i;
                if (i < currentNumberFields) {
                    document.querySelector(id).style.display = "block";
                } else {
                    document.querySelector(id).style.display = "none";
                }
            }
            if (currentNumberFields == maxNumberFields) {
                document.querySelector("#addAnswertextButton").disabled = true;
            } else {
                document.querySelector("#addAnswertextButton").disabled = false;
            }

            if (currentNumberFields <= 1) {
                document.querySelector("#removeLastAnswertextButton").disabled = true;
            } else {
                document.querySelector("#removeLastAnswertextButton").disabled = false;
            }

        }
    };
});