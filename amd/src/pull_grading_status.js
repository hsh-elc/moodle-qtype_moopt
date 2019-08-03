define(['jquery', 'core/ajax', 'core/modal_factory', 'core/modal_events', 'core/str'], function ($, ajax, ModalFactory, ModalEvents, Strings) {

    var timer;
    var qubaid;
    var isCurrentlyShowingModal = false;

    function checkGradingFinished() {
        ajax.call([{
                methodname: 'qtype_programmingtask_retrieve_grading_results',
                args: {qubaid: qubaid},
                done: function (result) {
                    if(result){
                        showReloadModal();
                    }
                },
                fail: function (errorObject) {
                    console.log(errorObject);
                }
            }]);
    }

    function showReloadModal() {
        if (isCurrentlyShowingModal)
            return;
        isCurrentlyShowingModal = true;

        let strings = [{
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

                modal.getRoot().on(ModalEvents.save, function (e) {
                    location.reload(true);
                    isCurrentlyShowingModal = false;
                });

                modal.getRoot().on(ModalEvents.hidden, function (e) {
                    isCurrentlyShowingModal = false;
                });

                modal.show();
            });
        });
    }

    return {

        init: function (qubaid_param) {
            qubaid = qubaid_param;
            if (typeof timer === 'undefined') {
                timer = setInterval(checkGradingFinished, 5000);
            }
        }
    };

});