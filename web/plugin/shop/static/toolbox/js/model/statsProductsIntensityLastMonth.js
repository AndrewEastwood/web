define('plugin/shop/toolbox/js/model/statsProductsIntensityLastMonth', [
    'plugin/shop/toolbox/js/model/basicStats'
], function (ModelBasicStats) {

    return ModelBasicStats.extend({
        type: 'products_intensity_last_month'
    });

});