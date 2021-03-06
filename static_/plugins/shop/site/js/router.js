define([
    'jquery',
    'underscore',
    'backbone',
    'cachejs',
    'auth',
    'handlebars',
    'plugins/shop/site/js/view/catalogFilterPanel',
    'plugins/shop/site/js/view/catalogBrowseContent',
    'plugins/shop/site/js/view/productItem',
    'plugins/shop/site/js/view/listProductCompare',
    'plugins/shop/site/js/view/cartStandalone',
    'plugins/shop/site/js/view/listProductWish',
    'plugins/shop/site/js/view/trackingStatus',
    'plugins/shop/site/js/view/listProducts',
    'plugins/shop/site/js/view/listUserOrders',

    'plugins/shop/site/js/view/menuCart',
    'plugins/shop/site/js/view/menuWishList',
    'plugins/shop/site/js/view/menuCompare',
    'plugins/shop/site/js/view/menuPayment',
    'plugins/shop/site/js/view/menuWarranty',
    'plugins/shop/site/js/view/menuShipping',
    'plugins/shop/site/js/view/menuOrderTracking',

    'plugins/shop/site/js/view/widgetAddress',
    'plugins/shop/site/js/view/widgetExchangeRates',
    'plugins/shop/site/js/view/widgetOrderTrackingButton',
    'plugins/shop/site/js/view/cartEmbedded',
    'plugins/shop/site/js/view/catalogNavigator',
    'plugins/shop/site/js/view/widgetPhonesList',

    'plugins/shop/site/js/collection/listProductCatalog',
    'plugins/shop/site/js/collection/listProductWish',
    'plugins/shop/site/js/collection/listProductCompare',

    'plugins/shop/site/js/model/order',
    'plugins/shop/site/js/model/catalogNavigator',
    'plugins/shop/common/js/model/setting'
], function ($, _, Backbone, Cache, Auth, Handlebars,
    
    // viewes
    ViewCatalogFilterPanel, ViewCatalogBrowseContent, ViewProductItem,
    ViewListProductCompare, ViewCartStandalone,
    ViewListProductWish, ViewTrackingStatus,
    ViewListProducts,
    ViewListUserOrders,

    // menu views
    ViewMenuItemCart,
    ViewMenuItemWishList,
    ViewMenuItemCompareList,
    ViewMenuItemPopupInfoPayment,
    ViewMenuItemPopupInfoWarranty,
    ViewMenuItemPopupInfoShipping,
    ViewMenuItemOrderTracking,

    // widgets
    ViewWidgetAddresses,
    ViewWidgetExchangeRates,
    ViewWidgetOrderTrackingButton,
    ViewWidgetCartEmbedded,
    ViewCatalogNavigator,
    ViewWidgetPhonesList,

    // collections
    CollectionCatalog,
    CollectionWishList,
    CollectionCompareList,

    ModelOrder, ModelCatalogNavigator, SiteSettings) {

    // permanent models and collections
    var modOrder = ModelOrder.getInstance({
            ID: 'temp'
        }),
        modCatalogNavigator = ModelCatalogNavigator.getInstance(),
        collCatalog = CollectionCatalog.getInstance(),
        collWithList = CollectionWishList.getInstance(),
        collCompareList = CollectionCompareList.getInstance();

    // why it's here?
    // order.url = APP.getApiLink({
    //     source: 'shop',
    //     fn: 'orders'
    // });

    var ShopPlugin = Backbone.View.extend({

        settings: null,

        // routes: routes,
        urls: {},

        catalogNavigator: modCatalogNavigator,
        order: modOrder,
        wishList: collWithList,
        compareList: collCompareList,

        getCatalogUrl: function (externalKey, pageNo) {
            var urlTemplate = null,
                urlOptions = {asRoot: true, category: externalKey};
            if (pageNo > 1) {
                urlTemplate = this.urls.shopCatalogCategoryPage;
                urlOptions.page = pageNo;
            } else {
                urlTemplate = this.urls.shopCatalogCategory;
            }
            return Handlebars.helpers.bb_link(urlTemplate, urlOptions);
        },

        initialize: function (options, callback) {
            this.pending = true;

            var that = this,
                settings = new SiteSettings();

            // attach plugin instance to views
            ViewProductItem.plugin = this;
            ViewCatalogFilterPanel.plugin = this;
            ViewCatalogBrowseContent.plugin = this;
            ViewWidgetExchangeRates.plugin = this;
            ViewWidgetAddresses.plugin = this;

            // configure plugin
            this.options = options || {};
            this.urls = options && options.urls || {};

            // fetch data
            modCatalogNavigator.fetch();
            modOrder.fetch();
            collCompareList.fetch();
            collWithList.fetch();
            settings.fetch().done(function () {
                that.settings = settings.toSettings();
                that.settings._user = {
                    activeCurrency: ViewWidgetExchangeRates.getActiveCurrencyName(
                        that.settings.MISC.SiteDefaultPriceCurrencyType && that.settings.MISC.SiteDefaultPriceCurrencyType,
                        !!that.settings.MISC.ShowSiteCurrencySelector),
                    activeAddress: ViewWidgetAddresses.getActiveAddress()
                }
                // console.log('shop settings ready: calling callback');
                callback();
                // console.log('finished loading shop');
                Backbone.on('changed:plugin-shop-currency', function (currencyName) {
                    that.settings._user.activeCurrency = currencyName;
                });
            });
            _.bindAll(this, 'setActiveAddress');
        },

        newProducts: function (options) {
            var listProductLatest = new ViewListProducts(_.extend({}, options, {type: 'new'}));
            listProductLatest.collection.fetch({
                reset: true
            });
            return listProductLatest;
        },

        viewedProducts: function (options) {
            var listProductViewed = new ViewListProducts(_.extend({}, options, {type: 'viewed'}));
            listProductViewed.collection.fetch({
                reset: true
            });
            return listProductViewed;
        },

        topProducts: function (options) {
            var listProductTop = new ViewListProducts(_.extend({}, options, {type: 'top'}));
            listProductTop.collection.fetch({
                reset: true
            });
            return listProductTop;
        },

        featuredProducts: function (options) {
            var listProductFeatured = new ViewListProducts(_.extend({}, options, {type: 'featured'}));
            listProductFeatured.collection.fetch({
                reset: true
            });
            return listProductFeatured;
        },

        hotOffers: function (options) {
            var listProductOffers = new ViewListProducts(_.extend({}, options, {type: 'offers'}));
            listProductOffers.collection.fetch({
                reset: true
            });
            return listProductOffers;
        },

        onSaleProducts: function (options) {
            var listProductOffers = new ViewListProducts(_.extend({}, options, {type: 'onsale'}));
            listProductOffers.collection.fetch({
                reset: true
            });
            return listProductOffers;
        },

        searchProducts: function (options) {
            var listProductSearch = new ViewListProducts(_.extend({}, options, {type: 'search'}));
            listProductSearch.collection.fetch({
                reset: true
            });
            return listProductSearch;
        },
        // menu items

        menuItemCart: function () {
            var menuCart = new ViewMenuItemCart();
            menuCart.render();
            return menuCart;
        },
        menuItemWishList: function () {
            var menuWishList = new ViewMenuItemWishList();
            menuWishList.render();
            return menuWishList;
        },
        menuItemCompareList: function () {
            var menuCompare = new ViewMenuItemCompareList();
            menuCompare.render();
            return menuCompare;
        },
        menuItemPopupInfoPayment: function () {
            var menuPayment = new ViewMenuItemPopupInfoPayment();
            menuPayment.render();
            return menuPayment;
        },
        menuItemPopupInfoWarranty: function () {
            var menuWarranty = new ViewMenuItemPopupInfoWarranty();
            menuWarranty.render();
            return menuWarranty;
        },
        menuItemPopupInfoShipping: function () {
            var menuShipping = new ViewMenuItemPopupInfoShipping();
            menuShipping.render();
            return menuShipping;
        },
        menuItemOrderTracking: function () {
            var menuTracking = new ViewMenuItemOrderTracking();
            menuTracking.render();
            return menuTracking;
        },

        // widgets
        widgetAddresses: function () {
            var addr = new ViewWidgetAddresses();
            if (this.settings && this.settings.ADDRESS) {
                addr.collection.set(this.settings.ADDRESS);
            } else {
                addr.collection.fetch({reset: true});
            }
            addr.render();
            return addr;
        },
        widgetExchangeRates: function () {
            var rates = new ViewWidgetExchangeRates();
            if (APP.instances.shop.settings.MISC.ShowSiteCurrencySelector) {
                rates.render();
            }
            return rates;
        },
        widgetTrackOrderButton: function () {
            // inject tracking order
            var orderTrackingButton = new ViewWidgetOrderTrackingButton();
            orderTrackingButton.render();
            return orderTrackingButton;
        },
        widgetCartButton: function () {
            // inject embedded shopping cart
            var cartEmbedded = new ViewWidgetCartEmbedded();
            cartEmbedded.render();
            return cartEmbedded;
        },
        widgetPhonesList: function () {
            // inject embedded shopping cart
            var phonesList = new ViewWidgetPhonesList();
            phonesList.render();
            return phonesList;
        },
        catalogNavigator: function (options) {
            // catalog navigation panel
            var cBar = new ViewCatalogNavigator(options || {});
            cBar.render();
            return cBar;
        },

        // pages
        catalogFilterPanel: function (categoryID, pageNo, manualFilters) {
            // create new view or get existent
            var that = this;
            this.viewCatalogFilterPanelTmp = this.viewCatalogFilterPanelTmp || new ViewCatalogFilterPanel();
            collCatalog.setCategoryID(categoryID);
            if (pageNo) {
                var _pageNo = parseInt(pageNo, 10);
                if (_pageNo.toString() === pageNo) {
                    this.viewCatalogFilterPanelTmp.collection.setFilter('filter_viewPageNum', pageNo);
                }
            }
            if (manualFilters) {
                _(manualFilters).each(function (filterValue, filterKey) {
                    that.viewCatalogFilterPanelTmp.collection.setFilter(filterKey, filterValue);
                });
            }
            collCatalog.fetch({
                reset: true
            });
            return this.viewCatalogFilterPanelTmp;
        },
        catalogBrowseContent: function (options) {
            // create new view
            var view = new ViewCatalogBrowseContent(options || {});
            return view;
        },

        product: function (productID) {
            // create new view
            var viewProductItem = new ViewProductItem({
                productID: productID,
                design: {
                    style: 'full'
                }
            });
            viewProductItem.model.fetch();
            return viewProductItem;
        },

        compare: function () {
            // create new view
            var listProductCompare = new ViewListProductCompare();
            listProductCompare.render();
            return listProductCompare;
        },

        cart: function () {
            // debugger;
            var plgAccount = APP.instances.account;
            var accountModel = null;
            if (plgAccount) {
                accountModel = plgAccount.constructor.account;
                accountModel.on('change', function () {
                    console.log('account model changed');
                    if (accountModel.has('ID'))
                        modOrder.set('account', accountModel.toJSON());
                    else
                        modOrder.unset('account');
                });
            }
            // var accountModel = Cache.getObject('account:model');
            // APP.Sandbox.eventSubscribe('plugin:account:model:change', function (accountModel) {
            //     // debugger;
            //     if (accountModel.has('ID'))
            //         modOrder.set('account', accountModel.toJSON());
            //     else
            //         modOrder.unset('account');
            // });
            if (accountModel && accountModel.has('ID')) {
                console.log('account model has data');
                modOrder.set('account', accountModel.toJSON());
            }
            // create new view
            var cartStandalone = new ViewCartStandalone();
            // cartStandalone.collection.fetch({merge:true});
            cartStandalone.render();
            return cartStandalone;
        },

        wishlist: function () {
            // create new view
            var listProductWish = new ViewListProductWish();
            listProductWish.render();
            return listProductWish;
        },

        tracking: function (orderHash) {
            // create new view
            var trackingStatus = new ViewTrackingStatus();
            if (orderHash) {
                trackingStatus.setOrderHash(orderHash);
            }
            return trackingStatus;
        },

        userOrders: function () {
            // create new view
            var listUserOrders = new ViewListUserOrders();
            return listUserOrders;
        },

        // utils 
        setActiveAddress: function (addr) {
            this.settings._activeAddress = addr;
        }
        //
        // shop_profile_orders: function () {
        //     APP.getCustomer().setBreadcrumb();
        //     if (!Site.hasPlugin('account') || !Auth.getUserID()) {
        //         Backbone.history.navigate("", true);
        //         return;
        //     }
            // re quire([''], function () {
        //         // Cache.withObject('ProfileOrders', function (cachedView) {
        //         // debugger;
        //         // remove previous view
        //         // if (cachedView && cachedView.remove)
        //         //     cachedView.remove();

        //         // create new view
        //         var profileOrders = new ProfileOrders();
        //         // view.setPagePlaceholder(profileOrders.$el);
        //         profileOrders.fetchAndRender({
        //             profileID: Cache.getObject('AccountProfileID')
        //         });

        //         APP.Sandbox.eventNotify('plugin:account:profile:show', profileOrders.$el);

        //         // return view object to pass it into this function at next invocation
        //         // return profileOrders;
        //         // });
        //     });
        // }

    });

    return ShopPlugin;
});