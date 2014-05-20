define('plugin/shop/toolbox/js/model/popupCategory', [
    'default/js/model/mModel',
    'plugin/shop/common/js/lib/utils'
], function (MModel, ShopUtils) {

    var Model = MModel.getNew();
    var ToolboxOrderItem = Model.extend({
        source: 'shop',
        fn: 'shop_manage_cetegories',
        urlOptions: {
            action: 'get'
        },
        parse: function (data) {
            if (data.boughts)
                data.boughts = ShopUtils.adjustProductItem({products: data.boughts});
            return data;
        }
    });

    return ToolboxOrderItem;

});