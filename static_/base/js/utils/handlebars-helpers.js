/* Simple native bridge to 3dp template engine
 * --------
 */
define([
    'handlebars',
    'underscore',
    'formatter-price',
    'string'
], function (Handlebars, _, priceFmt) {
    // The module to be exported
    var helpers = {
        contains: function (str, pattern, options) {
            if (str.indexOf(pattern) !== -1) {
                return options.fn(this);
            }
            return options.inverse(this);
        },
        and: function (a, b, options) {
            if (a && b) {
                return options.fn(this);
            } else {
                return options.inverse(this);
            }
        },
        gt: function (value, test, options) {
            if (value > test) {
                return options.fn(this);
            } else {
                return options.inverse(this);
            }
        },
        gte: function (value, test, options) {
            if (value >= test) {
                return options.fn(this);
            } else {
                return options.inverse(this);
            }
        },
        is: function (value, test, options) {
            if (value === test) {
                return options.fn(this);
            } else {
                return options.inverse(this);
            }
        },
        isnt: function (value, test, options) {
            if (value !== test) {
                return options.fn(this);
            } else {
                return options.inverse(this);
            }
        },
        lt: function (value, test, options) {
            if (value < test) {
                return options.fn(this);
            } else {
                return options.inverse(this);
            }
        },
        lte: function (value, test, options) {
            if (value <= test) {
                return options.fn(this);
            } else {
                return options.inverse(this);
            }
        },
        /**
         * Or
         * Conditionally render a block if one of the values is truthy.
         */
        or: function (a, b, options) {
            if (a || b) {
                return options.fn(this);
            } else {
                return options.inverse(this);
            }
        },
        /**
         * {{#compare}}...{{/compare}}
         *
         * @credit: OOCSS
         * @param left value
         * @param operator The operator, must be between quotes ">", "=", "<=", etc...
         * @param right value
         * @param options option object sent by handlebars
         * @return {String} formatted html
         *
         * @example:
         *   {{#compare unicorns "<" ponies}}
         *     I knew it, unicorns are just low-quality ponies!
         *   {{/compare}}
         *
         *   {{#compare value ">=" 10}}
         *     The value is greater or equal than 10
         *     {{else}}
         *     The value is lower than 10
         *   {{/compare}}
         */
        compare: function (left, operator, right, options) {
            /*jshint eqeqeq: false*/
            if (arguments.length < 3) {
                throw new Error('Handlerbars Helper "compare" needs 2 parameters');
            }
            if (options === undefined) {
                options = right;
                right = operator;
                operator = '===';
            }
            var operators = {
                '==': function (l, r) {
                    return l == r;
                },
                '===': function (l, r) {
                    return l === r;
                },
                '!=': function (l, r) {
                    return l != r;
                },
                '!==': function (l, r) {
                    return l !== r;
                },
                '<': function (l, r) {
                    return l < r;
                },
                '>': function (l, r) {
                    return l > r;
                },
                '<=': function (l, r) {
                    return l <= r;
                },
                '>=': function (l, r) {
                    return l >= r;
                },
                'typeof': function (l, r) {
                    return typeof l == r;
                }
            };
            if (!operators[operator]) {
                throw new Error('Handlerbars Helper "compare" doesn\'t know the operator ' + operator);
            }
            var result = operators[operator](left, right);
            if (result) {
                return options.fn(this);
            } else {
                return options.inverse(this);
            }
        },
        /**
         * {{if_eq}}
         *
         * @author: Dan Harper <http://github.com/danharper>
         *
         * @param  {[type]} context [description]
         * @param  {[type]} options [description]
         * @return {[type]}             [description]
         *
         * @example: {{if_eq this compare=that}}
         */
        if_eq: function (left, right, options) {
            if (left === right) {
                return options.fn(this);
            }
            return options.inverse(this);
        },
        /**
         * {{unless_eq}}
         * @author: Dan Harper <http://github.com/danharper>
         *
         * @param  {[type]} context [description]
         * @param  {[type]} options [description]
         * @return {[type]}             [description]
         *
         * @example: {{unless_eq this compare=that}}
         */
        unless_eq: function (context, options) {
            if (context === options.hash.compare) {
                return options.inverse(this);
            }
            return options.fn(this);
        },
        /**
         * {{if_gt}}
         * @author: Dan Harper <http://github.com/danharper>
         *
         * @param  {[type]} context [description]
         * @param  {[type]} options [description]
         * @return {[type]}             [description]
         *
         * @example: {{if_gt this compare=that}}
         */
        if_gt: function (context, options) {
            if (context > options.hash.compare) {
                return options.fn(this);
            }
            return options.inverse(this);
        },
        /**
         * {{unless_gt}}
         * @author: Dan Harper <http://github.com/danharper>
         *
         * @param  {[type]} context [description]
         * @param  {[type]} options [description]
         * @return {[type]}             [description]
         *
         * @example: {{unless_gt this compare=that}}
         */
        unless_gt: function (context, options) {
            if (context > options.hash.compare) {
                return options.inverse(this);
            }
            return options.fn(this);
        },
        /**
         * {{if_lt}}
         * @author: Dan Harper <http://github.com/danharper>
         *
         * @param  {[type]} context [description]
         * @param  {[type]} options [description]
         * @return {[type]}             [description]
         *
         * @example: {{if_lt this compare=that}}
         */
        if_lt: function (context, options) {
            if (context < options.hash.compare) {
                return options.fn(this);
            }
            return options.inverse(this);
        },
        /**
         * {{unless_lt}}
         * @author: Dan Harper <http://github.com/danharper>
         *
         * @param  {[type]} context [description]
         * @param  {[type]} options [description]
         * @return {[type]}             [description]
         *
         * @example: {{unless_lt this compare=that}}
         */
        unless_lt: function (context, options) {
            if (context < options.hash.compare) {
                return options.inverse(this);
            }
            return options.fn(this);
        },
        /**
         * {{if_gteq}}
         * @author: Dan Harper <http://github.com/danharper>
         *
         * @param  {[type]} context [description]
         * @param  {[type]} options [description]
         * @return {[type]}             [description]
         *
         * @example: {{if_gteq this compare=that}}
         */
        if_gteq: function (context, options) {
            if (context >= options.hash.compare) {
                return options.fn(this);
            }
            return options.inverse(this);
        },
        /**
         * {{unless_gteq}}
         * @author: Dan Harper <http://github.com/danharper>
         *
         * @param  {[type]} context [description]
         * @param  {[type]} options [description]
         * @return {[type]}             [description]
         *
         * @example: {{unless_gteq this compare=that}}
         */
        unless_gteq: function (context, options) {
            if (context >= options.hash.compare) {
                return options.inverse(this);
            }
            return options.fn(this);
        },
        /**
         * {{if_lteq}}
         * @author: Dan Harper <http://github.com/danharper>
         *
         * @param  {[type]} context [description]
         * @param  {[type]} options [description]
         * @return {[type]}             [description]
         *
         * @example: {{if_lteq this compare=that}}
         */
        if_lteq: function (context, options) {
            if (context <= options.hash.compare) {
                return options.fn(this);
            }
            return options.inverse(this);
        },
        /**
         * {{unless_lteq}}
         * @author: Dan Harper <http://github.com/danharper>
         *
         * @param  {[type]} context [description]
         * @param  {[type]} options [description]
         * @return {[type]}             [description]
         *
         * @example: {{unless_lteq this compare=that}}
         */
        unless_lteq: function (context, options) {
            if (context <= options.hash.compare) {
                return options.inverse(this);
            }
            return options.fn(this);
        },
        /**
         * {{ifAny}}
         * Similar to {{#if}} block helper but accepts multiple arguments.
         * @author: Dan Harper <http://github.com/danharper>
         *
         * @param  {[type]} context [description]
         * @param  {[type]} options [description]
         * @return {[type]}             [description]
         *
         * @example: {{ifAny this compare=that}}
         */
        ifAny: function () {
            var argLength = arguments.length - 2;
            var content = arguments[argLength + 1];
            var success = true;
            var i = 0;
            while (i < argLength) {
                if (!arguments[i]) {
                    success = false;
                    break;
                }
                i += 1;
            }
            if (success) {
                return content(this);
            } else {
                return content.inverse(this);
            }
        },
        doesNotMatchAll: function () {
            var argLength = arguments.length - 2;
            var content = arguments[argLength + 1];
            var matchAny = false;
            var i = 1;
            while (i <= argLength) {
                if (arguments[0] === arguments[i]) {
                    matchAny = true;
                    break;
                }
                i += 1;
            }
            if (matchAny) {
                return content.inverse(this);
            } else {
                return content.fn(this);
            }
        }
    };
    // Aliases
    helpers.ifeq = helpers.if_eq;
    helpers.unlessEq = helpers.unless_eq;
    helpers.ifgt = helpers.if_gt;
    helpers.unlessGt = helpers.unless_gt;
    helpers.iflt = helpers.if_lt;
    helpers.unlessLt = helpers.unless_lt;
    helpers.ifgteq = helpers.if_gteq;
    helpers.unlessGtEq = helpers.unless_gteq;
    helpers.ifLtEq = helpers.if_lteq;
    helpers.unlessLtEq = helpers.unless_lteq;
    helpers.srtStartsWith = function (text, startPart, options) {
        // debugger
        if (text && text.toString().startWith(startPart)) {
            return options.fn(this);
        }
        return options.inverse(this);
    }
    // from: http://stackoverflow.com/a/12002281
    helpers.foreach = function (arr, options) {
        if (options.inverse && (!arr || !arr.length))
            return options.inverse(this);
        return arr.map(function (item, index) {
            item.$index = index;
            item.$first = index === 0;
            item.$last = index === arr.length - 1;
            return options.fn(item);
        }).join('');
    }
    // helpers.mpwsPartial = function (partialID, partialData) {
    // }
    helpers.mpwsIsEmpty = function (object, options) {
        return _.isEmpty(object) ? options.fn(this) : options.inverse(this);
    }
    helpers.mpwsIsNotEmpty = function (object, options) {
        return !_.isEmpty(object) ? options.fn(this) : options.inverse(this);
    }
    helpers.in_array = function (array, value, options) {
        return array && _(array).indexOf(value) > -1 ? options.fn(this) : options.inverse(this);
    }
    helpers.has = function (object, key, options) {
        // debugger;
        return _(object).has(key) ? options.fn(this) : options.inverse(this);
    }
    helpers.isNull = function (value, options) {
        return value === null ? options.fn(this) : options.inverse(this);
    }
    // helpers.mpwsPartial = function (templateName, partialData) {
    //     var _partial = Handlebars.partials[templateName];
    //     return new Handlebars.SafeString(Handlebars.compile(_partial)(partialData || this));
    // }
    helpers.mpwsToInt = function (value, context) {
        return parseInt(value, 10);
    }
    helpers.debug = function (optionalValue) {
        console.log("Current Context");
        console.log("====================");
        console.log(this);
        if (optionalValue) {
            console.log("Value");
            console.log("====================");
            console.log(optionalValue);
        }
    }
    helpers.toLowerCase = function (str) {
        return str.toLowerCase();
    }
    helpers.mpwsGetValueByKey = function (dictionary, context) {
        // debugger;
        context = context || {hash: {}};
        var key = context.hash.key || context.key || '',
            prefix = context.hash.prefix || context.prefix || '',
            suffix = context.hash.suffix || context.suffix || '',
            empty = _.isUndefined(context.hash.empty) ? (prefix + key + suffix) : context.empty;
        // console.log('helpers.mpwsGetValueByKey: empty=' + empty);
        // console.log(context.hash);
        return dictionary[prefix + key + suffix] || empty;
    }
    helpers.withItem = function(object, options) {
        // debugger;
        // console.log(object);
        object = object || {};
        return options.fn(object[options.hash.key]);
    }
    // Warning: untested code
    helpers.each_upto = function (ary, max, options) {
        if (!ary || ary.length == 0)
            return options.inverse(this);

        var result = [];
        for (var i = 0; i < max && i < ary.length; ++i)
            result.push(options.fn(ary[i]));
        return result.join('');
    }
    helpers.for = function (from, to, step, options) {
        // if (!ary || ary.length == 0)
        //     return options.inverse(this);
        // debugger
        if (typeof to === "undefined") {
            throw "Neet at least one argument for this loop";
        }

        if (typeof step === "undefined") {
            options = to;
            step = 1;
            to = from;
            from = 0
        }

        if (typeof options === "undefined") {
            options = step;
            step = 1;
        }

        from = from || 0;
        to = to || 0;
        step = step && Math.abs(step) || 1;

        var result = [];
        for (var i = from; i < to; i += step) {
            // debugger
            result.push(options.fn(_.extend({}, this, {
                $index: i,
                $indexPlus1: i + 1,
                $first: i === 0,
                $last: i + step <= to
            })));
        }
        return result.join('');
    }
    var audaciousFn;
    helpers.recursive = function (children, options) {
        var out = '';
        if (typeof options.fn !== "undefined") {
            audaciousFn = options.fn;
        }
        _(children).each(function (child) {
            out = out + audaciousFn(child);
        });
        return out;
    }
    helpers.currency = function (amount, options) {
        if (!options.hash || !options.hash.display || !options.hash.currency) {
            console.warn("Either display or currency is not provided");
            return amount;
        }
        return priceFmt(amount, options.hash.currency, options.hash.display);
    }
    helpers.selectValueByCurrency = function (listWithAmounts, options) {
        if (options.hash.currency && listWithAmounts[options.hash.currency]) {
            return helpers.currency(listWithAmounts[options.hash.currency], options);
        }
    }
    helpers.array_length = function (value) {
        if (_.isArray(value)) {
            return value.length;
        }
        return 0;
    }
    helpers.default_value = function (value, defaultValue) {
        return _.isUndefined(value) || _.isNull(value) ? defaultValue : value;
    }
    helpers._if = function (value, options) {
        if (value.valueOf()) {
            return options.fn(this);
        } else{
            return options.inverse(this);
        }
    }
    helpers.bb_link = function (url, options) {
        var config = options && options.hash || options || {};
        url = url || "";
        url = url.replace(/^(\/#)|^#|^!|^(\/#!)|^\//, '');
        if (!config.skipHash) {
            url = "#!" + url;
        }
        if (config.asRoot) {
            url = '/' + url;
        }
        if (config.fullUrl) {
            if (url[0] !== '/') {
                url = '/' + url;
            }
            url = location.protocol + '//' + location.hostname + url;
        }
        // IF parameter is optional (:xxx)
        // then you may pass _xxx into options
        // to replace round braces
        _(config).each(function (v, k) {
            if (/^_/.test(k))
                url = url.replace("(:" + k.substr(1) + ")", v);
            else
                url = url.replace(":" + k, v);
        });
        if (config.encode) {
            return encodeURIComponent(url);
        }
        return url;
    }
    helpers.matchState = function (pattern, opt) {
        if (new RegExp(pattern, 'i').test(location.hash)) {
            return opt.fn(this);
        } else {
            return opt.inverse(this);
        }
    }
    helpers.encodeURIComponent = function (val) {
        return encodeURIComponent(val);
    }
    helpers.ifAny = function ( /* arg1, arg2, argn, options*/ ) {
        var rez = false,
            args = [].slice.call(arguments),
            options = args.pop();

        _(args).each(function (predicate) {
            rez = rez || predicate;
        });

        if (rez) {
            return options.fn(this);
        } else {
            return options.inverse(this);
        }
    }
    helpers.ifModulEq = function (value, mod, eq, opt) {
        if (value % mod === eq) {
            return opt.fn(this);
        } else {
            return opt.inverse(this);
        }
    }
    helpers.nocacheurl = function (url) {
        return url + (url.indexOf('?') > 0 ? '&' : '?') + 'bust=' + Date.now();
    }

    // Export helpers
    for (var helper in helpers)
        Handlebars.registerHelper(helper, helpers[helper]);


    return helpers;

});