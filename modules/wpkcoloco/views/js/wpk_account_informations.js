let wpk_account_informations = {
    init: () => {
        let self = wpk_account_informations;
        self._init.elements();
        self._init.events();
    },
    _init: {
        elements: () => {
            let self = wpk_account_informations;
            self.vars = {};
            self.elem = {
                newPasswordToggleBtn: $('#toggle-new-password'),
                changePreferredStoreToggleBtn: $('#toggle-store-locator'),
                postcodeField: $('input[name="postcode"]'),
                preferredStoreBlock: $('#preferred-store-block'),
                storeNearbyBlock: $('#store-nearby-block'),

                search_button: $('#search_stores'),
                postal_code_search: $('#postal_code_search'),
                country_search: $('#country_search'),
                range_search: $('#range_search'),
                store_nearby: $('#store_nearby'),
            };
        },
        events: () => {
            let self = wpk_account_informations;

            self.evt.onLoad();

            self.elem.newPasswordToggleBtn.on('click', self.evt.onNewPasswordToggleBtnClick);
            self.elem.changePreferredStoreToggleBtn.on('click', self.evt.onPreferredStoreToggleBtnClick);
            self.elem.search_button.on('click', self.evt.onSearchButtonClick);
        }
    },
    fct: {
        displayStoreList: () => {
            let self = wpk_account_informations;
            $.ajax({
                type: 'POST',
                url: loyaltyController,
                data: {
                    ajax: true,
                    action: 'getNearbyShop',
                    postal_code_search: self.elem.postal_code_search.val(),
                    country_search: self.elem.country_search.val(),
                    range_search: self.elem.range_search.val(),
                    preselect: 1,
                },
                beforeSend: () => {
                    self.elem.storeNearbyBlock.slideUp();
                },
                success: (res) => {
                    self.elem.store_nearby.html(res);
                    self.elem.storeNearbyBlock.slideDown();
                },
            });
        },
    },
    evt: {
        onLoad: () => {
            let self = wpk_account_informations;

            // Hide store nearby shop
            self.elem.storeNearbyBlock.hide();

            let value = self.elem.postcodeField.val();

            if (value !== undefined && value.length > 0) {
                // Fill postcode search with customer's postcode
                self.elem.postal_code_search.val(value);
                self.fct.displayStoreList();
            }
        },
        onNewPasswordToggleBtnClick: (e) => {
            let self = wpk_account_informations;

            let newPasswordFormGroupElement = document.getElementsByName('new_password')[0].parentElement.parentElement;
            let displayStyle = newPasswordFormGroupElement.style.display;

            if (displayStyle === 'none') {
                $(newPasswordFormGroupElement).slideDown();
            } else {
                $(newPasswordFormGroupElement).slideUp();
            }
        },
        onPreferredStoreToggleBtnClick: (e) => {
            let self = wpk_account_informations;

            let newPasswordFormGroupElement = document.getElementById('preferred-store-block');
            let displayStyle = newPasswordFormGroupElement.style.display;

            if (displayStyle === 'none') {
                $(newPasswordFormGroupElement).slideDown();
            } else {
                $(newPasswordFormGroupElement).slideUp();
            }
        },
        onSearchButtonClick: (e) => {
            e.preventDefault();

            let self = wpk_account_informations;

            self.fct.displayStoreList();
        }
    }
};
$(() => {
    wpk_account_informations.init();
});