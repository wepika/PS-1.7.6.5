var wpk = {

    init: function () {
        var self = wpk;

        self._init.elements();
        self._init.events();
    },


    _init: {
        elements: function () {
            var self = wpk;

            self.elem = {
                search_button: $('#search_stores'),
                postal_code_search: $('#postal_code_search'),
                country_search: $('#country_search'),
                range_search: $('#range_search'),
                store_nearby: $('#store_nearby'),
                request_a_card: $('#request_a_card'),
                request_card_form: $('#request_card_form'),
            };

        },

        events: function () {
            var self = wpk;
            self.elem.search_button.on('click', self.evt.wantAStore);
            self.elem.request_a_card.on('mouseup', self.evt.waitACard);
        },

    },

    fct: {},

    evt: {
        wantAStore: function (e) {
            var self = wpk;
            e.preventDefault();
            $.ajax({
                    type: 'POST',
                    url: loyaltyController,
                    data: {
                        ajax: true,
                        action: 'getNearbyShop',
                        postal_code_search: self.elem.postal_code_search.val(),
                        country_search: self.elem.country_search.val(),
                        range_search: self.elem.range_search.val(),
                    },
                    success: (res) => {
                        self.elem.store_nearby.html(res);
                    },

                }
            )
            ;
        },
        waitACard: function (e) {
            var self = wpk;
            self.elem.request_card_form.submit();
            self.elem.request_a_card.attr("disabled","disabled");
            self.elem.request_a_card.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
        },
    }
};

$(function () {
    wpk.init();
});