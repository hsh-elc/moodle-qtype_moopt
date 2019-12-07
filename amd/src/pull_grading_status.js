define(['jquery', 'core/ajax', 'core/modal_factory', 'core/modal_events', 'core/str'], function ($, ajax, ModalFactory, ModalEvents, Strings) {

    var timer;
    var qubaid;
    var isCurrentlyShowingModal = false;

    function checkGradingFinished() {
        ajax.call([{
                methodname: 'qtype_programmingtask_retrieve_grading_results',
                args: {qubaid: qubaid},
                done: function (result) {
                    if (result) {
                        showReloadModal();
                    }
                },
                fail: function (errorObject) {
                    console.log(errorObject);
                }
            }]);
    }

    function showReloadModal() {
        if (isCurrentlyShowingModal) {
            return;
        }
        isCurrentlyShowingModal = true;

        var strings = [{
                key: 'reloadpage',
                component: 'qtype_programmingtask'
            }, {
                key: 'gradeprocessfinished',
                component: 'qtype_programmingtask'
            }, {
                key: 'reload',
                component: 'qtype_programmingtask'
            }];

        Strings.get_strings(strings).then(function (values) {
            ModalFactory.create({
                type: ModalFactory.types.SAVE_CANCEL,
                title: values[0],
                body: values[1]
            }).done(function (modal) {
                modal.setSaveButtonText(values[2]);

                modal.getRoot().on(ModalEvents.save, function () {
                    location.reload(true);
                    isCurrentlyShowingModal = false;
                });

                modal.getRoot().on(ModalEvents.hidden, function () {
                    isCurrentlyShowingModal = false;
                });

                modal.show();
            });
        });
    }

    return {

        init: function (qubaid_param, polling_interval) {
            //Don't show the retry button yet
            $( "input[name='redoslot2']" ).remove();

            qubaid = qubaid_param;
            if (typeof timer === 'undefined') {
                timer = setInterval(checkGradingFinished, polling_interval);
            }
        }
    };

});