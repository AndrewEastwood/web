define('plugin/shop/toolbox/js/model/stats', [
    'default/js/lib/backbone'
], function (Backbone) {

    var Stats = Backbone.Model.extend({
        initialize: function (type) {
            this.url = APP.getApiLink({
                source: 'shop',
                fn: 'overview',
                type: type
            });
        }
    });

    return Stats;
});