define("plugin/toolbox/js/toolbox", [
    'default/js/lib/sandbox',
    'customer/js/site',
    'cmn_jquery',
    'default/js/lib/underscore',
    'default/js/lib/backbone',
    'default/js/lib/cache',
    // 'plugin/toolbox/js/view/bridge'
], function (Sandbox, Site, $, _, Backbone, Cache) {

    // var bridge = new ViewBridge();

    var Router = Backbone.Router.extend({
        routes: {
            "signin": "signin",
            "signout": "signout",
        },

        initialize: function () {
            var self = this;

            Sandbox.eventSubscribe('plugin:toolbox:status:received', function (status) {
                debugger;
                // Sandbox.eventNotify('global:breadcrumb:show');
                if (!status && Backbone.history.fragment !== "signin")
                    Backbone.history.navigate('signin', true);
            });
            Sandbox.eventSubscribe('plugin:toolbox:signed:out', function (status) {
                Backbone.history.navigate('signin', true);
            });
            // Sandbox.eventSubscribe('toolbox:page:login', function () {
            //     self.login();
            // });
            Sandbox.eventSubscribe('global:route', function (hash) {
                // debugger;
            });

            Sandbox.eventSubscribe('global:loader:complete', function (hash) {
                // debugger;
            });

            Sandbox.eventSubscribe('global:page:index', function () {
                // debugger;
                // Sandbox.eventNotify('global:breadcrumb:show');
            });

            Sandbox.eventSubscribe('plugin:toolbox:page:show', function (page) {
                // debugger;
                // Sandbox.eventNotify('global:breadcrumb:show');
                // debugger;
                self.showToolboxPage(page);
            });


            // Sandbox.eventSubscribe('global:page:show', function (page) {
            //     debugger;
            //     // Sandbox.eventNotify('toolbox:breadcrumb:show');
            //     self.showToolboxPage(page);
            // });
        },

        // dashboard: function () {
            
        // },

        signin: function () {
            debugger;
            var self = this;
            require(['plugin/toolbox/js/view/signin'], function (SignIn) {
                // using this wrapper to cleanup previous view and create new one
                Cache.withObject('SignIn', function (cachedView) {
                    debugger;
                    // remove previous view
                    if (cachedView && cachedView.remove)
                        cachedView.remove();

                    // create new view
                    var signin = new SignIn();
                    signin.render();
                    Sandbox.eventNotify('global:content:render', {
                        name: 'SignIn',
                        el: signin.el
                    });

                    // return view object to pass it into this function at next invocation
                    return signin;
                });
            });
        },

        signout: function () {
            var self = this;
            require(['plugin/toolbox/js/view/signout'], function (SignOut) {
                // using this wrapper to cleanup previous view and create new one
                Cache.withObject('SignOut', function (cachedView) {
                    // debugger;
                    // remove previous view
                    if (cachedView && cachedView.remove)
                        cachedView.remove();

                    // create new view
                    var signout = new SignOut();
                    signout.render();
                    // return view object to pass it into this function at next invocation
                    return signout;
                });
            });
        },

        showToolboxPage: function (pageContent) {
            require(['plugin/toolbox/js/view/bridge'], function (Bridge) {
                // using this wrapper to cleanup previous view and create new one
                Cache.withObject('Bridge', function (cachedView) {
                    // debugger;
                    // remove previous view
                    if (cachedView && cachedView.remove)
                        cachedView.remove();

                    // create new view
                    var bridge = new Bridge();
                    // Site.placeholders.account.pageProfile.html(bridge.el);
                    bridge.on('mview:renderComplete', function () {
                        bridge.setPagePlaceholder(pageContent);
                    });
                    bridge.render();
                    Sandbox.eventNotify('global:content:render', {
                        name: 'Bridge',
                        el: bridge.el
                    });

                    // return view object to pass it into this function at next invocation
                    return bridge;
                });
            });
        }

    });

    return Router;
});