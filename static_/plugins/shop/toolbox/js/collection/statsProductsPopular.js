define([
    'plugins/shop/toolbox/js/collection/listProducts'
], function (ListProducts) {

    var StatsListProductsTodays = ListProducts.extend({
        initialize: function () {
            ListProducts.prototype.initialize.apply(this);
            this.url = APP.getApiLink('shop','shopstats',{
                type: 'products_list_popular'
            });
        },
        mode: "client",
        parseState: function (resp) {
            var state = {
                totalRecords: parseInt(resp && resp.items && resp.items.length || 0, 10),
                currentPage: 1
            };
            return state;
        }
    });

    return StatsListProductsTodays;
});