define(['core/ajax', 'core/modal_save_cancel', 'core/modal_events', 'core/str', 'core/pending'],
function (ajax, SaveCancelModal, ModalEvents, Strings, Pending) {

    var timer;
    var qubaid;
    var isCurrentlyShowingModal = false;
    var gradingPending;

    function checkGradingFinished() {
        ajax.call([
            {
                methodname: 'qtype_moopt_service_retrieve_grading_results',
                args: {qubaid: qubaid},
                done: function (result) {
                    result['estimatedSecondsRemainingForEachQuestion'].forEach(function (estimatedSecondsRemainingForOneQuestion) {
                        let elems = document.getElementsByClassName("estimatedSecondsRemaining_" + estimatedSecondsRemainingForOneQuestion['questionId']);
                            Array.from(elems).forEach(function(div) {
                            if (div.style.display === 'none') {
                                div.style.display = 'block';
                            }
                        });
                        elems = document.getElementsByClassName("estimatedSecondsRemainingValue_" + estimatedSecondsRemainingForOneQuestion['questionId']);
                        Array.from(elems).forEach(function(elem) {
                            elem.innerHTML = estimatedSecondsRemainingForOneQuestion['estimatedSecondsRemaining'];
                        })
                    });
                    if (result['finished']) {
                        showReloadModal();
                    }
                },
                fail: function (errorObject) {
                    console.log(errorObject);
                }
            }
        ]);
    }

    function showReloadModal() {
        if (isCurrentlyShowingModal) {
            return;
        }
        isCurrentlyShowingModal = true;

        var strings = [
        {
            key: 'reloadpage',
            component: 'qtype_moopt'
        },
        {
            key: 'gradeprocessfinished',
            component: 'qtype_moopt'
        },
        {
            key: 'reload',
            component: 'qtype_moopt'
        }];

        Strings.get_strings(strings).then(values => {
            SaveCancelModal.create({
                title: values[0],
                body: values[1],
                show: true,
                removeOnClose: true,
                buttons: {
                    save: values[2]
                }
            }).then(modal => {
                modal.getRoot().on(ModalEvents.save, () => {
                    location.reload(true);
                });
                gradingPending.resolve(); // So behat knows when the grading is ready and the reload button exists
            });
        });
    }

    return {

        init: function (qubaid_param, slot, polling_interval) {
            // Don't show the retry button yet.
            document.querySelectorAll("input[name='redoslot" + slot + "'").forEach(function (elem) {
                elem.remove();
            });

            qubaid = qubaid_param;
            if (typeof timer === 'undefined') {
                // Needed because the grading is async and behat would otherwise click reload immediately after
                // this function returns (see https://jsdoc.moodledev.io/main/module-core_pending.html)
                gradingPending = new Pending('qtype_moopt/pull_grading_status:grading');
                
                timer = setInterval(checkGradingFinished, polling_interval);
            }
        }
    };

});