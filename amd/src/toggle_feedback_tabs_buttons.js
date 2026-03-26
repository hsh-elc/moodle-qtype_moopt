
define([], function () {

    return {
        init: function (blockid) {
            var self = this;            
            let showstudentfeedbackbtn = document.querySelector('#' + blockid + '-show-student-feedback-button');
            showstudentfeedbackbtn.addEventListener('click', function(e) {
                e.preventDefault();
                self.toggle(blockid, 'student');
            });
            let showteacherfeedbackbtn = document.querySelector('#' + blockid + '-show-teacher-feedback-button');
            showteacherfeedbackbtn.addEventListener('click', function(e) {
                e.preventDefault();
                self.toggle(blockid, 'teacher');
            });
        },

        toggle: function (feedbackblockid, tab) {
            let block = document.querySelector('#' + feedbackblockid);
            let studentfeedbacktabs = block.querySelectorAll('.moopt-student-feedback-tab');
            let teacherfeedbacktabs = block.querySelectorAll('.moopt-teacher-feedback-tab');
            if (tab === 'student') {
                studentfeedbacktabs.forEach(t => {
                    t.classList.remove('hidden');
                });
                teacherfeedbacktabs.forEach(t => {
                    t.classList.add('hidden');
                });
            } else if (tab === 'teacher') {
                teacherfeedbacktabs.forEach(t => {
                    t.classList.remove('hidden');
                });
                studentfeedbacktabs.forEach(t => {
                    t.classList.add('hidden');
                });
            }
        }
    };
});
