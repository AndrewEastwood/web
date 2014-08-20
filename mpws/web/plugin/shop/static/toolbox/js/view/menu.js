define("plugin/shop/toolbox/js/view/menu", [
    'default/js/lib/sandbox',
    'default/js/lib/backbone',
    'default/js/lib/utils',
    'default/js/plugin/hbs!plugin/shop/toolbox/hbs/menu',
    /* lang */
    'default/js/plugin/i18n!plugin/shop/toolbox/nls/translation',
], function (Sandbox, Backbone, Utils, tpl, lang) {

    var menu = new (Backbone.View.extend({
        tagName: 'li',
        id: 'shop-menu-ID',
        attributes: {
            rel: "menu"
        },
        lang: lang,
        template: tpl,
        render: function () {
            this.$el.html(tpl(Utils.getHBSTemplateData(this)));
        }
    }))();

    Sandbox.eventSubscribe('global:loader:complete', function (CustomerMenuView) {
        menu.render();
        Sandbox.eventNotify('global:content:render', {
            name: 'MenuLeft',
            el: menu.$el,
            append: true
        });
    });

});