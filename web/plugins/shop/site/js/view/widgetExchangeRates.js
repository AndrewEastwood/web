define([
    'underscore',
    'backbone',
    'utils',
    'cache',
    /* template */
    'hbs!plugins/shop/site/hbs/widgetExchangeRates',
    /* lang */
    'i18n!plugins/shop/site/nls/translation'
], function (_, Backbone, Utils, Cache, tpl, lang) {

    var WidgetExchangeRates = Backbone.View.extend({
        className: 'btn-group exchange-rates-widget',
        id: 'exchange-rates-widget-ID',
        template: tpl,
        lang: lang,
        events: {
            'click .currency': 'changeCurrency'
        },
        render: function () {
            var data = Utils.getHBSTemplateData(this),
                currencyName = WidgetExchangeRates.getActiveCurrencyName(
                    APP.instances.shop.settings.MISC.SiteDefaultPriceCurrencyType,
                    APP.instances.shop.settings.MISC.ShowSiteCurrencySelector);
            this.$el.html(this.template(data));
            if (currencyName) {
                this.$('.active-currency').text(this.$('.dropdown-menu .' + currencyName).text());
            }
            return this;
        },
        changeCurrency: function (event) {
            var currencyName = $(event.target).parents('li').data('currency');
            Cache.set('userСurrencyName', currencyName);
            this.$('.active-currency').text(this.$('.dropdown-menu .' + currencyName).text());
            Backbone.trigger('changed:plugin-shop-currency', currencyName);
        }
    }, {
        getActiveCurrencyName: function (defaultCurrency, isSwitcherActive) {
            var displayCurrency = Cache.get('userСurrencyName') || defaultCurrency;
            if (!isSwitcherActive && defaultCurrency !== displayCurrency) {
                displayCurrency = defaultCurrency
                Cache.set('userСurrencyName', displayCurrency);
            }
            return displayCurrency;
        }
    });

    return WidgetExchangeRates;

});