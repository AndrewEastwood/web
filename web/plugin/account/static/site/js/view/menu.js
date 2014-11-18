define("plugin/account/site/js/view/menu", [
    'default/js/lib/sandbox',
    'plugin/account/site/js/view/menuAccount',
    'plugin/account/site/js/view/menuSignUp',
], function (Sandbox, MenuAccount, MenuSignUp) {

    return function (models) {
        // create SignUp button
        var menuSignUp = new MenuSignUp({
            model: models.account
        });
        menuSignUp.render();

        // create SignIn button
        var menuAccount = new MenuAccount({
            model: models.account
        });
        menuAccount.render();

        Sandbox.eventSubscribe('global:loader:complete', function () {
            // placeholders.common.menu
            Sandbox.eventNotify('global:content:render', [
                {
                    name: 'CommonMenuRight',
                    el: menuSignUp.$el,
                    append: true
                },
                {
                    name: 'CommonMenuRight',
                    el: menuAccount.$el,
                    append: true
                }
            ]);
        });
    }

});