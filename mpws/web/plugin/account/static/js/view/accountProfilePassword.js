define("plugin/account/js/view/accountProfilePassword", [
    'default/js/lib/sandbox',
    'default/js/view/mView',
    'default/js/lib/bootstrap-dialog',
    'plugin/account/js/model/account',
    'default/js/plugin/hbs!plugin/account/hbs/accountProfilePassword',
    /* lang */
    'default/js/plugin/i18n!plugin/account/nls/site/translation'
], function (Sandbox, MView, BootstrapDialog, ModelAccountInstance, tpl, lang) {

    var AccountProfilePassword = MView.extend({
        // tagName: 'li',
        // className: 'col-sm-9 col-md-9',
        template: tpl,
        lang: lang,
        model: ModelAccountInstance,
        events: {
            'click #account-password-pwdgen-btn-ID': 'generatePassword',
            'submit .account-profile-password form': 'changePassword'
        },
        initialize: function () {
            this.model.clearErrors();
            this.model.clearStates();
            this.listenTo(this.model, "change", this.render);
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

            var pwd = Math.random().toString(36).slice(-8);
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

    return AccountProfilePassword;

});