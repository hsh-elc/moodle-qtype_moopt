define(['core/ajax'], function (ajax) {
    return {

        initForFileSubmissions: function (checkbuttonid, filemanagerid, draftareaid) {
            let self = this;

            self.toggleCheckbuttonForFileSubmissions(checkbuttonid, draftareaid);

            let target = document.getElementById(filemanagerid);
            let observer = new MutationObserver(function(mutations) {
                self.toggleCheckbuttonForFileSubmissions(checkbuttonid, draftareaid);
            });
            observer.observe(target, {
                attributes:    true,
                childList:     true,
                characterData: true
            });
        },
        initForFreetextSubmissions: function (checkbuttonid, textareaids) {
            let self = this;

            self.toggleCheckbutton(checkbuttonid, self.checkIfAllTextareasAreEmpty(textareaids));

            let observer = new MutationObserver(function(mutations) {
                self.toggleCheckbutton(checkbuttonid, self.checkIfAllTextareasAreEmpty(textareaids));
            });
            textareaids.forEach(function(textareaid) {
                let target = document.getElementById(textareaid);
                observer.observe(target, {
                    attributes: true,
                    attributeFilter: ["value"],
                    subtree: true,
                    childList: true
                });
            });
        },
        initForFileAndFreetextSubmissions: function (checkbuttonid, filemanagerid, draftareaid, textareaids) {
            let self = this;

            self.toggleForFileAndFreetextSubmissions(checkbuttonid, draftareaid, textareaids);

            let target = document.getElementById(filemanagerid);
            let observerForFileManager = new MutationObserver(function(mutations) {
                self.toggleForFileAndFreetextSubmissions(checkbuttonid, draftareaid, textareaids);
            });
            observerForFileManager.observe(target, {
                attributes:    true,
                childList:     true,
                characterData: true
            });

            let observerForFreetext = new MutationObserver(function(mutations) {
                self.toggleForFileAndFreetextSubmissions(checkbuttonid, draftareaid, textareaids);
            });
            textareaids.forEach(function(textareaid) {
                let target = document.getElementById(textareaid);
                observerForFreetext.observe(target, {
                    attributes: true,
                    attributeFilter: ["value"],
                    subtree: true,
                    childList: true
                });
            });
        },

        toggleCheckbuttonForFileSubmissions: function (checkbuttonid, draftareaid) {
            let self = this;
            ajax.call([
                {
                    methodname: 'qtype_moopt_check_if_filearea_is_empty',
                    args: {itemid: draftareaid},
                    done: function (result) {
                        self.toggleCheckbutton(checkbuttonid, result);
                    },
                    fail: function (errorObject) {
                        console.error(errorObject);
                        //Activate that button if something went wrong so the answer can still be submitted
                        self.toggleCheckbutton(checkbuttonid, false);
                    }
                }
            ]);
        },
        checkIfAllTextareasAreEmpty: function (textareaids) {
            let onlyEmptyAreas = true;
            for (let i = 0; i < textareaids.length; i++) {
                if (document.querySelector("#" + textareaids[i] + " textarea").value.length > 0) {
                    onlyEmptyAreas = false;
                    break;
                }
            }
            return onlyEmptyAreas;
        },
        toggleForFileAndFreetextSubmissions: function(checkbuttonid, draftareaid, textareaids) {
            let self = this;
            if (self.checkIfAllTextareasAreEmpty(textareaids)) {
                self.toggleCheckbuttonForFileSubmissions(checkbuttonid, draftareaid);
            } else {
                self.toggleCheckbutton(checkbuttonid, false);
            }
        },

        toggleCheckbutton: function (checkbuttonid, disable) {
            document.getElementById(checkbuttonid).disabled = disable;
        }
    };
});