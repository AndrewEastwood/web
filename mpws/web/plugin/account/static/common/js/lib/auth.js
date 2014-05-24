define("plugin/account/common/js/lib/auth", [
    "default/js/lib/sandbox",
    "cmn_jquery",
    "default/js/lib/underscore",
    "default/js/lib/cache"
], function (Sandbox, $, _, Cache) {

    var Auth = {
        getStatus: function () {
            var url = APP.getApiLink({
                source: 'account',
                fn: 'status'
            });
            return $.get(url, function (response) {
                Cache.setCookie('account', response);
                Sandbox.eventNotify('plugin:account:status:received', response);
            }).error(function(){
                Sandbox.eventNotify('plugin:account:status:received', null);
            });
        },
        signin: function (email, password) {
            // debugger;
            var url = APP.getApiLink({
                source: 'account',
                fn: 'signin'
            });
            return $.post(url, {
                email: email,
                password: password
            }, function (response) {
                Cache.setCookie('account', response);
                if (response)
                    Sandbox.eventNotify('plugin:account:signed:in', response);
            }).error(function(){
                debugger;
                Sandbox.eventNotify('plugin:account:signed:in', false);
            });
        },
        signout: function () {
            // debugger;
            var url = APP.getApiLink({
                source: 'account',
                fn: 'signout'
            });
            return $.post(url, function () {
                Cache.setCookie('account', null);
                Sandbox.eventNotify('plugin:account:signed:out', null);
            }).error(function(){
                debugger;
                Sandbox.eventNotify('plugin:account:signed:out', false);
            });
        }
    };

    return Auth;

});