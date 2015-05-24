define([
    'jquery',
    'underscore',
    'backbone',
    'handlebars',
    'auth',
    'plugins/system/site/js/view/menu',
    'plugins/system/site/js/view/authorize',
    'plugins/system/common/js/view/editUser'
], function ($, _, Backbone, Handlebars, Auth, ViewMenu, ViewAuthorize, ViewEditUser) {

    // return false;
    // this is the user instance
    // var user = new User();

    // SiteMenu({
    //     user: user
    // });

    // Cache.setObject('user:model', user);

    // // user.on('change', function () {
    // //     APP.Sandbox.eventNotify('plugin:user:model:change', user);
    // // });

    // Auth.on('registered', function () {
    //     var authUserID = Auth.getUserID();
    //     if (authUserID) {
    //         user.set('ID', authUserID);
    //         user.fetch();
    //     }
    //     _navigateToUserIfAuthorizedFn();
    // });

    // Auth.on('guest', function () {
    //     user.clear();
    //     _navigateToHomeIfNotAuthorizedFn();
    // });

    // var _navigateToHomeIfNotAuthorizedFn = function () {
    //     var isUserPage = /^user/.test(Backbone.history.fragment);
    //     var isSignUp = /^user\/create/.test(Backbone.history.fragment);
    //     if (!Auth.getUserID() && !isSignUp && isUserPage) {
    //         Backbone.history.navigate("", true);
    //         return true;
    //     }
    //     return false;
    // }

    // var _navigateToUserIfAuthorizedFn = function () {
    //     var isUserPage = /^user/.test(Backbone.history.fragment);
    //     var isSignUp = /^user\/create/.test(Backbone.history.fragment);
    //     if (Auth.getUserID() && isSignUp) {
    //         Backbone.history.navigate("user/summary", true);
    //         return true;
    //     }
    //     return false;
    // }

    var Router = Backbone.View.extend({
        urls: {},
        // routes: {
        //     "user/summary": "summary",
        //     "user/create": "create",
        //     "user/password": "password",
        //     "user/edit": "edit",
        //     "user/addresses": "addresses",
        //     "user/delete": "delete"
        // },

        // initialize: function () {
        //     var self = this;
        //     APP.Sandbox.eventSubscribe('plugin:user:profile:show', function(pageContent) {
        //         self.showToolbar(pageContent);
        //     });
        // },
        initialize: function (options, callback) {
            // configure plugin
            this.options = options || {};
            this.urls = options && options.urls || {};
            // fetch data
            // callback();
        },

        menu: function (options) {
            var menu = new ViewMenu(_.extend({}, options || {}));
            menu.render();
            return menu;
        },


        // signup: function () {
        //     // if (_navigateToUserIfAuthorizedFn())
        //     //     return;
        //     // // APP.Sandbox.eventNotify('global:breadcrumb:show');
        //     // APP.getCustomer().setBreadcrumb();
        //     // require(['plugins/system/site/js/view/userCreate'], function (UserCreate) {
        //     //     // create new view
        //     //     var userCreate = new UserCreate({
        //     //         model: user
        //     //     });
        //     //     APP.Sandbox.eventNotify('global:content:render', {
        //     //         name: 'CommonBodyCenter',
        //     //         el: userCreate.el
        //     //     });
        //     //     userCreate.render();
        //     // });

        // },

        authorize: function () {
            // debugger;
            // create new view
            var authorize = new ViewAuthorize();
            authorize.render();
            return authorize;
        },

        userPanel: function (id) {
            // debugger;
            // create new view
            var viewEditUser = new ViewEditUser();
            viewEditUser.model.set('ID', id, {silent: true});
            viewEditUser.model.fetch({reset: true});
            return viewEditUser;
        },


        // summary: function () {
        //     if (_navigateToHomeIfNotAuthorizedFn())
        //         return;
        //     // APP.Sandbox.eventNotify('global:breadcrumb:show');
        //     var self = this;
        //     APP.getCustomer().setBreadcrumb();
        //     require(['plugins/system/site/js/view/userSummary'], function (UserSummary) {
        //         var userSummary = new UserSummary({
        //             model: user
        //         });
        //         userSummary.render();
        //         self.showToolbar(userSummary.$el);
        //     });
        // },

        // password: function () {
        //     if (_navigateToHomeIfNotAuthorizedFn())
        //         return;
        //     // APP.Sandbox.eventNotify('global:breadcrumb:show');
        //     var self = this;
        //     APP.getCustomer().setBreadcrumb();
        //     require(['plugins/system/site/js/view/userPassword'], function (UserPassword) {
        //         // create new view
        //         var userPassword = new UserPassword({
        //             model: user
        //         });
        //         userPassword.render();
        //         self.showToolbar(userPassword.$el);
        //     });
        // },

        // edit: function () {
        //     if (_navigateToHomeIfNotAuthorizedFn())
        //         return;
        //     // APP.Sandbox.eventNotify('global:breadcrumb:show');
        //     var self = this;
        //     APP.getCustomer().setBreadcrumb();
        //     require(['plugins/system/site/js/view/userEdit'], function (UserEdit) {
        //         // create new view
        //         var userEdit = new UserEdit({
        //             model: user
        //         });
        //         // self.setPagePlaceholder(userEdit.el);
        //         self.showToolbar(userEdit.$el);
        //         userEdit.render();
        //     });
        // },

        // addresses: function () {
        //     if (_navigateToHomeIfNotAuthorizedFn())
        //         return;
        //     var self = this;
        //     // APP.Sandbox.eventNotify('global:breadcrumb:show');
        //     APP.getCustomer().setBreadcrumb();
        //     require(['plugins/system/site/js/view/userAddresses'], function (UserAddresses) {
        //         // create new view
        //         var userAddresses = new UserAddresses({
        //             model: user
        //         });
        //         // view.setPagePlaceholder(userAddresses.el);
        //         self.showToolbar(userAddresses.$el);
        //         userAddresses.render();
        //     });
        // },

        // delete: function () {
        //     if (_navigateToHomeIfNotAuthorizedFn())
        //         return;
        //     var self = this;
        //     // APP.Sandbox.eventNotify('global:breadcrumb:show');
        //     APP.getCustomer().setBreadcrumb();
        //     require(['plugins/system/site/js/view/userDelete'], function (UserDelete) {
        //         // create new view
        //         var userDelete = new UserDelete();
        //         // view.setPagePlaceholder(userDelete.el);
        //         self.showToolbar(userDelete.el);
        //         userDelete.fetchAndRender({
        //             action: 'status'
        //         });
        //     });
        // },

        // showToolbar: function (pageContent) {
        //     APP.getCustomer().setBreadcrumb();
        //     require(['plugins/system/site/js/view/userHolder'], function (ViewUserHolder) {
        //         // create new view
        //         var viewUserHolder = new ViewUserHolder({
        //             model: user
        //         });
        //         APP.Sandbox.eventNotify('global:content:render', {
        //             name: 'CommonBodyCenter',
        //             el: viewUserHolder.$el
        //         });
        //         viewUserHolder.render(pageContent);
        //     });
        // }

    });

    return Router;

});