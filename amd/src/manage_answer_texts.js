define([], function () {

    var maxNumberFields;
    var currentNumberFields;
    return {

        init: function (maxFields, initialNumberFields, questionid) {

            maxNumberFields = maxFields;
            currentNumberFields = initialNumberFields;

            this.adjustVisibility(questionid);
            var self = this;
            document.querySelector("#addAnswertextButton_" + questionid).addEventListener("click", function (event) {
                if (currentNumberFields < maxNumberFields) {
                    currentNumberFields++;
                    self.adjustVisibility(questionid);
                }
                event.preventDefault();
            });
            document.querySelector("#removeLastAnswertextButton_" + questionid).addEventListener("click", function (event) {
                if (currentNumberFields > 1) {
                    currentNumberFields--;
                    self.adjustVisibility(questionid);
                }
                event.preventDefault();
            });
        },

        adjustVisibility: function (questionid) {
            for (var i = 0; i < maxNumberFields; i++) {
                var id = "#qtype_moopt_answertext_" + questionid + "_" + i;
                if (i < currentNumberFields) {
                    document.querySelector(id).style.display = "block";
                } else {
                    document.querySelector(id).style.display = "none";
                }
            }
            if (currentNumberFields == maxNumberFields) {
                document.querySelector("#addAnswertextButton_" + questionid).disabled = true;
            } else {
                document.querySelector("#addAnswertextButton_" + questionid).disabled = false;
            }

            if (currentNumberFields <= 1) {
                document.querySelector("#removeLastAnswertextButton_" + questionid).disabled = true;
            } else {
                document.querySelector("#removeLastAnswertextButton_" + questionid).disabled = false;
            }

        }
    };
});