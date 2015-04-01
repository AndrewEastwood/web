define([
    'jquery',
    'underscore',
    'backbone',
    'handlebars',
    'echo',
    // page templates
    'text!./../hbs/breadcrumb.hbs',
    'text!./../hbs/homeFrame.hbs',
    'text!./../hbs/productsTab.hbs',
    'text!./../hbs/viewedProducts.hbs',
    'text!./../hbs/page404.hbs',
    'text!./../hbs/categoriesRibbon.hbs',
    'text!./../hbs/productComparisons.hbs',
    'text!./../hbs/productWishlist.hbs',
    'owl.carousel',
    'bootstrap'
], function ($, _, Backbone, Handlebars, echo,
     tplBreadcrumb,
     tplHomeFrame,
     tplProductsTab,
     tplViewedProducts,
     tplPage404,
     tplCategoriesRibbon,
     tplProductComparisons,
     tplProductWishlist) {

    // var _customerOptions = {};


    // function CustomerClass () {}

    // CustomerClass.prototype.renderProxy = function () {
    //     // console.log('customer renderProxy');
    //     // console.log(arguments);
    //     return true;
    // }
    var shopRoutes = {
        // '!/': 'home',
        '!/catalog/:category': 'shopCatalogCategory',
        '!/catalog/:category/:page': 'shopCatalogCategoryPage',
        '!/catalog/': 'shopCatalog',
        '!/product/:product': 'shopProduct',
        '!/cart': 'shopCart',
        '!/wishlist': 'shopWishlist',
        '!/compare': 'shopCompare',
        '!/tracking/(:id)': 'shopTracking'
        // "!/shop/profile/orders": "shop_profile_orders"
    };

    APP.configurePlugins({
        shop: {
            urls: _(shopRoutes).invert(),
            productShortClassNames: 'no-margin product-item-holder hover'
        }
    });


    var templatesCompiled = {
        homeFrame: $(Handlebars.compile(tplHomeFrame)()),
        categoriesRibbon: $(Handlebars.compile(tplCategoriesRibbon)()),
        breadcrumb: $(Handlebars.compile(tplBreadcrumb)()),
        viewedProducts: $(Handlebars.compile(tplViewedProducts)()),
        productComparisons: $(Handlebars.compile(tplProductComparisons)()),
        productsTab: $(Handlebars.compile(tplProductsTab)()),
        productWishlist: $(Handlebars.compile(tplProductWishlist)()),
        page404: $(Handlebars.compile(tplPage404)())
    };

    function getTemplate (key) {
        return function () {
            return templatesCompiled[key] && templatesCompiled[key].clone();
        }
    }

    var Router = Backbone.Router.extend({

        name: 'pb.com.ua',

        settings: {
            title: APP.config.TITLE,
            logoImageUrl: APP.config.URL_PUBLIC_LOGO
        },

        routes: _.extend.apply(_, [
            {
                '': 'home',
                '!': 'home',
                '!/': 'home',
                ':whatever': 'page404'
            },
            shopRoutes
        ]),

        plugins: {},

        elements: {},

        templates: {
            homeFrame: getTemplate('homeFrame'),
            categoriesRibbon: getTemplate('categoriesRibbon'),
            breadcrumb: getTemplate('breadcrumb'),
            viewedProducts: getTemplate('viewedProducts'),
            productsTab: getTemplate('productsTab'),
            productComparisons: getTemplate('productComparisons'),
            productWishlist: getTemplate('productWishlist'),
            page404: getTemplate('page404'),
        },

        views: {},

        getPlugin: function (name) {
            return this.plugins[name] || null;
        },

        setPlugin: function (plugin) {
            if (!plugin || !plugin.name)
                throw 'wrong plugin object. plugin is ' + (typeof plugin);
            this.plugins[plugin.name] = plugin;
        },
        initialize: function () {

            var that = this;

            this.on('app:ready', function () {

                that.setPlugin(APP.getPlugin('shop'));

                // menu items
                $('.mpws-js-menu-cart').html(that.plugins.shop.menuItemCart().$el);
                $('.mpws-js-menu-payment').html(that.plugins.shop.menuItemPopupInfoPayment().$el);
                $('.mpws-js-menu-warranty').html(that.plugins.shop.menuItemPopupInfoWarranty().$el);
                $('.mpws-js-menu-shipping').html(that.plugins.shop.menuItemPopupInfoShipping().$el);
                $('.mpws-js-menu-compare').html(that.plugins.shop.menuItemCompareList().$el);
                $('.mpws-js-menu-wishlist').html(that.plugins.shop.menuItemWishList().$el);

                // widgets
                $('.mpws-js-shop-addresses').html(that.plugins.shop.widgetAddresses().$el);
                $('.mpws-js-cart-embedded').html(that.plugins.shop.widgetCartButton().$el);
                $('.mpws-js-top-nav-right').html($('<li>').addClass('dropdown').html(that.plugins.shop.widgetExchangeRates().$el));

                // common elements


                // setup ribbon nav and breadcrumb
                var $tplBreadcrumb = that.templates.breadcrumb(),
                    $tplCategoriesRibbon = that.templates.categoriesRibbon(),
                    optionsCategoryMenu = {design: {className: 'nav navbar-nav'}},
                    optionsTopLevelList = {design: {style: 'toplevel', className: 'dropdown-menu'}};

                that.views.categoryHomeMenu = that.plugins.shop.categoryNavigator(optionsCategoryMenu);
                that.views.categoryRibbonMenu = that.plugins.shop.categoryNavigator(optionsCategoryMenu);
                that.views.categorySearchTopLevelList = that.plugins.shop.categoryNavigator(optionsTopLevelList);
                that.views.categoryBreadcrumbTopLevelList = that.plugins.shop.categoryNavigator(optionsTopLevelList);

                $tplBreadcrumb.find('li.mpws-js-shop-categories-toplist').append(that.views.categoryBreadcrumbTopLevelList.render().$el);
                $tplCategoriesRibbon.find('.mpws-js-catalog-tree').html(that.views.categoryRibbonMenu.render().$el);
                
                $('.mpws-js-breadcrumb').html($tplBreadcrumb);
                $('.mpws-js-shop-categories-ribbon').html($tplCategoriesRibbon);
                    // categoryListOptions = {design: {className: 'nav'}},
                    // categoryMenu = that.plugins.shop.categoryNavigator(categoryListOptions)
                    // categoryTopLevelList = that.plugins.shop.categoryNavigator({design: {style: 'toplevel', className: 'dropdown-menu'}})
                // set top category list with banner
                
                // setup home fame
                var $tplHomeFrame = that.templates.homeFrame();
                $tplHomeFrame.find('.mpws-js-catalog-tree').html(that.views.categoryHomeMenu.render().$el);
                $('.mpws-js-main-home-frame').html($tplHomeFrame);

                // update searchbox categories
                $('header li.mpws-js-shop-categories-toplist').append(that.views.categorySearchTopLevelList.$el);

                // setup recently viewed products
                var $tplViewedProducts = that.templates.viewedProducts(),
                    optionsViewedProducts = {design: {className: 'no-margin item carousel-item product-item-holder size-small hover'}};
                that.views.viewedProducts = that.plugins.shop.viewedProducts(optionsViewedProducts);

                $('div.mpws-js-shop-viewed-products', $tplViewedProducts).html(that.views.viewedProducts.$el);
                $('section.mpws-js-shop-viewed-products').html($tplViewedProducts);
                that.views.viewedProducts.$el.addClass('owl-carousel');

                // $owlEl.html($tplViewedProducts);
                var owl = that.views.viewedProducts.$el.data('owlCarousel');
                that.views.viewedProducts.on('shop:rendered', function () {
                    // console.log('viewed rendering');
                    // $tplViewedProducts.removeClass('hidden');
                    // debugger
                    // $owlEl.html(that.views.viewedProducts.$el);
                    // console.log('items are added');
                    // init some js
                    _.delay(function () {
                        initEchoJS();
                        // debugger
                        // owl.update();
                        // owl.destroy();
                        that.views.viewedProducts.$el.owlCarousel({
                            stopOnHover: true,
                            rewindNav: false,
                            items: 6,
                            pagination: false,
                            loop: false,
                            itemsTablet: [768, 3],
                            responsive:{
                                0:{
                                    items:1
                                },
                                600:{
                                    items:3
                                },
                                1000:{
                                    items:6
                                }
                            }
                        });
                        // $owlEl.trigger('destroy.owl.carousel');
                    }, 1000);
                });
                // that.views.viewedProducts.on('shop:emptylist', function () {
                //     $tplViewedProducts.addClass('hidden');
                // });
                $(".slider-next", $tplViewedProducts).click(function () {
                    that.views.viewedProducts.$el.trigger('next.owl.carousel', [1500]);
                });
                $(".slider-prev", $tplViewedProducts).click(function () {
                    that.views.viewedProducts.$el.trigger('prev.owl.carousel', [1500]);
                });
            });
        },
        refreshViewedProducts: function () {
            this.views.viewedProducts.collection.fetch({reset: true});
        },
        updateBreadcrumb: function (items) {
            $('.mpws-js-breadcrumb ul.mpws-js-breadcrumb-list > li:not(.locked)').remove();
            if (_.isString(items)) {
                items = [[items, null]];
            }
            _(items).each(function (item) {
                if (!item || !item[0]) {
                    return;
                }
                var text = item[0] || null,
                    url = item[1] || null,
                    $bcItem = $('<li>')
                    .addClass('breadcrumb-item'),
                    $bcLink = $('<a>').attr('href', url || 'javascript://').text(text);
                $bcItem.html($bcLink);
                if (item[2]) {
                    var $subMenu = $(item[2]);
                    if ($subMenu.is('ul')) {
                        $subMenu.addClass('dropdown-menu');
                        $bcItem.append($subMenu);
                        $bcLink.attr({
                            'class': 'dropdown-toggle',
                            'data-toggle': 'dropdown',
                            'aria-expanded': 'true'
                        });
                        $bcItem.addClass('dropdown');
                    }
                }
                $('.mpws-js-breadcrumb ul.mpws-js-breadcrumb-list').append($bcItem);
            });
            $('.mpws-js-breadcrumb ul.mpws-js-breadcrumb-list > li:last').addClass('current');
        },
        home: function () {
            this.toggleCategoryRibbonAndBreadcrumb(false);
            this.toggleHomeFrame(true);
            this.refreshViewedProducts();
            this.updateFooter();


            var $tplProductsTab = this.templates.productsTab(),
                productOptions = {design: {className: 'col-sm-4 col-md-3 no-margin product-item-holder hover', pageSize: 4}},
                featuredProducts = this.plugins.shop.featuredProducts(productOptions),
                newProducts = this.plugins.shop.newProducts(productOptions),
                topProducts = this.plugins.shop.topProducts(productOptions);

            // show more buttons selectors
            var $btnShowMoreFeatured = $tplProductsTab.find('#mpws-js-shop-tab-products-featured .btn-loadmore'),
                $btnShowMoreNew = $tplProductsTab.find('#mpws-js-shop-tab-products-new .btn-loadmore'),
                $btnShowMoreTop = $tplProductsTab.find('#mpws-js-shop-tab-products-top .btn-loadmore');
            function hideShowMoreButton ($btn) {
                return function () {
                    $($btn).addClass('hidden');
                }
            }
            function displayShowMoreButton ($btn) {
                return function () {
                    $($btn).removeClass('hidden');
                }
            }

            // display show more buttons
            displayShowMoreButton($btnShowMoreFeatured);
            displayShowMoreButton($btnShowMoreNew);
            displayShowMoreButton($btnShowMoreTop);

            // init image loading
            featuredProducts.on('shop:rendered', initEchoJS);
            newProducts.on('shop:rendered', initEchoJS);
            topProducts.on('shop:rendered', initEchoJS);

            // hide showmore button when all products are visible
            featuredProducts.on('shop:allvisible', hideShowMoreButton($btnShowMoreFeatured));
            newProducts.on('shop:allvisible', hideShowMoreButton($btnShowMoreNew));
            topProducts.on('shop:allvisible', hideShowMoreButton($btnShowMoreTop));

            // hide showmore button when no products
            featuredProducts.on('shop:emptylist', hideShowMoreButton($btnShowMoreFeatured));
            newProducts.on('shop:emptylist', hideShowMoreButton($btnShowMoreNew));
            topProducts.on('shop:emptylist', hideShowMoreButton($btnShowMoreTop));

            // append products tab
            $tplProductsTab.find('.mpws-js-shop-products-featured').html(featuredProducts.$el);
            $tplProductsTab.find('.mpws-js-shop-products-new').html(newProducts.$el);
            $tplProductsTab.find('.mpws-js-shop-products-top').html(topProducts.$el);
            $tplProductsTab.on('click', '#mpws-js-shop-tab-products-featured .btn-loadmore', featuredProducts.revealNextPage);
            $tplProductsTab.on('click', '#mpws-js-shop-tab-products-new .btn-loadmore', newProducts.revealNextPage);
            $tplProductsTab.on('click', '#mpws-js-shop-tab-products-top .btn-loadmore', topProducts.revealNextPage);

            $('section.mpws-js-main-section').html($tplProductsTab);
        },
        shopCart: function () {
            this.updateBreadcrumb('Кошик');
            this.toggleCategoryRibbonAndBreadcrumb(true);
            this.toggleHomeFrame(false);
            this.refreshViewedProducts();
            this.updateFooter();

            $('section.mpws-js-main-section').html(this.plugins.shop.cart().$el);

        },
        shopProduct: function (id) {
            var that = this;
            this.toggleCategoryRibbonAndBreadcrumb(true);
            this.toggleHomeFrame(false);
            this.refreshViewedProducts();
            this.updateFooter();

            var productView = this.plugins.shop.product(id);

            productView.on('render:complete', function () {
                var brItems = [],
                    productLocationPath = productView.getPathInCatalog();
                _(productLocationPath).each(function (locItem) {
                    var pathCategorySubList = that.plugins.shop.categoryNavigator({design: {style: 'toplevel', parentID: locItem.ID}}),
                        subList = pathCategorySubList.hasSubCategories(locItem.ID) && pathCategorySubList.render().$el;
                    brItems.push([locItem.Name, locItem.url, subList]);
                });
                brItems.push([productView.getDisplayName(), productView.getProductUrl()]);
                that.updateBreadcrumb(brItems);
            });

            $('section.mpws-js-main-section').html(productView.$el);

        },
        shopWishlist: function () {
            this.updateBreadcrumb('Мій список');
            this.toggleCategoryRibbonAndBreadcrumb(true);
            this.toggleHomeFrame(false);
            this.refreshViewedProducts();
            this.updateFooter();

            $('section.mpws-js-main-section').html(this.templates.productWishlist());
            // $('section.mpws-js-main-section').html(this.plugins.shop.wishlist().$el);
            $('section.mpws-js-main-section').find('.mpws-js-products-wishlist').html(this.plugins.shop.wishlist().$el);

        },
        shopCompare: function () {
            this.updateBreadcrumb('Порівняння');
            this.toggleCategoryRibbonAndBreadcrumb(true);
            this.toggleHomeFrame(false);
            this.refreshViewedProducts();
            this.updateFooter();

            $('section.mpws-js-main-section').html(this.templates.productComparisons());
            $('section.mpws-js-main-section').find('.mpws-js-product-comparisons').html(this.plugins.shop.compare().$el);

        },
        page404: function () {
            this.toggleCategoryRibbonAndBreadcrumb(true);
            this.toggleHomeFrame(false);
            this.refreshViewedProducts();
            this.updateFooter();

            //     $tplCategoriesRibbon = $(Handlebars.compile(tplCategoriesRibbon)()),
            //     categoryOptions = {design: {className: 'nav navbar-nav'}},
            //     categoryMenu = this.plugins.shop.categoryNavigator(categoryOptions);
            // $tplCategoriesRibbon.find('.mpws-js-catalog-tree').html(categoryMenu.render().$el);

            // $('.mpws-js-shop-categories-topnav').html($tplCategoriesRibbon);
            $('section.mpws-js-main-section').html(this.templates.page404());

        },

        // utils

        updateFooter: function () {
            // adding footer
            var $tplFooter = $('footer.mpws-js-main-footer'),
                productOptions = {limit: 5, design: {asList: true, style: 'minimal', wrap: '<div class="row"></div>'}},
                newProducts = this.plugins.shop.newProducts(productOptions),
                onSaleProducts = this.plugins.shop.onSaleProducts(productOptions),
                topProducts = this.plugins.shop.topProducts(productOptions),
                categoryTopLevelList = this.plugins.shop.categoryNavigator({design: {style: 'toplevel'}});

            $tplFooter.find('.mpws-js-shop-new-products-minimal-list').html(newProducts.$el);
            $tplFooter.find('.mpws-js-shop-onsale-products-minimal-list').html(onSaleProducts.$el);
            $tplFooter.find('.mpws-js-shop-top-products-minimal-list').html(topProducts.$el);
            $tplFooter.find('.mpws-js-shop-categories-toplist').html(categoryTopLevelList.$el);
        },
        toggleCategoryRibbonAndBreadcrumb: function (show) {
            $('.mpws-js-breadcrumb').toggleClass('hidden', !show);
            $('.mpws-js-shop-categories-ribbon').toggleClass('hidden', !show);
        },
        toggleHomeFrame: function (show) {
            $('.mpws-js-main-home-frame').toggleClass('hidden', !show);
        },

    });


    function initEchoJS () {
        echo.init({
            offset: 100,
            throttle: 250,
            callback: function(element, op) {
                if(op === 'load') {
                    element.classList.add('loaded');
                } else {
                    element.classList.remove('loaded');
                }
            }
        });
    }

    return Router;


    // CustomerClass.prototype.setBreadcrumb = function (options) {
        // breadcrumb.render(options);
        // APP.Sandbox.eventNotify('global:content:render', {
        //     name: 'CommonBreadcrumbTop',
        //     el: breadcrumb.$el.clone()
        // });
        // APP.Sandbox.eventNotify('global:content:render', {
        //     name: 'CommonBreadcrumbBottom',
        //     el: breadcrumb.$el.clone()
        // });
    // }

    // return CustomerClass;

});