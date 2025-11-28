
define([], function () {

    return {
        init: function (blockid) {
            var self = this;
            let expandbtn = document.querySelector("#" + blockid + "-expand-all-button");
            expandbtn.addEventListener('click', function(e) {
                e.preventDefault();
                self.toggle(blockid, "show");
            });
            let collapsebtn = document.querySelector("#" + blockid + "-collapse-all-button");
            collapsebtn.addEventListener('click', function(e) {
                e.preventDefault();
                self.toggle(blockid, "hide");
            });
        },

        toggle: function (feedbackblockid, operation) {
            let expanded = operation == "show" ? "false" : "true";
            let selector = "#" + feedbackblockid + " button[data-bs-toggle='collapse'][aria-expanded='" + expanded + "']";
            let buttons = document.querySelectorAll(selector);
            buttons.forEach(
                function(b) {
                    b.click();
                }
            );
        }
    };
});
