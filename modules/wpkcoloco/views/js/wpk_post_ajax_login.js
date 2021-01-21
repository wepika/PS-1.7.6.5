let wpk_post_ajax_login = {
    init: function () {
        let self = wpk_post_ajax_login;
        self._init.elements();
        self._init.events();
    },
    _init: {
        elements: function () {
            let self = wpk_post_ajax_login;
            self.vars = {
                isPostAjaxLoginCallable: isPostAjaxLoginCallable,
                controllerUrl: controllerUrl,
            };
            self.elem = {
                modals: {
                    bind_card: $('#wpkcoloco_bind_card'),
                    awaiting_email: $('#wpkcoloco_awaiting_email'),
                }
            };
        },
        events: function () {
            let self = wpk_post_ajax_login;

            // On load
            if (self.vars.isPostAjaxLoginCallable) {
                self.fct.postLoginAjaxCall();
            }
        }
    },
    fct: {
        postLoginAjaxCall: function () {
            let self = wpk_post_ajax_login;
            $.ajax({
                url: self.vars.controllerUrl,
                dataType: 'json',
                success: function (res) {
                    if (res.hasOwnProperty('action')) {
                        if (res.action === 'awaiting_email') {
                            self.elem.modals.awaiting_email.modal({backdrop: 'static'});
                        } else if (res.action === 'bind_card') {
                            self.elem.modals.bind_card.modal({backdrop: 'static'});
                        }
                    }
                }
            });
        },
    },
    evt: {}
};

$(function () {
    wpk_post_ajax_login.init();
});