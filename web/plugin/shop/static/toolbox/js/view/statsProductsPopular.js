define("plugin/shop/toolbox/js/view/statsProductsPopular", [
    'default/js/lib/backbone',
    'plugin/shop/toolbox/js/collection/statsProductsPopular',
    'plugin/shop/toolbox/js/view/listProducts',
    'default/js/lib/utils',
    /* template */
    'default/js/plugin/hbs!plugin/shop/toolbox/hbs/statsProductsPopular',
    /* lang */
    'default/js/plugin/i18n!plugin/shop/toolbox/nls/translation'
], function (Backbone, CollectionProductsPopular, ViewListProducts, Utils, tpl, lang) {

    return Backbone.View.extend({
        className: 'panel panel-default',
        lang: lang,
        template: tpl,
        initialize: function () {
            this.collection = new CollectionProductsPopular();
            this.viewList = new ViewListProducts({
                collection: this.collection,
                adjustColumns: function (columns) {
                    delete columns.columnID;
                    delete columns.columnDateUpdated;
                    delete columns.columnDateCreated;
                    delete columns.columnSKU;
                    delete columns.columnModel;
                    columns.SoldTotal = {
                        name: "SoldTotal",
                        label: "Продано",
                        cell: "string",
                        editable: false
                    };
                    columns.SumTotal = {
                        name: "SumTotal",
                        label: "На суму",
                        cell: "string",
                        editable: false,
                        formatter: {
                            fromRaw: function (value) {
                                var _currencyDisplay = APP.instances.shop.settings.DBPriceCurrencyType._display;
                                if (_currencyDisplay) {
                                    if (_currencyDisplay.showBeforeValue) {
                                        return _currencyDisplay.text + value;
                                    } else {
                                        return value + _currencyDisplay.text;
                                    }
                                } else {
                                    return value;
                                }
                            }
                        }
                    };
                    return columns;
                }
            });
            this.viewList.grid.emptyText = "Немає популярних товарів";
        },
        render: function () {
            this.$el.html(tpl(Utils.getHBSTemplateData(this)));
            this.$('.panel-body').html(this.viewList.$el);
            return this;
        }
    });
});