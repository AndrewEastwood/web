define([
    'backbone',
    'underscore',
    'plugins/shop/toolbox/js/model/product',
    'cachejs',
    'backbone-pageable'
], function (Backbone, _, ModelProduct, Cache) {

    var ListProducts = Backbone.PageableCollection.extend({

        model: ModelProduct,

        url: APP.getApiLink('shop', 'products'),

        // Initial pagination states
        state: {
            pageSize: 30,
            order: 1
        },

        initialize: function () {
            this.extras = {};
            // You can remap the query parameters from `state` keys from
            // the default to those your server supports
            this.queryParams = Cache.get('shopProductsListRD') || {
                totalPages: null,
                totalRecords: null
            };
            this.queryParams.pageSize = "limit";
            this.queryParams.sortKey = "sort";
        },

        setCustomQueryField: function (field, value) {
            this.queryParams['_f' + field] = value;
            Cache.set('shopProductsListRD', this.queryParams);
            return this;
        },

        getCustomQueryField: function (field) {
            return this.queryParams["_f" + field];
        },

        setCustomQueryParam: function (param, value) {
            this.queryParams['_p' + param] = value;
            Cache.set('shopProductsListRD', this.queryParams);
            return this;
        },

        getCustomQueryParam: function (param) {
            return this.queryParams["_p" + param];
        },

        removeCustomQueryField: function (field) {
            delete this.queryParams["_f" + field];
        },

        parseState: function (resp, queryParams, state, options) {
            var info = resp && resp.info || {},
                state = {
                    totalRecords: parseInt(info.total_entries || 0, 10),
                    currentPage: parseInt(info.page || 1, 10)
                };
            this.extras._category = resp && resp._category || null;
            return state;
        },

        parseRecords: function (resp, options) {
            return resp.items;
        }

    });

    return ListProducts;

});