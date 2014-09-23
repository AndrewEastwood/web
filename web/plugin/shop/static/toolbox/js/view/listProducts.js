define("plugin/shop/toolbox/js/view/listProducts", [
    'default/js/lib/sandbox',
    'default/js/lib/backbone',
    'default/js/lib/utils',
    "default/js/lib/backgrid",
    /* collection */
    "plugin/shop/toolbox/js/collection/basicProducts",
    /* template */
    'default/js/plugin/hbs!plugin/shop/toolbox/hbs/buttonMenuProductListItem',
    /* lang */
    'default/js/plugin/i18n!plugin/shop/toolbox/nls/translation',
    /* extensions */
    "default/js/lib/spin",
    "default/js/lib/backgrid-paginator",
    "default/js/lib/backgrid-select-all",
    "default/js/lib/backgrid-htmlcell"
], function (Sandbox, Backbone, Utils, Backgrid, CollectionProducts, tplBtnMenuMainItem, lang, Spinner) {

    var opts = {
        lines: 9, // The number of lines to draw
        length: 5, // The length of each line
        width: 8, // The line thickness
        radius: 15, // The radius of the inner circle
        corners: 0.9, // Corner roundness (0..1)
        rotate: 0, // The rotation offset
        direction: 1, // 1: clockwise, -1: counterclockwise
        color: '#000', // #rgb or #rrggbb or array of colors
        speed: 1.1, // Rounds per second
        trail: 58, // Afterglow percentage
        shadow: false, // Whether to render a shadow
        hwaccel: false, // Whether to use hardware acceleration
        className: 'spinner', // The CSS class to assign to the spinner
        zIndex: 2e9, // The z-index (defaults to 2000000000)
        top: '10%', // Top position relative to parent
        left: '50%' // Left position relative to parent
    };

    var spinner = new Spinner(opts).spin();

    function getColumns () {
        // TODO: do smth to fetch states from server
        var statuses = ["ACTIVE", "ARCHIVED", "DISCOUNT", "DEFECT", "WAITING", "PREORDER"];
        var orderStatusValues = _(statuses).map(function (status){ return [lang["product_status_" + status] || status, status]; });

        var columnActions = {
            className: "custom-row-context-menu",
            name: "Actions",
            label: "",
            cell: "html",
            editable: false,
            sortable: false,
            formatter: {
                fromRaw: function (value, model) {
                    var btn = tplBtnMenuMainItem(Utils.getHBSTemplateData(model.toJSON()));
                    return btn;
                }
            }
        };

        var columnName = {
            name: "Name",
            label: lang.pluginMenu_Products_Grid_Column_Name,
            cell: "string"
        };

        var columnModel = {
            name: "Model",
            label: lang.pluginMenu_Products_Grid_Column_Model,
            cell: "string"
        };

        var columnOriginName = {
            name: "OriginName",
            label: lang.pluginMenu_Products_Grid_Column_OriginName,
            cell: "string"
        };

        var columnSKU = {
            name: "SKU",
            label: lang.pluginMenu_Products_Grid_Column_SKU,
            cell: "string"
        };

        var columnPrice = {
            name: "Price",
            label: lang.pluginMenu_Products_Grid_Column_Price,
            cell: "number",
            formatter: {
                fromRaw: function (value) {
                    return value;
                },
                toRaw: function (value) {
                    var matches = value.match(/^([0-9\.]+)/)
                    if (matches && matches[1])
                        return parseFloat(matches[0]);
                    throw "CanParseProductPrise"
                }
            }
        };

        var columnStatus = {
            name: "Status",
            label: lang.pluginMenu_Products_Grid_Column_Status,
            cell: Backgrid.SelectCell.extend({
                // It's possible to render an option group or use a
                // function to provide option values too.
                optionValues: orderStatusValues,
                initialize: function (options) {
                    Backgrid.SelectCell.prototype.initialize.apply(this, arguments);
                    this.listenTo(this.model, "change:Status", function(model) {
                        model.save(model.changed, {
                            patch: true,
                            success: function() {
                                model.collection.fetch({reset: true});
                            }
                        });
                    });
                }
            })
        };

        var columnDateUpdated = {
            name: "DateUpdated",
            label: lang.pluginMenu_Products_Grid_Column_DateUpdated,
            cell: "string",
            editable: false
        };

        var columnDateCreated = {
            name: "DateCreated",
            label: lang.pluginMenu_Products_Grid_Column_DateCreated,
            cell: "string",
            editable: false
        };

        return _.extend({}, {
            columnActions: columnActions,
            columnName: columnName,
            columnModel: columnModel,
            columnOriginName: columnOriginName,
            columnSKU: columnSKU,
            columnPrice: columnPrice,
            columnStatus: columnStatus,
            columnDateUpdated: columnDateUpdated,
            columnDateCreated: columnDateCreated
        });
    }

    var ListOrders = Backbone.View.extend({
        initialize: function (options) {
            this.options = options || {};
            this.collection = this.collection || new CollectionProducts();
            this.listenTo(this.collection, 'reset', this.render);
            this.listenTo(this.collection, 'request', this.showLoading);
            var columns = getColumns();
            if (this.options.adjustColumns)
                columns = this.options.adjustColumns(columns);
            this.grid = new Backgrid.Grid({
                className: "backgrid table table-responsive",
                columns: _(columns).values(),
                collection: this.collection
            });
            this.paginator = new Backgrid.Extension.Paginator({
                collection: this.collection
            });
            _.bindAll(this, 'showLoading', 'render');
        },
        showLoading: function () {
            var self = this;
            setTimeout(function(){
                console.log('adding spinner');
                self.$el.append(spinner.el);
            }, 0);
        },
        render: function () {
            console.log('listOrders: render');
            // debugger;
            this.$el.off().empty();
            if (this.collection.length) {
                this.$el.append(this.grid.render().$el);
                this.$el.append(this.paginator.render().$el);
            } else {
                this.$el.html(this.grid.emptyText);
            }
            return this;
        }
    });

    return ListOrders;
});