define(['core/ajax'], function (ajax) {
    return {

        initForFileSubmissions: function (checkbuttonid, itemid) {
            let self = this;
            setInterval(function () { //TODO: maybe find a better solution than polling
                self.toggleCheckbuttonForFileSubmissions(checkbuttonid, itemid);
            }, 1000);
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

        toggleCheckbuttonForFileSubmissions: function (checkbuttonid, itemid) {
            let self = this;
            ajax.call([
                {
                    methodname: 'qtype_moopt_check_if_filearea_is_empty',
                    args: {itemid: itemid},
                    async: false,
                    done: function (result) {
                        self.toggleCheckbutton(checkbuttonid, result);
                    },
                    fail: function () {
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
        }

    };
});