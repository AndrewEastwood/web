define('plugin/shop/toolbox/js/collection/statsProductsPopular', [
    'plugin/shop/toolbox/js/collection/basicProducts'
], function (ListOrders) {

    var StatsListOrdersTodays = ListOrders.extend({
        initialize: function () {
            ListOrders.prototype.initialize.apply(this);
            this.url = APP.getApiLink({
                source: 'shop',
                fn: 'stats',
                type: 'products_list_popular'
            });
        },
        mode: "client"
    });

    return StatsListOrdersTodays;
});