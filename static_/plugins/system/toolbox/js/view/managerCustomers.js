define([
    'backbone',
    'handlebars',
    'utils',
    'plugins/system/toolbox/js/view/listCustomers',
    'bootstrap-dialog',
    'bootstrap-alert',
    /* template */
    'text!plugins/system/toolbox/hbs/managerCustomers.hbs',
    /* lang */
    'i18n!plugins/system/toolbox/nls/translation'
], function (Backbone, Handlebars, Utils, ViewListCustomers, BootstrapDialog, BSAlert, tpl, lang) {

    var ManagerCustomers = Backbone.View.extend({
        template: Handlebars.compile(tpl), // check
        lang: lang,
        className: 'plugin-system-customers',
        events: {
            'click a.js-customer-remove': 'customerRemove',
            'click a.js-customer-restore': 'customerRestore'
        },
        initialize: function (options) {
            this.options = options || {};
            this.viewCustomerList = new ViewListCustomers();
            if (this.options.status) {
                this.viewCustomerList.collection.setCustomQueryField("Status", this.options.status.toUpperCase());
            }
            this.collection = this.viewCustomerList.collection;
            this.listenTo(this.collection, 'reset', this.render);
            this.listenTo(this.collection, 'sync', this.updateTabs);
        },
        updateTabs: function () {
            var status = this.collection.getCustomQueryField('Status') || 'active';
            this.$('.tab-link').removeClass('active');
            this.$('.tab-link.customers-' + status.toLowerCase()).addClass('active');
        },
        render: function () {
            // TODO:
            // add expired and todays orders
            if (this.$el.is(':empty')) {
                this.$el.html(this.template(Utils.getHBSTemplateData(this)));
                this.viewCustomerList.grid.emptyText = $('<h4>').text(lang.managers.customers.noData);
                this.viewCustomerList.render();
                // show sub-view
                this.$('.customer-list').html(this.viewCustomerList.$el);
            }
            return this;
        },
        customerRestore: function (event) {
            var that = this;
            BootstrapDialog.confirm("Do you want to restore customer?", function (rez) {
                if (rez) {
                    // debugger
                    var $item = $(event.target).parents('.js-customer-restore');
                    var id = parseInt($item.data('id'), 10);
                    var model = that.collection.get(id);
                    if (model && model.save) {
                        model.save({
                            Status: 'ACTIVE'
                        }, {
                            patch: true,
                            success: function () {
                                that.collection.fetch({
                                    reset: true
                                });
                            },
                            error: function () {
                                BSAlert.danger('Unable to comple action.');
                            }
                        });
                    }
                }
            });
        },
        customerRemove: function (event) {
            var that = this;
            BootstrapDialog.confirm("Do you want to remove customer?", function (rez) {
                if (rez) {
                    var $item = $(event.target).parents('.js-customer-remove');
                    var id = parseInt($item.data('id'), 10);
                    var model = that.collection.get(id);
                    if (model && model.destroy) {
                        model.destroy({
                            success: function () {
                                that.collection.fetch({
                                    reset: true
                                });
                            },
                            error: function () {
                                // data.instance.refresh();
                                BSAlert.danger('Unable to comple action.');
                            }
                        });
                    }
                }
            });
        }
    });

    return ManagerCustomers;

});