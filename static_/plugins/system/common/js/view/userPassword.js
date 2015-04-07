define([
    'jquery',
    'backbone',
    'handlebars',
    'utils',
    'bootstrap-dialog',
    'text!plugins/system/site/hbs/userPassword.hbs',
    /* lang */
    'i18n!plugins/system/site/nls/translation',
    'passwordgenerator'
], function ($, Backbone, Handlebars, Utils, BootstrapDialog, tpl, lang) {

    var AccountPassword = Backbone.View.extend({
        // tagName: 'li',
        // className: 'col-sm-9 col-md-9',
        template: Handlebars.compile(tpl), // check
        lang: lang,
        events: {
            'click #account-password-pwdgen-btn-ID': 'generatePassword',
            'submit .account-profile-password form': 'changePassword'
        },
        initialize: function () {
            if (this.model)
                this.listenTo(this.model, 'change', this.render);
        },
        render: function () {
            var that = this;
            // debugger;
            this.$el.html(this.template(Utils.getHBSTemplateData(this)));
            setTimeout(function() {
                that.$('.alert.alert-success').fadeTo(1000, 0).slideUp(500, function(){
                    $(this).remove();
                });
            }, 1000);
            setTimeout(function() {
                that.$('.alert.alert-danger').fadeTo(5000, 0).slideUp(500, function(){
                    $(this).remove();
                });
            }, 30000);
            return this;
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
                $btn.text(lang.profile_page_password_button_generate);
                return;
            }

            var pwd = Utils.generatePwd();// Math.random().toString(36).slice(-8);
            this.$('#Password, #Verify').val(pwd).prop('disabled', true);

            $btn.data('pwd', pwd);
            $btn.text(lang.profile_page_password_button_generate_cancel);

            // show password
            BootstrapDialog.show({
                type: BootstrapDialog.TYPE_WARNING,
                title: lang.profile_page_password_popup_title,
                message: pwd
            });
        }
    });

    return AccountPassword;

});