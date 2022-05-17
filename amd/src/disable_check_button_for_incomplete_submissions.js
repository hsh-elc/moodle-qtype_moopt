define(['core/ajax'], function (ajax) {
    return {

        initForFileSubmissions: function (checkbuttonid, filemanagerid, draftareaid) {
            let self = this;
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
            setInterval(function() { //TODO: maybe find a better solution than polling
                self.toggleCheckbutton(checkbuttonid, self.checkIfAllTextareasAreEmpty(textareaids));
            }, 1000);
        },
        initForFileAndFreetextSubmissions: function (checkbuttonid, itemid, textareaids) {
            let self = this;
            setInterval(function () { //TODO: maybe find a better solution than polling
                if (self.checkIfAllTextareasAreEmpty(textareaids)) {
                    self.toggleCheckbuttonForFileSubmissions(checkbuttonid, itemid);
                } else {
                    self.toggleCheckbutton(checkbuttonid, false);
                }
            }, 1000);
        },

        toggleCheckbuttonForFileSubmissions: function (checkbuttonid, draftareaid) {
            let self = this;
            ajax.call([
                {
                    methodname: 'qtype_moopt_check_if_filearea_is_empty',
                    args: {itemid: draftareaid},
                    async: false,
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
                if (document.getElementById(textareaids[i]).value.length > 0) {
                    onlyEmptyAreas = false;
                    break;
                }
            }
            return onlyEmptyAreas;
        },

        toggleCheckbutton: function (checkbuttonid, disable) {
            document.getElementById(checkbuttonid).disabled = disable;
            // somewhat hacky, but the element id is not likely to change
            document.getElementById('mod_quiz-next-nav').disabled = disable;
        }
    };
});