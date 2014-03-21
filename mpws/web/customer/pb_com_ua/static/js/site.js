define("customer/js/site", [
    'default/js/lib/sandbox',
    'cmn_jquery',
    'default/js/lib/underscore',
    'default/js/site',
    'default/js/view/breadcrumb',
    'default/js/plugin/css!customer/css/siteTheme.css'

], function (Sandbox, $, _, SiteBase, Breadcrumb) {

    var _customerOptions = {};

    _customerOptions.site = {
        title: 'Test and demo site',
        logoImageUrl: app.config.URL_STATIC_CUSTOMER + '/img/mikserLogo.png'
    };

    _customerOptions.placeholders = {
        CommonHeader: $('.MPWSPageHeader'),
        CommonHeaderLeft: $('.MPWSPageHeader .MPWSBlockLeft'),
        CommonHeaderCenter: $('.MPWSPageHeader .MPWSBlockCenter'),
        CommonHeaderRight: $('.MPWSPageHeader .MPWSBlockRight'),
        CommonBody: $('.MPWSPageBody'),
        CommonBodyLeft: $('.MPWSPageBody .MPWSBlockLeft'),
        CommonBodyCenter: $('.MPWSPageBody .MPWSBlockCenter'),
        CommonBodyRight: $('.MPWSPageBody .MPWSBlockRight'),
        CommonFooter: $('.MPWSPageFooter'),
        CommonFooterLeft: $('.MPWSPageFooter .MPWSBlockLeft'),
        CommonFooterCenter: $('.MPWSPageFooter .MPWSBlockCenter'),
        CommonFooterRight: $('.MPWSPageFooter .MPWSBlockRight'),
        CommonBreadcrumb: $('.MPWSBreadcrumb'),
        CommonMenuLeft: $('.MPWSPageHeader .MPWSBlockCenter .navbar-nav-main-left'),
        CommonMenuRight: $('.MPWSPageHeader .MPWSBlockCenter .navbar-nav-main-right'),
        CommonWidgetsTop: $('.MPWSWidgetsTop'),
        CommonWidgetsBottom: $('.MPWSWidgetsBottom'),
        /* plugins  */
        /* = plugin shop */
        ShopOffers: $('.MPWSPageBody .MPWSBlockCenter'),
        ShopListProductLatest: $('.MPWSPageBody .MPWSBlockCenter'),
        ShopListProductCatalog: $('.MPWSPageBody .MPWSBlockCenter'),
        ShopProductItemStandalone: $('.MPWSPageBody .MPWSBlockCenter'),
        ShopProductCompare: $('.MPWSPageBody .MPWSBlockCenter'),
        ShopCartStandalone: $('.MPWSPageBody .MPWSBlockCenter'),
        ShopWishList: $('.MPWSPageBody .MPWSBlockCenter'),
        ShopWidgetShoppingCartEmbedded: $('.MPWSWidgetsTop'),
        ShopWidgetOrderStatusButton: $('.MPWSWidgetsTop'),
        ShopOrdertrackingStandalone: $('.MPWSPageBody .MPWSBlockCenter'),
        /* = plugin account */
        AccountWidgetButtonAccount: $('.MPWSWidgetsTop'),
        AccountProfileCreate: $('.MPWSPageBody .MPWSBlockCenter'),
        AccountProfile: $('.MPWSPageBody .MPWSBlockCenter'),
        AccountProfileOverview: $('.MPWSPageBody .MPWSBlockCenter'),
        AccountProfileEdit: $('.MPWSPageBody .MPWSBlockCenter'),
        AccountProfilePassword: $('.MPWSPageBody .MPWSBlockCenter'),
        AccountProfileDelete: $('.MPWSPageBody .MPWSBlockCenter'),
    };

    var site = new SiteBase(_customerOptions);

    Sandbox.eventSubscribe('global:loader:complete', function (options) {

        // configure titles and brand images
        $('head title').text(_customerOptions.site.title);
        $('#site-logo-ID').attr({
            src: _customerOptions.site.logoImageUrl,
            title: _customerOptions.site.title
        });
        $('.navbar-brand').removeClass('hide');

        // init site views
        var _views = {};

        _views.breadcrumb = new Breadcrumb({
            el: _customerOptions.placeholders.CommonBreadcrumb,
            template: 'default/js/plugin/hbs!customer/hbs/site/breadcrumb'
        });

        Sandbox.eventSubscribe('global:breadcrumb:show', function (options) {
            _views.breadcrumb.fetchAndRender(options);
        });
    });

    // this object will be passed into all enabled plugins
    // to inject additional components into page layout
    return site;

});