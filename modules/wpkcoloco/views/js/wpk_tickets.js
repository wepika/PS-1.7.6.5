var wpk = {

    init: function () {
        var self = wpk;

        self._init.elements();
        self._init.events();
    },


    _init: {
        elements: function() {
            var self = wpk;
            self.elem = {
                printButton: $('.print_button')
            };

        },

        events: function () {
            var self = wpk;
            self.elem.printButton.on('click', self.evt.clickOnPrintButton)

        },

    },

    fct: {

    },

    evt: {
        clickOnPrintButton : function (e){
            var self = wpk;

            id_ticket = $(e.target).attr('data-id_ticket');

            $('#ticket_content_' + id_ticket ).print();
        },
    }
};

$(function () {
    wpk.init();
});