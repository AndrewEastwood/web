define('plugin/shop/toolbox/js/model/statsOrdersOverview', [
    'default/js/lib/backbone'
], function (Backbone) {

    var Stats = Backbone.Model.extend({
        initialize: function (type) {
            this.url = APP.getApiLink({
                source: 'shop',
                fn: 'stats',
                type: 'overview_orders'
            });
        }
    });

    return Stats;
});