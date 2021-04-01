"use strict";
// plugin setup
var WIDGET_PORTLET = function(elementId, options) {
    // Main object
    var the = this;
    var init = false;

    // Get element object
    var element = THEMEUTIL.get(elementId);
    var body = THEMEUTIL.get('body');

    if (!element) {
        return;
    }

    // Default options
    var defaultOptions = {
        bodyToggleSpeed: 400,
        tooltips: true,
        tools: {
            toggle: {
                collapse: 'Collapse',
                expand: 'Expand'
            },
            reload: 'Reload',
            remove: 'Remove',
            fullscreen: {
                on: 'Fullscreen',
                off: 'Exit Fullscreen'
            }
        },
        sticky: {
            offset: 300,
            zIndex: 101
        }
    };

    ////////////////////////////
    // ** Private Methods  ** //
    ////////////////////////////

    var Plugin = {
        /**
         * Construct
         */

        construct: function(options) {
            if (THEMEUTIL.data(element).has('portlet')) {
                the = THEMEUTIL.data(element).get('portlet');
            } else {
                // reset menu
                Plugin.init(options);

                // build menu
                Plugin.build();

                THEMEUTIL.data(element).set('portlet', the);
            }

            return the;
        },

        /**
         * Init portlet
         */
        init: function(options) {
            the.element = element;
            the.events = [];

            // merge default and user defined options
            the.options = THEMEUTIL.deepExtend({}, defaultOptions, options);
            the.head = THEMEUTIL.child(element, '.kt-portlet__head');
            the.foot = THEMEUTIL.child(element, '.kt-portlet__foot');

            if (THEMEUTIL.child(element, '.kt-portlet__body')) {
                the.body = THEMEUTIL.child(element, '.kt-portlet__body');
            } else if (THEMEUTIL.child(element, '.kt-form')) {
                the.body = THEMEUTIL.child(element, '.kt-form');
            }
        },

        /**
         * Build Form Wizard
         */
        build: function() {
            // Remove
            var remove = THEMEUTIL.find(the.head, '[data-ktportlet-tool=remove]');
            if (remove) {
                THEMEUTIL.addEvent(remove, 'click', function(e) {
                    e.preventDefault();
                    Plugin.remove();
                });
            }

            // Reload
            var reload = THEMEUTIL.find(the.head, '[data-ktportlet-tool=reload]');
            if (reload) {
                THEMEUTIL.addEvent(reload, 'click', function(e) {
                    e.preventDefault();
                    Plugin.reload();
                });
            }

            // Toggle
            var toggle = THEMEUTIL.find(the.head, '[data-ktportlet-tool=toggle]');
            if (toggle) {
                THEMEUTIL.addEvent(toggle, 'click', function(e) {
                    e.preventDefault();
                    Plugin.toggle();
                });
            }

            //== Fullscreen
            var fullscreen = THEMEUTIL.find(the.head, '[data-ktportlet-tool=fullscreen]');
            if (fullscreen) {
                THEMEUTIL.addEvent(fullscreen, 'click', function(e) {
                    e.preventDefault();
                    Plugin.fullscreen();
                });
            }

            Plugin.setupTooltips();
        },

        /**
         * Enable stickt mode
         */
        initSticky: function() {
            var lastScrollTop = 0;
            var offset = the.options.sticky.offset;

            if (!the.head) {
                return;
            }

            window.addEventListener('scroll', Plugin.onScrollSticky);
        },

        /**
         * Window scroll handle event for sticky portlet
         */
        onScrollSticky: function(e) {
            var offset = the.options.sticky.offset;
            if(isNaN(offset)) return;

            var st = document.documentElement.scrollTop;

            if (st >= offset && THEMEUTIL.hasClass(body, 'kt-portlet--sticky') === false) {
                Plugin.eventTrigger('stickyOn');

                THEMEUTIL.addClass(body, 'kt-portlet--sticky');
                THEMEUTIL.addClass(element, 'kt-portlet--sticky');

                Plugin.updateSticky();

            } else if ((st*1.5) <= offset && THEMEUTIL.hasClass(body, 'kt-portlet--sticky')) {
                // back scroll mode
                Plugin.eventTrigger('stickyOff');

                THEMEUTIL.removeClass(body, 'kt-portlet--sticky');
                THEMEUTIL.removeClass(element, 'kt-portlet--sticky');

                Plugin.resetSticky();
            }
        },

        updateSticky: function() {
            if (!the.head) {
                return;
            }

            var top;

            if (THEMEUTIL.hasClass(body, 'kt-portlet--sticky')) {
                if (the.options.sticky.position.top instanceof Function) {
                    top = parseInt(the.options.sticky.position.top.call(this, the));
                } else {
                    top = parseInt(the.options.sticky.position.top);
                }

                var left;
                if (the.options.sticky.position.left instanceof Function) {
                    left = parseInt(the.options.sticky.position.left.call(this, the));
                } else {
                    left = parseInt(the.options.sticky.position.left);
                }

                var right;
                if (the.options.sticky.position.right instanceof Function) {
                    right = parseInt(the.options.sticky.position.right.call(this, the));
                } else {
                    right = parseInt(the.options.sticky.position.right);
                }

                THEMEUTIL.css(the.head, 'z-index', the.options.sticky.zIndex);
                THEMEUTIL.css(the.head, 'top', top + 'px');
                THEMEUTIL.css(the.head, 'left', left + 'px');
                THEMEUTIL.css(the.head, 'right', right + 'px');
            }
        },

        resetSticky: function() {
            if (!the.head) {
                return;
            }

            if (THEMEUTIL.hasClass(body, 'kt-portlet--sticky') === false) {
                THEMEUTIL.css(the.head, 'z-index', '');
                THEMEUTIL.css(the.head, 'top', '');
                THEMEUTIL.css(the.head, 'left', '');
                THEMEUTIL.css(the.head, 'right', '');
            }
        },

        /**
         * Remove portlet
         */
        remove: function() {
            if (Plugin.eventTrigger('beforeRemove') === false) {
                return;
            }

            if (THEMEUTIL.hasClass(body, 'kt-portlet--fullscreen') && THEMEUTIL.hasClass(element, 'kt-portlet--fullscreen')) {
                Plugin.fullscreen('off');
            }

            Plugin.removeTooltips();

            THEMEUTIL.remove(element);

            Plugin.eventTrigger('afterRemove');
        },

        /**
         * Set content
         */
        setContent: function(html) {
            if (html) {
                the.body.innerHTML = html;
            }
        },

        /**
         * Get body
         */
        getBody: function() {
            return the.body;
        },

        /**
         * Get self
         */
        getSelf: function() {
            return element;
        },

        /**
         * Setup tooltips
         */
        setupTooltips: function() {
            if (the.options.tooltips) {
                var collapsed = THEMEUTIL.hasClass(element, 'kt-portlet--collapse') || THEMEUTIL.hasClass(element, 'kt-portlet--collapsed');
                var fullscreenOn = THEMEUTIL.hasClass(body, 'kt-portlet--fullscreen') && THEMEUTIL.hasClass(element, 'kt-portlet--fullscreen');

                //== Remove
                var remove = THEMEUTIL.find(the.head, '[data-ktportlet-tool=remove]');
                if (remove) {
                    var placement = (fullscreenOn ? 'bottom' : 'top');
                    var tip = new Tooltip(remove, {
                        title: the.options.tools.remove,
                        placement: placement,
                        offset: (fullscreenOn ? '0,10px,0,0' : '0,5px'),
                        trigger: 'hover',
                        template: '<div class="tooltip tooltip-portlet tooltip bs-tooltip-' + placement + '" role="tooltip">\
                            <div class="tooltip-arrow arrow"></div>\
                            <div class="tooltip-inner"></div>\
                        </div>'
                    });

                    THEMEUTIL.data(remove).set('tooltip', tip);
                }

                //== Reload
                var reload = THEMEUTIL.find(the.head, '[data-ktportlet-tool=reload]');
                if (reload) {
                    var placement = (fullscreenOn ? 'bottom' : 'top');
                    var tip = new Tooltip(reload, {
                        title: the.options.tools.reload,
                        placement: placement,
                        offset: (fullscreenOn ? '0,10px,0,0' : '0,5px'),
                        trigger: 'hover',
                        template: '<div class="tooltip tooltip-portlet tooltip bs-tooltip-' + placement + '" role="tooltip">\
                            <div class="tooltip-arrow arrow"></div>\
                            <div class="tooltip-inner"></div>\
                        </div>'
                    });

                    THEMEUTIL.data(reload).set('tooltip', tip);
                }

                //== Toggle
                var toggle = THEMEUTIL.find(the.head, '[data-ktportlet-tool=toggle]');
                if (toggle) {
                    var placement = (fullscreenOn ? 'bottom' : 'top');
                    var tip = new Tooltip(toggle, {
                        title: (collapsed ? the.options.tools.toggle.expand : the.options.tools.toggle.collapse),
                        placement: placement,
                        offset: (fullscreenOn ? '0,10px,0,0' : '0,5px'),
                        trigger: 'hover',
                        template: '<div class="tooltip tooltip-portlet tooltip bs-tooltip-' + placement + '" role="tooltip">\
                            <div class="tooltip-arrow arrow"></div>\
                            <div class="tooltip-inner"></div>\
                        </div>'
                    });

                    THEMEUTIL.data(toggle).set('tooltip', tip);
                }

                //== Fullscreen
                var fullscreen = THEMEUTIL.find(the.head, '[data-ktportlet-tool=fullscreen]');
                if (fullscreen) {
                    var placement = (fullscreenOn ? 'bottom' : 'top');
                    var tip = new Tooltip(fullscreen, {
                        title: (fullscreenOn ? the.options.tools.fullscreen.off : the.options.tools.fullscreen.on),
                        placement: placement,
                        offset: (fullscreenOn ? '0,10px,0,0' : '0,5px'),
                        trigger: 'hover',
                        template: '<div class="tooltip tooltip-portlet tooltip bs-tooltip-' + placement + '" role="tooltip">\
                            <div class="tooltip-arrow arrow"></div>\
                            <div class="tooltip-inner"></div>\
                        </div>'
                    });

                    THEMEUTIL.data(fullscreen).set('tooltip', tip);
                }
            }
        },

        /**
         * Setup tooltips
         */
        removeTooltips: function() {
            if (the.options.tooltips) {
                //== Remove
                var remove = THEMEUTIL.find(the.head, '[data-ktportlet-tool=remove]');
                if (remove && THEMEUTIL.data(remove).has('tooltip')) {
                    THEMEUTIL.data(remove).get('tooltip').dispose();
                }

                //== Reload
                var reload = THEMEUTIL.find(the.head, '[data-ktportlet-tool=reload]');
                if (reload && THEMEUTIL.data(reload).has('tooltip')) {
                    THEMEUTIL.data(reload).get('tooltip').dispose();
                }

                //== Toggle
                var toggle = THEMEUTIL.find(the.head, '[data-ktportlet-tool=toggle]');
                if (toggle && THEMEUTIL.data(toggle).has('tooltip')) {
                    THEMEUTIL.data(toggle).get('tooltip').dispose();
                }

                //== Fullscreen
                var fullscreen = THEMEUTIL.find(the.head, '[data-ktportlet-tool=fullscreen]');
                if (fullscreen && THEMEUTIL.data(fullscreen).has('tooltip')) {
                    THEMEUTIL.data(fullscreen).get('tooltip').dispose();
                }
            }
        },

        /**
         * Reload
         */
        reload: function() {
            Plugin.eventTrigger('reload');
        },

        /**
         * Toggle
         */
        toggle: function() {
            if (THEMEUTIL.hasClass(element, 'kt-portlet--collapse') || THEMEUTIL.hasClass(element, 'kt-portlet--collapsed')) {
                Plugin.expand();
            } else {
                Plugin.collapse();
            }
        },

        /**
         * Collapse
         */
        collapse: function() {
            if (Plugin.eventTrigger('beforeCollapse') === false) {
                return;
            }

            THEMEUTIL.slideUp(the.body, the.options.bodyToggleSpeed, function() {
                Plugin.eventTrigger('afterCollapse');
            });

            THEMEUTIL.addClass(element, 'kt-portlet--collapse');

            var toggle = THEMEUTIL.find(the.head, '[data-ktportlet-tool=toggle]');
            if (toggle && THEMEUTIL.data(toggle).has('tooltip')) {
                THEMEUTIL.data(toggle).get('tooltip').updateTitleContent(the.options.tools.toggle.expand);
            }
        },

        /**
         * Expand
         */
        expand: function() {
            if (Plugin.eventTrigger('beforeExpand') === false) {
                return;
            }

            THEMEUTIL.slideDown(the.body, the.options.bodyToggleSpeed, function() {
                Plugin.eventTrigger('afterExpand');
            });

            THEMEUTIL.removeClass(element, 'kt-portlet--collapse');
            THEMEUTIL.removeClass(element, 'kt-portlet--collapsed');

            var toggle = THEMEUTIL.find(the.head, '[data-ktportlet-tool=toggle]');
            if (toggle && THEMEUTIL.data(toggle).has('tooltip')) {
                THEMEUTIL.data(toggle).get('tooltip').updateTitleContent(the.options.tools.toggle.collapse);
            }
        },

        /**
         * fullscreen
         */
        fullscreen: function(mode) {
            var d = {};
            var speed = 300;

            if (mode === 'off' || (THEMEUTIL.hasClass(body, 'kt-portlet--fullscreen') && THEMEUTIL.hasClass(element, 'kt-portlet--fullscreen'))) {
                Plugin.eventTrigger('beforeFullscreenOff');

                THEMEUTIL.removeClass(body, 'kt-portlet--fullscreen');
                THEMEUTIL.removeClass(element, 'kt-portlet--fullscreen');

                Plugin.removeTooltips();
                Plugin.setupTooltips();

                if (the.foot) {
                    THEMEUTIL.css(the.body, 'margin-bottom', '');
                    THEMEUTIL.css(the.foot, 'margin-top', '');
                }

                Plugin.eventTrigger('afterFullscreenOff');
            } else {
                Plugin.eventTrigger('beforeFullscreenOn');

                THEMEUTIL.addClass(element, 'kt-portlet--fullscreen');
                THEMEUTIL.addClass(body, 'kt-portlet--fullscreen');

                Plugin.removeTooltips();
                Plugin.setupTooltips();


                if (the.foot) {
                    var height1 = parseInt(THEMEUTIL.css(the.foot, 'height'));
                    var height2 = parseInt(THEMEUTIL.css(the.foot, 'height')) + parseInt(THEMEUTIL.css(the.head, 'height'));
                    THEMEUTIL.css(the.body, 'margin-bottom', height1 + 'px');
                    THEMEUTIL.css(the.foot, 'margin-top', '-' + height2 + 'px');
                }

                Plugin.eventTrigger('afterFullscreenOn');
            }
        },

        /**
         * Trigger events
         */
        eventTrigger: function(name) {
            //THEMEUTIL.triggerCustomEvent(name);
            for (var i = 0; i < the.events.length; i++) {
                var event = the.events[i];
                if (event.name == name) {
                    if (event.one == true) {
                        if (event.fired == false) {
                            the.events[i].fired = true;
                            event.handler.call(this, the);
                        }
                    } else {
                        event.handler.call(this, the);
                    }
                }
            }
        },

        addEvent: function(name, handler, one) {
            the.events.push({
                name: name,
                handler: handler,
                one: one,
                fired: false
            });

            return the;
        }
    };

    //////////////////////////
    // ** Public Methods ** //
    //////////////////////////

    /**
     * Set default options
     */

    the.setDefaults = function(options) {
        defaultOptions = options;
    };

    /**
     * Remove portlet
     * @returns {WIDGET_PORTLET}
     */
    the.remove = function() {
        return Plugin.remove(html);
    };

    /**
     * Remove portlet
     * @returns {WIDGET_PORTLET}
     */
    the.initSticky = function() {
        return Plugin.initSticky();
    };

    /**
     * Remove portlet
     * @returns {WIDGET_PORTLET}
     */
    the.updateSticky = function() {
        return Plugin.updateSticky();
    };

    /**
     * Remove portlet
     * @returns {WIDGET_PORTLET}
     */
    the.resetSticky = function() {
        return Plugin.resetSticky();
    };

    /**
     * Destroy sticky portlet
     */
    the.destroySticky = function() {
        Plugin.resetSticky();
        window.removeEventListener('scroll', Plugin.onScrollSticky);
    };

    /**
     * Reload portlet
     * @returns {WIDGET_PORTLET}
     */
    the.reload = function() {
        return Plugin.reload();
    };

    /**
     * Set portlet content
     * @returns {WIDGET_PORTLET}
     */
    the.setContent = function(html) {
        return Plugin.setContent(html);
    };

    /**
     * Toggle portlet
     * @returns {WIDGET_PORTLET}
     */
    the.toggle = function() {
        return Plugin.toggle();
    };

    /**
     * Collapse portlet
     * @returns {WIDGET_PORTLET}
     */
    the.collapse = function() {
        return Plugin.collapse();
    };

    /**
     * Expand portlet
     * @returns {WIDGET_PORTLET}
     */
    the.expand = function() {
        return Plugin.expand();
    };

    /**
     * Fullscreen portlet
     * @returns {MPortlet}
     */
    the.fullscreen = function() {
        return Plugin.fullscreen('on');
    };

    /**
     * Fullscreen portlet
     * @returns {MPortlet}
     */
    the.unFullscreen = function() {
        return Plugin.fullscreen('off');
    };

    /**
     * Get portletbody
     * @returns {jQuery}
     */
    the.getBody = function() {
        return Plugin.getBody();
    };

    /**
     * Get portletbody
     * @returns {jQuery}
     */
    the.getSelf = function() {
        return Plugin.getSelf();
    };

    /**
     * Attach event
     */
    the.on = function(name, handler) {
        return Plugin.addEvent(name, handler);
    };

    /**
     * Attach event that will be fired once
     */
    the.one = function(name, handler) {
        return Plugin.addEvent(name, handler, true);
    };

    // Construct plugin
    Plugin.construct.apply(the, [options]);

    return the;
};