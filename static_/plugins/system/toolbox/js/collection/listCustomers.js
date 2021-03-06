define([
    'backbone',
    'underscore',
    'plugins/system/toolbox/js/model/customer',
    'cachejs',
    'backbone-pageable'
], function (Backbone, _, ModelCustomer, Cache) {

    var ListCustomers = Backbone.PageableCollection.extend({

        model: ModelCustomer,

        url: APP.getApiLink('system', 'customers'),

        // Initial pagination states
        state: {
            pageSize: 30,
            order: 1
        },

        initialize: function () {
            this.extras = {};
            // You can remap the query parameters from `state` keys from
            // the default to those your server supports
            this.queryParams = Cache.get('systemCustomersListRD') || {
                totalPages: null,
                totalRecords: null
            };
            this.queryParams.pageSize = "limit";
            this.queryParams.sortKey = "sort";
        },

        setCustomQueryField: function (field, value) {
            this.queryParams['_f' + field] = value;
            Cache.set('systemCustomersListRD', this.queryParams);
            return this;
        },

        getCustomQueryField: function (field) {
            return this.queryParams["_f" + field];
        },

        setCustomQueryParam: function (param, value) {
            this.queryParams['_p' + param] = value;
            Cache.set('systemCustomersListRD', this.queryParams);
            return this;
        },

        getCustomQueryParam: function (param) {
            return this.queryParams["_p" + param];
        },

        removeCustomQueryField: function (field) {
            delete this.queryParams["_f" + field];
        },

        parseState: function (resp, queryParams, state, options) {
            // debugger
            var state = {
                totalRecords: 0,
                currentPage: 1
            };

            if (resp) {
                if (resp.info) {
                    state.totalRecords = parseInt(resp.info.total_entries, 10) || 0;
                    state.currentPage = parseInt(resp.info.page, 10) || 1;
                }
            }
            return state;
        },

        parseRecords: function (resp, options) {
            return resp.items;
        }

    });

    return ListCustomers;

});