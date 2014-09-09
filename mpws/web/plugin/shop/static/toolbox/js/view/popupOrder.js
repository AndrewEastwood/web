define("plugin/shop/toolbox/js/view/popupOrder", [
    'default/js/lib/sandbox',
    'default/js/lib/backbone',
    'plugin/shop/toolbox/js/model/order',
    'default/js/lib/utils',
    'default/js/lib/bootstrap-dialog',
    'default/js/lib/bootstrap-alert',
    /* template */
    'default/js/plugin/hbs!plugin/shop/toolbox/hbs/popupOrder',
    /* lang */
    'default/js/plugin/i18n!plugin/shop/toolbox/nls/translation',
    'default/js/lib/bootstrap-editable'
], function (Sandbox, Backbone, ModelOrder, Utils, BootstrapDialog, BSAlert, tpl, lang) {

    function _getTitleByStatus (status) {
        switch (status) {
            case 'NEW': {
                return $('<span/>').addClass('fa fa-dot-circle-o').append(' ', lang.order_status_NEW);
            }
            case 'ACTIVE': {
                return $('<span/>').addClass('fa fa-clock-o').append(' ', lang.order_status_ACTIVE);
            }
            case 'LOGISTIC_DELIVERING': {
                return $('<span/>').addClass('fa fa-plane').append(' ', lang.order_status_LOGISTIC_DELIVERING);
            }
            case 'LOGISTIC_DELIVERED': {
                return $('<span/>').addClass('fa fa-gift').append(' ', lang.order_status_LOGISTIC_DELIVERED);
            }
            case 'SHOP_CLOSED': {
                return $('<span/>').addClass('fa fa-check').append(' ', lang.order_status_SHOP_CLOSED);
            }
        }
    }

    var OrderItem = Backbone.View.extend({
        template: tpl,
        lang: lang,
        initialize: function () {
            this.model = new ModelOrder();
            this.listenTo(this.model, 'change', this.render);
            this.$title = $('<span/>');
            this.$dialog = new BootstrapDialog({
                title: this.$title,
                message: this.$el,
                cssClass: 'pluginShopOrderPopup',
                // onhide: function () {
                //     self.dialogIsShown = false;
                // },
                buttons: [{
                    label: "Надіслати фактуру",
                    action: function (dialog) {
                        dialog.close();
                    }
                }, {
                    label: "Друкувати фактуру",
                    action: function (dialog) {
                        dialog.close();
                    }
                }, {
                    label: "Надіслати код-відстеження",
                    action: function (dialog) {
                        dialog.close();
                    }
                }, {
                    label: lang.popup_order_button_Close,
                    cssClass: 'btn-warning',
                    action: function (dialog) {
                        dialog.close();
                    }
                }]
            });
        },
        render: function () {
            var self = this;

            this.$title.html(_getTitleByStatus(self.model.get('Status')));

            this.$el.html(tpl(Utils.getHBSTemplateData(this)));

            var orderID = this.model.id;
            // if (!dialogIsShown)
            var source = this.$('#order-status-control-ID option').map(function(idx, option){
                return {
                    value: $(option).attr('value'),
                    text: $(option).text()
                }
            });
            var $controlOrderStatus = this.$('#order-status-ID');
            $controlOrderStatus.editable({
                mode: 'inline',
                name: "Status",
                title: lang.popup_order_control_status,
                type: "select",
                value: this.model.get('Status'),
                pk: this.model.id,
                source: $.makeArray(source),
            });
            $controlOrderStatus.on('save', function(event, editData) {
                self.model.save({
                    Status: editData.newValue
                }, {
                    patch: true,
                    success: function (model, response) {
                        if (!response || !response.success) {
                            BSAlert.danger('Помилка під час оновлення замовлення');
                        }
                    }
                });
            });
            var $controlOrderInternalComment = this.$('#shop-order-internalComment-ID');
            var lazyLayout = _.debounce(function () {
                self.model.save({
                    InternalComment: $controlOrderInternalComment.val()
                }, {
                    patch: true,
                    success: function (model, response) {
                        if (!response || !response.success) {
                            BSAlert.danger('Помилка під час оновлення замовлення');
                        }
                    }
                });
            }, 300);
            $controlOrderInternalComment.on('keyup', lazyLayout);
            this.$('.helper').tooltip();

            if (!this.$dialog.isOpened())
                this.$dialog.open();
        }
    });

    return OrderItem;

});