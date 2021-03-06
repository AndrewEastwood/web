define([
    'backbone',
    'handlebars',
    'plugins/system/common/js/model/user',
    'plugins/system/common/js/view/userAddress',
    'utils',
    'auth',
    'createPopupTitle',
    'cachejs',
    'bootstrap-dialog',
    'bootstrap-alert',
    /* template */
    'text!plugins/system/common/hbs/editUser.hbs',
    /* lang */
    'i18n!plugins/system/common/nls/translation',
    // 'image-upload',
    'bootstrap-switch',
    'bootstrap-editable'
], function (Backbone, Handlebars, ModelUser, ViewAddress, Utils, Auth,
             createPopupTitle, cachejs, BootstrapDialog, BSAlert,
             tpl, lang, WgtImageUpload) {

    function _getTitle (isNew) {
        var backUrl = Auth.userData.p_CanMaintain ? APP.instances.system.urls.usersList : APP.instances.system.urls.dashboard;
        return createPopupTitle(isNew ? lang.editors.user.titleForNew : lang.editors.user.titleForExistent, backUrl);
    }

    var EditUser = Backbone.View.extend({
        template: Handlebars.compile(tpl), // check
        lang: lang,
        className: 'bootstrap-dialog type-primary size-normal plugin-system-edit-user',
        events: {
            'shown.bs.tab a[data-toggle="tab"]': 'saveActiveTab',
            "click .add-address": "addNewAddress",
            'click #account-password-pwdgen-btn-ID': 'generatePassword',
            'submit .account-profile-password form': 'changePassword'
        },
        initialize: function () {
            this.options = {};
            this.options.switchOptions = {
                onColor: 'success',
                size: 'mini',
                onText: '<i class="fa fa-check fa-fw"></i>',
                offText: '<i class="fa fa-times fa-fw"></i>'
            };
            this.addressViews = {};
            this.model = new ModelUser();
            this.listenTo(this.model, 'sync', this.render);
            _.bindAll(this, 'saveActiveTab', 'setActiveTab', 'showSuccessMessage', 'renderAddressItems', 'moveToDisabled', 'attachListenersToAddressView');
        },
        render: function () {
            var that = this;

            this.model.extras = {
                isNew: this.model.isNew()
            };

            // debugger
            var _addr = _.chain(this.model.get('Addresses')).each(function (addrItem) {
                 var view = new ViewAddress({
                    address: addrItem,
                    UserID: that.model.id
                });
                that.addressViews[view.cid] = view;
                that.attachListenersToAddressView(view);
            });

            var $dialog = new BootstrapDialog({
                closable: false,
                title: _getTitle(this.model.isNew()),
                message: $(this.template(Utils.getHBSTemplateData(this))),
                buttons: [{
                    label: lang.editors.user.buttonClose,
                    cssClass: 'btn-default btn-link' + (!APP.config.ISTOOLBOX ? ' hidden' : ''),
                    action: function (dialog) {
                        // debugger
                        if (APP.config.ISTOOLBOX) {
                            Backbone.history.navigate(APP.instances.system.urls.usersList, true);
                        } else {
                            Backbone.history.navigate(APP.instances.system.urls.account, true);
                        }
                    }
                }, {
                    label: lang.editors.user.buttonSave,
                    cssClass: 'btn-success btn-outline',
                    action: function (dialog) {

                        var pwd = that.$('#Password').val(),
                            pwdv = that.$('#Verify').val(),
                            newData = {
                                'FirstName': that.$('.js-firstname').val(),
                                'LastName': that.$('.js-lastname').val(),
                                'Phome': that.$('.js-phone').val(),
                                'EMail': that.$('.js-email').val(),
                                // 'Password': that.$('.js-password').val(),
                                // 'ConfirmPassword': that.$('.js-passwordverify').val(),
                            };

                            if (APP.config.ISTOOLBOX) {
                                _.extend(newData, {
                                    'p_CanAdmin': that.$('.js-p_CanAdmin').is(':checked'),
                                    'p_CanCreate': that.$('.js-p_CanCreate').is(':checked'),
                                    'p_CanEdit': that.$('.js-p_CanEdit').is(':checked'),
                                    'p_CanUpload': that.$('.js-p_CanUpload').is(':checked'),
                                    'p_CanViewReports': that.$('.js-p_CanViewReports').is(':checked'),
                                    'p_CanAddUsers': that.$('.js-p_CanAddUsers').is(':checked'),
                                    'p_CanMaintain': that.$('.js-p_CanMaintain').is(':checked'),
                                    'p_Others': that.$('.js-p_Other:checked').map(function () { return $(this).val(); }).toArray().join(';')
                                });
                            }

                        if (pwd || pwdv) {
                            newData.Password = pwd;
                            newData.ConfirmPassword = pwdv;
                        }

                        that.model.save(newData, {
                            wait: true,
                            success: function (model, response) {
                                Auth.getStatus();
                                // debugger;
                                if (response && response.success) {
                                    BSAlert.success(lang.editors.user.messageSuccess);
                                    // window.location.reload();
                                } else {
                                    BSAlert.danger(lang.editors.user.messageError);
                                }
                            }
                        });
                    }
                }]
            });
            // $dialog.open();
            $dialog.realize();
            $dialog.updateTitle();
            $dialog.updateMessage();
            $dialog.updateClosable();

            this.$el.html($dialog.getModalContent());
            this.$('.js-permissions .switcher').bootstrapSwitch(this.options.switchOptions);
            this.setActiveTab();
            this.renderAddressItems();

            if (this.model.get('success')) {
                this.showSuccessMessage();
            }

            return this;
        },
        moveToDisabled: function (addrView) {
            addrView.render().$el.prependTo(this.$('.account-addresses-removed'));
        },
        renderAddressItems: function () {
            var that = this;

            if (!_.isEmpty(that.addressViews)) {
                _(that.addressViews).invoke('remove');
            }
            that.addressView = {};


            that.$('.account-addresses-removed, .account-addresses').empty();
            _(this.addressViews).each(function (addrView) {
                if (addrView.isActive()) {
                    that.$('.account-addresses').prepend(addrView.render().$el);
                } else {
                    that.$('.account-addresses-removed').prepend(addrView.render().$el);
                }
            });
            this.refreshAddNewAddressButton();
        },
        saveActiveTab: function () {
            cachejs.set('toolboxUserEditActiveTab', this.$('.nav-tabs li.active').find('a').attr('href'));
        },
        setActiveTab: function () {
            var activeTab = cachejs.get('toolboxUserEditActiveTab') || '#general';
            this.$('.nav-tabs li').find('a[href="' + activeTab + '"]').tab('show');
        },
        showSuccessMessage: function () {
            var that = this;
            this.$('.alert.alert-success')
                .removeClass('hidden');
            _.delay(function () {
                that.$('.alert.alert-success .fa').removeClass('hidden').addClass('bounceIn');
            }, 100);
            _.delay(function () {
                that.$('.alert.alert-success')
                    .fadeTo(2000, 0)
                    .slideUp(500, function () {
                        $(this)
                            .addClass('hidden')
                            .removeAttr('style');
                        that.$('.alert.alert-success .fa').addClass('hidden').removeClass('bounceIn');
                    });
            }, 3000);
        },
        getActiveAddressesCount: function () {
            var statesCount = _.chain(this.addressViews).invoke('isActive').countBy(function (state) {
                return state ? 'active' : 'removed';
            }).value();
            return statesCount.active || 0;
        },
        canCreateAddress: function () {
            return this.getActiveAddressesCount() < 3;
        },
        toggleAddNewAddressButton: function (hide) {
            this.$(".add-address").toggleClass('hide', hide);
        },
        refreshAddNewAddressButton: function () {
            this.$(".add-address").toggleClass('hide', !this.canCreateAddress());
        },
        attachListenersToAddressView: function (addrView) {
            var that = this;
            addrView.on('custom:saved', function () {
                that.showSuccessMessage();
                that.refreshAddNewAddressButton();
            });
            addrView.on('custom:disabled', function (cid) {
                that.showSuccessMessage();
                that.moveToDisabled(that.addressViews[cid]);
                that.refreshAddNewAddressButton();
            });
            addrView.on('custom:cancel', function (cid) {
                that.addressViews[cid] = null;
                that.addressViews = _(that.addressViews).omit(cid);
                that.refreshAddNewAddressButton();
            });
        },
        addNewAddress: function (address) {
            var that = this;
            if (!this.canCreateAddress()) {
                return;
            }
            var view = new ViewAddress({
                UserID: that.model.id
            });
            that.addressViews[view.cid] = view;
            that.attachListenersToAddressView(view);
            this.toggleAddNewAddressButton(true);
            this.$('.account-addresses').prepend(view.render().$el);
        },
        changePassword: function () {
            this.model.changePassword(this.$('#Password').val(), this.$('#Verify').val());
            return false;
        },
        generatePassword: function (event) {
            var $btn = $(event.target);

            if ($btn.data('pwd')) {
                this.$('#Password, #Verify').val("").prop('disabled', false);
                $btn.data('pwd', false);
                $btn.text(lang.editors.user.buttonGeneratePassword);
                return;
            }

            var pwd = Utils.generatePwd();// Math.random().toString(36).slice(-8);
            this.$('#Password, #Verify').val(pwd).prop('disabled', true);

            $btn.data('pwd', pwd);
            $btn.text(lang.editors.user.buttonGeneratePasswordCancel + ': ' + pwd);

            // show password
            BootstrapDialog.show({
                type: BootstrapDialog.TYPE_WARNING,
                title: lang.editors.user.popups.password.title,
                message: pwd
            });
        }
    });

    return EditUser;

});