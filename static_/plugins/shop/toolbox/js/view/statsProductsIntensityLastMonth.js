define([
    'backbone',
    'handlebars',
    'plugins/shop/toolbox/js/model/statsProductsIntensityLastMonth',
    'utils',
    /* template */
    'text!plugins/shop/toolbox/hbs/statsProductsIntensityLastMonth.hbs',
    /* lang */
    'i18n!plugins/shop/toolbox/nls/translation',
    /* charts */
    'async!//maps.google.com/maps/api/js?sensor=false',
    'goog!visualization,1,packages:[corechart,geochart]'
], function (Backbone, Handlebars, ModelProductsIntensity, Utils, tpl, lang) {

    return Backbone.View.extend({
        className: 'panel panel-default',
        lang: lang,
        template: Handlebars.compile(tpl), // check
        initialize: function () {
            this.model = new ModelProductsIntensity();
            this.listenTo(this.model, 'change', this.render);
        },
        render: function () {
            this.$el.html(this.template(Utils.getHBSTemplateData(this)));
            if (google) {

                // products income for last month
                var productsActiveInstensity = this.model.get('ACTIVE');
                var productsDiscountInstensity = this.model.get('DISCOUNT');
                var productsPreorderInstensity = this.model.get('PREORDER');

                var mergedProductsDataOfIntensity = {};

                _(productsActiveInstensity).each(function (count, date) {
                    mergedProductsDataOfIntensity[date] = mergedProductsDataOfIntensity[date] || {};
                    mergedProductsDataOfIntensity[date]['active'] = parseInt(count, 10);
                });

                _(productsDiscountInstensity).each(function (count, date) {
                    mergedProductsDataOfIntensity[date] = mergedProductsDataOfIntensity[date] || {};
                    mergedProductsDataOfIntensity[date]['discount'] = parseInt(count, 10);
                });

                _(productsPreorderInstensity).each(function (count, date) {
                    mergedProductsDataOfIntensity[date] = mergedProductsDataOfIntensity[date] || {};
                    mergedProductsDataOfIntensity[date]['preorder'] = parseInt(count, 10);
                });

                var dataProducts = [['Дата', 'Нові', 'З знижкою', 'Для замовлення']];

                _(mergedProductsDataOfIntensity).each(function (values, date) {
                    dataProducts.push([date, values.active, values.discount || 0, values.preorder || 0]);
                });

                if (dataProducts.length === 1)
                    return this;

                var dataProducts = google.visualization.arrayToDataTable(dataProducts);

                var options = {
                    title: 'Надходження товарів',
                    legend: { position: 'bottom' }
                };

                var chart = new google.visualization.ColumnChart(this.$('.panel-body').get(0));
                chart.draw(dataProducts, options);
            }
            return this;
        }
    });
});