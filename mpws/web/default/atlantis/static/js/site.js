define("default/js/site", [
    'default/js/lib/sandbox',
    'cmn_jquery',
    'default/js/lib/underscore',
    'default/js/lib/backbone',
    'default/js/lib/extend.string'
], function (Sandbox, $, _, Backbone) {

    var Site = function (options) {

        var _views = {};
        var _placeholders = _.extend({}, options.placeholders || {});

        $.xhrPool = [];
        $.xhrPool.abortAll = function() {
            $(this).each(function(idx, jqXHR) {
                jqXHR.abort();
            });
            $.xhrPool.length = 0
        };

        $.ajaxSetup({
            beforeSend: function(xhr, settings) {
                $.xhrPool.push(xhr);
            },
            complete: function(jqXHR) {
                var index = $.xhrPool.indexOf(jqXHR);
                if (index > -1) {
                    $.xhrPool.splice(index, 1);
                }
            }
        });

        // Sandbox message handler
        $('body').on('click', '[data-action]', function (event) {
            // debugger;
            var _data = $(this).data() || {};
            _data.event = event;
            Sandbox.eventNotify($(this).data('action'), _data);
        });

        $(window).on('hashchange', function() {
            var _hashTags = window.location.hash.substr(1).split('/');
            $('body').attr('class', "MPWSPage");
            if (_hashTags && _hashTags[0])
                $('body').addClass("Page" + _hashTags[0].ucWords());
        });
        $(window).trigger('hashchange');

        var Router = Backbone.Router.extend({
            routes: {
                "": "index",
                "login": "login",
                "logout": "logout",
                // "*nothing": "unknown",
            },
            index: function () {
                // debugger;
                Sandbox.eventNotify('site:page:index');
            },
            login: function () {
                // debugger;
                Sandbox.eventNotify('site:page:login');
            },
            logout: function () {
                // debugger;
                Sandbox.eventNotify('site:page:logout');
            },
            unknown: function () {
                // debugger;
                Sandbox.eventNotify('site:page:404');
            }
        });

        var defaultRouter = new Router();

        var _site = {
            placeholders: _placeholders,
            config: app.config,
            options: options,
            views: _views,
            plugins: window.app.config.PLUGINS,
            hasPlugin: function (pluginName) {
                return _(window.app.config.PLUGINS).indexOf(pluginName) >= 0;
            },
            renderBefore: null,
            renderAfter: null,
            render: function (options) {
                // console.log('_renderFn', options);

                if (!options || !options.name)
                    return;

                if (_.isFunction(_site.renderBefore))
                    _site.renderBefore(options);

                // debugger;
                var _container = _placeholders[options.name];

                if (!_container || !_container.length)
                    return;

                if (options.append)
                    _container.append(options.el);
                else if (options.prepend)
                    _container.prepend(options.el);
                else
                    _container.html(options.el);

                if (_.isFunction(_site.renderAfter))
                    _site.renderAfter(options);
            }
        }

        Sandbox.eventSubscribe('site:content:render', function (options) {
            if (_.isArray(options))
                _(options).each(function(option){
                    _site.render(option);
                });
            else
                _site.render(options);
        });

        return _site;
    }


    return Site;
});