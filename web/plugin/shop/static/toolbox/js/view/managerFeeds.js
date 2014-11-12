define('plugin/shop/toolbox/js/view/managerFeeds', [
    'default/js/lib/sandbox',
    'default/js/lib/backbone',
    'default/js/lib/utils',
    'default/js/lib/bootstrap-dialog',
    /* collection */
    "plugin/shop/toolbox/js/collection/feeds",
    /* template */
    'default/js/plugin/hbs!plugin/shop/toolbox/hbs/managerFeeds',
    /* lang */
    'default/js/plugin/i18n!plugin/shop/toolbox/nls/translation',
    'default/js/lib/jquery.fileupload/vendor/jquery.ui.widget',
    'default/js/lib/jquery.fileupload/jquery.iframe-transport',
    'default/js/lib/jquery.fileupload/jquery.fileupload-validate',
    'default/js/lib/jquery.fileupload/jquery.fileupload',
], function (Sandbox, Backbone, Utils, BootstrapDialog, CollectionFeeds, tpl, lang) {

    var ManagerFeeds = Backbone.View.extend({
        template: tpl,
        lang: lang,
        className: 'shop-manager-feeds',
        events: {
            'click .start-import': 'importFeed',
            'click .generate': 'generateFeed',
            'click .delete-uploaded': 'deleteUploadedFeed',
            'click .download-import': 'downloadImportFeed'
        },
        initialize: function (options) {
            this.options = options || {};
            this.collection = new CollectionFeeds();
            this.listenTo(this.collection, 'reset', this.render);
        },
        render: function () {
            var that = this;
            this.$el.html(tpl(Utils.getHBSTemplateData(this)));
            this.$('#fileFeed').fileupload({
                url: APP.getUploadUrl({
                    source: 'shop',
                    fn: 'feeds'
                }),
                dataType: 'json',
                autoUpload: true,
                limitMultiFileUploads: 1,
                maxNumberOfFiles: 1,
                acceptFileTypes: /(\.|\/)(xls)$/i,
                maxFileSize: 15 * 1024 * 1024, // 15 MB
            }).on('fileuploadadd', function (e, data) {
            }).on('fileuploadprogressall', function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                self.$('#progress .progress-bar').css('width', progress + '%');
            }).on('fileuploadprocessalways', function (e, data) {
            }).on('fileuploaddone', function (e, data) {
                that.collection.fetch({reset: true});
            });
            return this;
        },
        generateFeed: function (event) {
            var that = this;
            BootstrapDialog.confirm('Generate new feed?', function (rez) {
                if (rez) {
                    that.collection.generateNewProductFeed();
                }
            });
        },
        importFeed: function (event) {
            var that = this,
                $feedItem = $(event.target).closest('.feed-item'),
                feedID = $feedItem.length && $feedItem.data('id'),
                feedModel = this.collection.get(feedID);
            if (!feedModel) {
                return;
            }
            BootstrapDialog.confirm('Import ' + feedModel.get('name') + ' feed?', function (rez) {
                if (rez) {
                    feedModel.importUploadedProductFeed({
                        patch: true,
                        success: function () {
                            that.collection.fetch({reset: true});
                        }
                    });
                }
            });
        },
        deleteUploadedFeed: function (event) {
            var that = this,
                $feedItem = $(event.target).closest('.feed-item'),
                feedID = $feedItem.length && $feedItem.data('id'),
                feedModel = this.collection.get(feedID);
            if (!feedModel) {
                return;
            }
            BootstrapDialog.confirm('Delete ' + feedModel.get('name') + ' feed?', function (rez) {
                if (rez) {
                    feedModel.destroy({
                        success: function () {
                            that.collection.fetch({reset: true});
                        }
                    });
                }
            });
        },
        downloadImportFeed: function (event) {
            var $el = $(event.target);
            if ($el.data('href')) {
                location.href = $el.data('href');
            }
        }
    });

    return ManagerFeeds;
});