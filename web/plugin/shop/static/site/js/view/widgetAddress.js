define("plugin/shop/site/js/view/widgetAddress", [
    'default/js/lib/underscore',
    'default/js/lib/backbone',
    'default/js/lib/utils',
    'default/js/lib/cache',
    /* collection */
    'plugin/shop/common/js/collection/settings',
    /* template */
    'default/js/plugin/hbs!plugin/shop/site/hbs/widgetAddress',
    /* lang */
    'default/js/plugin/i18n!plugin/shop/site/nls/translation'
], function (_, Backbone, Utils, Cache, CollectionSettings, tpl, lang) {

    var WidgetAddress = Backbone.View.extend({
        className: 'address-widget',
        id: 'address-widget-ID',
        template: tpl,
        lang: lang,
        events: {
            'click .address-option': 'changeUserAddress'
        },
        initialize: function() {
            this.collection = new CollectionSettings();
            this.collection.setCustomQueryField('Type', 'ADDRESS');
            this.collection.setCustomQueryField('Status', 'REMOVED:!=');
            this.listenTo(this.collection, 'reset', this.render);
        },
        render: function () {
            var data = Utils.getHBSTemplateData(this);
            this.settings = this.collection.toSettings();
            var userAddr = Cache.get('userAddr') || null;
            var activeAddress = null;
            var firstAddressUID = null;
            var addresses = _(settings.addresses).map(function (val) {
                if (_.isNull(userAddr)) {
                    userAddr = val.uid;
                    Cache.set('userAddr', userAddr);
                }
                if (_.isNull(firstAddressUID)) {
                    firstAddressUID = val.uid;
                }
                val.isActive = val.uid == userAddr;
                if (val.isActive) {
                    activeAddress = val;
                }
                return val;
            });
            if (activeAddress === null) {
                Cache.set('userAddr', firstAddressUID);
                activeAddress = settings.addresses[firstAddressUID];
                activeAddress.isActive = true;
            }
            this.$el.toggleClass('hidden', addresses.length === 0);
            // debugger;
            if (addresses.length) {
                data.extras = {
                    activeAddress: activeAddress,
                    addresses: addresses,
                    addressCount: addresses.length
                };
                APP.instances.shop.settings._activeAddress = activeAddress;
                this.$el.html(this.template(data));
            }
            return this;
        },
        changeUserAddress: function (event) {
            var addressUID = $(event.target).parents('li').data('ref');
            this.$('.address-item').addClass('hidden');
            this.$('#' + addressUID).removeClass('hidden');
            Cache.set('userAddr', addressUID);
            if (this.settings.addresses[addressUID]) {
                APP.instances.shop.settings._activeAddress = this.settings.addresses[addressUID];
                this.$('.address-switcher .shoptitle').text(this.settings.addresses[addressUID].ShopName.Value);
            }
        }
    });

    return WidgetAddress;

});