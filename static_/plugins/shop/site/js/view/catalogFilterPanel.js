define([
    'underscore',
    'backbone',
    'handlebars',
    'utils',
    'plugins/shop/site/js/collection/listProductCatalog',
    'bootstrap-dialog',
    'formatter-price',
    'text!plugins/shop/site/hbs/catalogFilterPanel.hbs',
    /* lang */
    'i18n!plugins/shop/site/nls/translation',
    'bootstrap',
    'bootstrap-select',
    'bootstrap-slider',
    'jquery.cookie'
], function (_, Backbone, Handlebars, Utils, CollListProductCatalog, BootstrapDialog, priceFmt, tpl, lang) {

    var ListProductCatalog = Backbone.View.extend({
        className: 'shop-catalog-filter',
        template: Handlebars.compile(tpl), // check
        lang: lang,
        events: {
            "change .selectpicker": 'filterProducts_Dropdowns',
            "change input[name^='filter_']": 'filterProducts_InputChecked',
            'click .shop-catalog-filter-widget.subcategories a.list-group-item': 'resetFilter',
            "click a.list-group-item:not(.disabled)": 'filterProducts_ListItemClicked',
            "slideStop input.slider": 'filterProducts_PriceChanged',
            "click .shop-filter-cancel": 'filterProducts_CancelFilter'
        },
        getPathInCatalog: function () {
            return this.collection._location;
        },
        getDisplayName: function () {
            return this.collection.category.Name;
        },
        getCatalogUrl: function () {
            var externalKey = this.collection.category.ExternalKey,
                pageNo = this.collection.getFilter('filter_viewPageNum');
            return CatalogBrowse.plugin.getCatalogUrl(externalKey, pageNo);
        },
        resetFilter: function () {
            console.log('resetFilter');
            this.collection.resetFilter();
            return true;
        },
        initialize: function (options) {
            this.collection = CollListProductCatalog.getInstance();
            _.bindAll(this, 'switchCurrency');
            // this.collection.setCategoryID(options.categoryID);
            this.listenTo(this.collection, 'sync', this.render);
            // this.collection.on('sync', this.render, this);
            // this.collection.on('reset', this.render, this);
            Backbone.on('changed:plugin-shop-currency', this.switchCurrency);
        },
        switchCurrency: function () {
            if (this.filterPrice && this.filterPrice.slider) {
                this.filterPrice.slider('refresh');
            }
        },
        render: function () {
            console.log('viewCatalogFilterPanelTmp render');
            if (this.collection.isEmpty()) {
                return this;
            }
            var that = this,
                data = Utils.getHBSTemplateData(this);
            data.filter = this.collection.filter;
            // unset current category in categories
            data.filter.filterOptionsAvailable.filter_categorySubCategories =
                _(data.filter.filterOptionsAvailable.filter_categorySubCategories).omit(this.collection.filter.info.category.ExternalKey);
            // data.filter.active = this.collection.isFilterApplied();

            this.$el.html(this.template(data));

            // enhance ui components
            this.filterPrice = this.$('.slider').slider({
                min: data.filter.filterOptionsAvailable.filter_commonPriceMin,
                max: data.filter.filterOptionsAvailable.filter_commonPriceMax,
                step: 1,
                selection: 'after',
                value: [data.filter.filterOptionsApplied.filter_commonPriceMin, data.filter.filterOptionsApplied.filter_commonPriceMax],
                formatter: function (val) {
                    // debugger
                    if (val instanceof Array) {
                        var activeCurr = APP.instances.shop.settings._user.activeCurrency;
                        var rate = APP.instances.shop.settings.CUSTOM.currencyList[activeCurr];
                        var leftEdge = val[0].toFixed(2) * rate.fromBaseToThis;
                        var rightEdge = val[1].toFixed(2) * rate.fromBaseToThis;
                        var leftFormattedValue = priceFmt(leftEdge, activeCurr, APP.instances.shop.settings.EXCHANAGERATESDISPLAY);
                        var rightFormattedValue = priceFmt(rightEdge, activeCurr, APP.instances.shop.settings.EXCHANAGERATESDISPLAY);
                        // debugger
                        var tooltip = [leftFormattedValue, rightFormattedValue];
                        return tooltip.join(':');
                    }
                    return val;
                }
            });
            var _filterDropdowns = this.$('.selectpicker').selectpicker();

            // this.delegateEvents();
            this.trigger('render:complete');
            return this;
        },
        getPageAttributes: function () {
            // seo start
            var formatTitle = "",
                formatKeywords = "",
                formatDescription = "";
            if (APP.instances.shop.settings.SEO.CategoryPageTitle) {
                formatTitle = APP.instances.shop.settings.SEO.CategoryPageTitle;
            }
            if (APP.instances.shop.settings.SEO.CategoryKeywords) {
                formatKeywords = APP.instances.shop.settings.SEO.CategoryKeywords;
            }
            if (APP.instances.shop.settings.SEO.CategoryDescription) {
                formatDescription = APP.instances.shop.settings.SEO.CategoryDescription;
            }

            var categoryFirst5Products = [];
            var catalogFirst5ProductNames = [], catalogFirst5OriginNames = [], catalogFirst5ProductModels = [];
            if (this.collection.length) {
                for (var i = 0, len = this.collection.length > 5 ? 5 : this.collection.length; i < len; i++) {
                    var productModel = this.collection.at(i);
                    catalogFirst5ProductNames.push(productModel.get('Name'));
                    catalogFirst5OriginNames.push(productModel.get('_origin')['Name']);
                    catalogFirst5ProductModels.push(productModel.get('Model'));
                }
            }

            // make them uniq
            catalogFirst5ProductNames = _(catalogFirst5ProductNames).uniq();
            catalogFirst5OriginNames = _(catalogFirst5OriginNames).uniq();
            catalogFirst5ProductModels = _(catalogFirst5ProductModels).uniq();

            var searchValues = ['\\[CatalogFirst5ProductNames\\]', '\\[CategoryName\\]', '\\[CatalogFirst5OriginNames\\]', '\\[CatalogFirst5ProductModels\\]'];
            var replaceValues = [catalogFirst5ProductNames.join(', '), this.collection.category.Name, catalogFirst5OriginNames.join(', '), catalogFirst5ProductModels.join(', ')];

            var title = APP.utils.replaceArray(formatTitle, searchValues, replaceValues);
            var keywords = APP.utils.replaceArray(formatKeywords, searchValues, replaceValues);
            var description = APP.utils.replaceArray(formatDescription, searchValues, replaceValues);

            return {title: title, keywords: keywords, description: description};
            // seo end
        },
        filterProducts_InputChecked: function (event) {
            // console.log('filterProducts_InputChecked');
            // event.preventDefault();
            // if (event && event.stopPropagation)
            //     event.stopPropagation();
            // debugger;
            var _targetFilterName = $(event.target).attr('name');

            var _filterOptions = {
                filter_viewPageNum: 0
            };

            _filterOptions[_targetFilterName] = this.collection.getFilter(_targetFilterName);
            // debugger;
            var idValuesHolders = ['filter_categorySubCategories', 'filter_commonFeatures', 'filter_categoryBrands'];
            var filterValue = $(event.target).val();
            if (_(idValuesHolders).indexOf(_targetFilterName) >= 0) {
                filterValue = parseInt(filterValue, 10);
            }

            // debugger;
            if ($(event.target).is(':checked'))
                _filterOptions[_targetFilterName].push(filterValue);
            else
                _filterOptions[_targetFilterName] = _.without(_filterOptions[_targetFilterName], filterValue);

            this.collection.setFilter('filter_viewPageNum', 0);
            this.collection.setFilter(_targetFilterName, _filterOptions[_targetFilterName]);

            this.collection.fetch();
            // return false;
        },
        filterProducts_Dropdowns: function (event) {
            // console.log(event);
            // debugger;
            var filter_viewSortBy = this.$('#shopProductListFiltering_SortByID').val();
            var filter_viewItemsOnPage = this.$('#shopProductListDisplayItems_DisplayCountID').val();

            this.collection.setFilter('filter_viewPageNum', 0);
            this.collection.setFilter('filter_viewSortBy', filter_viewSortBy);
            this.collection.setFilter('filter_viewItemsOnPage', filter_viewItemsOnPage);
            this.collection.fetch();
        },
        filterProducts_PriceChanged: function (event) {
            // console.log(event);
            // debugger;
            if (event && event.stopPropagation)
                event.stopPropagation();
            if (event && event.preventDefault)
                event.preventDefault();
            // debugger;
            var _priceRange = this.filterPrice.slider('getValue');// $(event.target).slider('getValue');

            var filter_commonPriceMin = _priceRange[0];
            var filter_commonPriceMax = _priceRange[1];

            this.collection.setFilter('filter_viewPageNum', 0);
            this.collection.setFilter('filter_commonPriceMin', filter_commonPriceMin);
            this.collection.setFilter('filter_commonPriceMax', filter_commonPriceMax);

            // this.$('.shop-filter-price-start').text(filter_commonPriceMin);
            // this.$('.shop-filter-price-end').text(filter_commonPriceMax);

            this.collection.fetch();
            return false;
        },
        filterProducts_CancelFilter: function () {
            // console.log('cancel filtering');
            this.collection.resetFilter().fetch({reset: true});
        },
        filterProducts_ListItemClicked: function (event) {
            // console.log('filterProducts_ListItemClicked');
            // debugger;
            var $el = $(event.target);
            if ($el.parents('a').attr('rel') === 'category' || $el.attr('rel') === 'category') {
                return;
            }
            if ($(event.target).is(':input')) {
                return;
            }
            var _innerCheckbox = null;
            if ($el.is(':checkbox')) {
                _innerCheckbox = $el;
            } else {
                _innerCheckbox = $el.find('input[type="checkbox"]');
            }
            // if ($el.parents('a').attr('rel') !== 'category' && $el.attr('rel') !== 'category') {
            if (event && event.stopPropagation)
                event.stopPropagation();
            if (event && event.preventDefault)
                event.preventDefault();
            //     return false;
            // }
            _innerCheckbox.prop('checked', !_innerCheckbox.prop('checked'));
            _innerCheckbox.trigger('change');
        },
        close: function () {
            this.unbind(); // Unbind all local event bindings
            this.collection.unbind('sync', this.render, this); // Unbind reference to the model
            this.remove(); // Remove view from DOM
            delete this.$el; // Delete the jQuery wrapped object variable
            delete this.el; // Delete the variable reference to this node
        }
    });

    return ListProductCatalog;

});