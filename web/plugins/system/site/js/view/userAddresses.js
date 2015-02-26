define([
    'sandbox',
    'jquery',
    'underscore',
    'backbone',
    'utils',
    'plugins/system/common/js/view/userAddress',
    'hbs!plugins/system/site/hbs/userAddresses',
    /* lang */
    'i18n!plugins/system/site/nls/translation',
    'base/js/lib/bootstrap-editable'
], function (Sandbox, $, _, Backbone, Utils, ViewUserAddress, tpl, lang) {

    var UserAddresses = Backbone.View.extend({
        // tagName: 'li',
        // className: 'col-sm-9 col-md-9',
        addressList: [],
        template: tpl,
        lang: lang,
        events: {
            "click #account-address-add-btn-ID": "addNewAddress",
        },
        initialize: function () {
            _(this.addressList).invoke('remove');
            this.addressList = [];
            if (this.model)
                this.listenTo(this.model, 'change', this.render);
            _.bindAll(this, 'addNewAddress', 'refreshAddNewAddressButton', 'renderAddressItem');
        },
        render: function () {
            var self = this;
            // debugger;
            this.$el.html(this.template(Utils.getHBSTemplateData(this)));

            var addresses = this.model.get('Addresses');

            // debugger;
            _(addresses).each(function(address){
                self.renderAddressItem(address);
            });

            this.$('.editable').editable({
                mode: 'inline',
                emptytext: lang.profile_page_addresses_label_emptyValue
            });

            this.refreshAddNewAddressButton();

            return this;
        },
        refreshAddNewAddressButton: function () {
            this.$("#account-address-add-btn-ID").toggleClass('hide', this.addressList.length >= 3);
        },
        renderAddressItem: function (address) {

            // debugger;
            var addressView = new ViewUserAddress({
                UserID: this.model.id,
                address: address
            });
            addressView.render();

            var self = this;

            addressView.model.on('change', function() {
                self.model.fetch({silent: true});
            });
            addressView.model.on('destroy:ok', function() {
                self.model.fetch({silent: true});
                addressView.remove();
                self.addressList = _(self.addressList).without(addressView);
                self.refreshAddNewAddressButton();
            });

            this.addressList.push(addressView);

            this.$('.account-addresses').append(addressView.$el);

            this.refreshAddNewAddressButton();
        },
        addNewAddress: function (address) {
            if (this.addressList.length >= 3)
                return;
            this.renderAddressItem();
        }
    });

    return UserAddresses;

});