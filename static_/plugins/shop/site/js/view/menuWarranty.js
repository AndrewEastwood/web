define([
    'backbone',
    'handlebars',
    'utils',
    'bootstrap-dialog',
    'text!plugins/shop/site/hbs/menuWarranty.hbs'
], function (Backbone, Handlebars, Utils, BootstrapDialog, tpl) {

    var MenuWarranty = Backbone.View.extend({
        tagName: 'a',
        attributes: {
            href: 'javascript://'
        },
        template: Handlebars.compile(tpl), // check
        events: {
            'click': 'showPopup'
        },
        render: function () {
            this.$el.html(this.template(Utils.getHBSTemplateData(this)));
            return this;
        },
        showPopup: function (event) {
            BootstrapDialog.show({
                draggable: false,
                cssClass: 'popup-shop-info popup-shop-warranty',
                type: BootstrapDialog.TYPE_WARNING,
                title: $(event.target).html().trim(),
                message: APP.instances.shop.settings._user.activeAddress.InfoWarranty
            });
        }
    });

    return MenuWarranty;

});