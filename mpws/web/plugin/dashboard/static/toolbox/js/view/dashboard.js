define("plugin/dashboard/toolbox/js/view/dashboard", [
    'cmn_jquery',
    'default/js/lib/underscore',
    'default/js/lib/backbone',
    /* template */
    'default/js/plugin/hbs!default/hbs/animationFacebook'
], function ($, _, Backbone, tplFBAnim/*, Utils, Auth, tpl, lang*/) {

    return Backbone.View.extend({
        id: 'dashboard-ID',
        className: 'plugin-dashboard',
        render: function () {
            var self = this;
            var pluginsItems = APP.config.PLUGINS;

            this.$el.empty();
            // debugger;
            _(pluginsItems).each(function (pluginName) {
                self.$el.append($('<div/>').attr({
                    name: 'DashboardForPlugin_' + pluginName
                }).html(tplFBAnim()));
            });

            return this;
        }
    });

});