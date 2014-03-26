define("plugin/toolbox/js/view/bridge", [
    'default/js/lib/sandbox',
    'default/js/view/mView',
    'default/js/lib/backbone',
    'plugin/toolbox/js/model/bridge',
    'default/js/lib/contentInjection',
    'default/js/plugin/hbs!plugin/toolbox/hbs/toolbox/bridge',
    /* lang */
    'default/js/plugin/i18n!plugin/toolbox/nls/toolbox'
], function (Sandbox, MView, Backbone, ModelToolboxInstance, contentInjection, tpl, lang) {

    var AccountProfile = MView.extend({
        id: 'toolbox-bridge-ID',
        notifyWhenRenderComplete: 'plugin:toolbox:render:complete',
        template: tpl,
        lang: lang,
        model: ModelToolboxInstance,
        initialize: function () {
            var self = this;

            this.model.clearErrors();
            this.model.clearStates();

            Sandbox.eventSubscribe('plugin:toolbox:menu:display', function (options) {
                self.setMenu(options);
            });
        },
        getContainerMenu: function () {
            return this.$('#toolbox-menu-ID');
        },
        getContainerPage: function () {
            return this.$('#toolbox-page-ID');
        },
        setPage: function (options) {
            contentInjection.injectContent(this.getContainerPage(), options);
        },
        setMenu: function (options) {
            contentInjection.injectContent(this.getContainerMenu(), options);
            // debugger;
            Sandbox.eventNotify('global:menu:set-active');
        },
    });

    return AccountProfile;

});