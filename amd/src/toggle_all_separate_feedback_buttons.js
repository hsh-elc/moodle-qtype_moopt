
define([], function () {

    return {
        init: function (feedbackblockid) {
            var self = this;
            let expandbtn = document.querySelector("#" + feedbackblockid + "-expand-all-button");
            expandbtn.addEventListener('click', function(e) {
                e.preventDefault();
                self.toggle(feedbackblockid, "show");
            });
            let collapsebtn = document.querySelector("#" + feedbackblockid + "-collapse-all-button");
            collapsebtn.addEventListener('click', function(e) {
                e.preventDefault();
                self.toggle(feedbackblockid, "hide");
            });
        },

        toggle: function (feedbackblockid, operation) {
            let expanded = operation == "show" ? "false" : "true";
            let selector = "#" + feedbackblockid + " button[data-toggle='collapse'][aria-expanded='" + expanded + "']";
            let buttons = document.querySelectorAll(selector);
            buttons.forEach(
                function(b) {
                    b.click();
                }
            );
        }
    };
});
