define([], function () {

    var maxNumberFields;
    var currentNumberFields;
    return {

        init: function (maxFields, initialNumberFields, questionid) {

            maxNumberFields = maxFields;
            currentNumberFields = initialNumberFields;

            this.adjustVisibility(questionid);
            var self = this;

            // The add/remove buttons are not rendered for students when filenames are fixed
            var addButton = document.querySelector("#addAnswertextButton_" + questionid);
            if (addButton) {
                addButton.addEventListener("click", function (event) {
                    if (currentNumberFields < maxNumberFields) {
                        currentNumberFields++;
                        self.adjustVisibility(questionid);
                    }
                    event.preventDefault();
                });
            }
            var removeButton = document.querySelector("#removeLastAnswertextButton_" + questionid);
            if (removeButton) {
                removeButton.addEventListener("click", function (event) {
                    if (currentNumberFields > 1) {
                        currentNumberFields--;
                        self.adjustVisibility(questionid);
                    }
                    event.preventDefault();
                });
            }
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

            var addButton = document.querySelector("#addAnswertextButton_" + questionid);
            if (addButton) {
                if (currentNumberFields == maxNumberFields) {
                    addButton.disabled = true;
                } else {
                    addButton.disabled = false;
                }
            }

            var removeButton = document.querySelector("#removeLastAnswertextButton_" + questionid);
            if (removeButton) {
                if (currentNumberFields <= 1) {
                    removeButton.disabled = true;
                } else {
                    removeButton.disabled = false;
                }
            }
        }
    };
});