define("plugin/account/site/js/view/accountProfileAddresses", [
    'default/js/lib/sandbox',
    'cmn_jquery',
    'default/js/lib/underscore',
    'default/js/lib/handlebars',
    'default/js/view/mView',
    'plugin/account/common/js/model/account',
    'default/js/plugin/hbs!plugin/account/site/hbs/accountProfileAddresses',
    'default/js/plugin/text!plugin/account/common/hbs/partials/accountAddress.hbs',
    /* lang */
    'default/js/plugin/i18n!plugin/account/site/nls/translation',
    'default/js/lib/bootstrap-editable'
], function (Sandbox, $, _, Handlebars, MView, ModelAccountInstance, tpl, tplAddress, lang) {

    var AccountProfileAddresses = MView.extend({
        // tagName: 'li',
        // className: 'col-sm-9 col-md-9',
        template: tpl,
        lang: lang,
        model: ModelAccountInstance,
        events: {
            "click #account-address-add-btn-ID": "addAddress"
        },
        initialize: function () {
            var self = this;

            this.model.clearErrors();
            this.model.clearStates();

            this.listenTo(this.model, "change", this.render);

            Sandbox.eventSubscribe("plugin:account:address:remove", function (data) {
                if (data.id)
                    self.model.removeAddress(data.id);
                else
                    $(data.event.target).parents('table.account-profile-address-entry').remove();
                self.$("#account-address-add-btn-ID").removeClass('hide');
            });

            Sandbox.eventSubscribe("plugin:account:address:save", function (data) {
                var _addressBlock = $(data.event.target).parents('table.account-profile-address-entry');
                if (data.id)
                    self.model.updateAddress(data.id, _addressBlock.find('.editable').editable('getValue'));
                else
                    self.model.addAddress(_addressBlock.find('.editable').editable('getValue'));
            });

            this.on('mview:renderComplete', function () {
                var profile = self.model.get('profile');
                if (profile && profile.addresses) {
                    // debugger;
                    if (profile.addresses.length >= 3)
                        self.$("#account-address-add-btn-ID").addClass('hide');
                    else
                        self.$("#account-address-add-btn-ID").removeClass('hide');
                    _(profile.addresses).each(function(address){
                        self.addAddress(address);
                    });
                }
                self.$('.editable').editable({
                    mode: 'inline',
                    emptytext: lang.profile_page_addresses_label_emptyValue
                });
            });
        },
        addAddress: function (address) {
            if (this.$('.account-addresses .account-profile-address-entry').length >= 3) {
                this.$("#account-address-add-btn-ID").addClass('hide');
                return false;
            }

            var _entryFn = Handlebars.compile(tplAddress);
            var _tplData = this.getTemplateData();
            if (address)
                _tplData.data = address;
            var _addressField = $(_entryFn(_tplData));
            _addressField.find('.editable').editable({
                mode: 'inline',
                emptytext: lang.profile_page_addresses_label_emptyValue
            });
            this.$('.account-addresses').append(_addressField);

            if (this.$('.account-addresses .account-profile-address-entry').length >= 3)
                this.$("#account-address-add-btn-ID").addClass('hide');
        }
    });

    return AccountProfileAddresses;

});