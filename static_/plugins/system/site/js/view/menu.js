define([
    'plugins/system/site/js/view/menuUser',
    'plugins/system/site/js/view/menuSignUp',
], function (MenuUser, MenuSignUp) {

    return function (models) {
        // create SignUp button
        var menuSignUp = new MenuSignUp({
            model: models.user
        });
        menuSignUp.render();

        // create SignIn button
        var menuUser = new MenuUser({
            model: models.user
        });
        menuUser.render();

        APP.Sandbox.eventSubscribe('global:loader:complete', function () {
            // placeholders.common.menu
            APP.Sandbox.eventNotify('global:content:render', [
                {
                    name: 'CommonMenuRight',
                    el: menuSignUp.$el,
                    append: true
                },
                {
                    name: 'CommonMenuRight',
                    el: menuUser.$el,
                    append: true
                }
            ]);
        });
    }

});