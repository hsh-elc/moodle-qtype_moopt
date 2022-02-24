define(['core/ajax'], function (ajax) {
    return {

        initForFileSubmissions: function (checkbuttonid, itemid) {
            let self = this;
            setInterval(function () { //TODO: maybe find a better solution than polling
                self.toggleForFileSubmissions(checkbuttonid, itemid);
            }, 1000);
        },
        toggleForFileSubmissions: function (checkbuttonid, itemid) {
            ajax.call([
                {
                    methodname: 'qtype_moopt_check_if_filearea_is_empty',
                    args: {itemid: itemid},
                    done: function (result) {
                        document.getElementById(checkbuttonid).disabled = result;
                    },
                    fail: function () {
                        //Activate that button if something went wrong so the answer can still be submitted
                        document.getElementById(checkbuttonid).disabled = true;
                    }
                }
            ]);
        },

        initForFreetextSubmissions: function (checkbuttonid, textareaids) {
            let self = this;
            setInterval(function() { //TODO: maybe find a better solution than polling
                self.toggleForFreetextSubmissions(checkbuttonid, textareaids);
            }, 1000);
        },
        toggleForFreetextSubmissions: function (checkbuttonid, textareaids) {
            let onlyEmptyAreas = true;
            for (let i = 0; i < textareaids.length; i++) {
                if (document.getElementById(textareaids[i]).value.length > 0) {
                    onlyEmptyAreas = false;
                    break;
                }
            }
            document.getElementById(checkbuttonid).disabled = onlyEmptyAreas;
        }

    };
});