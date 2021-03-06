define([
    'underscore',
    'backbone',
    'handlebars',
    'utils',
    'cachejs',
    'plugins/shop/toolbox/js/view/managerProducts',
    'plugins/shop/toolbox/js/view/filterPanelOrigins',
    'plugins/shop/toolbox/js/view/filterTreeCategories',
    /* template */
    'text!plugins/shop/toolbox/hbs/managerContent.hbs',
    /* lang */
    'i18n!plugins/shop/toolbox/nls/translation',
    'toastr'
], function (_, Backbone, Handlebars, Utils, Cache, ViewListProducts, ViewListOrigins, ViewCategoriesTree, tpl, lang, toastr) {

    var ManagerOrders = Backbone.View.extend({
        template: Handlebars.compile(tpl), // check
        lang: lang,
        className: 'plugin-shop-content',
        initialize: function (options) {
            // set options
            // ini sub-views
            // debugger;
            var productListOptions = _.extend({}, options, {
                adjustColumns: function (columns) {
                    return _(columns).omit(
                       'columnID', 'columnDateUpdated', 'columnDateCreated',
                       'columnSKU');
                }
            });

            var that = this;

            this.viewProductsList = new ViewListProducts(productListOptions);
            this.viewOriginsList = new ViewListOrigins(options);
            this.viewCatergoriesTree = new ViewCategoriesTree(options);

            // subscribe on events
            this.listenTo(this.viewProductsList.collection, 'reset', this.render);

            this.viewCatergoriesTree.on('categoryTree:changed:category', _.debounce(function (activeCategory) {
                if (activeCategory.id < 0) {
                    // show all categories
                    this.viewProductsList.collection.setCustomQueryField("CategoryID", void(0));
                } else {
                    this.viewProductsList.collection.setCustomQueryField("CategoryID", activeCategory.allIDs.join(',') + ':IN');
                }
                this.viewProductsList.collection.fetch({
                    reset: true
                });
            }, 200), this);

            this.viewOriginsList.on('originTree:changed:origin', _.debounce(function (activeOrigins) {
                if (activeOrigins.length) {
                    this.viewProductsList.collection.setCustomQueryField("OriginID", activeOrigins.join(',') + ':IN');
                } else {
                    // skip filter by origins
                    this.viewProductsList.collection.setCustomQueryField("OriginID", void(0));
                }
                this.viewProductsList.collection.fetch({
                    reset: true
                });
            }, 200), this);

            this.viewCatergoriesTree.on('productCategoryChanged', function (eventData) {
                if (eventData.productID < 0 || eventData.categoryID < 0) {
                    return;
                }
                var productModel = that.viewProductsList.collection.get(eventData.productID);
                productModel.setCategoryID(eventData.categoryID).done(function () {
                    toastr.success('Успішно');
                    that.viewProductsList.collection.fetch({
                        reset: true
                    });
                });
            });

            _.bindAll(this, 'saveLayout');

            var that = this;
            Backbone.history.on('route', function() {
                if (that.interval_saveLayout) {
                    // debugger
                    clearInterval(that.interval_saveLayout);
                }
            });
        },
        saveLayout: function () {
            // console.log('saving layout manager content');
            Cache.set("shopManagerContentLayoutRD", {
                activeFilterTabID: this.$('.plugin-shop-content-filters li.active a').attr('href').substr(1)
            });
        },
        restoreLayout: function () {
            // debugger;
            var layoutConfig = Cache.get("shopManagerContentLayoutRD");
            layoutConfig = _.defaults({}, layoutConfig || {}, {
                activeFilterTabID: 'tree'
            });
            this.$('.nav > li').removeClass('active');
            this.$('.nav a[href="#' + layoutConfig.activeFilterTabID + '"]').parent().addClass('active');
            this.$('.tab-pane.' + layoutConfig.activeFilterTabID).addClass('in active');
            this.interval_saveLayout = setInterval(this.saveLayout, 800);
        },
        render: function () {
            // TODO:
            // add expired and todays products
            // permanent layout and some elements
            if (this.$el.is(':empty')) {
                this.$el.html(this.template(Utils.getHBSTemplateData(this)));
                this.viewProductsList.grid.emptyText = $('<h4>').text(lang.managerContent.products.nodata);
                this.$('.tree').html(this.viewCatergoriesTree.$el);
                this.$('.products').html(this.viewProductsList.$el);
                this.$('.origins').html(this.viewOriginsList.$el);
                this.$('.plugin-shop-content-filters').tab();
            }
            this.restoreLayout();
            return this;
        }
    });

    return ManagerOrders;

});