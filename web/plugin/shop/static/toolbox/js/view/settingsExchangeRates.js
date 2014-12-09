define("plugin/shop/toolbox/js/view/settingsExchangeRates", [
    'default/js/lib/backbone',
    'plugin/shop/toolbox/js/collection/listExchangeRates',
    'default/js/lib/utils',
    'default/js/lib/bootstrap-dialog',
    'default/js/lib/bootstrap-alert',
    /* template */
    'default/js/plugin/hbs!plugin/shop/toolbox/hbs/settingsExchangeRates',
    /* lang */
    'default/js/plugin/i18n!plugin/shop/toolbox/nls/translation',
    'default/js/lib/select2/select2',
    'default/js/lib/bootstrap-editable',
    'default/js/lib/bootstrap-switch'
], function (Backbone, CollectionExchangeRates, Utils, BootstrapDialog, BSAlerts, tpl, lang) {

    return Backbone.View.extend({
        className: 'panel panel-info',
        lang: lang,
        template: tpl,
        events: {
            'click .add-currency': 'addExchangeRate',
            'click .create-currency': 'createExchangeRate',
            'click .remove-currency': 'deleteExchangeRate'
        },
        initialize: function () {
            this.options = {};
            this.collection = new CollectionExchangeRates();
            this.collection.queryParams.limit = 0;
            this.listenTo(this.collection, 'reset', this.render);
            this.options.editableOptions = {
                rate: {
                    mode: 'popup',
                    savenochange: true,
                    unsavedclass: '',
                    emptytext: lang.rates.editableEmptyRateValue,
                    validate: function (value) {
                        var _rate = parseFloat(value, 10);
                        if ($.trim(value) === '') {
                            return lang.rates.message_error_emptyValue;
                        }
                        if (_rate <= 0) {
                            return lang.rates.message_error_negativeRate;
                        }
                        if (_rate.toString() !== value) {
                            return lang.rates.message_error_wrongValue;
                        }
                    }
                },
                currency: {
                    type: 'select',
                    mode: 'popup',
                    name: 'Currency',
                    unsavedclass: '',
                    emptytext: lang.rates.editableEmptyCurrencyValue,
                    // not working for now
                    // select2: {
                    //     placeholder: 'Валюта',
                    //     data: []
                    // }
                }
            };
            _.bindAll(this, 'updateExchangeRate', 'hideSaveButton', 'showSaveButton');
        },
        render: function () {
            this.options.editableOptions.currency.source =  _(this.collection.currencyList).map(function (item) {
                return {
                    value: item,
                    text: item
                };
            });
            this.$el.html(tpl(Utils.getHBSTemplateData(this)));
            this.$('.editable').editable(this.options.editableOptions.rate)
                .on('save', this.updateExchangeRate)
                .on('shown', this.hideSaveButton)
                .on('hidden', this.showSaveButton);
            return this;
        },
        hideSaveButton: function (event) {
            var $item = $(event.target).closest('.list-group-item');
            $item.find('.create-currency').addClass('hidden');
        },
        showSaveButton: function (event) {
            var $item = $(event.target).closest('.list-group-item');
            // turn back save butto when current item is new entry
            if (typeof $item.data('id') === 'undefined') {
                $item.find('.create-currency').removeClass('hidden');
            }
        },
        addExchangeRate: function () {
            var $newAgenctTpl = this.$('.currency-template').clone();
            $newAgenctTpl.removeClass('hidden currency-template');
            this.$('.list-group').append($newAgenctTpl);
            $newAgenctTpl.find('.editable').editable(this.options.editableOptions.rate)
                .on('shown', this.hideSaveButton)
                .on('hidden', this.showSaveButton);
            $newAgenctTpl.find('.editable-select2').editable(this.options.editableOptions.currency)
                .on('shown', this.hideSaveButton)
                .on('hidden', this.showSaveButton);
            // hide add-new button
            this.$('.add-currency').addClass('hidden');
            this.$('.list-group').removeClass('hidden');
            this.$('.label-no-data').addClass('hidden');
        },
        createExchangeRate: function (event) {
            var self = this,
                $item = $(event.target).closest('.list-group-item'),
                $errorsList = $item.find('.errors');
            if ($item.length !== 1)
                return;
            $errorsList.empty();
            this.collection.create({
                RateA: parseFloat($item.find('.rate-a').text(), 10),
                CurrencyA: $item.find('.currency-a').text(),
                RateB: parseFloat($item.find('.rate-b').text(), 10),
                CurrencyB: $item.find('.currency-b').text()
            }, {
                success: function (model, resp) {
                    if (model.id) {
                        $errorsList.addClass('hidden');
                        $item.data('id', model.id);
                        $item.removeClass('is-new');
                        // show add-new button
                        self.$('.add-currency').removeClass('hidden');
                        BSAlerts.success(lang.settings_message_success);
                        self.collection.fetch({
                            reset: true
                        });
                    }
                    if (!_.isEmpty(resp.errors)) {
                        _(resp.errors).each(function (v, k) {
                            $errorsList.append($('<ul>').text(k));
                        });
                        $errorsList.removeClass('hidden');
                    }
                }
            });
        },
        updateExchangeRate: function (event, editData) {
            var self = this,
                $item = $(event.target).closest('.list-group-item'),
                id = $item.data('id'),
                updatingKey = $(event.target).data('name'),
                newData = $(event.target).editable('getValue'),
                model = this.collection.get(id);

            if (model && editData && editData.newValue) {
                newData[updatingKey] = parseFloat(editData.newValue, 10);
                model.save(newData, {
                    patch: true,
                    success: function () {
                        BSAlerts.success(lang.settings_message_success);
                        self.collection.fetch({
                            reset: true
                        });
                    },
                    error: function () {
                        BSAlerts.danger(lang.settings_error_save);
                        self.collection.fetch({
                            reset: true
                        });
                    }
                });
            }
        },
        deleteExchangeRate: function (event) {
            var self = this,
                $item = $(event.target).closest('.list-group-item'),
                id = $item.data('id'),
                model = this.collection.get(id);

            if (!model) {
                // show add-new button
                this.$('.add-currency').removeClass('hidden');
                $item.remove();
                if (!this.$('.list-group .list-group-item').length) {
                    this.$('.list-group').addClass('hidden');
                    this.$('.label-no-data').removeClass('hidden');
                }
                return;
            }

            BootstrapDialog.confirm(lang.rates.message_confirmation_delete, function (rez) {
                if (rez) {
                    model.destroy({
                        success: function () {
                            $item.remove();
                        }
                    });
                }
            });
        }
    });

});