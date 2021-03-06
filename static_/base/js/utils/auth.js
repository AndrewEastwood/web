define([
    "jquery",
    "underscore",
    'backbone',
    "cachejs"
], function ($, _, Backbone, Cache) {

    var authKey = APP.config.AUTHKEY,
        user = Cache.get('user') || {},
        lastAuthEvent = null;

    function authOK () {
        return Auth.verifyStatus() === true;
    }
    function authNotOK () {
        return Auth.verifyStatus() === false;
    }
    var actions = {
        ifRegistered: ifRegistered,
        ifNotRegistered: ifNotRegistered
    };
    function ifRegistered (fn) { if (authOK()) { fn(); } return actions; }
    function ifNotRegistered (fn) { if (authNotOK()) { fn(); } return actions; }

    var Auth = _.extend({
        user: null,
        userData: user,
        perms: getCurrentUserPerms(user),
        verifyStatus: function () {
            var user = Auth.getUserID();
            Backbone.trigger('auth:info', user);
            if (Auth.user === user) {
                // APP.Sandbox.eventNotify("global:auth:status:unchanged", user);
                if (Auth.user === null) {
                    this.triggerGuest();
                    return false;
                }
                this.triggerRegistered();
                return true;
            }
            Auth.user = user;
            if (Auth.user) {
                // Backbone.trigger('auth:registered', user);
                this.triggerRegistered();
                return true;
                // APP.Sandbox.eventNotify("global:auth:status:active");
            } else {
                // Backbone.trigger('auth:guest', user);
                this.triggerGuest();
                // APP.Sandbox.eventNotify("global:auth:status:inactive");
            }
            return false;
        },
        triggerRegistered: function () {
            if (lastAuthEvent !== 'registered') {
                lastAuthEvent = 'registered';
                this.trigger('registered');
            }
        },
        triggerGuest: function () {
            if (lastAuthEvent !== 'guest') {
                lastAuthEvent = 'guest';
                this.trigger('guest');
            }
        },
        verifyStatusAndThen: function () {
            return actions;
        },
        getUserID: function () {
            return Cache.getRawCookie(authKey) || null;
        },
        getStatus: function (callback) {
            var that = this;
            // var query = {
            //     fn: 'status'
            // };
            return $.get(APP.getAuthLink(/*query*/), function (response) {
                that.userData = response || {};
                that.perms = getCurrentUserPerms(that.userData);
                Cache.set('user', that.userData);
                // Cache.set(authKey, that.userData.ValidationString);
                if (_.isFunction(callback)) {
                    callback(Auth.getUserID(), response);
                }
            });
        },
        signin: function (email, password, remember, callback) {
            var that = this;
            // var query = {
            //     fn: 'signin'
            // };
            $dfd = $.post(APP.getAuthLink(/*query*/), {
                email: email,
                password: password,
                remember: remember,
            }, function (response) {
                that.trigger('signin:ok');
                that.userData = response || {};
                that.perms = getCurrentUserPerms(that.userData);
                Cache.set('user', that.userData);
                // Cache.set(authKey, that.userData.ValidationString);
                if (_.isFunction(callback)) {
                    callback(Auth.getUserID(), response);
                }
            });

            $dfd.fail(function () {
                that.trigger('signin:fail');
            });

            return $dfd;
        },
        signout: function (callback) {
            // debugger
            var that = this;
            // var query = {
            //     fn: 'signout'
            // };
            return $.ajax({
                url: APP.getAuthLink(/*query*/),
                type: 'DELETE',
                success: function (response) {
                    that.trigger('signout:ok');
                    that.userData = {};
                    that.perms = {};
                    Cache.set('user', that.userData);
                    // Cache.set(authKey, that.userData);
                    if (_.isFunction(callback)) {
                        callback(Auth.getUserID(), response);
                    }
                }
            });
        },
        canDo: function (action) {
            return this.perms['p_' + action] === true;
        }
    }, Backbone.Events);

    function getCurrentUserPerms (userData) {
        if (userData) {
            var perms = _(userData).omit(function (v, k) {
                return !/^p_Can/.test(k) && k !== 'p_Others';
            });
            _(perms.p_Others).each(function (v) {
                perms['p_' + v] = true;
            });
            return perms;
        }
        return {};
    }

    // init user data
    Auth.user = Auth.getUserID()

    Backbone.on("global:ajax:response", function (/*data*/) {
        Auth.verifyStatus();
    });

    return Auth;

});