/*! Copyright (c) 2013 Brandon Aaron (http://brandon.aaron.sh)
 * Licensed under the MIT License (LICENSE.txt).
 *
 * Version: 3.1.11
 *
 * Requires: jQuery 1.2.2+
 */

(function (factory) {
    if ( typeof define === 'function' && define.amd ) {
        // AMD. Register as an anonymous module.
        define(['jquery'], factory);
    } else if (typeof exports === 'object') {
        // Node/CommonJS style for Browserify
        module.exports = factory;
    } else {
        // Browser globals
        factory(jQuery);
    }
}(function ($) {

    var toFix  = ['wheel', 'mousewheel', 'DOMMouseScroll', 'MozMousePixelScroll'],
        toBind = ( 'onwheel' in document || document.documentMode >= 9 ) ?
                    ['wheel'] : ['mousewheel', 'DomMouseScroll', 'MozMousePixelScroll'],
        slice  = Array.prototype.slice,
        nullLowestDeltaTimeout, lowestDelta;

    if ( $.event.fixHooks ) {
        for ( var i = toFix.length; i; ) {
            $.event.fixHooks[ toFix[--i] ] = $.event.mouseHooks;
        }
    }

    var special = $.event.special.mousewheel = {
        version: '3.1.11',

        setup: function() {
            if ( this.addEventListener ) {
                for ( var i = toBind.length; i; ) {
                    this.addEventListener( toBind[--i], handler, false );
                }
            } else {
                this.onmousewheel = handler;
            }
            // Store the line height and page height for this particular element
            $.data(this, 'mousewheel-line-height', special.getLineHeight(this));
            $.data(this, 'mousewheel-page-height', special.getPageHeight(this));
        },

        teardown: function() {
            if ( this.removeEventListener ) {
                for ( var i = toBind.length; i; ) {
                    this.removeEventListener( toBind[--i], handler, false );
                }
            } else {
                this.onmousewheel = null;
            }
            // Clean up the data we added to the element
            $.removeData(this, 'mousewheel-line-height');
            $.removeData(this, 'mousewheel-page-height');
        },

        getLineHeight: function(elem) {
            var $parent = $(elem)['offsetParent' in $.fn ? 'offsetParent' : 'parent']();
            if (!$parent.length) {
                $parent = $('body');
            }
            return parseInt($parent.css('fontSize'), 10);
        },

        getPageHeight: function(elem) {
            return $(elem).height();
        },

        settings: {
            adjustOldDeltas: true, // see shouldAdjustOldDeltas() below
            normalizeOffset: true  // calls getBoundingClientRect for each event
        }
    };

    $.fn.extend({
        mousewheel: function(fn) {
            return fn ? this.bind('mousewheel', fn) : this.trigger('mousewheel');
        },

        unmousewheel: function(fn) {
            return this.unbind('mousewheel', fn);
        }
    });


    function handler(event) {
        var orgEvent   = event || window.event,
            args       = slice.call(arguments, 1),
            delta      = 0,
            deltaX     = 0,
            deltaY     = 0,
            absDelta   = 0,
            offsetX    = 0,
            offsetY    = 0;
        event = $.event.fix(orgEvent);
        event.type = 'mousewheel';

        // Old school scrollwheel delta
        if ( 'detail'      in orgEvent ) { deltaY = orgEvent.detail * -1;      }
        if ( 'wheelDelta'  in orgEvent ) { deltaY = orgEvent.wheelDelta;       }
        if ( 'wheelDeltaY' in orgEvent ) { deltaY = orgEvent.wheelDeltaY;      }
        if ( 'wheelDeltaX' in orgEvent ) { deltaX = orgEvent.wheelDeltaX * -1; }

        // Firefox < 17 horizontal scrolling related to DOMMouseScroll event
        if ( 'axis' in orgEvent && orgEvent.axis === orgEvent.HORIZONTAL_AXIS ) {
            deltaX = deltaY * -1;
            deltaY = 0;
        }

        // Set delta to be deltaY or deltaX if deltaY is 0 for backwards compatabilitiy
        delta = deltaY === 0 ? deltaX : deltaY;

        // New school wheel delta (wheel event)
        if ( 'deltaY' in orgEvent ) {
            deltaY = orgEvent.deltaY * -1;
            delta  = deltaY;
        }
        if ( 'deltaX' in orgEvent ) {
            deltaX = orgEvent.deltaX;
            if ( deltaY === 0 ) { delta  = deltaX * -1; }
        }

        // No change actually happened, no reason to go any further
        if ( deltaY === 0 && deltaX === 0 ) { return; }

        // Need to convert lines and pages to pixels if we aren't already in pixels
        // There are three delta modes:
        //   * deltaMode 0 is by pixels, nothing to do
        //   * deltaMode 1 is by lines
        //   * deltaMode 2 is by pages
        if ( orgEvent.deltaMode === 1 ) {
            var lineHeight = $.data(this, 'mousewheel-line-height');
            delta  *= lineHeight;
            deltaY *= lineHeight;
            deltaX *= lineHeight;
        } else if ( orgEvent.deltaMode === 2 ) {
            var pageHeight = $.data(this, 'mousewheel-page-height');
            delta  *= pageHeight;
            deltaY *= pageHeight;
            deltaX *= pageHeight;
        }

        // Store lowest absolute delta to normalize the delta values
        absDelta = Math.max( Math.abs(deltaY), Math.abs(deltaX) );

        if ( !lowestDelta || absDelta < lowestDelta ) {
            lowestDelta = absDelta;

            // Adjust older deltas if necessary
            if ( shouldAdjustOldDeltas(orgEvent, absDelta) ) {
                lowestDelta /= 40;
            }
        }

        // Adjust older deltas if necessary
        if ( shouldAdjustOldDeltas(orgEvent, absDelta) ) {
            // Divide all the things by 40!
            delta  /= 40;
            deltaX /= 40;
            deltaY /= 40;
        }

        // Get a whole, normalized value for the deltas
        delta  = Math[ delta  >= 1 ? 'floor' : 'ceil' ](delta  / lowestDelta);
        deltaX = Math[ deltaX >= 1 ? 'floor' : 'ceil' ](deltaX / lowestDelta);
        deltaY = Math[ deltaY >= 1 ? 'floor' : 'ceil' ](deltaY / lowestDelta);

        // Normalise offsetX and offsetY properties
        if ( special.settings.normalizeOffset && this.getBoundingClientRect ) {
            var boundingRect = this.getBoundingClientRect();
            offsetX = event.clientX - boundingRect.left;
            offsetY = event.clientY - boundingRect.top;
        }

        // Add information to the event object
        event.deltaX = deltaX;
        event.deltaY = deltaY;
        event.deltaFactor = lowestDelta;
        event.offsetX = offsetX;
        event.offsetY = offsetY;
        // Go ahead and set deltaMode to 0 since we converted to pixels
        // Although this is a little odd since we overwrite the deltaX/Y
        // properties with normalized deltas.
        event.deltaMode = 0;

        // Add event and delta to the front of the arguments
        args.unshift(event, delta, deltaX, deltaY);

        // Clearout lowestDelta after sometime to better
        // handle multiple device types that give different
        // a different lowestDelta
        // Ex: trackpad = 3 and mouse wheel = 120
        if (nullLowestDeltaTimeout) { clearTimeout(nullLowestDeltaTimeout); }
        nullLowestDeltaTimeout = setTimeout(nullLowestDelta, 200);

        return ($.event.dispatch || $.event.handle).apply(this, args);
    }

    function nullLowestDelta() {
        lowestDelta = null;
    }

    function shouldAdjustOldDeltas(orgEvent, absDelta) {
        // If this is an older event and the delta is divisable by 120,
        // then we are assuming that the browser is treating this as an
        // older mouse wheel event and that we should divide the deltas
        // by 40 to try and get a more usable deltaFactor.
        // Side note, this actually impacts the reported scroll distance
        // in older browsers and can cause scrolling to be slower than native.
        // Turn this off by setting $.event.special.mousewheel.settings.adjustOldDeltas to false.
        return special.settings.adjustOldDeltas && orgEvent.type === 'mousewheel' && absDelta % 120 === 0;
    }

}));




(function($) {
  $.fn.extend({
    stickyMojo: function(options) {

      // Exit if there are no elements to avoid errors:
      if (this.length === 0) {
        return this;
      }

      var settings = $.extend({
        'footerID': '',
        'contentID': '',
        'orientation': $(this).css('float')
      }, options);

      var sticky = {
        'el': $(this),
        'stickyLeft': $(settings.contentID).outerWidth() + $(settings.contentID).offset.left,
        'stickyTop2': $(this).offset().top,
        'stickyHeight': $(this).outerHeight(true),
        'contentHeight': $(settings.contentID).outerHeight(true),
        'win': $(window),
        'breakPoint': $(this).outerWidth(true) + $(settings.contentID).outerWidth(true),
        'marg': parseInt($(this).css('margin-top'), 10)
      };

      var errors = checkSettings();
      cacheElements();

      return this.each(function() {
        buildSticky();
      });

      function buildSticky() {
        if (!errors.length) {
          sticky.el.css('left', sticky.stickyLeft);

          sticky.win.bind({
            'load': stick,
            'scroll': stick,
            'resize': function() {
              sticky.el.css('left', sticky.stickyLeft);
              stick();
            }
          });
        } else {
          if (console && console.warn) {
            console.warn(errors);
          } else {
            alert(errors);
          }
        }
      }

      // Caches the footer and content elements into jquery objects
      function cacheElements() {
        settings.footerID = $(settings.footerID);
        settings.contentID = $(settings.contentID);
      }

      //  Calcualtes the limits top and bottom limits for the sidebar
      function calculateLimits() {
        return {
          limit: settings.footerID.offset().top - sticky.stickyHeight,
          windowTop: sticky.win.scrollTop(),
          stickyTop: sticky.stickyTop2 - sticky.marg
        }
      }

      // Sets sidebar to fixed position
      function setFixedSidebar() {
        sticky.el.css({
          position: 'fixed',
          top: 0
        });
      }

      // Determines the sidebar orientation and sets margins accordingly
      function checkOrientation() {
        if (settings.orientation === "left") {
          settings.contentID.css('margin-left', sticky.el.outerWidth(true));
        } else {
          sticky.el.css('margin-left', settings.contentID.outerWidth(true));
        }
      }

      // sets sidebar to a static positioned element
      function setStaticSidebar() {
        sticky.el.css({
          'position': 'static',
          'margin-left': '0px'
        })
        settings.contentID.css('margin-left', '0px');
      }

      // initiated to stop the sidebar from intersecting the footer
      function setLimitedSidebar(diff) {
        sticky.el.css({
          top: diff
        })
      }

      //determines whether sidebar should stick and applies appropriate settings to make it stick
      function stick() {
        var tops = calculateLimits();
        var hitBreakPoint = tops.stickyTop < tops.windowTop && (sticky.win.width() >= sticky.breakPoint);

        if (hitBreakPoint) {
          setFixedSidebar();
          checkOrientation();
        } else {
          setStaticSidebar();
        }
        if (tops.limit < tops.windowTop) {
          var diff = tops.limit - tops.windowTop;
          setLimitedSidebar(diff);
        }
      }

      // verifies that all settings are correct
      function checkSettings() {
        var errors = [];
        for (var key in settings) {
          if (!settings[key]) {
            errors.push(settings[key]);
          }
        }
        ieVersion() && errors.push("NO IE 7");
        return errors;
      }

      function ieVersion() {
        if(document.querySelector) {
          return false;
        }
        else {
          return true;
        }
      }
    }
  });
})(jQuery);



/*!
 * jQuery Raty - A Star Rating Plugin
 *
 * Licensed under The MIT License
 *
 * @version        2.5.2
 * @author         Washington Botelho
 * @documentation  wbotelhos.com/raty
 *
 */

/*!
 * jQuery Raty - A Star Rating Plugin
 *
 * The MIT License
 *
 * @author  : Washington Botelho
 * @doc     : http://wbotelhos.com/raty
 * @version : 2.7.0
 *
 */

;
(function($) {
    'use strict';

    var methods = {
        init: function(options) {
            return this.each(function() {
                this.self = $(this);

                methods.destroy.call(this.self);

                this.opt = $.extend(true, {}, $.fn.raty.defaults, options);

                methods._adjustCallback.call(this);
                methods._adjustNumber.call(this);
                methods._adjustHints.call(this);

                this.opt.score = methods._adjustedScore.call(this, this.opt.score);

                if (this.opt.starType !== 'img') {
                    methods._adjustStarType.call(this);
                }

                methods._adjustPath.call(this);
                methods._createStars.call(this);

                if (this.opt.cancel) {
                    methods._createCancel.call(this);
                }

                if (this.opt.precision) {
                    methods._adjustPrecision.call(this);
                }

                methods._createScore.call(this);
                methods._apply.call(this, this.opt.score);
                methods._setTitle.call(this, this.opt.score);
                methods._target.call(this, this.opt.score);

                if (this.opt.readOnly) {
                    methods._lock.call(this);
                } else {
                    this.style.cursor = 'pointer';

                    methods._binds.call(this);
                }
            });
        },

        _adjustCallback: function() {
            var options = ['number', 'readOnly', 'score', 'scoreName', 'target'];

            for (var i = 0; i < options.length; i++) {
                if (typeof this.opt[options[i]] === 'function') {
                    this.opt[options[i]] = this.opt[options[i]].call(this);
                }
            }
        },

        _adjustedScore: function(score) {
            if (!score) {
                return score;
            }

            return methods._between(score, 0, this.opt.number);
        },

        _adjustHints: function() {
            if (!this.opt.hints) {
                this.opt.hints = [];
            }

            if (!this.opt.halfShow && !this.opt.half) {
                return;
            }

            var steps = this.opt.precision ? 10 : 2;

            for (var i = 0; i < this.opt.number; i++) {
                var group = this.opt.hints[i];

                if (Object.prototype.toString.call(group) !== '[object Array]') {
                    group = [group];
                }

                this.opt.hints[i] = [];

                for (var j = 0; j < steps; j++) {
                    var
                        hint = group[j],
                        last = group[group.length - 1];

                    if (last === undefined) {
                        last = null;
                    }

                    this.opt.hints[i][j] = hint === undefined ? last : hint;
                }
            }
        },

        _adjustNumber: function() {
            this.opt.number = methods._between(this.opt.number, 1, this.opt.numberMax);
        },

        _adjustPath: function() {
            this.opt.path = this.opt.path || '';

            if (this.opt.path && this.opt.path.charAt(this.opt.path.length - 1) !== '/') {
                this.opt.path += '/';
            }
        },

        _adjustPrecision: function() {
            this.opt.half = true;
        },

        _adjustStarType: function() {
            var replaces = ['cancelOff', 'cancelOn', 'starHalf', 'starOff', 'starOn'];

            this.opt.path = '';

            for (var i = 0; i < replaces.length; i++) {
                this.opt[replaces[i]] = this.opt[replaces[i]].replace('.', '-');
            }
        },

        _apply: function(score) {
            methods._fill.call(this, score);

            if (score) {
                if (score > 0) {
                    this.score.val(score);
                }

                methods._roundStars.call(this, score);
            }
        },

        _between: function(value, min, max) {
            return Math.min(Math.max(parseFloat(value), min), max);
        },

        _binds: function() {
            if (this.cancel) {
                methods._bindOverCancel.call(this);
                methods._bindClickCancel.call(this);
                methods._bindOutCancel.call(this);
            }

            methods._bindOver.call(this);
            methods._bindClick.call(this);
            methods._bindOut.call(this);
        },

        _bindClick: function() {
            var that = this;

            that.stars.on('click.raty', function(evt) {
                var
                    execute = true,
                    score   = (that.opt.half || that.opt.precision) ? that.self.data('score') : (this.alt || $(this).data('alt')),
                    realScore   = (that.opt.half || that.opt.precision) ? that.self.data('score') : ($(this).data('alt'));

                that.opt.realScore = realScore;

                if (that.opt.click) {
                    execute = that.opt.click.call(that, +score, evt);
                }

                if (execute || execute === undefined) {
                    if (that.opt.half && !that.opt.precision) {
                        score = methods._roundHalfScore.call(that, score);
                    }

                    methods._apply.call(that, score);
                }
            });
        },

        _bindClickCancel: function() {
            var that = this;

            that.cancel.on('click.raty', function(evt) {
                that.score.removeAttr('value');

                if (that.opt.click) {
                    that.opt.click.call(that, null, evt);
                }
            });
        },

        _bindOut: function() {
            var that = this;

            that.self.on('mouseleave.raty', function(evt) {
                var score = +that.score.val() || undefined;

                methods._apply.call(that, score);
                methods._target.call(that, score, evt);
                methods._resetTitle.call(that);

                if (that.opt.mouseout) {
                    that.opt.mouseout.call(that, score, evt);
                }
            });
        },

        _bindOutCancel: function() {
            var that = this;

            that.cancel.on('mouseleave.raty', function(evt) {
                var icon = that.opt.cancelOff;

                if (that.opt.starType !== 'img') {
                    icon = that.opt.cancelClass + ' ' + icon;
                }

                methods._setIcon.call(that, this, icon);

                if (that.opt.mouseout) {
                    var score = +that.score.val() || undefined;

                    that.opt.mouseout.call(that, score, evt);
                }
            });
        },

        _bindOver: function() {
            var that   = this,
                action = that.opt.half ? 'mousemove.raty' : 'mouseover.raty';

            that.stars.on(action, function(evt) {
                var score = methods._getScoreByPosition.call(that, evt, this);

                methods._fill.call(that, score, true);

                if (that.opt.half) {
                    methods._roundStars.call(that, score, evt);
                    methods._setTitle.call(that, score, evt);

                    that.self.data('score', score);
                }

                methods._target.call(that, score, evt);

                if (that.opt.mouseover) {
                    that.opt.mouseover.call(that, score, evt);
                }
            });
        },

        _bindOverCancel: function() {
            var that = this;

            that.cancel.on('mouseover.raty', function(evt) {
                var
                    starOff = that.opt.path + that.opt.starOff,
                    icon    = that.opt.cancelOn;

                if (that.opt.starType === 'img') {
                    that.stars.attr('src', starOff);
                } else {
                    icon = that.opt.cancelClass + ' ' + icon;

                    that.stars.attr('class', starOff);
                }

                methods._setIcon.call(that, this, icon);
                methods._target.call(that, null, evt);

                if (that.opt.mouseover) {
                    that.opt.mouseover.call(that, null);
                }
            });
        },

        _buildScoreField: function() {
            return $('<input />', { name: this.opt.scoreName, type: 'hidden' }).appendTo(this);
        },

        _createCancel: function() {
            var icon   = this.opt.path + this.opt.cancelOff,
                cancel = $('<' + this.opt.starType + ' />', { title: this.opt.cancelHint, 'class': this.opt.cancelClass });

            if (this.opt.starType === 'img') {
                cancel.attr({ src: icon, alt: 'x' });
            } else {
                // TODO: use $.data
                cancel.attr('data-alt', 'x').addClass(icon);
            }

            if (this.opt.cancelPlace === 'left') {
                this.self.prepend('&#160;').prepend(cancel);
            } else {
                this.self.append('&#160;').append(cancel);
            }

            this.cancel = cancel;
        },

        _createScore: function() {
            var score = $(this.opt.targetScore);

            this.score = score.length ? score : methods._buildScoreField.call(this);
        },

        _createStars: function() {
            var useSpecialScore = false;
            if($(this).attr("data-score-range")) {
                useSpecialScore = true;
                var ratings = $(this).attr("data-score-range").split(",");
            }
            for (var i = 1; i <= this.opt.number; i++) {
                var
                    name  = methods._nameForIndex.call(this, i),
                    attrs = { alt: i, src: this.opt.path + this.opt[name], 'data-alt': i };
                if(useSpecialScore) {
                    attrs.alt = ratings[i-1];

                }
                if (this.opt.starType !== 'img') {
                    attrs = { 'data-alt': i, 'class': attrs.src }; // TODO: use $.data.
                }

                attrs.title = methods._getHint.call(this, i);

                $('<' + this.opt.starType + ' />', attrs).appendTo(this);

                if (this.opt.space) {
                    this.self.append(i < this.opt.number ? '&#160;' : '');
                }
            }

            this.stars = this.self.children(this.opt.starType);
        },

        _error: function(message) {
            $(this).text(message);

            $.error(message);
        },

        _fill: function(score, supress) {
            var hash = 0;
            var that = this;
            supress = typeof supress !== 'undefined' ? supress : false;

            for (var i = 1; i <= this.stars.length; i++) {
                var
                    icon,
                    star   = this.stars[i - 1],
                    turnOn = methods._turnOn.call(this, i, score);
                if($(this).attr('data-score-range') && !supress) {
                    turnOn = methods._turnOn.call(this,i, that.opt.realScore);
                }

                if (this.opt.iconRange && this.opt.iconRange.length > hash) {
                    var irange = this.opt.iconRange[hash];

                    icon = methods._getRangeIcon.call(this, irange, turnOn);

                    if (i <= irange.range) {
                        methods._setIcon.call(this, star, icon);
                    }

                    if (i === irange.range) {
                        hash++;
                    }
                } else {
                    icon = this.opt[turnOn ? 'starOn' : 'starOff'];

                    methods._setIcon.call(this, star, icon);
                }
            }
        },

        _getFirstDecimal: function(number) {
            var
                decimal = number.toString().split('.')[1],
                result  = 0;

            if (decimal) {
                result = parseInt(decimal.charAt(0), 10);

                if (decimal.slice(1, 5) === '9999') {
                    result++;
                }
            }

            return result;
        },

        _getRangeIcon: function(irange, turnOn) {
            return turnOn ? irange.on || this.opt.starOn : irange.off || this.opt.starOff;
        },

        _getScoreByPosition: function(evt, icon) {
            if($(this).attr('data-score-range')) {
                var score = parseInt(icon.getAttribute('data-alt'), 10);
            } else {
                var score = parseInt(icon.alt || icon.getAttribute('data-alt'), 10);

            }

            if (this.opt.half) {
                var
                    size    = methods._getWidth.call(this),
                    percent = parseFloat((evt.pageX - $(icon).offset().left) / size);

                score = score - 1 + percent;
            }

            return score;
        },

        _getHint: function(score, evt) {
            if (score !== 0 && !score) {
                return this.opt.noRatedMsg;
            }

            var
                decimal = methods._getFirstDecimal.call(this, score),
                integer = Math.ceil(score),
                group   = this.opt.hints[(integer || 1) - 1],
                hint    = group,
                set     = !evt || this.move;

            if (this.opt.precision) {
                if (set) {
                    decimal = decimal === 0 ? 9 : decimal - 1;
                }

                hint = group[decimal];
            } else if (this.opt.halfShow || this.opt.half) {
                decimal = set && decimal === 0 ? 1 : decimal > 5 ? 1 : 0;

                hint = group[decimal];
            }

            return hint === '' ? '' : hint || score;
        },

        _getWidth: function() {
            var width = this.stars[0].width || parseFloat(this.stars.eq(0).css('font-size'));

            if (!width) {
                methods._error.call(this, 'Could not get the icon width!');
            }

            return width;
        },

        _lock: function() {
            var hint = methods._getHint.call(this, this.score.val());

            this.style.cursor = '';
            this.title        = hint;

            this.score.prop('readonly', true);
            this.stars.prop('title', hint);

            if (this.cancel) {
                this.cancel.hide();
            }

            this.self.data('readonly', true);
        },

        _nameForIndex: function(i) {
            return this.opt.score && this.opt.score >= i ? 'starOn' : 'starOff';
        },

        _resetTitle: function(star) {
            for (var i = 0; i < this.opt.number; i++) {
                this.stars[i].title = methods._getHint.call(this, i + 1);
            }
        },

        _roundHalfScore: function(score) {
            var integer = parseInt(score, 10),
                decimal = methods._getFirstDecimal.call(this, score);

            if (decimal !== 0) {
                decimal = decimal > 5 ? 1 : 0.5;
            }

            return integer + decimal;
        },

        _roundStars: function(score, evt) {
            var
                decimal = (score % 1).toFixed(2),
                name    ;

            if (evt || this.move) {
                name = decimal > 0.5 ? 'starOn' : 'starHalf';
            } else if (decimal > this.opt.round.down) {               // Up:   [x.76 .. x.99]
                name = 'starOn';

                if (this.opt.halfShow && decimal < this.opt.round.up) { // Half: [x.26 .. x.75]
                    name = 'starHalf';
                } else if (decimal < this.opt.round.full) {             // Down: [x.00 .. x.5]
                    name = 'starOff';
                }
            }

            if (name) {
                var
                    icon = this.opt[name],
                    star = this.stars[Math.ceil(score) - 1];

                methods._setIcon.call(this, star, icon);
            }                                                         // Full down: [x.00 .. x.25]
        },

        _setIcon: function(star, icon) {
            star[this.opt.starType === 'img' ? 'src' : 'className'] = this.opt.path + icon;
        },

        _setTarget: function(target, score) {
            if (score) {
                score = this.opt.targetFormat.toString().replace('{score}', score);
            }

            if (target.is(':input')) {
                target.val(score);
            } else {
                target.html(score);
            }
        },

        _setTitle: function(score, evt) {
            if (score) {
                var
                    integer = parseInt(Math.ceil(score), 10),
                    star    = this.stars[integer - 1];

                star.title = methods._getHint.call(this, score, evt);
            }
        },

        _target: function(score, evt) {
            if (this.opt.target) {
                var target = $(this.opt.target);

                if (!target.length) {
                    methods._error.call(this, 'Target selector invalid or missing!');
                }

                var mouseover = evt && evt.type === 'mouseover';

                if (score === undefined) {
                    score = this.opt.targetText;
                } else if (score === null) {
                    score = mouseover ? this.opt.cancelHint : this.opt.targetText;
                } else {
                    if (this.opt.targetType === 'hint') {
                        score = methods._getHint.call(this, score, evt);
                    } else if (this.opt.precision) {
                        score = parseFloat(score).toFixed(1);
                    }

                    var mousemove = evt && evt.type === 'mousemove';

                    if (!mouseover && !mousemove && !this.opt.targetKeep) {
                        score = this.opt.targetText;
                    }
                }

                methods._setTarget.call(this, target, score);
            }
        },

        _turnOn: function(i, score) {
            return this.opt.single ? (i === score) : (i <= score);
        },

        _unlock: function() {
            this.style.cursor = 'pointer';
            this.removeAttribute('title');

            this.score.removeAttr('readonly');

            this.self.data('readonly', false);

            for (var i = 0; i < this.opt.number; i++) {
                this.stars[i].title = methods._getHint.call(this, i + 1);
            }

            if (this.cancel) {
                this.cancel.css('display', '');
            }
        },

        cancel: function(click) {
            return this.each(function() {
                var self = $(this);

                if (self.data('readonly') !== true) {
                    methods[click ? 'click' : 'score'].call(self, null);

                    this.score.removeAttr('value');
                }
            });
        },

        click: function(score) {
            return this.each(function() {
                if ($(this).data('readonly') !== true) {
                    score = methods._adjustedScore.call(this, score);

                    methods._apply.call(this, score);

                    if (this.opt.click) {
                        this.opt.click.call(this, score, $.Event('click'));
                    }

                    methods._target.call(this, score);
                }
            });
        },

        destroy: function() {
            return this.each(function() {
                var self = $(this),
                    raw  = self.data('raw');

                if (raw) {
                    self.off('.raty').empty().css({ cursor: raw.style.cursor }).removeData('readonly');
                } else {
                    self.data('raw', self.clone()[0]);
                }
            });
        },

        getScore: function() {
            var score = [],
                value ;

            this.each(function() {
                value = this.score.val();

                score.push(value ? +value : undefined);
            });

            return (score.length > 1) ? score : score[0];
        },

        move: function(score) {
            return this.each(function() {
                var
                    integer  = parseInt(score, 10),
                    decimal  = methods._getFirstDecimal.call(this, score);

                if (integer >= this.opt.number) {
                    integer = this.opt.number - 1;
                    decimal = 10;
                }

                var
                    width   = methods._getWidth.call(this),
                    steps   = width / 10,
                    star    = $(this.stars[integer]),
                    percent = star.offset().left + steps * decimal,
                    evt     = $.Event('mousemove', { pageX: percent });

                this.move = true;

                star.trigger(evt);

                this.move = false;
            });
        },

        readOnly: function(readonly) {
            return this.each(function() {
                var self = $(this);

                if (self.data('readonly') !== readonly) {
                    if (readonly) {
                        self.off('.raty').children('img').off('.raty');

                        methods._lock.call(this);
                    } else {
                        methods._binds.call(this);
                        methods._unlock.call(this);
                    }

                    self.data('readonly', readonly);
                }
            });
        },

        reload: function() {
            return methods.set.call(this, {});
        },

        score: function() {
            var self = $(this);

            return arguments.length ? methods.setScore.apply(self, arguments) : methods.getScore.call(self);
        },

        set: function(options) {
            return this.each(function() {
                $(this).raty($.extend({}, this.opt, options));
            });
        },

        setScore: function(score) {
            return this.each(function() {
                if ($(this).data('readonly') !== true) {
                    score = methods._adjustedScore.call(this, score);

                    methods._apply.call(this, score);
                    methods._target.call(this, score);
                }
            });
        }
    };

    $.fn.raty = function(method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist!');
        }
    };

    $.fn.raty.defaults = {
        cancel       : false,
        cancelClass  : 'raty-cancel',
        cancelHint   : 'Cancel this rating!',
        cancelOff    : 'cancel-off.png',
        cancelOn     : 'cancel-on.png',
        cancelPlace  : 'left',
        click        : undefined,
        half         : false,
        halfShow     : true,
        hints        : ['bad', 'poor', 'regular', 'good', 'gorgeous'],
        iconRange    : undefined,
        mouseout     : undefined,
        mouseover    : undefined,
        noRatedMsg   : 'Not rated yet!',
        number       : 5,
        numberMax    : 20,
        path         : undefined,
        precision    : false,
        readOnly     : false,
        realScore    : 0,
        round        : { down: 0.25, full: 0.6, up: 0.76 },
        score        : undefined,
        scoreName    : 'score',
        single       : false,
        space        : true,
        starHalf     : 'star-half.png',
        starOff      : 'star-off.png',
        starOn       : 'star-on.png',
        starType     : 'img',
        target       : undefined,
        targetFormat : '{score}',
        targetKeep   : false,
        targetScore  : undefined,
        targetText   : '',
        targetType   : 'hint'
    };

})(jQuery);

/*
 * jQuery Easing v1.3 - http://gsgd.co.uk/sandbox/jquery/easing/
 *
 * Uses the built in easing capabilities added In jQuery 1.1
 * to offer multiple easing options
 *
 * TERMS OF USE - jQuery Easing
 * 
 * Open source under the BSD License. 
 * 
 * Copyright ÂŠ 2008 George McGinley Smith
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * Redistributions of source code must retain the above copyright notice, this list of 
 * conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this list 
 * of conditions and the following disclaimer in the documentation and/or other materials 
 * provided with the distribution.
 * 
 * Neither the name of the author nor the names of contributors may be used to endorse 
 * or promote products derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY 
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 *  COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 *  EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
 *  GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED 
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 *  NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE. 
 *
*/

// t: current time, b: begInnIng value, c: change In value, d: duration
jQuery.easing['jswing'] = jQuery.easing['swing'];

jQuery.extend( jQuery.easing,
{
  def: 'easeOutQuad',
  swing: function (x, t, b, c, d) {
    //alert(jQuery.easing.default);
    return jQuery.easing[jQuery.easing.def](x, t, b, c, d);
  },
  easeInQuad: function (x, t, b, c, d) {
    return c*(t/=d)*t + b;
  },
  easeOutQuad: function (x, t, b, c, d) {
    return -c *(t/=d)*(t-2) + b;
  },
  easeInOutQuad: function (x, t, b, c, d) {
    if ((t/=d/2) < 1) return c/2*t*t + b;
    return -c/2 * ((--t)*(t-2) - 1) + b;
  },
  easeInCubic: function (x, t, b, c, d) {
    return c*(t/=d)*t*t + b;
  },
  easeOutCubic: function (x, t, b, c, d) {
    return c*((t=t/d-1)*t*t + 1) + b;
  },
  easeInOutCubic: function (x, t, b, c, d) {
    if ((t/=d/2) < 1) return c/2*t*t*t + b;
    return c/2*((t-=2)*t*t + 2) + b;
  },
  easeInQuart: function (x, t, b, c, d) {
    return c*(t/=d)*t*t*t + b;
  },
  easeOutQuart: function (x, t, b, c, d) {
    return -c * ((t=t/d-1)*t*t*t - 1) + b;
  },
  easeInOutQuart: function (x, t, b, c, d) {
    if ((t/=d/2) < 1) return c/2*t*t*t*t + b;
    return -c/2 * ((t-=2)*t*t*t - 2) + b;
  },
  easeInQuint: function (x, t, b, c, d) {
    return c*(t/=d)*t*t*t*t + b;
  },
  easeOutQuint: function (x, t, b, c, d) {
    return c*((t=t/d-1)*t*t*t*t + 1) + b;
  },
  easeInOutQuint: function (x, t, b, c, d) {
    if ((t/=d/2) < 1) return c/2*t*t*t*t*t + b;
    return c/2*((t-=2)*t*t*t*t + 2) + b;
  },
  easeInSine: function (x, t, b, c, d) {
    return -c * Math.cos(t/d * (Math.PI/2)) + c + b;
  },
  easeOutSine: function (x, t, b, c, d) {
    return c * Math.sin(t/d * (Math.PI/2)) + b;
  },
  easeInOutSine: function (x, t, b, c, d) {
    return -c/2 * (Math.cos(Math.PI*t/d) - 1) + b;
  },
  easeInExpo: function (x, t, b, c, d) {
    return (t==0) ? b : c * Math.pow(2, 10 * (t/d - 1)) + b;
  },
  easeOutExpo: function (x, t, b, c, d) {
    return (t==d) ? b+c : c * (-Math.pow(2, -10 * t/d) + 1) + b;
  },
  easeInOutExpo: function (x, t, b, c, d) {
    if (t==0) return b;
    if (t==d) return b+c;
    if ((t/=d/2) < 1) return c/2 * Math.pow(2, 10 * (t - 1)) + b;
    return c/2 * (-Math.pow(2, -10 * --t) + 2) + b;
  },
  easeInCirc: function (x, t, b, c, d) {
    return -c * (Math.sqrt(1 - (t/=d)*t) - 1) + b;
  },
  easeOutCirc: function (x, t, b, c, d) {
    return c * Math.sqrt(1 - (t=t/d-1)*t) + b;
  },
  easeInOutCirc: function (x, t, b, c, d) {
    if ((t/=d/2) < 1) return -c/2 * (Math.sqrt(1 - t*t) - 1) + b;
    return c/2 * (Math.sqrt(1 - (t-=2)*t) + 1) + b;
  },
  easeInElastic: function (x, t, b, c, d) {
    var s=1.70158;var p=0;var a=c;
    if (t==0) return b;  if ((t/=d)==1) return b+c;  if (!p) p=d*.3;
    if (a < Math.abs(c)) { a=c; var s=p/4; }
    else var s = p/(2*Math.PI) * Math.asin (c/a);
    return -(a*Math.pow(2,10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )) + b;
  },
  easeOutElastic: function (x, t, b, c, d) {
    var s=1.70158;var p=0;var a=c;
    if (t==0) return b;  if ((t/=d)==1) return b+c;  if (!p) p=d*.3;
    if (a < Math.abs(c)) { a=c; var s=p/4; }
    else var s = p/(2*Math.PI) * Math.asin (c/a);
    return a*Math.pow(2,-10*t) * Math.sin( (t*d-s)*(2*Math.PI)/p ) + c + b;
  },
  easeInOutElastic: function (x, t, b, c, d) {
    var s=1.70158;var p=0;var a=c;
    if (t==0) return b;  if ((t/=d/2)==2) return b+c;  if (!p) p=d*(.3*1.5);
    if (a < Math.abs(c)) { a=c; var s=p/4; }
    else var s = p/(2*Math.PI) * Math.asin (c/a);
    if (t < 1) return -.5*(a*Math.pow(2,10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )) + b;
    return a*Math.pow(2,-10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )*.5 + c + b;
  },
  easeInBack: function (x, t, b, c, d, s) {
    if (s == undefined) s = 1.70158;
    return c*(t/=d)*t*((s+1)*t - s) + b;
  },
  easeOutBack: function (x, t, b, c, d, s) {
    if (s == undefined) s = 1.70158;
    return c*((t=t/d-1)*t*((s+1)*t + s) + 1) + b;
  },
  easeInOutBack: function (x, t, b, c, d, s) {
    if (s == undefined) s = 1.70158; 
    if ((t/=d/2) < 1) return c/2*(t*t*(((s*=(1.525))+1)*t - s)) + b;
    return c/2*((t-=2)*t*(((s*=(1.525))+1)*t + s) + 2) + b;
  },
  easeInBounce: function (x, t, b, c, d) {
    return c - jQuery.easing.easeOutBounce (x, d-t, 0, c, d) + b;
  },
  easeOutBounce: function (x, t, b, c, d) {
    if ((t/=d) < (1/2.75)) {
      return c*(7.5625*t*t) + b;
    } else if (t < (2/2.75)) {
      return c*(7.5625*(t-=(1.5/2.75))*t + .75) + b;
    } else if (t < (2.5/2.75)) {
      return c*(7.5625*(t-=(2.25/2.75))*t + .9375) + b;
    } else {
      return c*(7.5625*(t-=(2.625/2.75))*t + .984375) + b;
    }
  },
  easeInOutBounce: function (x, t, b, c, d) {
    if (t < d/2) return jQuery.easing.easeInBounce (x, t*2, 0, c, d) * .5 + b;
    return jQuery.easing.easeOutBounce (x, t*2-d, 0, c, d) * .5 + c*.5 + b;
  }
});

/*
 *
 * TERMS OF USE - EASING EQUATIONS
 * 
 * Open source under the BSD License. 
 * 
 * Copyright ÂŠ 2001 Robert Penner
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * Redistributions of source code must retain the above copyright notice, this list of 
 * conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this list 
 * of conditions and the following disclaimer in the documentation and/or other materials 
 * provided with the distribution.
 * 
 * Neither the name of the author nor the names of contributors may be used to endorse 
 * or promote products derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY 
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 *  COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 *  EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
 *  GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED 
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 *  NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE. 
 *
 */

 ;(function($) {

  $.slidebars = function(options) {

    // ----------------------
    // 001 - Default Settings

    var settings = $.extend({
      siteClose: true, // true or false - Enable closing of Slidebars by clicking on #sb-site.
      siteLock: false, // true or false - Prevent scrolling of site when a Slidebar is open.
      disableOver: false, // integer or false - Hide Slidebars over a specific width.
      hideControlClasses: false, // true or false - Hide controls at same width as disableOver.
      slidebarLinks: 'standard' // 'close' or 'standard' - Links clicked in Slidebars close Slidebars.
    }, options);

    // -----------------------
    // 002 - Feature Detection

    var test = document.createElement('div').style, // Create element to test on.
    supportTransition = false, // Variable for testing transitions.
    supportTransform = false; // variable for testing transforms.

    // Test for CSS Transitions
    if (test.MozTransition === '' || test.WebkitTransition === '' || test.OTransition === '' || test.transition === '') supportTransition = true;

    // Test for CSS Transforms
    if (test.MozTransform === '' || test.WebkitTransform === '' || test.OTransform === '' || test.transform === '') supportTransform = true;

    // -----------------
    // 003 - User Agents

    var ua = navigator.userAgent, // Get user agent string.
    android = false, // Variable for storing android version.
    iOS = false; // Variable for storing iOS version.
    
    if (/Android/.test(ua)) { // Detect Android in user agent string.
      android = ua.substr(ua.indexOf('Android')+8, 3); // Set version of Android.
    } else if (/(iPhone|iPod|iPad)/.test(ua)) { // Detect iOS in user agent string.
      iOS = ua.substr(ua.indexOf('OS ')+3, 3).replace('_', '.'); // Set version of iOS.
    }
    
    if (android && android < 3 || iOS && iOS < 5) $('html').addClass('sb-static'); // Add helper class for older versions of Android & iOS.

    // -----------
    // 004 - Setup

    // Site Container
    var $site = $('#sb-site, .sb-site'); // Cache the selector.

    // Left Slidebar  
    if ($('.sb-left').length) { // Check if the left Slidebar exists.
      var $left = $('.sb-left'), // Cache the selector.
      leftActive = false; // Used to check whether the left Slidebar is open or closed.
    }

    // Right Slidebar
    if ($('.sb-right').length) { // Check if the right Slidebar exists.
      var $right = $('.sb-right'), // Cache the selector.
      rightActive = false; // Used to check whether the right Slidebar is open or closed.
    }
        
    var init = false, // Initialisation variable.
    windowWidth = $(window).width(), // Get width of window.
    $controls = $('.sb-toggle-left, .sb-toggle-right, .sb-open-left, .sb-open-right, .sb-close'), // Cache the control classes.
    $slide = $('.sb-slide'); // Cache users elements to animate.
    
    // Initailise Slidebars
    function initialise() {
      if (!settings.disableOver || (typeof settings.disableOver === 'number' && settings.disableOver >= windowWidth)) { // False or larger than window size. 
        init = true; // true enabled Slidebars to open.
        $('html').addClass('sb-init'); // Add helper class.
        if (settings.hideControlClasses) $controls.removeClass('sb-hide'); // Remove class just incase Slidebars was originally disabled.
        css(); // Set required inline styles.
      } else if (typeof settings.disableOver === 'number' && settings.disableOver < windowWidth) { // Less than window size.
        init = false; // false stop Slidebars from opening.
        $('html').removeClass('sb-init'); // Remove helper class.
        if (settings.hideControlClasses) $controls.addClass('sb-hide'); // Hide controls
        $site.css('minHeight', ''); // Remove minimum height.
        if (leftActive || rightActive) close(); // Close Slidebars if open.
      }
    }
    initialise();
    
    // Inline CSS
    function css() {
      // Set minimum height.
      $site.css('minHeight', ''); // Reset minimum height.
      $site.css('minHeight', $('html').height() + 'px'); // Set minimum height of the site to the minimum height of the html.
      
      // Custom Slidebar widths.
      if ($left && $left.hasClass('sb-width-custom')) $left.css('width', $left.attr('data-sb-width')); // Set user custom width.
      if ($right && $right.hasClass('sb-width-custom')) $right.css('width', $right.attr('data-sb-width')); // Set user custom width.
      
      // Set off-canvas margins for Slidebars with push and overlay animations.
      if ($left && ($left.hasClass('sb-style-push') || $left.hasClass('sb-style-overlay'))) $left.css('marginLeft', '-' + $left.css('width'));
      if ($right && ($right.hasClass('sb-style-push') || $right.hasClass('sb-style-overlay'))) $right.css('marginRight', '-' + $right.css('width'));
      
      // Site lock.
      if (settings.siteLock) $('html').addClass('sb-site-lock');
    }
    
    // Resize Functions
    $(window).resize(function() {
      var resizedWindowWidth = $(window).width(); // Get resized window width.
      if (windowWidth !== resizedWindowWidth) { // Slidebars is running and window was actually resized.
        windowWidth = resizedWindowWidth; // Set the new window width.
        initialise(); // Call initalise to see if Slidebars should still be running.
        if (leftActive) open('left'); // If left Slidebar is open, calling open will ensure it is the correct size.
        if (rightActive) open('right'); // If right Slidebar is open, calling open will ensure it is the correct size.
      }
    });
    // I may include a height check along side a width check here in future.

    // ---------------
    // 005 - Animation

    var animation; // Animation type.

    // Set Animation Type
    if (supportTransition && supportTransform) { // Browser supports css transitions and transforms.
      animation = 'translate'; // Translate for browsers that support it.
      if (android && android < 4.4) animation = 'side'; // Android supports both, but can't translate any fixed positions, so use left instead.
    } else {
      animation = 'jQuery'; // Browsers that don't support css transitions and transitions.
    }

    // Animate Mixin
    function animate(object, amount, side) {
      // Choose selectors depending on animation style.
      var selector;
      
      if (object.hasClass('sb-style-push')) {
        selector = $site.add(object).add($slide); // Push - Animate site, Slidebar and user elements.
      } else if (object.hasClass('sb-style-overlay')) {
        selector = object; // Overlay - Animate Slidebar only.
      } else {
        selector = $site.add($slide); // Reveal - Animate site and user elements.
      }
      
      // Apply Animation
      if (animation === 'translate') {
        selector.css('transform', 'translate(' + amount + ')');
      } else if (animation === 'side') {    
        if (amount[0] === '-') amount = amount.substr(1); // Remove the '-' from the passed amount for side animations.
        selector.css(side, amount);
      } else if (animation === 'jQuery') {
        if (amount[0] === '-') amount = amount.substr(1); // Remove the '-' from the passed amount for jQuery animations.
        var properties = {};
        properties[side] = amount;
        selector.stop().animate(properties, 400); // Stop any current jQuery animation before starting another.
      }
      
      // If closed, remove the inline styling on completion of the animation.
      setTimeout(function() {
        if (amount === '0px') {
          selector.removeAttr('style');
          css();
        }
      }, 400);
    }

    // ----------------
    // 006 - Operations

    // Open a Slidebar
    function open(side) {
      // Check to see if opposite Slidebar is open.
      if (side === 'left' && $left && rightActive || side === 'right' && $right && leftActive) { // It's open, close it, then continue.
        close();
        setTimeout(proceed, 400);
      } else { // Its not open, continue.
        proceed();
      }

      // Open
      function proceed() {
        if (init && side === 'left' && $left) { // Slidebars is initiated, left is in use and called to open.
          $('html').addClass('sb-active sb-active-left'); // Add active classes.
          $left.addClass('sb-active');
          animate($left, $left.css('width'), 'left'); // Animation
          setTimeout(function() { leftActive = true; }, 400); // Set active variables.
        } else if (init && side === 'right' && $right) { // Slidebars is initiated, right is in use and called to open.
          $('html').addClass('sb-active sb-active-right'); // Add active classes.
          $right.addClass('sb-active');
          animate($right, '-' + $right.css('width'), 'right'); // Animation
          setTimeout(function() { rightActive = true; }, 400); // Set active variables.
        }
      }
    }
      
    // Close either Slidebar
    function close(link) {
      if (leftActive || rightActive) { // If a Slidebar is open.
        if (leftActive) {
          animate($left, '0px', 'left'); // Animation
          leftActive = false;
        }
        if (rightActive) {
          animate($right, '0px', 'right'); // Animation
          rightActive = false;
        }
      
        setTimeout(function() { // Wait for closing animation to finish.
          $('html').removeClass('sb-active sb-active-left sb-active-right'); // Remove active classes.
          if ($left) $left.removeClass('sb-active');
          if ($right) $right.removeClass('sb-active');
          if (link) window.location = link; // If a link has been passed to the function, go to it.
        }, 400);
      }
    }
    
    // Toggle either Slidebar
    function toggle(side) {
      if (side === 'left' && $left) { // If left Slidebar is called and in use.
        if (!leftActive) {
          open('left'); // Slidebar is closed, open it.
        } else {
          close(); // Slidebar is open, close it.
        }
      }
      if (side === 'right' && $right) { // If right Slidebar is called and in use.
        if (!rightActive) {
          open('right'); // Slidebar is closed, open it.
        } else {
          close(); // Slidebar is open, close it.
        }
      }
    }

    // ---------
    // 007 - API
    
    this.slidebars = {
      open: open, // Maps user variable name to the open method.
      close: close, // Maps user variable name to the close method.
      toggle: toggle, // Maps user variable name to the toggle method.
      init: function() { // Returns true or false whether Slidebars are running or not.
        return init; // Returns true or false whether Slidebars are running.
      },
      active: function(side) { // Returns true or false whether Slidebar is open or closed.
        if (side === 'left' && $left) return leftActive;
        if (side === 'right' && $right) return rightActive;
      },
      destroy: function(side) { // Removes the Slidebar from the DOM.
        if (side === 'left' && $left) {
          if (leftActive) close(); // Close if its open.
          setTimeout(function() {
            $left.remove(); // Remove it.
            $left = false; // Set variable to false so it cannot be opened again.
          }, 400);
        }
        if (side === 'right' && $right) {
          if (rightActive) close(); // Close if its open.
          setTimeout(function() {
            $right.remove(); // Remove it.
            $right = false; // Set variable to false so it cannot be opened again.
          }, 400);
        }
      }
    };

    // ----------------
    // 008 - User Input
    
    function eventHandler(event, selector) {
      event.stopPropagation(); // Stop event bubbling.
      event.preventDefault(); // Prevent default behaviour
      if (event.type === 'touchend') selector.off('click'); // If event type was touch turn off clicks to prevent phantom clicks.
    }
    
    // Toggle Left Slidebar
    $('.sb-toggle-left').on('touchend click', function(event) {
      eventHandler(event, $(this)); // Handle the event.
      toggle('left'); // Toggle the left Slidbar.
    });
    
    // Toggle Right Slidebar
    $('.sb-toggle-right').on('touchend click', function(event) {
      eventHandler(event, $(this)); // Handle the event.
      toggle('right'); // Toggle the right Slidbar.
    });
    
    // Open Left Slidebar
    $('.sb-open-left').on('touchend click', function(event) {
      eventHandler(event, $(this)); // Handle the event.
      open('left'); // Open the left Slidebar.
    });
    
    // Open Right Slidebar
    $('.sb-open-right').on('touchend click', function(event) {
      eventHandler(event, $(this)); // Handle the event.
      open('right'); // Open the right Slidebar.
    });
    
    // Close a Slidebar
    $('.sb-close').on('touchend click', function(event) {
      eventHandler(event, $(this)); // Handle the event.
      close(); // Close either Slidebar.
    });
    $('.sb-slidebar a.closeSlidebar').on('touchend click', function(event) {
      eventHandler(event, $(this)); // Handle the event.
      close(); // Close either Slidebar.
    });
    
    // Close Slidebar via Link
    $('.sb-slidebar a').not('.sb-disable-close').on('click', function(event) {
      if (settings.slidebarLinks == 'close' || settings.slidebarLinks == 'standard' && $(this).hasClass('sb-enable-close')) {
        eventHandler(event, $(this)); // Handle the event.
        close( $(this).attr('href') ); // Close the Slidebar and pass link.
      }
    });
    
    // Close Slidebar via Site
    $site.on('touchend click', function(event) {
      if (settings.siteClose && (leftActive || rightActive)) { // If settings permit closing by site and left or right Slidebar is open.
        eventHandler(event, $(this)); // Handle the event.
        close(); // Close it.
      }
    });
    
  }; // End slidebars function.

}) (jQuery);



;(function ( $, window, document, undefined ) {
    
    var defaults = {
        orientation: 'left',
        mode: 'push',
        static: false
    };

    // The actual plugin constructor
    function Slidepanel( $element, options ) {
        this.$element = $element;
        this.options = $.extend( {}, defaults, options) ;
        this._defaults = defaults;
        this.init();
    }

    Slidepanel.prototype.init = function () {
        
        var base = this;

        if($('#slidepanel').length == 0){
            var panel_html = '<div id="slidepanel" class="cb_slide_panel"><div class="wrapper"><a href="#" class="close">Close</a><div class="inner"><div class="wrapper"></div></div></div></div>';
            $(panel_html).hide().appendTo($('body'));    
        }

        this.$panel = $('#slidepanel');
        this.$body = $('body');
        this.$body_position = this.$body.css('position');

        //hide the panel and set orientation class for display
        this.$panel.hide().addClass('panel_' + this.options.orientation);
        
        //set current trigger link to false for the current panel
        this.$panel.data('slidepanel-current', false);
        this.$panel.data('slidepanel-loaded', false);
        

        //reset any defined a positions
        this.$panel.css('left', '').css('right', '').css('top', '').css('bottom', '');

        //set a default top value for left and right orientations
        //and set the starting position based on element width
        if(this.options.orientation == 'left' || this.options.orientation == 'right') {
            var options = {};
            options['top'] = 0;
            options[this.options.orientation] = -this.$panel.width();
            this.$panel.css(options);
        }

        //set a default left value for top and bottom orientations
        //and set the starting position based on element height
        if(this.options.orientation == 'top' || this.options.orientation == 'bottom') {
            var options = {};
            options['left'] = 0;
            options[this.options.orientation] = -this.$panel.height();
            this.$panel.css(options);
        }

        //bind click event to trigger ajax load of html content
        //and panel display to any elements that have the attribute rel="panel"
        $(this.$element).on('click', function(e) {
            e.preventDefault();
             
            //if the request mode is static
            if(base.options.static) { 
                //show the panel
                base.expand();
            }
            // if the reques mode is ajax 
            else {
                //load the external html
                base.load();
            };
        });

        //listen for a click on the close buttons for this panel
        $('.close', this.$panel).click(function(e) {
            e.preventDefault();
            base.collapse();
        });
        
    };

    Slidepanel.prototype.load = function() {
            var base = this;
            //if the current trigger element is the element that just triggered a load
            if(this.$panel.data('slidepanel-current') == this.$element) {
                //collapse the current panel
                this.collapse();
                return;
            } else {
                //show the slide panel
                this.expand();
                //get the target url
                var href = $(this.$element).attr('href');

                //prevent an ajax request if the current URL is the the target URL
                if(this.$panel.data('slidepanel-loaded') !== href){
                    //load the content from the target url, and update the panel html
                    $('.inner .wrapper', this.$panel).html('').load(href, function() {
                        //remove the loading indicator
                        base.$panel.removeClass('loading');
                        //set the current loaded URL to the target URL
                        base.$panel.data('slidepanel-loaded', href);
                    });
                //  the current URL is already loaded
                } else {
                    //remove the loading indicator
                    this.$panel.removeClass('loading');
                }
            }
            //set the current source element to this element that triggered the load
            this.$panel.data('slidepanel-current', this.$element);
    };


    Slidepanel.prototype.expand = function() {
        var base = this;
                //set the css properties to animatate

        var panel_options = {};
        var body_options = {};
        panel_options.visible = 'show';
        panel_options[this.options.orientation] = 0;
        body_options[this.options.orientation] = (this.options.orientation == 'top' || this.options.orientation == 'bottom') ? this.$panel.height() : this.$panel.width();
        
        //if the animation mode is set to push, we move the body in relation to the panel
        //else the panel is overlayed on top of the body
        if(this.options.mode == 'push'){
            //animate the body position in relation to the panel dimensions
            this.$body.css('position', 'absolute').animate(body_options, 250);
        }

        //animate the panel into view
        this.$panel.addClass('loading').animate(panel_options, 250, function() {
            //show the panel's close button
            $('.close', base.$panel).fadeIn(250);
        });
    };

    Slidepanel.prototype.collapse = function() {
        //hide the close button for this panel
        $('.close', this.$panel).hide();

        //set the css properties to animatate
        var panel_options = {};
        var body_options = {};
        panel_options.visible = 'hide';
        panel_options[this.options.orientation] = -(this.$panel.width() + 40);
        body_options[this.options.orientation] = 0;
        
        //if the animation mode is push, move the document body back to it's original position
        if(this.options.mode == 'push'){
            this.$body.css('position', this.$body_position).animate(body_options, 250);
        }
        //animate the panel out of view
        this.$panel.animate(panel_options, 250).data('slidepanel-current', false);
    };

    $.fn['slidepanel'] = function ( options ) {
        return this.each(function () {
            if (!$.data(this, 'plugin_slidepanel')) {
                $.data(this, 'plugin_slidepanel', new Slidepanel( this, options ));
            }
        });
    }

})(jQuery, window);

/*
 * jQuery dropdown: A simple dropdown plugin
 *
 *
jQuery&&function(e){function t(t,i){var s=t?e(this):i,o=e(s.attr("data-dropdown")),u=s.hasClass("dropdown-open");if(t){if(e(t.target).hasClass("dropdown-ignore"))return;t.preventDefault();t.stopPropagation()}else if(s!==i.target&&e(i.target).hasClass("dropdown-ignore"))return;n();if(u||s.hasClass("dropdown-disabled"))return;s.addClass("dropdown-open");o.data("dropdown-trigger",s).show();r();o.trigger("show",{dropdown:o,trigger:s})}function n(t){var n=t?e(t.target).parents().addBack():null;if(n&&n.is(".dropdown")){if(!n.is(".dropdown-menu"))return;if(!n.is("A"))return}e(document).find(".dropdown:visible").each(function(){var t=e(this);t.hide().removeData("dropdown-trigger").trigger("hide",{dropdown:t})});e(document).find(".dropdown-open").removeClass("dropdown-open")}function r(){var t=e(".dropdown:visible").eq(0),n=t.data("dropdown-trigger"),r=n?parseInt(n.attr("data-horizontal-offset")||0,10):null,i=n?parseInt(n.attr("data-vertical-offset")||0,10):null;if(t.length===0||!n)return;t.hasClass("dropdown-relative")?t.css({left:t.hasClass("dropdown-anchor-right")?n.position().left-(t.outerWidth(!0)-n.outerWidth(!0))-parseInt(n.css("margin-right"),10)+r:n.position().left+parseInt(n.css("margin-left"),10)+r,top:n.position().top+n.outerHeight(!0)-parseInt(n.css("margin-top"),10)+i}):t.css({left:t.hasClass("dropdown-anchor-right")?n.offset().left-(t.outerWidth()-n.outerWidth())+r:n.offset().left+r,top:n.offset().top+n.outerHeight()+i})}e.extend(e.fn,{dropdown:function(r,i){switch(r){case"show":t(null,e(this));return e(this);case"hide":n();return e(this);case"attach":return e(this).attr("data-dropdown",i);case"detach":n();return e(this).removeAttr("data-dropdown");case"disable":return e(this).addClass("dropdown-disabled");case"enable":n();return e(this).removeClass("dropdown-disabled")}}});e(document).on("click.dropdown","[data-dropdown]",t);e(document).on("click.dropdown",n);e(window).on("resize",r)}(jQuery);
*/


/*www.zoomsl.tw1.ru Sergey Zaragulov skype: deeserge icq: 287295769 sergeland@mail.ru*/
(function(e) {
    var k = !0,
        r = !1;
    e.fn.imagezoomsl = function(d) {
        d = d || {};
        return this.each(function() {
            if (!e(this).is("img")) return k;
            var c = this;
            setTimeout(function() {
                e(new Image).load(function() {
                    y.F(e(c), d)
                }).attr("src", e(c).attr("src"))
            }, 30)
        })
    };
    var y = {};
    e.extend(y, {
        dsetting: {
            loadinggif: "",
            loadopacity: 0.1,
            loadbackground: "#878787",
            cursorshade: k,
            magnifycursor: "crosshair",
            cursorshadecolor: "#fff",
            cursorshadeopacity: 0.3,
            cursorshadeborder: "1px solid black",
            zindex: "",
            stepzoom: 0.5,
            zoomrange: [2, 2],
            zoomstart: 2,
            disablewheel: k,
            showstatus: k,
            showstatustime: 2E3,
            statusdivborder: "1px solid black",
            statusdivbackground: "#C0C0C0",
            statusdivpadding: "4px",
            statusdivfont: "bold 13px Arial",
            statusdivopacity: 0.8,
            magnifierpos: "right",
            magnifiersize: [0, 0],
            magnifiereffectanimate: "showIn",
            innerzoom: r,
            innerzoommagnifier: r,
            descarea: r,
            leftoffset: 15,
            rightoffset: 15,
            switchsides: k,
            magnifierborder: "1px solid black",
            textdnbackground: "#fff",
            textdnpadding: "10px",
            textdnfont: "13px/20px cursive",
            scrollspeedanimate: 5,
            zoomspeedanimate: 7,
            loopspeedanimate: 2.5,
            magnifierspeedanimate: 350,
            classmagnifier: "magnifier",
            classcursorshade: "cursorshade",
            classstatusdiv: "statusdiv",
            classtextdn: "textdn"
        },
        U: -1 != navigator.userAgent.indexOf("MSIE") ? k : r,
        T: function(d) {
            var c = 0,
                a;
            d.parents().add(d).each(function() {
                a = e(this).css("zIndex");
                a = isNaN(a) ? 0 : +a;
                c = Math.max(c, a)
            });
            return c
        },
        L: function(d, c, a) {
            if ("left" == d) return d = -a.f.b * a.k + a.e.b, 0 < c ? 0 : c < d ? d : c;
            d = -a.f.d * a.k + a.e.d;
            return 0 < c ? 0 : c < d ? d : c
        },
        H: function(d) {
            var c = this,
                a = d.data("specs");
            if (a) {
                var e = a.r.offsetsl(),
                    s = c.a.g - e.left,
                    l = c.a.i - e.top;
                c.a.B += (c.a.g -
                    c.a.B) / 2.45342;
                c.a.C += (c.a.i - c.a.C) / 2.45342;
                a.G.css({
                    left: c.a.B - 10,
                    top: c.a.C + 20
                });
                var h = Math.round(a.e.b / a.k),
                    b = Math.round(a.e.d / a.k);
                c.a.z += (s - c.a.z) / a.c.loopspeedanimate;
                c.a.A += (l - c.a.A) / a.c.loopspeedanimate;
                a.K.css({
                    left: a.f.b > h ? Math.min(a.f.b - h, Math.max(0, c.a.z - h / 2)) + e.left - a.w.t.N : e.left - a.w.t.N,
                    top: a.f.d > b ? Math.min(a.f.d - b, Math.max(0, c.a.A - b / 2)) + e.top - a.w.t.R : e.top - a.w.t.R
                });
                a.c.innerzoommagnifier && (c.a.p += (c.a.g - c.a.p) / a.c.loopspeedanimate, c.a.q += (c.a.i - c.a.q) / a.c.loopspeedanimate, a.l.css({
                    left: c.a.p - Math.round(a.e.b / 2),
                    top: c.a.q - Math.round(a.e.d / 2)
                }), a.s.css({
                    left: c.a.p - Math.round(a.e.b / 2),
                    top: c.a.q + a.e.d / 2
                }));
                c.a.u += (s - c.a.u) / a.c.scrollspeedanimate;
                c.a.v += (l - c.a.v) / a.c.scrollspeedanimate;
                a.J.css({
                    left: c.L("left", -c.a.u * a.k + a.e.b / 2, a),
                    top: c.L("top", -c.a.v * a.k + a.e.d / 2, a)
                });
                c.a.n = setTimeout(function() {
                    c.H(d)
                }, 30)
            }
        },
        I: function(d) {
            var c = this,
                a = d.data("specs");
            a && (a.h += (a.k - a.h) / a.c.zoomspeedanimate, a.h = Math.round(1E3 * a.h) / 1E3, a.K.css({
                width: a.f.b > Math.round(a.e.b / a.h) ? Math.round(a.e.b / a.h) : a.f.b,
                height: a.f.d > Math.round(a.e.d / a.h) ? Math.round(a.e.d / a.h) : a.f.d
            }), a.J.css({
                width: Math.round(a.h * a.m.b * (a.f.b / a.m.b)),
                height: Math.round(a.h * a.m.d * (a.f.d / a.m.d))
            }), c.a.o = setTimeout(function() {
                c.I(d)
            }, 30))
        },
        a: {},
        P: function(d) {
            function c() {}
            var a = d.data("specs");
            d = a.c.magnifiersize[0];
            var p = a.c.magnifiersize[1],
                s, l = a.r.offsetsl(),
                h = 0,
                b = 0;
            s = l.left + ("left" === a.c.magnifierpos ? -a.e.b - a.c.leftoffset : a.f.b + a.c.rightoffset);
            a.c.switchsides && !a.c.innerzoom && ("left" !== a.c.magnifierpos && s + a.e.b + a.c.leftoffset >=
                e(window).width() && l.left - a.e.b >= a.c.leftoffset ? s = l.left - a.e.b - a.c.leftoffset : "left" === a.c.magnifierpos && 0 > s && (s = l.left + a.f.b + a.c.rightoffset));
            h = s;
            b = l.top;
            a.l.css({
                visibility: "visible",
                display: "none"
            });
            a.c.descarea && (h = e(a.c.descarea).offsetsl().left, b = e(a.c.descarea).offsetsl().top);
            a.c.innerzoommagnifier && (h = this.a.g - Math.round(a.e.b / 2), b = this.a.i - Math.round(a.e.d / 2));
            c = function() {
                a.s.stop(k, k).fadeIn(a.c.magnifierspeedanimate);
                a.c.innerzoommagnifier || a.s.css({
                    left: h,
                    top: b + p
                })
            };
            a.c.innerzoom &&
                (h = l.left, b = l.top, c = function() {
                a.r.css({
                    visibility: "hidden"
                });
                a.s.css({
                    left: h,
                    top: b + p
                }).stop(k, k).fadeIn(a.c.magnifierspeedanimate)
            });
            switch (a.c.magnifiereffectanimate) {
                case "slideIn":
                    a.l.css({
                        left: h,
                        top: b - p / 3,
                        width: d,
                        height: p
                    }).stop(k, k).show().animate({
                        top: b
                    }, a.c.magnifierspeedanimate, "easeOutBounceSL", c);
                    break;
                case "showIn":
                    a.l.css({
                        left: l.left + Math.round(a.f.b / 2),
                        top: l.top + Math.round(a.f.d / 2),
                        width: Math.round(a.e.b / 5),
                        height: Math.round(a.e.d / 5)
                    }).stop(k, k).show().css({
                        opacity: "0.1"
                    }).animate({
                        left: h,
                        top: b,
                        opacity: "1",
                        width: d,
                        height: p
                    }, a.c.magnifierspeedanimate, c);
                    break;
                default:
                    a.l.css({
                        left: h,
                        top: b,
                        width: d,
                        height: p
                    }).stop(k, k).fadeIn(a.c.magnifierspeedanimate, c)
            }
            a.c.showstatus && (a.Q || a.M) ? a.G.html(a.Q + '<div style="font-size:80%">' + a.M + "</div>").stop(k, k).fadeIn().delay(a.c.showstatustime).fadeOut("slow") : a.G.hide()
        },
        S: function(d) {
            var c = d.data("specs");
            d = c.r.offsetsl();
            switch (c.c.magnifiereffectanimate) {
                case "showIn":
                    c.l.stop(k, k).animate({
                        left: d.left + Math.round(c.f.b / 2),
                        top: d.top + Math.round(c.f.d /
                            2),
                        opacity: "0.1",
                        width: Math.round(c.e.b / 5),
                        height: Math.round(c.e.d / 5)
                    }, c.c.magnifierspeedanimate, function() {
                        c.l.hide()
                    });
                    break;
                default:
                    c.l.stop(k, k).fadeOut(c.c.magnifierspeedanimate)
            }
        },
        F: function(d, c, a) {
            function p() {
                this.i = this.g = 0
            }

            function s(a) {
                g.data("specs", {
                    c: b,
                    Q: y,
                    M: E,
                    r: d,
                    l: u,
                    J: a,
                    G: n,
                    K: q,
                    s: t,
                    f: m,
                    m: {
                        b: a.width(),
                        d: a.height()
                    },
                    e: {
                        b: u.width(),
                        d: u.height()
                    },
                    w: {
                        b: q.width(),
                        d: q.height(),
                        t: {
                            N: parseInt(q.css("border-left-width")) || 0,
                            R: parseInt(q.css("border-top-width")) || 0
                        }
                    },
                    h: B,
                    k: B
                })
            }

            function l(a) {
                return !a.complete ||
                    "undefined" !== typeof a.naturalWidth && 0 === a.naturalWidth ? r : k
            }

            function h(a) {
                var b = a || window.event,
                    c = [].slice.call(arguments, 1),
                    d = 0,
                    f = 0,
                    g = 0,
                    h = 0,
                    h = 0;
                a = e.event.fix(b);
                a.type = "mousewheel";
                b.wheelDelta && (d = b.wheelDelta);
                b.detail && (d = -1 * b.detail);
                b.deltaY && (d = g = -1 * b.deltaY);
                b.deltaX && (f = b.deltaX, d = -1 * f);
                void 0 !== b.wheelDeltaY && (g = b.wheelDeltaY);
                void 0 !== b.wheelDeltaX && (f = -1 * b.wheelDeltaX);
                h = Math.abs(d);
                if (!z || h < z) z = h;
                h = Math.max(Math.abs(g), Math.abs(f));
                if (!w || h < w) w = h;
                b = 0 < d ? "floor" : "ceil";
                d = Math[b](d / z);
                f =
                    Math[b](f / w);
                g = Math[b](g / w);
                c.unshift(a, d, f, g);
                return (e.event.dispatch || e.event.handle).apply(this, c)
            }
            var b = e.extend({}, this.dsetting, c),
                x = b.zindex || this.T(d),
                m = {
                    b: d.width(),
                    d: d.height()
                }, p = new p,
                y = d.attr("data-title") ? d.attr("data-title") : "",
                E = d.attr("data-help") ? d.attr("data-help") : "",
                C = d.attr("data-text-bottom") ? d.attr("data-text-bottom") : "",
                f = this,
                B, v, u, q, n, g, t;
            if (0 === m.d || 0 === m.b) e(new Image).load(function() {
                f.F(d, c)
            }).attr("src", d.attr("src"));
            else {
                d.css({
                    visibility: "visible"
                });
                b.j = d.attr("data-large") ||
                    d.attr("src");
                for (v in b) "" === b[v] && (b[v] = this.dsetting[v]);
                B = b.zoomrange[0] < b.zoomstart ? b.zoomstart : b.zoomrange[0];
                if ("0,0" === b.magnifiersize.toString() || "" === b.magnifiersize.toString()) b.magnifiersize = b.innerzoommagnifier ? [m.b / 2, m.d / 2] : [m.b, m.d];
                b.descarea && e(b.descarea).length ? 0 === e(b.descarea).width() || 0 === e(b.descarea).height() ? b.descarea = r : b.magnifiersize = [e(b.descarea).width(), e(b.descarea).height()] : b.descarea = r;
                b.innerzoom && (b.magnifiersize = [m.b, m.d], c.cursorshade || (b.cursorshade = r), c.scrollspeedanimate ||
                    (b.scrollspeedanimate = 10));
                if (b.innerzoommagnifier) {
                    if (!c.magnifycursor && (window.chrome || window.sidebar)) b.magnifycursor = "none";
                    b.cursorshade = r;
                    b.magnifiereffectanimate = "fadeIn"
                }
                v = ["wheel", "mousewheel", "DOMMouseScroll", "MozMousePixelScroll"];
                var A = "onwheel" in document || 9 <= document.documentMode ? ["wheel"] : ["mousewheel", "DomMouseScroll", "MozMousePixelScroll"],
                    z, w;
                if (e.event.fixHooks)
                    for (var D = v.length; D;) e.event.fixHooks[v[--D]] = e.event.mouseHooks;
                e.event.special.mousewheel = {
                    setup: function() {
                        if (this.addEventListener)
                            for (var a =
                                A.length; a;) this.addEventListener(A[--a], h, r);
                        else this.onmousewheel = h
                    },
                    teardown: function() {
                        if (this.removeEventListener)
                            for (var a = A.length; a;) this.removeEventListener(A[--a], h, r);
                        else this.onmousewheel = null
                    }
                };
                e.fn.offsetsl = function() {
                    var a = this.get(0);
                    if (a.getBoundingClientRect) a = this.offset();
                    else {
                        for (var b = 0, c = 0; a;) b += parseInt(a.offsetTop), c += parseInt(a.offsetLeft), a = a.offsetParent;
                        a = {
                            top: b,
                            left: c
                        }
                    }
                    return a
                };
                e.easing.easeOutBounceSL = function(a, b, c, d, e) {
                    return (b /= e) < 1 / 2.75 ? d * 7.5625 * b * b + c : b < 2 / 2.75 ?
                        d * (7.5625 * (b -= 1.5 / 2.75) * b + 0.75) + c : b < 2.5 / 2.75 ? d * (7.5625 * (b -= 2.25 / 2.75) * b + 0.9375) + c : d * (7.5625 * (b -= 2.625 / 2.75) * b + 0.984375) + c
                };
                u = e("<div />").attr({
                    "class": b.classmagnifier
                }).css({
                    position: "absolute",
                    zIndex: x,
                    width: b.magnifiersize[0],
                    height: b.magnifiersize[1],
                    left: -1E4,
                    top: -1E4,
                    visibility: "hidden",
                    overflow: "hidden"
                }).appendTo(document.body);
                c.classmagnifier || u.css({
                    border: b.magnifierborder
                });
                q = e("<div />");
                b.cursorshade && (q.attr({
                    "class": b.classcursorshade
                }).css({
                    zIndex: x,
                    display: "none",
                    position: "absolute",
                    width: Math.round(b.magnifiersize[0] / b.zoomstart),
                    height: Math.round(b.magnifiersize[1] / b.zoomstart),
                    top: 0,
                    left: 0
                }).appendTo(document.body), c.classcursorshade || q.css({
                    border: b.cursorshadeborder,
                    opacity: b.cursorshadeopacity,
                    backgroundColor: b.cursorshadecolor
                }));
                b.loadinggif || (b.loadinggif = "data:image/gif;base64,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");
                n = e("<div />").attr({
                    "class": b.classstatusdiv + " preloadevt"
                }).css({
                    position: "absolute",
                    display: "none",
                    zIndex: x,
                    top: 0,
                    left: 0
                }).html('<img src="' + b.loadinggif + '" />').appendTo(document.body);
                g = e("<div />").attr({
                    "class": "tracker"
                }).css({
                    zIndex: x,
                    backgroundImage: f.U ? "url(cannotbe)" : "none",
                    position: "absolute",
                    width: m.b,
                    height: m.d,
                    left: a ? d.offsetsl().left : -1E4,
                    top: a ? d.offsetsl().top : -1E4
                }).appendTo(document.body);
                t = e("<div />");
                C && (t.attr({
                    "class": b.classtextdn
                }).css({
                    position: "absolute",
                    zIndex: x,
                    left: 0,
                    top: 0,
                    display: "none"
                }).html(C).appendTo(document.body), c.classtextdn || t.css({
                    border: b.magnifierborder,
                    background: b.textdnbackground,
                    padding: b.textdnpadding,
                    font: b.textdnfont
                }), t.css({
                    width: b.magnifiersize[0] - parseInt(t.css("padding-left")) - parseInt(t.css("padding-right"))
                }));
                g.data("largeimage", b.j);
                e(window).bind("resize", function() {
                    var a = d.offsetsl();
                    g.data("loadimgevt") && g.css({
                        left: a.left,
                        top: a.top
                    });
                    n.filter(".preloadevt").css({
                        left: a.left + m.b / 2 - n.width() / 2,
                        top: a.top + m.d / 2 - n.height() / 2,
                        visibility: "visible"
                    })
                });
                e(document).mousemove(function(a) {
                    f.a.D = a.pageX;
                    f.a.g !== f.a.D && (clearTimeout(f.a.n), clearTimeout(f.a.o), d.css({
                        visibility: "visible"
                    }))
                });
                d.mouseover(function() {
                    var a = d.offsetsl();
                    g.css({
                        left: a.left,
                        top: a.top
                    }).show()
                });
                g.mouseover(function(a) {
                    f.a.g = a.pageX;
                    f.a.i = a.pageY;
                    p.g = a.pageX;
                    p.i = a.pageY;
                    f.a.D = a.pageX;
                    var h = d.offsetsl();
                    a = f.a.g - h.left;
                    h = f.a.i - h.top;
                    f.a.z = a;
                    f.a.A = h;
                    f.a.u = a;
                    f.a.v = h;
                    f.a.p = f.a.g;
                    f.a.q = f.a.i;
                    f.a.B = f.a.g - 10;
                    f.a.C = f.a.i + 20;
                    g.css({
                        cursor: b.magnifycursor
                    });
                    b.j = d.attr("data-large") ||
                        d.attr("src");
                    n.show();
                    clearTimeout(f.a.n);
                    clearTimeout(f.a.o);
                    b.j !== g.data("largeimage") && (e(new Image).load(function() {}).attr("src", b.j), e(g).unbind(), e(n).remove(), e(q).remove(), e(u).remove(), e(g).remove(), e(t).remove(), f.F(d, c, k));
                    g.data("loadevt") && (q.fadeIn(), f.P(g), f.H(g), f.I(g))
                });
                g.mousemove(function(a) {
                    b.j = d.attr("data-large") || d.attr("src");
                    b.j !== g.data("largeimage") && (e(new Image).load(function() {}).attr("src", b.j), e(g).unbind(), e(n).remove(), e(q).remove(), e(u).remove(), e(g).remove(),
                        e(t).remove(), f.F(d, c, k));
                    f.a.g = a.pageX;
                    f.a.i = a.pageY;
                    p.g = a.pageX;
                    p.i = a.pageY;
                    f.a.D = a.pageX
                });
                g.mouseout(function() {
                    clearTimeout(f.a.n);
                    clearTimeout(f.a.o);
                    d.css({
                        visibility: "visible"
                    });
                    t.hide();
                    q.add(n.not(".preloadevt")).stop(k, k).hide()
                });
                g.one("mouseover", function() {
                    var a = d.offsetsl(),
                        h = e('<img src="' + b.j + '"/>').css({
                            position: "relative",
                            maxWidth: "none"
                        }).appendTo(u);
                    f.O[b.j] || (g.css({
                        opacity: b.loadopacity,
                        background: b.loadbackground
                    }), g.data("loadimgevt", k), n.css({
                        left: a.left + m.b / 2 - n.width() / 2,
                        top: a.top + m.d / 2 - n.height() / 2,
                        visibility: "visible"
                    }));
                    h.bind("loadevt", function(a, e) {
                        if ("error" !== e.type) {
                            g.mouseout(function() {
                                f.S(g);
                                clearTimeout(f.a.n);
                                clearTimeout(f.a.o);
                                d.css({
                                    visibility: "visible"
                                });
                                t.hide();
                                g.hide().css({
                                    left: -1E4,
                                    top: -1E4
                                })
                            });
                            g.mouseover(function() {
                                l.h = l.k
                            });
                            g.data("loadimgevt", r);
                            g.css({
                                opacity: 0,
                                cursor: b.magnifycursor
                            });
                            n.empty();
                            c.classstatusdiv || n.css({
                                border: b.statusdivborder,
                                background: b.statusdivbackground,
                                padding: b.statusdivpadding,
                                font: b.statusdivfont,
                                opacity: b.statusdivopacity
                            });
                            n.hide().removeClass("preloadevt");
                            f.O[b.j] = k;
                            s(h);
                            p.g == f.a.D && (q.fadeIn(), f.P(g), clearTimeout(f.a.n), clearTimeout(f.a.o), f.H(g), f.I(g));
                            var l = g.data("specs");
                            h.css({
                                width: b.zoomstart * l.m.b * (m.b / l.m.b),
                                height: b.zoomstart * l.m.d * (m.d / l.m.d)
                            });
                            g.data("loadevt", k);
                            b.zoomrange && b.zoomrange[1] > b.zoomrange[0] ? g.bind("mousewheel", function(a, c) {
                                var d = l.k,
                                    d = "in" == (0 > c ? "out" : "in") ? Math.min(d + b.stepzoom, b.zoomrange[1]) : Math.max(d - b.stepzoom, b.zoomrange[0]);
                                l.k = d;
                                l.V = c;
                                a.preventDefault()
                            }) : b.disablewheel && g.bind("mousewheel",
                                function(a) {
                                    a.preventDefault()
                                })
                        }
                    });
                    l(h.get(0)) ? h.trigger("loadevt", {
                        type: "load"
                    }) : h.bind("load error", function(a) {
                        h.trigger("loadevt", a)
                    })
                })
            }
        },
        O: {}
    })
})(jQuery, window);

/* == jquery mousewheel plugin == Version: 3.1.11, License: MIT License (MIT) */
//!function(a){"function"==typeof define&&define.amd?define(["jquery"],a):"object"==typeof exports?module.exports=a:a(jQuery)}(function(a){function b(b){var g=b||window.event,h=i.call(arguments,1),j=0,l=0,m=0,n=0,o=0,p=0;if(b=a.event.fix(g),b.type="mousewheel","detail"in g&&(m=-1*g.detail),"wheelDelta"in g&&(m=g.wheelDelta),"wheelDeltaY"in g&&(m=g.wheelDeltaY),"wheelDeltaX"in g&&(l=-1*g.wheelDeltaX),"axis"in g&&g.axis===g.HORIZONTAL_AXIS&&(l=-1*m,m=0),j=0===m?l:m,"deltaY"in g&&(m=-1*g.deltaY,j=m),"deltaX"in g&&(l=g.deltaX,0===m&&(j=-1*l)),0!==m||0!==l){if(1===g.deltaMode){var q=a.data(this,"mousewheel-line-height");j*=q,m*=q,l*=q}else if(2===g.deltaMode){var r=a.data(this,"mousewheel-page-height");j*=r,m*=r,l*=r}if(n=Math.max(Math.abs(m),Math.abs(l)),(!f||f>n)&&(f=n,d(g,n)&&(f/=40)),d(g,n)&&(j/=40,l/=40,m/=40),j=Math[j>=1?"floor":"ceil"](j/f),l=Math[l>=1?"floor":"ceil"](l/f),m=Math[m>=1?"floor":"ceil"](m/f),k.settings.normalizeOffset&&this.getBoundingClientRect){var s=this.getBoundingClientRect();o=b.clientX-s.left,p=b.clientY-s.top}return b.deltaX=l,b.deltaY=m,b.deltaFactor=f,b.offsetX=o,b.offsetY=p,b.deltaMode=0,h.unshift(b,j,l,m),e&&clearTimeout(e),e=setTimeout(c,200),(a.event.dispatch||a.event.handle).apply(this,h)}}function c(){f=null}function d(a,b){return k.settings.adjustOldDeltas&&"mousewheel"===a.type&&b%120===0}var e,f,g=["wheel","mousewheel","DOMMouseScroll","MozMousePixelScroll"],h="onwheel"in document||document.documentMode>=9?["wheel"]:["mousewheel","DomMouseScroll","MozMousePixelScroll"],i=Array.prototype.slice;if(a.event.fixHooks)for(var j=g.length;j;)a.event.fixHooks[g[--j]]=a.event.mouseHooks;var k=a.event.special.mousewheel={version:"3.1.11",setup:function(){if(this.addEventListener)for(var c=h.length;c;)this.addEventListener(h[--c],b,!1);else this.onmousewheel=b;a.data(this,"mousewheel-line-height",k.getLineHeight(this)),a.data(this,"mousewheel-page-height",k.getPageHeight(this))},teardown:function(){if(this.removeEventListener)for(var c=h.length;c;)this.removeEventListener(h[--c],b,!1);else this.onmousewheel=null;a.removeData(this,"mousewheel-line-height"),a.removeData(this,"mousewheel-page-height")},getLineHeight:function(b){var c=a(b)["offsetParent"in a.fn?"offsetParent":"parent"]();return c.length||(c=a("body")),parseInt(c.css("fontSize"),10)},getPageHeight:function(b){return a(b).height()},settings:{adjustOldDeltas:!0,normalizeOffset:!0}};a.fn.extend({mousewheel:function(a){return a?this.bind("mousewheel",a):this.trigger("mousewheel")},unmousewheel:function(a){return this.unbind("mousewheel",a)}})});
/*! Copyright (c) 2013 Brandon Aaron (http://brandon.aaron.sh)
 * Licensed under the MIT License (LICENSE.txt).
 *
 * Version: 3.1.11
 *
 * Requires: jQuery 1.2.2+
 */
!function(a){"function"==typeof define&&define.amd?define(["jquery"],a):"object"==typeof exports?module.exports=a:a(jQuery)}(function(a){function b(b){var g=b||window.event,h=i.call(arguments,1),j=0,l=0,m=0,n=0,o=0,p=0;if(b=a.event.fix(g),b.type="mousewheel","detail"in g&&(m=-1*g.detail),"wheelDelta"in g&&(m=g.wheelDelta),"wheelDeltaY"in g&&(m=g.wheelDeltaY),"wheelDeltaX"in g&&(l=-1*g.wheelDeltaX),"axis"in g&&g.axis===g.HORIZONTAL_AXIS&&(l=-1*m,m=0),j=0===m?l:m,"deltaY"in g&&(m=-1*g.deltaY,j=m),"deltaX"in g&&(l=g.deltaX,0===m&&(j=-1*l)),0!==m||0!==l){if(1===g.deltaMode){var q=a.data(this,"mousewheel-line-height");j*=q,m*=q,l*=q}else if(2===g.deltaMode){var r=a.data(this,"mousewheel-page-height");j*=r,m*=r,l*=r}if(n=Math.max(Math.abs(m),Math.abs(l)),(!f||f>n)&&(f=n,d(g,n)&&(f/=40)),d(g,n)&&(j/=40,l/=40,m/=40),j=Math[j>=1?"floor":"ceil"](j/f),l=Math[l>=1?"floor":"ceil"](l/f),m=Math[m>=1?"floor":"ceil"](m/f),k.settings.normalizeOffset&&this.getBoundingClientRect){var s=this.getBoundingClientRect();o=b.clientX-s.left,p=b.clientY-s.top}return b.deltaX=l,b.deltaY=m,b.deltaFactor=f,b.offsetX=o,b.offsetY=p,b.deltaMode=0,h.unshift(b,j,l,m),e&&clearTimeout(e),e=setTimeout(c,200),(a.event.dispatch||a.event.handle).apply(this,h)}}function c(){f=null}function d(a,b){return k.settings.adjustOldDeltas&&"mousewheel"===a.type&&b%120===0}var e,f,g=["wheel","mousewheel","DOMMouseScroll","MozMousePixelScroll"],h="onwheel"in document||document.documentMode>=9?["wheel"]:["mousewheel","DomMouseScroll","MozMousePixelScroll"],i=Array.prototype.slice;if(a.event.fixHooks)for(var j=g.length;j;)a.event.fixHooks[g[--j]]=a.event.mouseHooks;var k=a.event.special.mousewheel={version:"3.1.11",setup:function(){if(this.addEventListener)for(var c=h.length;c;)this.addEventListener(h[--c],b,!1);else this.onmousewheel=b;a.data(this,"mousewheel-line-height",k.getLineHeight(this)),a.data(this,"mousewheel-page-height",k.getPageHeight(this))},teardown:function(){if(this.removeEventListener)for(var c=h.length;c;)this.removeEventListener(h[--c],b,!1);else this.onmousewheel=null;a.removeData(this,"mousewheel-line-height"),a.removeData(this,"mousewheel-page-height")},getLineHeight:function(b){var c=a(b)["offsetParent"in a.fn?"offsetParent":"parent"]();return c.length||(c=a("body")),parseInt(c.css("fontSize"),10)},getPageHeight:function(b){return a(b).height()},settings:{adjustOldDeltas:!0,normalizeOffset:!0}};a.fn.extend({mousewheel:function(a){return a?this.bind("mousewheel",a):this.trigger("mousewheel")},unmousewheel:function(a){return this.unbind("mousewheel",a)}})});

/*
 * transform: A jQuery cssHooks adding cross-browser 2d transform capabilities to $.fn.css() and $.fn.animate()
 *
 * limitations:
 * - requires jQuery 1.4.3+
 * - Should you use the *translate* property, then your elements need to be absolutely positionned in a relatively positionned wrapper **or it will fail in IE678**.
 * - transformOrigin is not accessible
 *
 * latest version and complete README available on Github:
 * https://github.com/louisremi/jquery.transform.js
 *
 * Copyright 2011 @louis_remi
 * Licensed under the MIT license.
 *
 * This saved you an hour of work?
 * Send me music http://www.amazon.co.uk/wishlist/HNTU0468LQON
 *
 */
(function( $, window, document, Math, undefined ) {

/*
 * Feature tests and global variables
 */
var div = document.createElement("div"),
  divStyle = div.style,
  suffix = "Transform",
  testProperties = [
    "O" + suffix,
    "ms" + suffix,
    "Webkit" + suffix,
    "Moz" + suffix
  ],
  i = testProperties.length,
  supportProperty,
  supportMatrixFilter,
  supportFloat32Array = "Float32Array" in window,
  propertyHook,
  propertyGet,
  rMatrix = /Matrix([^)]*)/,
  rAffine = /^\s*matrix\(\s*1\s*,\s*0\s*,\s*0\s*,\s*1\s*(?:,\s*0(?:px)?\s*){2}\)\s*$/,
  _transform = "transform",
  _transformOrigin = "transformOrigin",
  _translate = "translate",
  _rotate = "rotate",
  _scale = "scale",
  _skew = "skew",
  _matrix = "matrix";

// test different vendor prefixes of these properties
while ( i-- ) {
  if ( testProperties[i] in divStyle ) {
    $.support[_transform] = supportProperty = testProperties[i];
    $.support[_transformOrigin] = supportProperty + "Origin";
    continue;
  }
}
// IE678 alternative
if ( !supportProperty ) {
  $.support.matrixFilter = supportMatrixFilter = divStyle.filter === "";
}

// px isn't the default unit of these properties
$.cssNumber[_transform] = $.cssNumber[_transformOrigin] = true;

/*
 * fn.css() hooks
 */
if ( supportProperty && supportProperty != _transform ) {
  // Modern browsers can use jQuery.cssProps as a basic hook
  $.cssProps[_transform] = supportProperty;
  $.cssProps[_transformOrigin] = supportProperty + "Origin";

  // Firefox needs a complete hook because it stuffs matrix with "px"
  if ( supportProperty == "Moz" + suffix ) {
    propertyHook = {
      get: function( elem, computed ) {
        return (computed ?
          // remove "px" from the computed matrix
          $.css( elem, supportProperty ).split("px").join(""):
          elem.style[supportProperty]
        );
      },
      set: function( elem, value ) {
        // add "px" to matrices
        elem.style[supportProperty] = /matrix\([^)p]*\)/.test(value) ?
          value.replace(/matrix((?:[^,]*,){4})([^,]*),([^)]*)/, _matrix+"$1$2px,$3px"):
          value;
      }
    };
  /* Fix two jQuery bugs still present in 1.5.1
   * - rupper is incompatible with IE9, see http://jqbug.com/8346
   * - jQuery.css is not really jQuery.cssProps aware, see http://jqbug.com/8402
   */
  } else if ( /^1\.[0-5](?:\.|$)/.test($.fn.jquery) ) {
    propertyHook = {
      get: function( elem, computed ) {
        return (computed ?
          $.css( elem, supportProperty.replace(/^ms/, "Ms") ):
          elem.style[supportProperty]
        );
      }
    };
  }
  /* TODO: leverage hardware acceleration of 3d transform in Webkit only
  else if ( supportProperty == "Webkit" + suffix && support3dTransform ) {
    propertyHook = {
      set: function( elem, value ) {
        elem.style[supportProperty] = 
          value.replace();
      }
    }
  }*/

} else if ( supportMatrixFilter ) {
  propertyHook = {
    get: function( elem, computed, asArray ) {
      var elemStyle = ( computed && elem.currentStyle ? elem.currentStyle : elem.style ),
        matrix, data;

      if ( elemStyle && rMatrix.test( elemStyle.filter ) ) {
        matrix = RegExp.$1.split(",");
        matrix = [
          matrix[0].split("=")[1],
          matrix[2].split("=")[1],
          matrix[1].split("=")[1],
          matrix[3].split("=")[1]
        ];
      } else {
        matrix = [1,0,0,1];
      }

      if ( ! $.cssHooks[_transformOrigin] ) {
        matrix[4] = elemStyle ? parseInt(elemStyle.left, 10) || 0 : 0;
        matrix[5] = elemStyle ? parseInt(elemStyle.top, 10) || 0 : 0;

      } else {
        data = $._data( elem, "transformTranslate", undefined );
        matrix[4] = data ? data[0] : 0;
        matrix[5] = data ? data[1] : 0;
      }

      return asArray ? matrix : _matrix+"(" + matrix + ")";
    },
    set: function( elem, value, animate ) {
      var elemStyle = elem.style,
        currentStyle,
        Matrix,
        filter,
        centerOrigin;

      if ( !animate ) {
        elemStyle.zoom = 1;
      }

      value = matrix(value);

      // rotate, scale and skew
      Matrix = [
        "Matrix("+
          "M11="+value[0],
          "M12="+value[2],
          "M21="+value[1],
          "M22="+value[3],
          "SizingMethod='auto expand'"
      ].join();
      filter = ( currentStyle = elem.currentStyle ) && currentStyle.filter || elemStyle.filter || "";

      elemStyle.filter = rMatrix.test(filter) ?
        filter.replace(rMatrix, Matrix) :
        filter + " progid:DXImageTransform.Microsoft." + Matrix + ")";

      if ( ! $.cssHooks[_transformOrigin] ) {

        // center the transform origin, from pbakaus's Transformie http://github.com/pbakaus/transformie
        if ( (centerOrigin = $.transform.centerOrigin) ) {
          elemStyle[centerOrigin == "margin" ? "marginLeft" : "left"] = -(elem.offsetWidth/2) + (elem.clientWidth/2) + "px";
          elemStyle[centerOrigin == "margin" ? "marginTop" : "top"] = -(elem.offsetHeight/2) + (elem.clientHeight/2) + "px";
        }

        // translate
        // We assume that the elements are absolute positionned inside a relative positionned wrapper
        elemStyle.left = value[4] + "px";
        elemStyle.top = value[5] + "px";

      } else {
        $.cssHooks[_transformOrigin].set( elem, value );
      }
    }
  };
}
// populate jQuery.cssHooks with the appropriate hook if necessary
if ( propertyHook ) {
  $.cssHooks[_transform] = propertyHook;
}
// we need a unique setter for the animation logic
propertyGet = propertyHook && propertyHook.get || $.css;

/*
 * fn.animate() hooks
 */
$.fx.step.transform = function( fx ) {
  var elem = fx.elem,
    start = fx.start,
    end = fx.end,
    pos = fx.pos,
    transform = "",
    precision = 1E5,
    i, startVal, endVal, unit;

  // fx.end and fx.start need to be converted to interpolation lists
  if ( !start || typeof start === "string" ) {

    // the following block can be commented out with jQuery 1.5.1+, see #7912
    if ( !start ) {
      start = propertyGet( elem, supportProperty );
    }

    // force layout only once per animation
    if ( supportMatrixFilter ) {
      elem.style.zoom = 1;
    }

    // replace "+=" in relative animations (-= is meaningless with transforms)
    end = end.split("+=").join(start);

    // parse both transform to generate interpolation list of same length
    $.extend( fx, interpolationList( start, end ) );
    start = fx.start;
    end = fx.end;
  }

  i = start.length;

  // interpolate functions of the list one by one
  while ( i-- ) {
    startVal = start[i];
    endVal = end[i];
    unit = +false;

    switch ( startVal[0] ) {

      case _translate:
        unit = "px";
      case _scale:
        unit || ( unit = "");

        transform = startVal[0] + "(" +
          Math.round( (startVal[1][0] + (endVal[1][0] - startVal[1][0]) * pos) * precision ) / precision + unit +","+
          Math.round( (startVal[1][1] + (endVal[1][1] - startVal[1][1]) * pos) * precision ) / precision + unit + ")"+
          transform;
        break;

      case _skew + "X":
      case _skew + "Y":
      case _rotate:
        transform = startVal[0] + "(" +
          Math.round( (startVal[1] + (endVal[1] - startVal[1]) * pos) * precision ) / precision +"rad)"+
          transform;
        break;
    }
  }

  fx.origin && ( transform = fx.origin + transform );

  propertyHook && propertyHook.set ?
    propertyHook.set( elem, transform, +true ):
    elem.style[supportProperty] = transform;
};

/*
 * Utility functions
 */

// turns a transform string into its "matrix(A,B,C,D,X,Y)" form (as an array, though)
function matrix( transform ) {
  transform = transform.split(")");
  var
      trim = $.trim
    , i = -1
    // last element of the array is an empty string, get rid of it
    , l = transform.length -1
    , split, prop, val
    , prev = supportFloat32Array ? new Float32Array(6) : []
    , curr = supportFloat32Array ? new Float32Array(6) : []
    , rslt = supportFloat32Array ? new Float32Array(6) : [1,0,0,1,0,0]
    ;

  prev[0] = prev[3] = rslt[0] = rslt[3] = 1;
  prev[1] = prev[2] = prev[4] = prev[5] = 0;

  // Loop through the transform properties, parse and multiply them
  while ( ++i < l ) {
    split = transform[i].split("(");
    prop = trim(split[0]);
    val = split[1];
    curr[0] = curr[3] = 1;
    curr[1] = curr[2] = curr[4] = curr[5] = 0;

    switch (prop) {
      case _translate+"X":
        curr[4] = parseInt(val, 10);
        break;

      case _translate+"Y":
        curr[5] = parseInt(val, 10);
        break;

      case _translate:
        val = val.split(",");
        curr[4] = parseInt(val[0], 10);
        curr[5] = parseInt(val[1] || 0, 10);
        break;

      case _rotate:
        val = toRadian(val);
        curr[0] = Math.cos(val);
        curr[1] = Math.sin(val);
        curr[2] = -Math.sin(val);
        curr[3] = Math.cos(val);
        break;

      case _scale+"X":
        curr[0] = +val;
        break;

      case _scale+"Y":
        curr[3] = val;
        break;

      case _scale:
        val = val.split(",");
        curr[0] = val[0];
        curr[3] = val.length>1 ? val[1] : val[0];
        break;

      case _skew+"X":
        curr[2] = Math.tan(toRadian(val));
        break;

      case _skew+"Y":
        curr[1] = Math.tan(toRadian(val));
        break;

      case _matrix:
        val = val.split(",");
        curr[0] = val[0];
        curr[1] = val[1];
        curr[2] = val[2];
        curr[3] = val[3];
        curr[4] = parseInt(val[4], 10);
        curr[5] = parseInt(val[5], 10);
        break;
    }

    // Matrix product (array in column-major order)
    rslt[0] = prev[0] * curr[0] + prev[2] * curr[1];
    rslt[1] = prev[1] * curr[0] + prev[3] * curr[1];
    rslt[2] = prev[0] * curr[2] + prev[2] * curr[3];
    rslt[3] = prev[1] * curr[2] + prev[3] * curr[3];
    rslt[4] = prev[0] * curr[4] + prev[2] * curr[5] + prev[4];
    rslt[5] = prev[1] * curr[4] + prev[3] * curr[5] + prev[5];

    prev = [rslt[0],rslt[1],rslt[2],rslt[3],rslt[4],rslt[5]];
  }
  return rslt;
}

// turns a matrix into its rotate, scale and skew components
// algorithm from http://hg.mozilla.org/mozilla-central/file/7cb3e9795d04/layout/style/nsStyleAnimation.cpp
function unmatrix(matrix) {
  var
      scaleX
    , scaleY
    , skew
    , A = matrix[0]
    , B = matrix[1]
    , C = matrix[2]
    , D = matrix[3]
    ;

  // Make sure matrix is not singular
  if ( A * D - B * C ) {
    // step (3)
    scaleX = Math.sqrt( A * A + B * B );
    A /= scaleX;
    B /= scaleX;
    // step (4)
    skew = A * C + B * D;
    C -= A * skew;
    D -= B * skew;
    // step (5)
    scaleY = Math.sqrt( C * C + D * D );
    C /= scaleY;
    D /= scaleY;
    skew /= scaleY;
    // step (6)
    if ( A * D < B * C ) {
      A = -A;
      B = -B;
      skew = -skew;
      scaleX = -scaleX;
    }

  // matrix is singular and cannot be interpolated
  } else {
    // In this case the elem shouldn't be rendered, hence scale == 0
    scaleX = scaleY = skew = 0;
  }

  // The recomposition order is very important
  // see http://hg.mozilla.org/mozilla-central/file/7cb3e9795d04/layout/style/nsStyleAnimation.cpp#l971
  return [
    [_translate, [+matrix[4], +matrix[5]]],
    [_rotate, Math.atan2(B, A)],
    [_skew + "X", Math.atan(skew)],
    [_scale, [scaleX, scaleY]]
  ];
}

// build the list of transform functions to interpolate
// use the algorithm described at http://dev.w3.org/csswg/css3-2d-transforms/#animation
function interpolationList( start, end ) {
  var list = {
      start: [],
      end: []
    },
    i = -1, l,
    currStart, currEnd, currType;

  // get rid of affine transform matrix
  ( start == "none" || isAffine( start ) ) && ( start = "" );
  ( end == "none" || isAffine( end ) ) && ( end = "" );

  // if end starts with the current computed style, this is a relative animation
  // store computed style as the origin, remove it from start and end
  if ( start && end && !end.indexOf("matrix") && toArray( start ).join() == toArray( end.split(")")[0] ).join() ) {
    list.origin = start;
    start = "";
    end = end.slice( end.indexOf(")") +1 );
  }

  if ( !start && !end ) { return; }

  // start or end are affine, or list of transform functions are identical
  // => functions will be interpolated individually
  if ( !start || !end || functionList(start) == functionList(end) ) {

    start && ( start = start.split(")") ) && ( l = start.length );
    end && ( end = end.split(")") ) && ( l = end.length );

    while ( ++i < l-1 ) {
      start[i] && ( currStart = start[i].split("(") );
      end[i] && ( currEnd = end[i].split("(") );
      currType = $.trim( ( currStart || currEnd )[0] );

      append( list.start, parseFunction( currType, currStart ? currStart[1] : 0 ) );
      append( list.end, parseFunction( currType, currEnd ? currEnd[1] : 0 ) );
    }

  // otherwise, functions will be composed to a single matrix
  } else {
    list.start = unmatrix(matrix(start));
    list.end = unmatrix(matrix(end))
  }

  return list;
}

function parseFunction( type, value ) {
  var
    // default value is 1 for scale, 0 otherwise
    defaultValue = +(!type.indexOf(_scale)),
    scaleX,
    // remove X/Y from scaleX/Y & translateX/Y, not from skew
    cat = type.replace( /e[XY]/, "e" );

  switch ( type ) {
    case _translate+"Y":
    case _scale+"Y":

      value = [
        defaultValue,
        value ?
          parseFloat( value ):
          defaultValue
      ];
      break;

    case _translate+"X":
    case _translate:
    case _scale+"X":
      scaleX = 1;
    case _scale:

      value = value ?
        ( value = value.split(",") ) && [
          parseFloat( value[0] ),
          parseFloat( value.length>1 ? value[1] : type == _scale ? scaleX || value[0] : defaultValue+"" )
        ]:
        [defaultValue, defaultValue];
      break;

    case _skew+"X":
    case _skew+"Y":
    case _rotate:
      value = value ? toRadian( value ) : 0;
      break;

    case _matrix:
      return unmatrix( value ? toArray(value) : [1,0,0,1,0,0] );
      break;
  }

  return [[ cat, value ]];
}

function isAffine( matrix ) {
  return rAffine.test(matrix);
}

function functionList( transform ) {
  return transform.replace(/(?:\([^)]*\))|\s/g, "");
}

function append( arr1, arr2, value ) {
  while ( value = arr2.shift() ) {
    arr1.push( value );
  }
}

// converts an angle string in any unit to a radian Float
function toRadian(value) {
  return ~value.indexOf("deg") ?
    parseInt(value,10) * (Math.PI * 2 / 360):
    ~value.indexOf("grad") ?
      parseInt(value,10) * (Math.PI/200):
      parseFloat(value);
}

// Converts "matrix(A,B,C,D,X,Y)" to [A,B,C,D,X,Y]
function toArray(matrix) {
  // remove the unit of X and Y for Firefox
  matrix = /([^,]*),([^,]*),([^,]*),([^,]*),([^,p]*)(?:px)?,([^)p]*)(?:px)?/.exec(matrix);
  return [matrix[1], matrix[2], matrix[3], matrix[4], matrix[5], matrix[6]];
}

$.transform = {
  centerOrigin: "margin"
};

})( jQuery, window, document, Math );


(function() {
  var $;

  $ = jQuery;

  $.fn.extend({
    eqHeight: function(column_selector, option) {
      if (option == null) {
        option = {
          equalize_interval: null
        };
      }
      return this.each(function() {
        var columns, equalizer, infinite_equalizing, start_equalizing, timer, _equalize_marked_columns;
        timer = null;
        columns = $(this).find(column_selector);
        if (columns.length === 0) {
          columns = $(this).children(column_selector);
        }
        if (columns.length === 0) {
          return;
        }
        _equalize_marked_columns = function() {
          var marked_columns, max_col_height;
          marked_columns = $(".eqHeight_row");
          max_col_height = 0;
          marked_columns.each(function() {
            return max_col_height = Math.max($(this).height(), max_col_height);
          });
          marked_columns.height(max_col_height);
          return $(".eqHeight_row").removeClass("eqHeight_row");
        };
        equalizer = function() {
          var row_top_value;
          columns.height("auto");
          row_top_value = columns.first().position().top;
          columns.each(function() {
            var current_top;
            current_top = $(this).position().top;
            if (current_top !== row_top_value) {
              _equalize_marked_columns();
              row_top_value = $(this).position().top;
            }
            return $(this).addClass("eqHeight_row");
          });
          return _equalize_marked_columns();
        };
        start_equalizing = function() {
          clearTimeout(timer);
          return timer = setTimeout(equalizer, 100);
        };
        infinite_equalizing = function() {
          equalizer();
          return timer = setTimeout(infinite_equalizing, option.equalize_interval);
        };
        $(window).load(equalizer);
        if (typeof option.equalize_interval === "number") {
          return infinite_equalizing();
        } else {
          return $(window).resize(start_equalizing);
        }
      });
    }
  });

}).call(this);

/*!
 * jQuery Browser Plugin v0.0.6
 * https://github.com/gabceb/jquery-browser-plugin
 *
 * Original jquery-browser code Copyright 2005, 2013 jQuery Foundation, Inc. and other contributors
 * http://jquery.org/license
 *
 * Modifications Copyright 2013 Gabriel Cebrian
 * https://github.com/gabceb
 *
 * Released under the MIT license
 *
 * Date: 2013-07-29T17:23:27-07:00
 */

(function( jQuery, window, undefined ) {
  "use strict";

  var matched, browser;

  jQuery.uaMatch = function( ua ) {
    ua = ua.toLowerCase();

    var match = /(opr)[\/]([\w.]+)/.exec( ua ) ||
      /(chrome)[ \/]([\w.]+)/.exec( ua ) ||
      /(version)[ \/]([\w.]+).*(safari)[ \/]([\w.]+)/.exec( ua ) ||
      /(webkit)[ \/]([\w.]+)/.exec( ua ) ||
      /(opera)(?:.*version|)[ \/]([\w.]+)/.exec( ua ) ||
      /(msie) ([\w.]+)/.exec( ua ) ||
      ua.indexOf("trident") >= 0 && /(rv)(?::| )([\w.]+)/.exec( ua ) ||
      ua.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec( ua ) ||
      [];

    var platform_match = /(ipad)/.exec( ua ) ||
      /(iphone)/.exec( ua ) ||
      /(android)/.exec( ua ) ||
      /(windows phone)/.exec( ua ) ||
      /(win)/.exec( ua ) ||
      /(mac)/.exec( ua ) ||
      /(linux)/.exec( ua ) ||
      /(cros)/i.exec( ua ) ||
      [];

    return {
      browser: match[ 3 ] || match[ 1 ] || "",
      version: match[ 2 ] || "0",
      platform: platform_match[ 0 ] || ""
    };
  };

  matched = jQuery.uaMatch( window.navigator.userAgent );
  browser = {};

  if ( matched.browser ) {
    browser[ matched.browser ] = true;
    browser.version = matched.version;
    browser.versionNumber = parseInt(matched.version);
  }

  if ( matched.platform ) {
    browser[ matched.platform ] = true;
  }

  // These are all considered mobile platforms, meaning they run a mobile browser
  if ( browser.android || browser.ipad || browser.iphone || browser[ "windows phone" ] ) {
    browser.mobile = true;
  }

  // These are all considered desktop platforms, meaning they run a desktop browser
  if ( browser.cros || browser.mac || browser.linux || browser.win ) {
    browser.desktop = true;
  }

  // Chrome, Opera 15+ and Safari are webkit based browsers
  if ( browser.chrome || browser.opr || browser.safari ) {
    browser.webkit = true;
  }

  // IE11 has a new token so we will assign it msie to avoid breaking changes
  if ( browser.rv )
  {
    var ie = "msie";

    matched.browser = ie;
    browser[ie] = true;
  }

  // Opera 15+ are identified as opr
  if ( browser.opr )
  {
    var opera = "opera";

    matched.browser = opera;
    browser[opera] = true;
  }

  // Stock Android browsers are marked as Safari on Android.
  if ( browser.safari && browser.android )
  {
    var android = "android";

    matched.browser = android;
    browser[android] = true;
  }

  // Assign the name and platform variable
  browser.name = matched.browser;
  browser.platform = matched.platform;


  jQuery.browser = browser;
})( jQuery, window );


/**
 * jQuery Plugin to obtain touch gestures from iPhone, iPod Touch and iPad, should also work with Android mobile phones (not tested yet!)
 * Common usage: wipe images (left and right to show the previous or next image)
 * 
 * @author Andreas Waltl, netCU Internetagentur (http://www.netcu.de)
 * @version 1.1.1 (9th December 2010) - fix bug (older IE's had problems)
 * @version 1.1 (1st September 2010) - support wipe up and wipe down
 * @version 1.0 (15th July 2010)
 */
(function($){$.fn.touchwipe=function(settings){var config={min_move_x:20,min_move_y:20,wipeLeft:function(){},wipeRight:function(){},wipeUp:function(){},wipeDown:function(){},preventDefaultEvents:true};if(settings)$.extend(config,settings);this.each(function(){var startX;var startY;var isMoving=false;function cancelTouch(){this.removeEventListener('touchmove',onTouchMove);startX=null;isMoving=false}function onTouchMove(e){if(config.preventDefaultEvents){e.preventDefault()}if(isMoving){var x=e.touches[0].pageX;var y=e.touches[0].pageY;var dx=startX-x;var dy=startY-y;if(Math.abs(dx)>=config.min_move_x){cancelTouch();if(dx>0){config.wipeLeft()}else{config.wipeRight()}}else if(Math.abs(dy)>=config.min_move_y){cancelTouch();if(dy>0){config.wipeDown()}else{config.wipeUp()}}}}function onTouchStart(e){if(e.touches.length==1){startX=e.touches[0].pageX;startY=e.touches[0].pageY;isMoving=true;this.addEventListener('touchmove',onTouchMove,false)}}if('ontouchstart'in document.documentElement){this.addEventListener('touchstart',onTouchStart,false)}});return this}})(jQuery);

/*
Masked Input plugin for jQuery
Copyright (c) 2007-@Year Josh Bush (digitalbush.com)
Licensed under the MIT license (http://digitalbush.com/projects/masked-input-plugin/#license) 
Version: @version
*/
(function ($) {
    var pasteEventName = ($.browser.msie ? 'paste' : 'input') + ".mask";
    var iPhone = (window.orientation != undefined);
    var isAndroid = navigator.userAgent.toLowerCase().indexOf("android") > -1;

    $.mask = {
        //Predefined character definitions
        definitions: {
            '9': "[0-9]",
            'a': "[A-Za-z]",
            '*': "[A-Za-z0-9]"
        },
        dataName: "rawMaskFn"
    };

    $.fn.extend({
        //Helper Function for Caret positioning
        caret: function (begin, end) {
            if (this.length == 0) return;
            if (typeof begin == 'number') {
                end = (typeof end == 'number') ? end : begin;
                return this.each(function () {
                    if (this.setSelectionRange) {
                        if (isAndroid) {
                            var that = this;
                            setTimeout(function () { that.setSelectionRange(begin, end); }, 0);
                        }
                        else {
                            this.setSelectionRange(begin, end);
                        }
                    } else if (this.createTextRange) {
                        var range = this.createTextRange();
                        range.collapse(true);
                        range.moveEnd('character', end);
                        range.moveStart('character', begin);
                        range.select();
                    }
                });
            } else {
                if (this[0].setSelectionRange) {
                    begin = this[0].selectionStart;
                    end = this[0].selectionEnd;
                } else if (document.selection && document.selection.createRange) {
                    var range = document.selection.createRange();
                    begin = 0 - range.duplicate().moveStart('character', -100000);
                    end = begin + range.text.length;
                }
                return { begin: begin, end: end };
            }
        },
        unmask: function () { return this.trigger("unmask"); },
        mask: function (mask, settings) {
            if (!mask && this.length > 0) {
                var input = $(this[0]);
                return input.data($.mask.dataName)();
            }
            settings = $.extend({
                placeholder: "_",
                completed: null
            }, settings);

            var defs = $.mask.definitions;
            var tests = [];
            var partialPosition = mask.length;
            var firstNonMaskPos = null;
            var len = mask.length;

            $.each(mask.split(""), function (i, c) {
                if (c == '?') {
                    len--;
                    partialPosition = i;
                } else if (defs[c]) {
                    tests.push(new RegExp(defs[c]));
                    if (firstNonMaskPos == null)
                        firstNonMaskPos = tests.length - 1;
                } else {
                    tests.push(null);
                }
            });

            return this.trigger("unmask").each(function () {
                var input = $(this);
                var buffer = $.map(mask.split(""), function (c, i) { if (c != '?') return defs[c] ? settings.placeholder : c });
                var focusText = input.val();

                function seekNext(pos) {
                    while (++pos <= len && !tests[pos]);
                    return pos;
                };
                function seekPrev(pos) {
                    while (--pos >= 0 && !tests[pos]);
                    return pos;
                };

                function shiftL(begin, end) {
                    if (begin < 0)
                        return;
                    for (var i = begin, j = seekNext(end); i < len; i++) {
                        if (tests[i]) {
                            if (j < len && tests[i].test(buffer[j])) {
                                buffer[i] = buffer[j];
                                buffer[j] = settings.placeholder;
                            } else
                                break;
                            j = seekNext(j);
                        }
                    }
                    writeBuffer();
                    input.caret(Math.max(firstNonMaskPos, begin));
                };

                function shiftR(pos) {
                    for (var i = pos, c = settings.placeholder; i < len; i++) {
                        if (tests[i]) {
                            var j = seekNext(i);
                            var t = buffer[i];
                            buffer[i] = c;
                            if (j < len && tests[j].test(t))
                                c = t;
                            else
                                break;
                        }
                    }
                };

                function keydownEvent(e) {
                    var k = e.which;

                    //backspace, delete, and escape get special treatment
                    if (k == 8 || k == 46 || (iPhone && k == 127)) {
                        var pos = input.caret(),
              begin = pos.begin,
              end = pos.end;

                        if (end - begin == 0) {
                            begin = k != 46 ? seekPrev(begin) : (end = seekNext(begin - 1));
                            end = k == 46 ? seekNext(end) : end;
                        }
                        clearBuffer(begin, end);
                        shiftL(begin, end - 1);

                        return false;
                    } else if (k == 27) {//escape
                        input.val(focusText);
                        input.caret(0, checkVal());
                        return false;
                    }
                };

                function keypressEvent(e) {
                    var k = e.which,
            pos = input.caret();
                    if (e.ctrlKey || e.altKey || e.metaKey || k < 32) {//Ignore
                        return true;
                    } else if (k) {
                        if (pos.end - pos.begin != 0) {
                            clearBuffer(pos.begin, pos.end);
                            shiftL(pos.begin, pos.end - 1);
                        }

                        var p = seekNext(pos.begin - 1);
                        if (p < len) {
                            var c = String.fromCharCode(k);
                            if (tests[p].test(c)) {
                                shiftR(p);
                                buffer[p] = c;
                                writeBuffer();
                                var next = seekNext(p);
                                input.caret(next);
                                if (settings.completed && next >= len)
                                    settings.completed.call(input);
                            }
                        }
                        return false;
                    }
                };

                function clearBuffer(start, end) {
                    for (var i = start; i < end && i < len; i++) {
                        if (tests[i])
                            buffer[i] = settings.placeholder;
                    }
                };

                function writeBuffer() { return input.val(buffer.join('')).val(); };

                function checkVal(allow) {
                    //try to place characters where they belong
                    var test = input.val();
                    var lastMatch = -1;
                    for (var i = 0, pos = 0; i < len; i++) {
                        if (tests[i]) {
                            buffer[i] = settings.placeholder;
                            while (pos++ < test.length) {
                                var c = test.charAt(pos - 1);
                                if (tests[i].test(c)) {
                                    buffer[i] = c;
                                    lastMatch = i;
                                    break;
                                }
                            }
                            if (pos > test.length)
                                break;
                        } else if (buffer[i] == test.charAt(pos) && i != partialPosition) {
                            pos++;
                            lastMatch = i;
                        }
                    }
                    if (!allow && lastMatch + 1 < partialPosition) {
                        input.val("");
                        clearBuffer(0, len);
                    } else if (allow || lastMatch + 1 >= partialPosition) {
                        writeBuffer();
                        if (!allow) input.val(input.val().substring(0, lastMatch + 1));
                    }
                    return (partialPosition ? i : firstNonMaskPos);
                };

                input.data($.mask.dataName, function () {
                    return $.map(buffer, function (c, i) {
                        return tests[i] && c != settings.placeholder ? c : null;
                    }).join('');
                })

                if (!input.attr("readonly"))
                    input
          .one("unmask", function () {
              input
              .unbind(".mask")
              .removeData($.mask.dataName);
          })
          .bind("focus.mask", function () {
              focusText = input.val();
              var pos = checkVal();
              writeBuffer();
              var moveCaret = function () {
                  if (pos == mask.length)
                      input.caret(0, pos);
                  else
                      input.caret(pos);
              };
              ($.browser.msie ? moveCaret : function () { setTimeout(moveCaret, 0) })();
          })
          .bind("blur.mask", function () {
              checkVal();
              if (input.val() != focusText)
                  input.change();
          })
          .bind("keydown.mask", keydownEvent)
          .bind("keypress.mask", keypressEvent)
          .bind(pasteEventName, function () {
              setTimeout(function () { input.caret(checkVal(true)); }, 0);
          });

                checkVal(); //Perform initial check for existing values
            });
        }
    });
})(jQuery);


(function (root, factory) {
  var moduleName = 'ResizeDimension';
  if (typeof define === 'function' && define.amd) {
    define(['jquery'], function ($) {
        return (root[moduleName] = factory($));
    });
  } else {
    root[moduleName] = factory(root.$);
  }
}(this, function ($) {

  var $window = $(window);

  var ResizeDimension = function ($el, dimension, handler, options) {

    if (! (this instanceof ResizeDimension)) {
      return new ResizeDimension($el, dimension, handler, options);
    }

    this.$el = $el;

    this.init(dimension, handler, options);

    return this;
  };

  /**
   * Stub - overridden on #init()
   */
  ResizeDimension.prototype.onResize = function () {};

  ResizeDimension.bound = {};

  ResizeDimension.bind = function (dimension, options) {
    if (ResizeDimension.bound[dimension]) return;
    ResizeDimension.bound[dimension] = true;
    $window.resizeDimension(dimension, function () {
      $window.trigger('resize-' + dimension);
    }, options);
  };

  ResizeDimension.prototype.init = function (dimension, handler, options) {

    if (typeof dimension === 'object') {
      options = dimension;
      dimension = options.dimension;
      handler = options.handler;
    }

    options = $.extend({}, options);
    options.dimension = dimension;
    options.handler = handler;

    this.options = options;

    if ($.isFunction(options.changed)) {
      this.changed = options.changed;
    }

    this.dimension = this.normalize(options.dimension);
    this.handler = options.handler;
    this.previousValue = this.value();

    var proxied = $.proxy(this.handle, this);
    if (options.throttler) {
      this.onResize = options.throttler(proxied);
    }
    else {
      this.onResize = proxied;
    }
  };

  ResizeDimension.prototype.normalize = function (dimension) {
    return dimension;
  };
  ResizeDimension.prototype.changed = function (previous, current) {
    return previous !== current;
  };

  ResizeDimension.prototype.value = function (e) {
    return this.$el[this.dimension]();
  };

  ResizeDimension.prototype.handle = function (e) {
    var currentValue = this.value();
    if (this.changed(this.previousValue, currentValue)) {
      this.previousValue = currentValue;
      return this.handler.apply(this.$el, e);
    }
  };

  var $resizeDimension = function () {
    var args = Array.prototype.slice.call(arguments);
    return this.each( function() {
      var $el = $(this);
      args = [$el].concat(args);
      var instance = ResizeDimension.apply(null, args);
      $el.on('resize', $.proxy(instance.onResize, instance));
    });
  };

  $.fn.resizeDimension = $resizeDimension;

  return ResizeDimension;

}));


/*! jQuery Validation Plugin - v1.13.0 - 7/1/2014
 * http://jqueryvalidation.org/
 * Copyright (c) 2014 JĂśrn Zaefferer; Licensed MIT */
!function(a){"function"==typeof define&&define.amd?define(["jquery"],a):a(jQuery)}(function(a){a.extend(a.fn,{validate:function(b){if(!this.length)return void(b&&b.debug&&window.console&&console.warn("Nothing selected, can't validate, returning nothing."));var c=a.data(this[0],"validator");return c?c:(this.attr("novalidate","novalidate"),c=new a.validator(b,this[0]),a.data(this[0],"validator",c),c.settings.onsubmit&&(this.validateDelegate(":submit","click",function(b){c.settings.submitHandler&&(c.submitButton=b.target),a(b.target).hasClass("cancel")&&(c.cancelSubmit=!0),void 0!==a(b.target).attr("formnovalidate")&&(c.cancelSubmit=!0)}),this.submit(function(b){function d(){var d;return c.settings.submitHandler?(c.submitButton&&(d=a("<input type='hidden'/>").attr("name",c.submitButton.name).val(a(c.submitButton).val()).appendTo(c.currentForm)),c.settings.submitHandler.call(c,c.currentForm,b),c.submitButton&&d.remove(),!1):!0}return c.settings.debug&&b.preventDefault(),c.cancelSubmit?(c.cancelSubmit=!1,d()):c.form()?c.pendingRequest?(c.formSubmitted=!0,!1):d():(c.focusInvalid(),!1)})),c)},valid:function(){var b,c;return a(this[0]).is("form")?b=this.validate().form():(b=!0,c=a(this[0].form).validate(),this.each(function(){b=c.element(this)&&b})),b},removeAttrs:function(b){var c={},d=this;return a.each(b.split(/\s/),function(a,b){c[b]=d.attr(b),d.removeAttr(b)}),c},rules:function(b,c){var d,e,f,g,h,i,j=this[0];if(b)switch(d=a.data(j.form,"validator").settings,e=d.rules,f=a.validator.staticRules(j),b){case"add":a.extend(f,a.validator.normalizeRule(c)),delete f.messages,e[j.name]=f,c.messages&&(d.messages[j.name]=a.extend(d.messages[j.name],c.messages));break;case"remove":return c?(i={},a.each(c.split(/\s/),function(b,c){i[c]=f[c],delete f[c],"required"===c&&a(j).removeAttr("aria-required")}),i):(delete e[j.name],f)}return g=a.validator.normalizeRules(a.extend({},a.validator.classRules(j),a.validator.attributeRules(j),a.validator.dataRules(j),a.validator.staticRules(j)),j),g.required&&(h=g.required,delete g.required,g=a.extend({required:h},g),a(j).attr("aria-required","true")),g.remote&&(h=g.remote,delete g.remote,g=a.extend(g,{remote:h})),g}}),a.extend(a.expr[":"],{blank:function(b){return!a.trim(""+a(b).val())},filled:function(b){return!!a.trim(""+a(b).val())},unchecked:function(b){return!a(b).prop("checked")}}),a.validator=function(b,c){this.settings=a.extend(!0,{},a.validator.defaults,b),this.currentForm=c,this.init()},a.validator.format=function(b,c){return 1===arguments.length?function(){var c=a.makeArray(arguments);return c.unshift(b),a.validator.format.apply(this,c)}:(arguments.length>2&&c.constructor!==Array&&(c=a.makeArray(arguments).slice(1)),c.constructor!==Array&&(c=[c]),a.each(c,function(a,c){b=b.replace(new RegExp("\\{"+a+"\\}","g"),function(){return c})}),b)},a.extend(a.validator,{defaults:{messages:{},groups:{},rules:{},errorClass:"error",validClass:"valid",errorElement:"label",focusInvalid:!0,errorContainer:a([]),errorLabelContainer:a([]),onsubmit:!0,ignore:":hidden",ignoreTitle:!1,onfocusin:function(a){this.lastActive=a,this.settings.focusCleanup&&!this.blockFocusCleanup&&(this.settings.unhighlight&&this.settings.unhighlight.call(this,a,this.settings.errorClass,this.settings.validClass),this.hideThese(this.errorsFor(a)))},onfocusout:function(a){this.checkable(a)||!(a.name in this.submitted)&&this.optional(a)||this.element(a)},onkeyup:function(a,b){(9!==b.which||""!==this.elementValue(a))&&(a.name in this.submitted||a===this.lastElement)&&this.element(a)},onclick:function(a){a.name in this.submitted?this.element(a):a.parentNode.name in this.submitted&&this.element(a.parentNode)},highlight:function(b,c,d){"radio"===b.type?this.findByName(b.name).addClass(c).removeClass(d):a(b).addClass(c).removeClass(d)},unhighlight:function(b,c,d){"radio"===b.type?this.findByName(b.name).removeClass(c).addClass(d):a(b).removeClass(c).addClass(d)}},setDefaults:function(b){a.extend(a.validator.defaults,b)},messages:{required:"This field is required.",remote:"Please fix this field.",email:"Please enter a valid email address.",url:"Please enter a valid URL.",date:"Please enter a valid date.",dateISO:"Please enter a valid date ( ISO ).",number:"Please enter a valid number.",digits:"Please enter only digits.",creditcard:"Please enter a valid credit card number.",equalTo:"Please enter the same value again.",maxlength:a.validator.format("Please enter no more than {0} characters."),minlength:a.validator.format("Please enter at least {0} characters."),rangelength:a.validator.format("Please enter a value between {0} and {1} characters long."),range:a.validator.format("Please enter a value between {0} and {1}."),max:a.validator.format("Please enter a value less than or equal to {0}."),min:a.validator.format("Please enter a value greater than or equal to {0}.")},autoCreateRanges:!1,prototype:{init:function(){function b(b){var c=a.data(this[0].form,"validator"),d="on"+b.type.replace(/^validate/,""),e=c.settings;e[d]&&!this.is(e.ignore)&&e[d].call(c,this[0],b)}this.labelContainer=a(this.settings.errorLabelContainer),this.errorContext=this.labelContainer.length&&this.labelContainer||a(this.currentForm),this.containers=a(this.settings.errorContainer).add(this.settings.errorLabelContainer),this.submitted={},this.valueCache={},this.pendingRequest=0,this.pending={},this.invalid={},this.reset();var c,d=this.groups={};a.each(this.settings.groups,function(b,c){"string"==typeof c&&(c=c.split(/\s/)),a.each(c,function(a,c){d[c]=b})}),c=this.settings.rules,a.each(c,function(b,d){c[b]=a.validator.normalizeRule(d)}),a(this.currentForm).validateDelegate(":text, [type='password'], [type='file'], select, textarea, [type='number'], [type='search'] ,[type='tel'], [type='url'], [type='email'], [type='datetime'], [type='date'], [type='month'], [type='week'], [type='time'], [type='datetime-local'], [type='range'], [type='color'], [type='radio'], [type='checkbox']","focusin focusout keyup",b).validateDelegate("select, option, [type='radio'], [type='checkbox']","click",b),this.settings.invalidHandler&&a(this.currentForm).bind("invalid-form.validate",this.settings.invalidHandler),a(this.currentForm).find("[required], [data-rule-required], .required").attr("aria-required","true")},form:function(){return this.checkForm(),a.extend(this.submitted,this.errorMap),this.invalid=a.extend({},this.errorMap),this.valid()||a(this.currentForm).triggerHandler("invalid-form",[this]),this.showErrors(),this.valid()},checkForm:function(){this.prepareForm();for(var a=0,b=this.currentElements=this.elements();b[a];a++)this.check(b[a]);return this.valid()},element:function(b){var c=this.clean(b),d=this.validationTargetFor(c),e=!0;return this.lastElement=d,void 0===d?delete this.invalid[c.name]:(this.prepareElement(d),this.currentElements=a(d),e=this.check(d)!==!1,e?delete this.invalid[d.name]:this.invalid[d.name]=!0),a(b).attr("aria-invalid",!e),this.numberOfInvalids()||(this.toHide=this.toHide.add(this.containers)),this.showErrors(),e},showErrors:function(b){if(b){a.extend(this.errorMap,b),this.errorList=[];for(var c in b)this.errorList.push({message:b[c],element:this.findByName(c)[0]});this.successList=a.grep(this.successList,function(a){return!(a.name in b)})}this.settings.showErrors?this.settings.showErrors.call(this,this.errorMap,this.errorList):this.defaultShowErrors()},resetForm:function(){a.fn.resetForm&&a(this.currentForm).resetForm(),this.submitted={},this.lastElement=null,this.prepareForm(),this.hideErrors(),this.elements().removeClass(this.settings.errorClass).removeData("previousValue").removeAttr("aria-invalid")},numberOfInvalids:function(){return this.objectLength(this.invalid)},objectLength:function(a){var b,c=0;for(b in a)c++;return c},hideErrors:function(){this.hideThese(this.toHide)},hideThese:function(a){a.not(this.containers).text(""),this.addWrapper(a).hide()},valid:function(){return 0===this.size()},size:function(){return this.errorList.length},focusInvalid:function(){if(this.settings.focusInvalid)try{a(this.findLastActive()||this.errorList.length&&this.errorList[0].element||[]).filter(":visible").focus().trigger("focusin")}catch(b){}},findLastActive:function(){var b=this.lastActive;return b&&1===a.grep(this.errorList,function(a){return a.element.name===b.name}).length&&b},elements:function(){var b=this,c={};return a(this.currentForm).find("input, select, textarea").not(":submit, :reset, :image, [disabled]").not(this.settings.ignore).filter(function(){return!this.name&&b.settings.debug&&window.console&&console.error("%o has no name assigned",this),this.name in c||!b.objectLength(a(this).rules())?!1:(c[this.name]=!0,!0)})},clean:function(b){return a(b)[0]},errors:function(){var b=this.settings.errorClass.split(" ").join(".");return a(this.settings.errorElement+"."+b,this.errorContext)},reset:function(){this.successList=[],this.errorList=[],this.errorMap={},this.toShow=a([]),this.toHide=a([]),this.currentElements=a([])},prepareForm:function(){this.reset(),this.toHide=this.errors().add(this.containers)},prepareElement:function(a){this.reset(),this.toHide=this.errorsFor(a)},elementValue:function(b){var c,d=a(b),e=b.type;return"radio"===e||"checkbox"===e?a("input[name='"+b.name+"']:checked").val():"number"===e&&"undefined"!=typeof b.validity?b.validity.badInput?!1:d.val():(c=d.val(),"string"==typeof c?c.replace(/\r/g,""):c)},check:function(b){b=this.validationTargetFor(this.clean(b));var c,d,e,f=a(b).rules(),g=a.map(f,function(a,b){return b}).length,h=!1,i=this.elementValue(b);for(d in f){e={method:d,parameters:f[d]};try{if(c=a.validator.methods[d].call(this,i,b,e.parameters),"dependency-mismatch"===c&&1===g){h=!0;continue}if(h=!1,"pending"===c)return void(this.toHide=this.toHide.not(this.errorsFor(b)));if(!c)return this.formatAndAdd(b,e),!1}catch(j){throw this.settings.debug&&window.console&&console.log("Exception occurred when checking element "+b.id+", check the '"+e.method+"' method.",j),j}}if(!h)return this.objectLength(f)&&this.successList.push(b),!0},customDataMessage:function(b,c){return a(b).data("msg"+c.charAt(0).toUpperCase()+c.substring(1).toLowerCase())||a(b).data("msg")},customMessage:function(a,b){var c=this.settings.messages[a];return c&&(c.constructor===String?c:c[b])},findDefined:function(){for(var a=0;a<arguments.length;a++)if(void 0!==arguments[a])return arguments[a];return void 0},defaultMessage:function(b,c){return this.findDefined(this.customMessage(b.name,c),this.customDataMessage(b,c),!this.settings.ignoreTitle&&b.title||void 0,a.validator.messages[c],"<strong>Warning: No message defined for "+b.name+"</strong>")},formatAndAdd:function(b,c){var d=this.defaultMessage(b,c.method),e=/\$?\{(\d+)\}/g;"function"==typeof d?d=d.call(this,c.parameters,b):e.test(d)&&(d=a.validator.format(d.replace(e,"{$1}"),c.parameters)),this.errorList.push({message:d,element:b,method:c.method}),this.errorMap[b.name]=d,this.submitted[b.name]=d},addWrapper:function(a){return this.settings.wrapper&&(a=a.add(a.parent(this.settings.wrapper))),a},defaultShowErrors:function(){var a,b,c;for(a=0;this.errorList[a];a++)c=this.errorList[a],this.settings.highlight&&this.settings.highlight.call(this,c.element,this.settings.errorClass,this.settings.validClass),this.showLabel(c.element,c.message);if(this.errorList.length&&(this.toShow=this.toShow.add(this.containers)),this.settings.success)for(a=0;this.successList[a];a++)this.showLabel(this.successList[a]);if(this.settings.unhighlight)for(a=0,b=this.validElements();b[a];a++)this.settings.unhighlight.call(this,b[a],this.settings.errorClass,this.settings.validClass);this.toHide=this.toHide.not(this.toShow),this.hideErrors(),this.addWrapper(this.toShow).show()},validElements:function(){return this.currentElements.not(this.invalidElements())},invalidElements:function(){return a(this.errorList).map(function(){return this.element})},showLabel:function(b,c){var d,e,f,g=this.errorsFor(b),h=this.idOrName(b),i=a(b).attr("aria-describedby");g.length?(g.removeClass(this.settings.validClass).addClass(this.settings.errorClass),g.html(c)):(g=a("<"+this.settings.errorElement+">").attr("id",h+"-error").addClass(this.settings.errorClass).html(c||""),d=g,this.settings.wrapper&&(d=g.hide().show().wrap("<"+this.settings.wrapper+"/>").parent()),this.labelContainer.length?this.labelContainer.append(d):this.settings.errorPlacement?this.settings.errorPlacement(d,a(b)):d.insertAfter(b),g.is("label")?g.attr("for",h):0===g.parents("label[for='"+h+"']").length&&(f=g.attr("id"),i?i.match(new RegExp("\b"+f+"\b"))||(i+=" "+f):i=f,a(b).attr("aria-describedby",i),e=this.groups[b.name],e&&a.each(this.groups,function(b,c){c===e&&a("[name='"+b+"']",this.currentForm).attr("aria-describedby",g.attr("id"))}))),!c&&this.settings.success&&(g.text(""),"string"==typeof this.settings.success?g.addClass(this.settings.success):this.settings.success(g,b)),this.toShow=this.toShow.add(g)},errorsFor:function(b){var c=this.idOrName(b),d=a(b).attr("aria-describedby"),e="label[for='"+c+"'], label[for='"+c+"'] *";return d&&(e=e+", #"+d.replace(/\s+/g,", #")),this.errors().filter(e)},idOrName:function(a){return this.groups[a.name]||(this.checkable(a)?a.name:a.id||a.name)},validationTargetFor:function(a){return this.checkable(a)&&(a=this.findByName(a.name).not(this.settings.ignore)[0]),a},checkable:function(a){return/radio|checkbox/i.test(a.type)},findByName:function(b){return a(this.currentForm).find("[name='"+b+"']")},getLength:function(b,c){switch(c.nodeName.toLowerCase()){case"select":return a("option:selected",c).length;case"input":if(this.checkable(c))return this.findByName(c.name).filter(":checked").length}return b.length},depend:function(a,b){return this.dependTypes[typeof a]?this.dependTypes[typeof a](a,b):!0},dependTypes:{"boolean":function(a){return a},string:function(b,c){return!!a(b,c.form).length},"function":function(a,b){return a(b)}},optional:function(b){var c=this.elementValue(b);return!a.validator.methods.required.call(this,c,b)&&"dependency-mismatch"},startRequest:function(a){this.pending[a.name]||(this.pendingRequest++,this.pending[a.name]=!0)},stopRequest:function(b,c){this.pendingRequest--,this.pendingRequest<0&&(this.pendingRequest=0),delete this.pending[b.name],c&&0===this.pendingRequest&&this.formSubmitted&&this.form()?(a(this.currentForm).submit(),this.formSubmitted=!1):!c&&0===this.pendingRequest&&this.formSubmitted&&(a(this.currentForm).triggerHandler("invalid-form",[this]),this.formSubmitted=!1)},previousValue:function(b){return a.data(b,"previousValue")||a.data(b,"previousValue",{old:null,valid:!0,message:this.defaultMessage(b,"remote")})}},classRuleSettings:{required:{required:!0},email:{email:!0},url:{url:!0},date:{date:!0},dateISO:{dateISO:!0},number:{number:!0},digits:{digits:!0},creditcard:{creditcard:!0}},addClassRules:function(b,c){b.constructor===String?this.classRuleSettings[b]=c:a.extend(this.classRuleSettings,b)},classRules:function(b){var c={},d=a(b).attr("class");return d&&a.each(d.split(" "),function(){this in a.validator.classRuleSettings&&a.extend(c,a.validator.classRuleSettings[this])}),c},attributeRules:function(b){var c,d,e={},f=a(b),g=b.getAttribute("type");for(c in a.validator.methods)"required"===c?(d=b.getAttribute(c),""===d&&(d=!0),d=!!d):d=f.attr(c),/min|max/.test(c)&&(null===g||/number|range|text/.test(g))&&(d=Number(d)),d||0===d?e[c]=d:g===c&&"range"!==g&&(e[c]=!0);return e.maxlength&&/-1|2147483647|524288/.test(e.maxlength)&&delete e.maxlength,e},dataRules:function(b){var c,d,e={},f=a(b);for(c in a.validator.methods)d=f.data("rule"+c.charAt(0).toUpperCase()+c.substring(1).toLowerCase()),void 0!==d&&(e[c]=d);return e},staticRules:function(b){var c={},d=a.data(b.form,"validator");return d.settings.rules&&(c=a.validator.normalizeRule(d.settings.rules[b.name])||{}),c},normalizeRules:function(b,c){return a.each(b,function(d,e){if(e===!1)return void delete b[d];if(e.param||e.depends){var f=!0;switch(typeof e.depends){case"string":f=!!a(e.depends,c.form).length;break;case"function":f=e.depends.call(c,c)}f?b[d]=void 0!==e.param?e.param:!0:delete b[d]}}),a.each(b,function(d,e){b[d]=a.isFunction(e)?e(c):e}),a.each(["minlength","maxlength"],function(){b[this]&&(b[this]=Number(b[this]))}),a.each(["rangelength","range"],function(){var c;b[this]&&(a.isArray(b[this])?b[this]=[Number(b[this][0]),Number(b[this][1])]:"string"==typeof b[this]&&(c=b[this].replace(/[\[\]]/g,"").split(/[\s,]+/),b[this]=[Number(c[0]),Number(c[1])]))}),a.validator.autoCreateRanges&&(b.min&&b.max&&(b.range=[b.min,b.max],delete b.min,delete b.max),b.minlength&&b.maxlength&&(b.rangelength=[b.minlength,b.maxlength],delete b.minlength,delete b.maxlength)),b},normalizeRule:function(b){if("string"==typeof b){var c={};a.each(b.split(/\s/),function(){c[this]=!0}),b=c}return b},addMethod:function(b,c,d){a.validator.methods[b]=c,a.validator.messages[b]=void 0!==d?d:a.validator.messages[b],c.length<3&&a.validator.addClassRules(b,a.validator.normalizeRule(b))},methods:{required:function(b,c,d){if(!this.depend(d,c))return"dependency-mismatch";if("select"===c.nodeName.toLowerCase()){var e=a(c).val();return e&&e.length>0}return this.checkable(c)?this.getLength(b,c)>0:a.trim(b).length>0},email:function(a,b){return this.optional(b)||/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/.test(a)},url:function(a,b){return this.optional(b)||/^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(a)},date:function(a,b){return this.optional(b)||!/Invalid|NaN/.test(new Date(a).toString())},dateISO:function(a,b){return this.optional(b)||/^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])$/.test(a)},number:function(a,b){return this.optional(b)||/^-?(?:\d+|\d{1,3}(?:,\d{3})+)?(?:\.\d+)?$/.test(a)},digits:function(a,b){return this.optional(b)||/^\d+$/.test(a)},creditcard:function(a,b){if(this.optional(b))return"dependency-mismatch";if(/[^0-9 \-]+/.test(a))return!1;var c,d,e=0,f=0,g=!1;if(a=a.replace(/\D/g,""),a.length<13||a.length>19)return!1;for(c=a.length-1;c>=0;c--)d=a.charAt(c),f=parseInt(d,10),g&&(f*=2)>9&&(f-=9),e+=f,g=!g;return e%10===0},minlength:function(b,c,d){var e=a.isArray(b)?b.length:this.getLength(a.trim(b),c);return this.optional(c)||e>=d},maxlength:function(b,c,d){var e=a.isArray(b)?b.length:this.getLength(a.trim(b),c);return this.optional(c)||d>=e},rangelength:function(b,c,d){var e=a.isArray(b)?b.length:this.getLength(a.trim(b),c);return this.optional(c)||e>=d[0]&&e<=d[1]},min:function(a,b,c){return this.optional(b)||a>=c},max:function(a,b,c){return this.optional(b)||c>=a},range:function(a,b,c){return this.optional(b)||a>=c[0]&&a<=c[1]},equalTo:function(b,c,d){var e=a(d);return this.settings.onfocusout&&e.unbind(".validate-equalTo").bind("blur.validate-equalTo",function(){a(c).valid()}),b===e.val()},remote:function(b,c,d){if(this.optional(c))return"dependency-mismatch";var e,f,g=this.previousValue(c);return this.settings.messages[c.name]||(this.settings.messages[c.name]={}),g.originalMessage=this.settings.messages[c.name].remote,this.settings.messages[c.name].remote=g.message,d="string"==typeof d&&{url:d}||d,g.old===b?g.valid:(g.old=b,e=this,this.startRequest(c),f={},f[c.name]=b,a.ajax(a.extend(!0,{url:d,mode:"abort",port:"validate"+c.name,dataType:"json",data:f,context:e.currentForm,success:function(d){var f,h,i,j=d===!0||"true"===d;e.settings.messages[c.name].remote=g.originalMessage,j?(i=e.formSubmitted,e.prepareElement(c),e.formSubmitted=i,e.successList.push(c),delete e.invalid[c.name],e.showErrors()):(f={},h=d||e.defaultMessage(c,"remote"),f[c.name]=g.message=a.isFunction(h)?h(b):h,e.invalid[c.name]=!0,e.showErrors(f)),g.valid=j,e.stopRequest(c,j)}},d)),"pending")}}}),a.format=function(){throw"$.format has been deprecated. Please use $.validator.format instead."};var b,c={};a.ajaxPrefilter?a.ajaxPrefilter(function(a,b,d){var e=a.port;"abort"===a.mode&&(c[e]&&c[e].abort(),c[e]=d)}):(b=a.ajax,a.ajax=function(d){var e=("mode"in d?d:a.ajaxSettings).mode,f=("port"in d?d:a.ajaxSettings).port;return"abort"===e?(c[f]&&c[f].abort(),c[f]=b.apply(this,arguments),c[f]):b.apply(this,arguments)}),a.extend(a.fn,{validateDelegate:function(b,c,d){return this.bind(c,function(c){var e=a(c.target);return e.is(b)?d.apply(e,arguments):void 0})}})});

/*! jQuery Validation Plugin - v1.13.0 - 7/1/2014
 * http://jqueryvalidation.org/
 * Copyright (c) 2014 JĂśrn Zaefferer; Licensed MIT */
!function(a){"function"==typeof define&&define.amd?define(["jquery","./jquery.validate.min"],a):a(jQuery)}(function(a){!function(){function b(a){return a.replace(/<.[^<>]*?>/g," ").replace(/&nbsp;|&#160;/gi," ").replace(/[.(),;:!?%#$'\"_+=\/\-âââ]*/g,"")}a.validator.addMethod("maxWords",function(a,c,d){return this.optional(c)||b(a).match(/\b\w+\b/g).length<=d},a.validator.format("Please enter {0} words or less.")),a.validator.addMethod("minWords",function(a,c,d){return this.optional(c)||b(a).match(/\b\w+\b/g).length>=d},a.validator.format("Please enter at least {0} words.")),a.validator.addMethod("rangeWords",function(a,c,d){var e=b(a),f=/\b\w+\b/g;return this.optional(c)||e.match(f).length>=d[0]&&e.match(f).length<=d[1]},a.validator.format("Please enter between {0} and {1} words."))}(),a.validator.addMethod("accept",function(b,c,d){var e,f,g="string"==typeof d?d.replace(/\s/g,"").replace(/,/g,"|"):"image/*",h=this.optional(c);if(h)return h;if("file"===a(c).attr("type")&&(g=g.replace(/\*/g,".*"),c.files&&c.files.length))for(e=0;e<c.files.length;e++)if(f=c.files[e],!f.type.match(new RegExp(".?("+g+")$","i")))return!1;return!0},a.validator.format("Please enter a value with a valid mimetype.")),a.validator.addMethod("alphanumeric",function(a,b){return this.optional(b)||/^\w+$/i.test(a)},"Letters, numbers, and underscores only please"),a.validator.addMethod("bankaccountNL",function(a,b){if(this.optional(b))return!0;if(!/^[0-9]{9}|([0-9]{2} ){3}[0-9]{3}$/.test(a))return!1;var c,d,e,f=a.replace(/ /g,""),g=0,h=f.length;for(c=0;h>c;c++)d=h-c,e=f.substring(c,c+1),g+=d*e;return g%11===0},"Please specify a valid bank account number"),a.validator.addMethod("bankorgiroaccountNL",function(b,c){return this.optional(c)||a.validator.methods.bankaccountNL.call(this,b,c)||a.validator.methods.giroaccountNL.call(this,b,c)},"Please specify a valid bank or giro account number"),a.validator.addMethod("bic",function(a,b){return this.optional(b)||/^([A-Z]{6}[A-Z2-9][A-NP-Z1-2])(X{3}|[A-WY-Z0-9][A-Z0-9]{2})?$/.test(a)},"Please specify a valid BIC code"),a.validator.addMethod("cifES",function(a){"use strict";var b,c,d,e,f,g,h=[];if(a=a.toUpperCase(),!a.match("((^[A-Z]{1}[0-9]{7}[A-Z0-9]{1}$|^[T]{1}[A-Z0-9]{8}$)|^[0-9]{8}[A-Z]{1}$)"))return!1;for(d=0;9>d;d++)h[d]=parseInt(a.charAt(d),10);for(c=h[2]+h[4]+h[6],e=1;8>e;e+=2)f=(2*h[e]).toString(),g=f.charAt(1),c+=parseInt(f.charAt(0),10)+(""===g?0:parseInt(g,10));return/^[ABCDEFGHJNPQRSUVW]{1}/.test(a)?(c+="",b=10-parseInt(c.charAt(c.length-1),10),a+=b,h[8].toString()===String.fromCharCode(64+b)||h[8].toString()===a.charAt(a.length-1)):!1},"Please specify a valid CIF number."),a.validator.addMethod("creditcardtypes",function(a,b,c){if(/[^0-9\-]+/.test(a))return!1;a=a.replace(/\D/g,"");var d=0;return c.mastercard&&(d|=1),c.visa&&(d|=2),c.amex&&(d|=4),c.dinersclub&&(d|=8),c.enroute&&(d|=16),c.discover&&(d|=32),c.jcb&&(d|=64),c.unknown&&(d|=128),c.all&&(d=255),1&d&&/^(5[12345])/.test(a)?16===a.length:2&d&&/^(4)/.test(a)?16===a.length:4&d&&/^(3[47])/.test(a)?15===a.length:8&d&&/^(3(0[012345]|[68]))/.test(a)?14===a.length:16&d&&/^(2(014|149))/.test(a)?15===a.length:32&d&&/^(6011)/.test(a)?16===a.length:64&d&&/^(3)/.test(a)?16===a.length:64&d&&/^(2131|1800)/.test(a)?15===a.length:128&d?!0:!1},"Please enter a valid credit card number."),a.validator.addMethod("currency",function(a,b,c){var d,e="string"==typeof c,f=e?c:c[0],g=e?!0:c[1];return f=f.replace(/,/g,""),f=g?f+"]":f+"]?",d="^["+f+"([1-9]{1}[0-9]{0,2}(\\,[0-9]{3})*(\\.[0-9]{0,2})?|[1-9]{1}[0-9]{0,}(\\.[0-9]{0,2})?|0(\\.[0-9]{0,2})?|(\\.[0-9]{1,2})?)$",d=new RegExp(d),this.optional(b)||d.test(a)},"Please specify a valid currency"),a.validator.addMethod("dateFA",function(a,b){return this.optional(b)||/^[1-4]\d{3}\/((0?[1-6]\/((3[0-1])|([1-2][0-9])|(0?[1-9])))|((1[0-2]|(0?[7-9]))\/(30|([1-2][0-9])|(0?[1-9]))))$/.test(a)},"Please enter a correct date"),a.validator.addMethod("dateITA",function(a,b){var c,d,e,f,g,h=!1,i=/^\d{1,2}\/\d{1,2}\/\d{4}$/;return i.test(a)?(c=a.split("/"),d=parseInt(c[0],10),e=parseInt(c[1],10),f=parseInt(c[2],10),g=new Date(f,e-1,d,12,0,0,0),h=g.getUTCFullYear()===f&&g.getUTCMonth()===e-1&&g.getUTCDate()===d?!0:!1):h=!1,this.optional(b)||h},"Please enter a correct date"),a.validator.addMethod("dateNL",function(a,b){return this.optional(b)||/^(0?[1-9]|[12]\d|3[01])[\.\/\-](0?[1-9]|1[012])[\.\/\-]([12]\d)?(\d\d)$/.test(a)},"Please enter a correct date"),a.validator.addMethod("extension",function(a,b,c){return c="string"==typeof c?c.replace(/,/g,"|"):"png|jpe?g|gif",this.optional(b)||a.match(new RegExp(".("+c+")$","i"))},a.validator.format("Please enter a value with a valid extension.")),a.validator.addMethod("giroaccountNL",function(a,b){return this.optional(b)||/^[0-9]{1,7}$/.test(a)},"Please specify a valid giro account number"),a.validator.addMethod("iban",function(a,b){if(this.optional(b))return!0;var c,d,e,f,g,h,i,j,k,l=a.replace(/ /g,"").toUpperCase(),m="",n=!0,o="",p="";if(!/^([a-zA-Z0-9]{4} ){2,8}[a-zA-Z0-9]{1,4}|[a-zA-Z0-9]{12,34}$/.test(l))return!1;if(c=l.substring(0,2),h={AL:"\\d{8}[\\dA-Z]{16}",AD:"\\d{8}[\\dA-Z]{12}",AT:"\\d{16}",AZ:"[\\dA-Z]{4}\\d{20}",BE:"\\d{12}",BH:"[A-Z]{4}[\\dA-Z]{14}",BA:"\\d{16}",BR:"\\d{23}[A-Z][\\dA-Z]",BG:"[A-Z]{4}\\d{6}[\\dA-Z]{8}",CR:"\\d{17}",HR:"\\d{17}",CY:"\\d{8}[\\dA-Z]{16}",CZ:"\\d{20}",DK:"\\d{14}",DO:"[A-Z]{4}\\d{20}",EE:"\\d{16}",FO:"\\d{14}",FI:"\\d{14}",FR:"\\d{10}[\\dA-Z]{11}\\d{2}",GE:"[\\dA-Z]{2}\\d{16}",DE:"\\d{18}",GI:"[A-Z]{4}[\\dA-Z]{15}",GR:"\\d{7}[\\dA-Z]{16}",GL:"\\d{14}",GT:"[\\dA-Z]{4}[\\dA-Z]{20}",HU:"\\d{24}",IS:"\\d{22}",IE:"[\\dA-Z]{4}\\d{14}",IL:"\\d{19}",IT:"[A-Z]\\d{10}[\\dA-Z]{12}",KZ:"\\d{3}[\\dA-Z]{13}",KW:"[A-Z]{4}[\\dA-Z]{22}",LV:"[A-Z]{4}[\\dA-Z]{13}",LB:"\\d{4}[\\dA-Z]{20}",LI:"\\d{5}[\\dA-Z]{12}",LT:"\\d{16}",LU:"\\d{3}[\\dA-Z]{13}",MK:"\\d{3}[\\dA-Z]{10}\\d{2}",MT:"[A-Z]{4}\\d{5}[\\dA-Z]{18}",MR:"\\d{23}",MU:"[A-Z]{4}\\d{19}[A-Z]{3}",MC:"\\d{10}[\\dA-Z]{11}\\d{2}",MD:"[\\dA-Z]{2}\\d{18}",ME:"\\d{18}",NL:"[A-Z]{4}\\d{10}",NO:"\\d{11}",PK:"[\\dA-Z]{4}\\d{16}",PS:"[\\dA-Z]{4}\\d{21}",PL:"\\d{24}",PT:"\\d{21}",RO:"[A-Z]{4}[\\dA-Z]{16}",SM:"[A-Z]\\d{10}[\\dA-Z]{12}",SA:"\\d{2}[\\dA-Z]{18}",RS:"\\d{18}",SK:"\\d{20}",SI:"\\d{15}",ES:"\\d{20}",SE:"\\d{20}",CH:"\\d{5}[\\dA-Z]{12}",TN:"\\d{20}",TR:"\\d{5}[\\dA-Z]{17}",AE:"\\d{3}\\d{16}",GB:"[A-Z]{4}\\d{14}",VG:"[\\dA-Z]{4}\\d{16}"},g=h[c],"undefined"!=typeof g&&(i=new RegExp("^[A-Z]{2}\\d{2}"+g+"$",""),!i.test(l)))return!1;for(d=l.substring(4,l.length)+l.substring(0,4),j=0;j<d.length;j++)e=d.charAt(j),"0"!==e&&(n=!1),n||(m+="0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ".indexOf(e));for(k=0;k<m.length;k++)f=m.charAt(k),p=""+o+f,o=p%97;return 1===o},"Please specify a valid IBAN"),a.validator.addMethod("integer",function(a,b){return this.optional(b)||/^-?\d+$/.test(a)},"A positive or negative non-decimal number please"),a.validator.addMethod("ipv4",function(a,b){return this.optional(b)||/^(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)$/i.test(a)},"Please enter a valid IP v4 address."),a.validator.addMethod("ipv6",function(a,b){return this.optional(b)||/^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(a)},"Please enter a valid IP v6 address."),a.validator.addMethod("lettersonly",function(a,b){return this.optional(b)||/^[a-z]+$/i.test(a)},"Letters only please"),a.validator.addMethod("letterswithbasicpunc",function(a,b){return this.optional(b)||/^[a-z\-.,()'"\s]+$/i.test(a)},"Letters or punctuation only please"),a.validator.addMethod("mobileNL",function(a,b){return this.optional(b)||/^((\+|00(\s|\s?\-\s?)?)31(\s|\s?\-\s?)?(\(0\)[\-\s]?)?|0)6((\s|\s?\-\s?)?[0-9]){8}$/.test(a)},"Please specify a valid mobile number"),a.validator.addMethod("mobileUK",function(a,b){return a=a.replace(/\(|\)|\s+|-/g,""),this.optional(b)||a.length>9&&a.match(/^(?:(?:(?:00\s?|\+)44\s?|0)7(?:[1345789]\d{2}|624)\s?\d{3}\s?\d{3})$/)},"Please specify a valid mobile number"),a.validator.addMethod("nieES",function(a){"use strict";return a=a.toUpperCase(),a.match("((^[A-Z]{1}[0-9]{7}[A-Z0-9]{1}$|^[T]{1}[A-Z0-9]{8}$)|^[0-9]{8}[A-Z]{1}$)")?/^[T]{1}/.test(a)?a[8]===/^[T]{1}[A-Z0-9]{8}$/.test(a):/^[XYZ]{1}/.test(a)?a[8]==="TRWAGMYFPDXBNJZSQVHLCKE".charAt(a.replace("X","0").replace("Y","1").replace("Z","2").substring(0,8)%23):!1:!1},"Please specify a valid NIE number."),a.validator.addMethod("nifES",function(a){"use strict";return a=a.toUpperCase(),a.match("((^[A-Z]{1}[0-9]{7}[A-Z0-9]{1}$|^[T]{1}[A-Z0-9]{8}$)|^[0-9]{8}[A-Z]{1}$)")?/^[0-9]{8}[A-Z]{1}$/.test(a)?"TRWAGMYFPDXBNJZSQVHLCKE".charAt(a.substring(8,0)%23)===a.charAt(8):/^[KLM]{1}/.test(a)?a[8]===String.fromCharCode(64):!1:!1},"Please specify a valid NIF number."),a.validator.addMethod("nowhitespace",function(a,b){return this.optional(b)||/^\S+$/i.test(a)},"No white space please"),a.validator.addMethod("pattern",function(a,b,c){return this.optional(b)?!0:("string"==typeof c&&(c=new RegExp(c)),c.test(a))},"Invalid format."),a.validator.addMethod("phoneNL",function(a,b){return this.optional(b)||/^((\+|00(\s|\s?\-\s?)?)31(\s|\s?\-\s?)?(\(0\)[\-\s]?)?|0)[1-9]((\s|\s?\-\s?)?[0-9]){8}$/.test(a)},"Please specify a valid phone number."),a.validator.addMethod("phoneUK",function(a,b){return a=a.replace(/\(|\)|\s+|-/g,""),this.optional(b)||a.length>9&&a.match(/^(?:(?:(?:00\s?|\+)44\s?)|(?:\(?0))(?:\d{2}\)?\s?\d{4}\s?\d{4}|\d{3}\)?\s?\d{3}\s?\d{3,4}|\d{4}\)?\s?(?:\d{5}|\d{3}\s?\d{3})|\d{5}\)?\s?\d{4,5})$/)},"Please specify a valid phone number"),a.validator.addMethod("phoneUS",function(a,b){return a=a.replace(/\s+/g,""),this.optional(b)||a.length>9&&a.match(/^(\+?1-?)?(\([2-9]([02-9]\d|1[02-9])\)|[2-9]([02-9]\d|1[02-9]))-?[2-9]([02-9]\d|1[02-9])-?\d{4}$/)},"Please specify a valid phone number"),a.validator.addMethod("phonesUK",function(a,b){return a=a.replace(/\(|\)|\s+|-/g,""),this.optional(b)||a.length>9&&a.match(/^(?:(?:(?:00\s?|\+)44\s?|0)(?:1\d{8,9}|[23]\d{9}|7(?:[1345789]\d{8}|624\d{6})))$/)},"Please specify a valid uk phone number"),a.validator.addMethod("postalCodeCA",function(a,b){return this.optional(b)||/^[ABCEGHJKLMNPRSTVXY]\d[A-Z] \d[A-Z]\d$/.test(a)},"Please specify a valid postal code"),a.validator.addMethod("postalcodeIT",function(a,b){return this.optional(b)||/^\d{5}$/.test(a)},"Please specify a valid postal code"),a.validator.addMethod("postalcodeNL",function(a,b){return this.optional(b)||/^[1-9][0-9]{3}\s?[a-zA-Z]{2}$/.test(a)},"Please specify a valid postal code"),a.validator.addMethod("postcodeUK",function(a,b){return this.optional(b)||/^((([A-PR-UWYZ][0-9])|([A-PR-UWYZ][0-9][0-9])|([A-PR-UWYZ][A-HK-Y][0-9])|([A-PR-UWYZ][A-HK-Y][0-9][0-9])|([A-PR-UWYZ][0-9][A-HJKSTUW])|([A-PR-UWYZ][A-HK-Y][0-9][ABEHMNPRVWXY]))\s?([0-9][ABD-HJLNP-UW-Z]{2})|(GIR)\s?(0AA))$/i.test(a)},"Please specify a valid UK postcode"),a.validator.addMethod("require_from_group",function(b,c,d){var e=a(d[1],c.form),f=e.eq(0),g=f.data("valid_req_grp")?f.data("valid_req_grp"):a.extend({},this),h=e.filter(function(){return g.elementValue(this)}).length>=d[0];return f.data("valid_req_grp",g),a(c).data("being_validated")||(e.data("being_validated",!0),e.each(function(){g.element(this)}),e.data("being_validated",!1)),h},a.validator.format("Please fill at least {0} of these fields.")),a.validator.addMethod("skip_or_fill_minimum",function(b,c,d){var e=a(d[1],c.form),f=e.eq(0),g=f.data("valid_skip")?f.data("valid_skip"):a.extend({},this),h=e.filter(function(){return g.elementValue(this)}).length,i=0===h||h>=d[0];return f.data("valid_skip",g),a(c).data("being_validated")||(e.data("being_validated",!0),e.each(function(){g.element(this)}),e.data("being_validated",!1)),i},a.validator.format("Please either skip these fields or fill at least {0} of them.")),jQuery.validator.addMethod("stateUS",function(a,b,c){var d,e="undefined"==typeof c,f=e||"undefined"==typeof c.caseSensitive?!1:c.caseSensitive,g=e||"undefined"==typeof c.includeTerritories?!1:c.includeTerritories,h=e||"undefined"==typeof c.includeMilitary?!1:c.includeMilitary;return d=g||h?g&&h?"^(A[AEKLPRSZ]|C[AOT]|D[CE]|FL|G[AU]|HI|I[ADLN]|K[SY]|LA|M[ADEINOPST]|N[CDEHJMVY]|O[HKR]|P[AR]|RI|S[CD]|T[NX]|UT|V[AIT]|W[AIVY])$":g?"^(A[KLRSZ]|C[AOT]|D[CE]|FL|G[AU]|HI|I[ADLN]|K[SY]|LA|M[ADEINOPST]|N[CDEHJMVY]|O[HKR]|P[AR]|RI|S[CD]|T[NX]|UT|V[AIT]|W[AIVY])$":"^(A[AEKLPRZ]|C[AOT]|D[CE]|FL|GA|HI|I[ADLN]|K[SY]|LA|M[ADEINOST]|N[CDEHJMVY]|O[HKR]|PA|RI|S[CD]|T[NX]|UT|V[AT]|W[AIVY])$":"^(A[KLRZ]|C[AOT]|D[CE]|FL|GA|HI|I[ADLN]|K[SY]|LA|M[ADEINOST]|N[CDEHJMVY]|O[HKR]|PA|RI|S[CD]|T[NX]|UT|V[AT]|W[AIVY])$",d=f?new RegExp(d):new RegExp(d,"i"),this.optional(b)||d.test(a)},"Please specify a valid state"),a.validator.addMethod("strippedminlength",function(b,c,d){return a(b).text().length>=d},a.validator.format("Please enter at least {0} characters")),a.validator.addMethod("time",function(a,b){return this.optional(b)||/^([01]\d|2[0-3])(:[0-5]\d){1,2}$/.test(a)},"Please enter a valid time, between 00:00 and 23:59"),a.validator.addMethod("time12h",function(a,b){return this.optional(b)||/^((0?[1-9]|1[012])(:[0-5]\d){1,2}(\ ?[AP]M))$/i.test(a)},"Please enter a valid time in 12-hour am/pm format"),a.validator.addMethod("url2",function(a,b){return this.optional(b)||/^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)*(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(a)},a.validator.messages.url),a.validator.addMethod("vinUS",function(a){if(17!==a.length)return!1;var b,c,d,e,f,g,h=["A","B","C","D","E","F","G","H","J","K","L","M","N","P","R","S","T","U","V","W","X","Y","Z"],i=[1,2,3,4,5,6,7,8,1,2,3,4,5,7,9,2,3,4,5,6,7,8,9],j=[8,7,6,5,4,3,2,10,0,9,8,7,6,5,4,3,2],k=0;for(b=0;17>b;b++){if(e=j[b],d=a.slice(b,b+1),8===b&&(g=d),isNaN(d)){for(c=0;c<h.length;c++)if(d.toUpperCase()===h[c]){d=i[c],d*=e,isNaN(g)&&8===c&&(g=h[c]);break}}else d*=e;k+=d}return f=k%11,10===f&&(f="X"),f===g?!0:!1},"The specified vehicle identification number (VIN) is invalid."),a.validator.addMethod("zipcodeUS",function(a,b){return this.optional(b)||/^\d{5}(-\d{4})?$/.test(a)},"The specified US ZIP Code is invalid"),a.validator.addMethod("ziprange",function(a,b){return this.optional(b)||/^90[2-5]\d\{2\}-\d{4}$/.test(a)},"Your ZIP-code must be in the range 902xx-xxxx to 905xx-xxxx")});

/**
 * @license jCanvas v15.08.17
 * Copyright 2015 Caleb Evans
 * Released under the MIT license
 */
(function( jQuery, global, factory ) {

	if ( typeof module === 'object' && typeof module.exports === 'object' ) {
		module.exports = global.document && typeof jQuery !== 'undefined' ?
			factory( global, true ) :
			function( jQuery, w ) {
				return factory( jQuery, w );
			};
	} else {
		factory( jQuery, global );
	}

// Pass this if window is not defined yet
}( typeof window !== 'undefined' ? window.$ : {}, typeof window !== 'undefined' ? window : this, function( $, window ) {

	var document = window.document,
		Image = window.Image,
		Array = window.Array,
		getComputedStyle = window.getComputedStyle,
		Math = window.Math,
		Number = window.Number,
		parseFloat = window.parseFloat,
		TRUE = true,
		FALSE = false,
		NULL = null,
	// jshint -W080
		UNDEFINED = undefined;

// Define local aliases to frequently used properties
	var defaults,
	// Aliases to jQuery methods
		extendObject = $.extend,
		inArray = $.inArray,
		typeOf = function ( operand ) {
			return Object.prototype.toString.call( operand )
				.slice( 8, -1 ).toLowerCase();
		},
		isFunction = $.isFunction,
		isPlainObject = $.isPlainObject,
	// Math constants and functions
		PI = Math.PI,
		round = Math.round,
		abs = Math.abs,
		sin = Math.sin,
		cos = Math.cos,
		atan2 = Math.atan2,
	// The Array slice() method
		arraySlice = Array.prototype.slice,
	// jQuery's internal event normalization function
		jQueryEventFix = $.event.fix,
	// Object for storing a number of internal property maps
		maps = {},
	// jQuery internal caches
		caches = {
			dataCache: {},
			propCache: {},
			imageCache: {}
		},
	// Base transformations
		baseTransforms = {
			rotate: 0,
			scaleX: 1,
			scaleY: 1,
			translateX: 0,
			translateY: 0,
			// Store all previous masks
			masks: []
		},
	// Object for storing CSS-related properties
		css = {},
		tangibleEvents = [
			'mousedown',
			'mousemove',
			'mouseup',
			'mouseover',
			'mouseout',
			'touchstart',
			'touchmove',
			'touchend'
		];

// Constructor for creating objects that inherit from jCanvas preferences and defaults
	function jCanvasObject( args ) {
		var params = this,
			propName;
		// Copy the given parameters into new object
		for ( propName in args ) {
			// Do not merge defaults into parameters
			if ( args.hasOwnProperty( propName ) ) {
				params[ propName ] = args[ propName ];
			}
		}
		return params;
	}

// jCanvas object in which global settings are other data are stored
	var jCanvas = {
		// Events object for storing jCanvas event initiation functions
		events: {},
		// Object containing all jCanvas event hooks
		eventHooks: {},
		// Settings for enabling future jCanvas features
		future: {}
	};

// jCanvas default property values
	function jCanvasDefaults() {
		extendObject( this, jCanvasDefaults.baseDefaults );
	}
	jCanvasDefaults.baseDefaults = {
		align: 'center',
		arrowAngle: 90,
		arrowRadius: 0,
		autosave: TRUE,
		baseline: 'middle',
		bringToFront: FALSE,
		ccw: FALSE,
		closed: FALSE,
		compositing: 'source-over',
		concavity: 0,
		cornerRadius: 0,
		count: 1,
		cropFromCenter: TRUE,
		crossOrigin: NULL,
		cursors: NULL,
		disableEvents: FALSE,
		draggable: FALSE,
		dragGroups: NULL,
		groups: NULL,
		data: NULL,
		dx: NULL,
		dy: NULL,
		end: 360,
		eventX: NULL,
		eventY: NULL,
		fillStyle: 'transparent',
		fontStyle: 'normal',
		fontSize: '12pt',
		fontFamily: 'sans-serif',
		fromCenter: TRUE,
		height: NULL,
		imageSmoothing: TRUE,
		inDegrees: TRUE,
		intangible: FALSE,
		index: NULL,
		letterSpacing: NULL,
		lineHeight: 1,
		layer: FALSE,
		mask: FALSE,
		maxWidth: NULL,
		miterLimit: 10,
		name: NULL,
		opacity: 1,
		r1: NULL,
		r2: NULL,
		radius: 0,
		repeat: 'repeat',
		respectAlign: FALSE,
		restrictDragToAxis: null,
		rotate: 0,
		rounded: FALSE,
		scale: 1,
		scaleX: 1,
		scaleY: 1,
		shadowBlur: 0,
		shadowColor: 'transparent',
		shadowStroke: FALSE,
		shadowX: 0,
		shadowY: 0,
		sHeight: NULL,
		sides: 0,
		source: '',
		spread: 0,
		start: 0,
		strokeCap: 'butt',
		strokeDash: NULL,
		strokeDashOffset: 0,
		strokeJoin: 'miter',
		strokeStyle: 'transparent',
		strokeWidth: 1,
		sWidth: NULL,
		sx: NULL,
		sy: NULL,
		text: '',
		translate: 0,
		translateX: 0,
		translateY: 0,
		type: NULL,
		visible: TRUE,
		width: NULL,
		x: 0,
		y: 0
	};
	defaults = new jCanvasDefaults();
	jCanvasObject.prototype = defaults;

	/* Internal helper methods */

// Determines if the given operand is a string
	function isString( operand ) {
		return ( typeOf( operand ) === 'string' );
	}

// Determines if the given operand is numeric
	function isNumeric( operand ) {
		return !isNaN( Number( operand ) ) && !isNaN( parseFloat( operand ) );
	}

// Get 2D context for the given canvas
	function _getContext( canvas ) {
		return ( canvas && canvas.getContext ? canvas.getContext( '2d' ) : NULL );
	}

// Coerce designated number properties from strings to numbers
	function _coerceNumericProps( props ) {
		var propName, propType, propValue;
		// Loop through all properties in given property map
		for ( propName in props ) {
			if ( props.hasOwnProperty( propName ) ) {
				propValue = props[ propName ];
				propType = typeOf( propValue );
				// If property is non-empty string and value is numeric
				if ( propType === 'string' && isNumeric( propValue ) && propName !== 'text' ) {
					// Convert value to number
					props[ propName ] = parseFloat( propValue );
				}
			}
		}
		// Ensure value of text property is always a string
		if ( props.text !== undefined ) {
			props.text = String(props.text);
		}
	}

// Clone the given transformations object
	function _cloneTransforms( transforms ) {
		// Clone the object itself
		transforms = extendObject( {}, transforms );
		// Clone the object's masks array
		transforms.masks = transforms.masks.slice( 0 );
		return transforms;
	}

// Save canvas context and update transformation stack
	function _saveCanvas( ctx, data ) {
		var transforms;
		ctx.save();
		transforms = _cloneTransforms( data.transforms );
		data.savedTransforms.push( transforms );
	}

// Restore canvas context update transformation stack
	function _restoreCanvas( ctx, data ) {
		if ( data.savedTransforms.length === 0 ) {
			// Reset transformation state if it can't be restored any more
			data.transforms = _cloneTransforms( baseTransforms );
		} else {
			// Restore canvas context
			ctx.restore();
			// Restore current transform state to the last saved state
			data.transforms = data.savedTransforms.pop();
		}
	}

// Set the style with the given name
	function _setStyle( canvas, ctx, params, styleName ) {
		if ( params[ styleName ] ) {
			if ( isFunction( params[ styleName ] ) ) {
				// Handle functions
				ctx[ styleName ] = params[ styleName ].call( canvas, params );
			} else {
				// Handle string values
				ctx[ styleName ] = params[ styleName ];
			}
		}
	}

// Set canvas context properties
	function _setGlobalProps( canvas, ctx, params ) {
		_setStyle( canvas, ctx, params, 'fillStyle' );
		_setStyle( canvas, ctx, params, 'strokeStyle' );
		ctx.lineWidth = params.strokeWidth;
		// Optionally round corners for paths
		if ( params.rounded ) {
			ctx.lineCap = ctx.lineJoin = 'round';
		} else {
			ctx.lineCap = params.strokeCap;
			ctx.lineJoin = params.strokeJoin;
			ctx.miterLimit = params.miterLimit;
		}
		// Reset strokeDash if null
		if ( !params.strokeDash ) {
			params.strokeDash = [];
		}
		// Dashed lines
		if ( ctx.setLineDash ) {
			ctx.setLineDash( params.strokeDash );
		}
		ctx.webkitLineDash = ctx.mozDash = params.strokeDash;
		ctx.lineDashOffset = ctx.webkitLineDashOffset = ctx.mozDashOffset = params.strokeDashOffset;
		// Drop shadow
		ctx.shadowOffsetX = params.shadowX;
		ctx.shadowOffsetY = params.shadowY;
		ctx.shadowBlur = params.shadowBlur;
		ctx.shadowColor = params.shadowColor;
		// Opacity and composite operation
		ctx.globalAlpha = params.opacity;
		ctx.globalCompositeOperation = params.compositing;
		// Support cross-browser toggling of image smoothing
		if ( params.imageSmoothing ) {
			ctx.imageSmoothingEnabled = ctx.mozImageSmoothingEnabled = params.imageSmoothingEnabled;
		}
	}

// Optionally enable masking support for this path
	function _enableMasking( ctx, data, params ) {
		if ( params.mask ) {
			// If jCanvas autosave is enabled
			if ( params.autosave ) {
				// Automatically save transformation state by default
				_saveCanvas( ctx, data );
			}
			// Clip the current path
			ctx.clip();
			// Keep track of current masks
			data.transforms.masks.push( params._args );
		}
	}

// Restore individual shape transformation
	function _restoreTransform( ctx, params ) {
		// If shape has been transformed by jCanvas
		if ( params._transformed ) {
			// Restore canvas context
			ctx.restore();
		}
	}

// Close current canvas path
	function _closePath( canvas, ctx, params ) {
		var data;

		// Optionally close path
		if ( params.closed ) {
			ctx.closePath();
		}

		if ( params.shadowStroke && params.strokeWidth !== 0 ) {
			// Extend the shadow to include the stroke of a drawing

			// Add a stroke shadow by stroking before filling
			ctx.stroke();
			ctx.fill();
			// Ensure the below stroking does not inherit a shadow
			ctx.shadowColor = 'transparent';
			ctx.shadowBlur = 0;
			// Stroke over fill as usual
			ctx.stroke();

		} else {
			// If shadowStroke is not enabled, stroke & fill as usual

			ctx.fill();
			// Prevent extra shadow created by stroke ( but only when fill is present )
			if ( params.fillStyle !== 'transparent' ) {
				ctx.shadowColor = 'transparent';
			}
			if ( params.strokeWidth !== 0 ) {
				// Only stroke if the stroke is not 0
				ctx.stroke();
			}

		}

		// Optionally close path
		if ( !params.closed ) {
			ctx.closePath();
		}

		// Restore individual shape transformation
		_restoreTransform( ctx, params );

		// Mask shape if chosen
		if ( params.mask ) {
			// Retrieve canvas data
			data = _getCanvasData( canvas );
			_enableMasking( ctx, data, params );
		}

	}

// Transform ( translate, scale, or rotate ) shape
	function _transformShape( canvas, ctx, params, width, height ) {

		// Get conversion factor for radians
		params._toRad = ( params.inDegrees ? ( PI / 180 ) : 1 );

		params._transformed = TRUE;
		ctx.save();

		// Optionally measure ( x, y ) position from top-left corner
		if ( !params.fromCenter && !params._centered && width !== UNDEFINED ) {
			// Always draw from center unless otherwise specified
			if ( height === UNDEFINED ) {
				height = width;
			}
			params.x += width / 2;
			params.y += height / 2;
			params._centered = TRUE;
		}
		// Optionally rotate shape
		if ( params.rotate ) {
			_rotateCanvas( ctx, params, NULL );
		}
		// Optionally scale shape
		if ( params.scale !== 1 || params.scaleX !== 1 || params.scaleY !== 1 ) {
			_scaleCanvas( ctx, params, NULL );
		}
		// Optionally translate shape
		if ( params.translate || params.translateX || params.translateY ) {
			_translateCanvas( ctx, params, NULL );
		}

	}

	/* Plugin API */

// Extend jCanvas with a user-defined method
	jCanvas.extend = function extend( plugin ) {

		// Create plugin
		if ( plugin.name ) {
			// Merge properties with defaults
			if ( plugin.props ) {
				extendObject( defaults, plugin.props );
			}
			// Define plugin method
			$.fn[ plugin.name ] = function self( args ) {
				var $canvases = this, canvas, e, ctx,
					params, layer;

				for ( e = 0; e < $canvases.length; e += 1 ) {
					canvas = $canvases[ e ];
					ctx = _getContext( canvas );
					if ( ctx ) {

						params = new jCanvasObject( args );
						layer = _addLayer( canvas, params, args, self );

						_setGlobalProps( canvas, ctx, params );
						plugin.fn.call( canvas, ctx, params );

					}
				}
				return $canvases;
			};
			// Add drawing type to drawing map
			if ( plugin.type ) {
				maps.drawings[ plugin.type ] = plugin.name;
			}
		}
		return $.fn[ plugin.name ];
	};

	/* Layer API */

// Retrieved the stored jCanvas data for a canvas element
	function _getCanvasData( canvas ) {
		var dataCache = caches.dataCache, data;
		if ( dataCache._canvas === canvas && dataCache._data ) {

			// Retrieve canvas data from cache if possible
			data = dataCache._data;

		} else {

			// Retrieve canvas data from jQuery's internal data storage
			data = $.data( canvas, 'jCanvas' );
			if ( !data ) {

				// Create canvas data object if it does not already exist
				data = {
					// The associated canvas element
					canvas: canvas,
					// Layers array
					layers: [],
					// Layer maps
					layer: {
						names: {},
						groups: {}
					},
					eventHooks: {},
					// All layers that intersect with the event coordinates ( regardless of visibility )
					intersecting: [],
					// The topmost layer whose area contains the event coordinates
					lastIntersected: NULL,
					cursor: $( canvas ).css( 'cursor' ),
					// Properties for the current drag event
					drag: {
						layer: NULL,
						dragging: FALSE
					},
					// Data for the current event
					event: {
						type: NULL,
						x: NULL,
						y: NULL
					},
					// Events which already have been bound to the canvas
					events: {},
					// The canvas's current transformation state
					transforms: _cloneTransforms( baseTransforms ),
					savedTransforms: [],
					// Whether a layer is being animated or not
					animating: FALSE,
					// The layer currently being animated
					animated: NULL,
					// The device pixel ratio
					pixelRatio: 1,
					// Whether pixel ratio transformations have been applied
					scaled: FALSE
				};
				// Use jQuery to store canvas data
				$.data( canvas, 'jCanvas', data );

			}
			// Cache canvas data for faster retrieval
			dataCache._canvas = canvas;
			dataCache._data = data;

		}
		return data;
	}

// Initialize all of a layer's associated jCanvas events
	function _addLayerEvents( $canvas, data, layer ) {
		var eventName;
		// Determine which jCanvas events need to be bound to this layer
		for ( eventName in jCanvas.events ) {
			if ( jCanvas.events.hasOwnProperty( eventName ) ) {
				// If layer has callback function to complement it
				if ( layer[ eventName ] || ( layer.cursors && layer.cursors[ eventName ] ) ) {
					// Bind event to layer
					_addLayerEvent( $canvas, data, layer, eventName );
				}
			}
		}
		if ( !data.events.mouseout ) {
			$canvas.bind( 'mouseout.jCanvas', function () {
				// Retrieve the layer whose drag event was canceled
				var layer = data.drag.layer, l;
				// If cursor mouses out of canvas while dragging
				if ( layer ) {
					// Cancel drag
					data.drag = {};
					_triggerLayerEvent( $canvas, data, layer, 'dragcancel' );
				}
				// Loop through all layers
				for (l = 0; l < data.layers.length; l += 1) {
					layer = data.layers[l];
					// If layer thinks it's still being moused over
					if ( layer._hovered ) {
						// Trigger mouseout on layer
						$canvas.triggerLayerEvent( data.layers[l], 'mouseout' );
					}
				}
				// Redraw layers
				$canvas.drawLayers();
			} );
			// Indicate that an event handler has been bound
			data.events.mouseout = TRUE;
		}
	}

// Initialize the given event on the given layer
	function _addLayerEvent( $canvas, data, layer, eventName ) {
		// Use touch events if appropriate
		// eventName = _getMouseEventName( eventName );
		// Bind event to layer
		jCanvas.events[ eventName ]( $canvas, data );
		layer._event = TRUE;
	}

// Enable drag support for this layer
	function _enableDrag( $canvas, data, layer ) {
		var dragHelperEvents, eventName, i;
		// Only make layer draggable if necessary
		if ( layer.draggable || layer.cursors ) {

			// Organize helper events which enable drag support
			dragHelperEvents = [ 'mousedown', 'mousemove', 'mouseup' ];

			// Bind each helper event to the canvas
			for ( i = 0; i < dragHelperEvents.length; i += 1 ) {
				// Use touch events if appropriate
				eventName = dragHelperEvents[ i ];
				// Bind event
				_addLayerEvent( $canvas, data, layer, eventName );
			}
			// Indicate that this layer has events bound to it
			layer._event = TRUE;

		}
	}

// Update a layer property map if property is changed
	function _updateLayerName( $canvas, data, layer, props ) {
		var nameMap = data.layer.names;

		// If layer name is being added, not changed
		if ( !props ) {

			props = layer;

		} else {

			// Remove old layer name entry because layer name has changed
			if ( props.name !== UNDEFINED && isString( layer.name ) && layer.name !== props.name ) {
				delete nameMap[ layer.name ];
			}

		}

		// Add new entry to layer name map with new name
		if ( isString( props.name ) ) {
			nameMap[ props.name ] = layer;
		}
	}

// Create or update the data map for the given layer and group type
	function _updateLayerGroups( $canvas, data, layer, props ) {
		var groupMap = data.layer.groups,
			group, groupName, g,
			index, l;

		// If group name is not changing
		if ( !props ) {

			props = layer;

		} else {

			// Remove layer from all of its associated groups
			if ( props.groups !== UNDEFINED && layer.groups !== NULL ) {
				for ( g = 0; g < layer.groups.length; g += 1 ) {
					groupName = layer.groups[ g ];
					group = groupMap[ groupName ];
					if ( group ) {
						// Remove layer from its old layer group entry
						for ( l = 0; l < group.length; l += 1 ) {
							if ( group[ l ] === layer ) {
								// Keep track of the layer's initial index
								index = l;
								// Remove layer once found
								group.splice( l, 1 );
								break;
							}
						}
						// Remove layer group entry if group is empty
						if ( group.length === 0 ) {
							delete groupMap[ groupName ];
						}
					}
				}
			}

		}

		// Add layer to new group if a new group name is given
		if ( props.groups !== UNDEFINED && props.groups !== NULL ) {

			for ( g = 0; g < props.groups.length; g += 1 ) {

				groupName = props.groups[ g ];

				group = groupMap[ groupName ];
				if ( !group ) {
					// Create new group entry if it doesn't exist
					group = groupMap[ groupName ] = [];
					group.name = groupName;
				}
				if ( index === UNDEFINED ) {
					// Add layer to end of group unless otherwise stated
					index = group.length;
				}
				// Add layer to its new layer group
				group.splice( index, 0, layer );

			}

		}
	}

// Get event hooks object for the first selected canvas
	$.fn.getEventHooks = function getEventHooks() {
		var $canvases = this, canvas, data,
			eventHooks = {};

		if ( $canvases.length !== 0 ) {
			canvas = $canvases[ 0 ];
			data = _getCanvasData( canvas );
			eventHooks = data.eventHooks;
		}
		return eventHooks;
	};

// Set event hooks for the selected canvases
	$.fn.setEventHooks = function setEventHooks( eventHooks ) {
		var $canvases = this, $canvas, e,
			data;
		for ( e = 0; e < $canvases.length; e += 1 ) {
			$canvas = $( $canvases[ e ] );
			data = _getCanvasData( $canvases[ e ] );
			extendObject( data.eventHooks, eventHooks );
		}
		return $canvases;
	};

// Get jCanvas layers array
	$.fn.getLayers = function getLayers( callback ) {
		var $canvases = this, canvas, data,
			layers, layer, l,
			matching = [];

		if ( $canvases.length !== 0 ) {

			canvas = $canvases[ 0 ];
			data = _getCanvasData( canvas );
			// Retrieve layers array for this canvas
			layers = data.layers;

			// If a callback function is given
			if ( isFunction( callback ) ) {

				// Filter the layers array using the callback
				for ( l = 0; l < layers.length; l += 1 ) {
					layer = layers[ l ];
					if ( callback.call( canvas, layer ) ) {
						// Add layer to array of matching layers if test passes
						matching.push( layer );
					}
				}

			} else {
				// Otherwise, get all layers

				matching = layers;

			}

		}
		return matching;
	};

// Get a single jCanvas layer object
	$.fn.getLayer = function getLayer( layerId ) {
		var $canvases = this, canvas,
			data, layers, layer, l,
			idType;

		if ( $canvases.length !== 0 ) {

			canvas = $canvases[ 0 ];
			data = _getCanvasData( canvas );
			layers = data.layers;
			idType = typeOf( layerId );

			if ( layerId && layerId.layer ) {

				// Return the actual layer object if given
				layer = layerId;

			} else if ( idType === 'number' ) {

				// Retrieve the layer using the given index

				// Allow for negative indices
				if ( layerId < 0 ) {
					layerId = layers.length + layerId;
				}
				// Get layer with the given index
				layer = layers[ layerId ];

			} else if ( idType === 'regexp' ) {

				// Get layer with the name that matches the given regex
				for ( l = 0; l < layers.length; l += 1 ) {
					// Check if layer matches name
					if ( isString( layers[ l ].name ) && layers[ l ].name.match( layerId ) ) {
						layer = layers[ l ];
						break;
					}
				}

			} else {

				// Get layer with the given name
				layer = data.layer.names[ layerId ];

			}

		}
		return layer;
	};

// Get all layers in the given group
	$.fn.getLayerGroup = function getLayerGroup( groupId ) {
		var $canvases = this, canvas, data,
			groups, groupName, group,
			idType = typeOf( groupId );

		if ( $canvases.length !== 0 ) {

			canvas = $canvases[ 0 ];

			if ( idType === 'array' ) {

				// Return layer group if given
				group = groupId;

			} else if ( idType === 'regexp' ) {

				// Get canvas data
				data = _getCanvasData( canvas );
				groups = data.layer.groups;
				// Loop through all layers groups for this canvas
				for ( groupName in groups ) {
					// Find a group whose name matches the given regex
					if ( groupName.match( groupId ) ) {
						group = groups[ groupName ];
						// Stop after finding the first matching group
						break;
					}
				}

			} else {

				// Find layer group with the given group name
				data = _getCanvasData( canvas );
				group = data.layer.groups[ groupId ];
			}

		}
		return group;
	};

// Get index of layer in layers array
	$.fn.getLayerIndex = function getLayerIndex( layerId ) {
		var $canvases = this,
			layers = $canvases.getLayers(),
			layer = $canvases.getLayer( layerId );

		return inArray( layer, layers );
	};

// Set properties of a layer
	$.fn.setLayer = function setLayer( layerId, props ) {
		var $canvases = this, $canvas, e,
			data, layer,
			propName, propValue, propType;

		for ( e = 0; e < $canvases.length; e += 1 ) {
			$canvas = $( $canvases[ e ] );
			data = _getCanvasData( $canvases[ e ] );

			layer = $( $canvases[ e ] ).getLayer( layerId );
			if ( layer ) {

				// Update layer property maps
				_updateLayerName( $canvas, data, layer, props );
				_updateLayerGroups( $canvas, data, layer, props );

				_coerceNumericProps( props );

				// Merge properties with layer
				for ( propName in props ) {
					if ( props.hasOwnProperty( propName ) ) {
						propValue = props[ propName ];
						propType = typeOf( propValue );
						if ( propType === 'object' && isPlainObject( propValue ) ) {
							// Clone objects
							layer[ propName ] = extendObject( {}, propValue );
							_coerceNumericProps( layer[ propName ] );
						} else if ( propType === 'array' ) {
							// Clone arrays
							layer[ propName ] = propValue.slice( 0 );
						} else if ( propType === 'string' ) {
							if ( propValue.indexOf( '+=' ) === 0 ) {
								// Increment numbers prefixed with +=
								layer[ propName ] += parseFloat( propValue.substr( 2 ) );
							} else if ( propValue.indexOf( '-=' ) === 0 ) {
								// Decrement numbers prefixed with -=
								layer[ propName ] -= parseFloat( propValue.substr( 2 ) );
							} else if ( !isNaN( propValue ) && isNumeric( propValue ) ) {
								// Convert numeric values as strings to numbers
								layer[ propName ] = parseFloat( propValue );
							} else {
								// Otherwise, set given string value
								layer[ propName ] = propValue;
							}
						} else {
							// Otherwise, set given value
							layer[ propName ] = propValue;
						}
					}
				}

				// Update layer events
				_addLayerEvents( $canvas, data, layer );
				_enableDrag( $canvas, data, layer );

				// If layer's properties were changed
				if ( $.isEmptyObject( props ) === FALSE ) {
					_triggerLayerEvent( $canvas, data, layer, 'change', props );
				}

			}
		}
		return $canvases;
	};

// Set properties of all layers ( optionally filtered by a callback )
	$.fn.setLayers = function setLayers( props, callback ) {
		var $canvases = this, $canvas, e,
			layers, l;
		for ( e = 0; e < $canvases.length; e += 1 ) {
			$canvas = $( $canvases[ e ] );

			layers = $canvas.getLayers( callback );
			// Loop through all layers
			for ( l = 0; l < layers.length; l += 1 ) {
				// Set properties of each layer
				$canvas.setLayer( layers[ l ], props );
			}
		}
		return $canvases;
	};

// Set properties of all layers in the given group
	$.fn.setLayerGroup = function setLayerGroup( groupId, props ) {
		var $canvases = this, $canvas, e,
			group, l;

		for ( e = 0; e < $canvases.length; e += 1 ) {
			// Get layer group
			$canvas = $( $canvases[ e ] );

			group = $canvas.getLayerGroup( groupId );
			// If group exists
			if ( group ) {

				// Loop through layers in group
				for ( l = 0; l < group.length; l += 1 ) {
					// Merge given properties with layer
					$canvas.setLayer( group[ l ], props );
				}

			}
		}
		return $canvases;
	};

// Move a layer to the given index in the layers array
	$.fn.moveLayer = function moveLayer( layerId, index ) {
		var $canvases = this, $canvas, e,
			data, layers, layer;

		for ( e = 0; e < $canvases.length; e += 1 ) {
			$canvas = $( $canvases[ e ] );
			data = _getCanvasData( $canvases[ e ] );

			// Retrieve layers array and desired layer
			layers = data.layers;
			layer = $canvas.getLayer( layerId );
			if ( layer ) {

				// Ensure layer index is accurate
				layer.index = inArray( layer, layers );

				// Remove layer from its current placement
				layers.splice( layer.index, 1 );
				// Add layer in its new placement
				layers.splice( index, 0, layer );

				// Handle negative indices
				if ( index < 0 ) {
					index = layers.length + index;
				}
				// Update layer's stored index
				layer.index = index;

				_triggerLayerEvent( $canvas, data, layer, 'move' );

			}
		}
		return $canvases;
	};

// Remove a jCanvas layer
	$.fn.removeLayer = function removeLayer( layerId ) {
		var $canvases = this, $canvas, e, data,
			layers, layer;

		for ( e = 0; e < $canvases.length; e += 1 ) {
			$canvas = $( $canvases[ e ] );
			data = _getCanvasData( $canvases[ e ] );

			// Retrieve layers array and desired layer
			layers = $canvas.getLayers();
			layer = $canvas.getLayer( layerId );
			// Remove layer if found
			if ( layer ) {

				// Ensure layer index is accurate
				layer.index = inArray( layer, layers );
				// Remove layer and allow it to be re-added later
				layers.splice( layer.index, 1 );
				delete layer._layer;

				// Update layer name map
				_updateLayerName( $canvas, data, layer, {
					name: NULL
				} );
				// Update layer group map
				_updateLayerGroups( $canvas, data, layer, {
					groups: NULL
				} );

				// Trigger 'remove' event
				_triggerLayerEvent( $canvas, data, layer, 'remove' );

			}
		}
		return $canvases;
	};

// Remove all layers
	$.fn.removeLayers = function removeLayers( callback ) {
		var $canvases = this, $canvas, e,
			data, layers, layer, l;

		for ( e = 0; e < $canvases.length; e += 1 ) {
			$canvas = $( $canvases[ e ] );
			data = _getCanvasData( $canvases[ e ] );
			layers = $canvas.getLayers( callback );
			// Remove all layers individually
			for ( l = 0; l < layers.length; l += 1 ) {
				layer = layers[ l ];
				$canvas.removeLayer( layer );
				// Ensure no layer is skipped over
				l -= 1;
			}
			// Update layer maps
			data.layer.names = {};
			data.layer.groups = {};
		}
		return $canvases;
	};

// Remove all layers in the group with the given ID
	$.fn.removeLayerGroup = function removeLayerGroup( groupId ) {
		var $canvases = this, $canvas, e, data,
			layers, group, l;

		if ( groupId !== UNDEFINED ) {
			for ( e = 0; e < $canvases.length; e += 1 ) {
				$canvas = $( $canvases[ e ] );
				data = _getCanvasData( $canvases[ e ] );

				layers = $canvas.getLayers();
				group = $canvas.getLayerGroup( groupId );
				// Remove layer group using given group name
				if ( group ) {

					// Clone groups array
					group = group.slice( 0 );

					// Loop through layers in group
					for ( l = 0; l < group.length; l += 1 ) {
						$canvas.removeLayer( group[ l ] );
					}

				}
			}
		}
		return $canvases;
	};

// Add an existing layer to a layer group
	$.fn.addLayerToGroup = function addLayerToGroup( layerId, groupName ) {
		var $canvases = this, $canvas, e,
			layer, groups = [ groupName ];

		for ( e = 0; e < $canvases.length; e += 1 ) {
			$canvas = $( $canvases[ e ] );
			layer = $canvas.getLayer( layerId );

			// If layer is not already in group
			if ( layer.groups ) {
				// Clone groups list
				groups = layer.groups.slice( 0 );
				// If layer is not already in group
				if ( inArray( groupName, layer.groups ) === -1 ) {
					// Add layer to group
					groups.push( groupName );
				}
			}
			// Update layer group maps
			$canvas.setLayer( layer, {
				groups: groups
			} );

		}
		return $canvases;
	};

// Remove an existing layer from a layer group
	$.fn.removeLayerFromGroup = function removeLayerFromGroup( layerId, groupName ) {
		var $canvases = this, $canvas, e,
			layer, groups = [],
			index;

		for ( e = 0; e < $canvases.length; e += 1 ) {
			$canvas = $( $canvases[ e ] );
			layer = $canvas.getLayer( layerId );

			if ( layer.groups ) {

				// Find index of layer in group
				index = inArray( groupName, layer.groups );

				// If layer is in group
				if ( index !== -1 ) {

					// Clone groups list
					groups = layer.groups.slice( 0 );

					// Remove layer from group
					groups.splice( index, 1 );

					// Update layer group maps
					$canvas.setLayer( layer, {
						groups: groups
					} );

				}

			}

		}
		return $canvases;
	};

// Get topmost layer that intersects with event coordinates
	function _getIntersectingLayer( data ) {
		var layer, i,
			mask, m;

		// Store the topmost layer
		layer = NULL;

		// Get the topmost layer whose visible area intersects event coordinates
		for ( i = data.intersecting.length - 1; i >= 0; i -= 1 ) {

			// Get current layer
			layer = data.intersecting[ i ];

			// If layer has previous masks
			if ( layer._masks ) {

				// Search previous masks to ensure
				// layer is visible at event coordinates
				for ( m = layer._masks.length - 1; m >= 0; m -= 1 ) {
					mask = layer._masks[ m ];
					// If mask does not intersect event coordinates
					if ( !mask.intersects ) {
						// Indicate that the mask does not
						// intersect event coordinates
						layer.intersects = FALSE;
						// Stop searching previous masks
						break;
					}

				}

				// If event coordinates intersect all previous masks
				// and layer is not intangible
				if ( layer.intersects && !layer.intangible ) {
					// Stop searching for topmost layer
					break;
				}

			}

		}
		// If resulting layer is intangible
		if ( layer && layer.intangible ) {
			// Cursor does not intersect this layer
			layer = NULL;
		}
		return layer;
	}

// Draw individual layer (internal)
	function _drawLayer( $canvas, ctx, layer, nextLayerIndex ) {
		if ( layer && layer.visible && layer._method ) {
			if ( nextLayerIndex ) {
				layer._next = nextLayerIndex;
			} else {
				layer._next = NULL;
			}
			// If layer is an object, call its respective method
			layer._method.call( $canvas, layer );
		}
	}

// Handle dragging of the currently-dragged layer
	function _handleLayerDrag( $canvas, data, eventType ) {
		var layers, layer, l,
			drag, dragGroups,
			group, groupName, g,
			newX, newY;

		drag = data.drag;
		layer = drag.layer;
		dragGroups = ( layer && layer.dragGroups ) || [];
		layers = data.layers;

		if ( eventType === 'mousemove' || eventType === 'touchmove' ) {
			// Detect when user is currently dragging layer

			if ( !drag.dragging ) {
				// Detect when user starts dragging layer

				// Signify that a layer on the canvas is being dragged
				drag.dragging = TRUE;
				layer.dragging = TRUE;

				// Optionally bring layer to front when drag starts
				if ( layer.bringToFront ) {
					// Remove layer from its original position
					layers.splice( layer.index, 1 );
					// Bring layer to front
					// push() returns the new array length
					layer.index = layers.push( layer );
				}

				// Set drag properties for this layer
				layer._startX = layer.x;
				layer._startY = layer.y;
				layer._endX = layer._eventX;
				layer._endY = layer._eventY;

				// Trigger dragstart event
				_triggerLayerEvent( $canvas, data, layer, 'dragstart' );

			}

			if ( drag.dragging ) {

				// Calculate position after drag
				newX = layer._eventX - ( layer._endX - layer._startX );
				newY = layer._eventY - ( layer._endY - layer._startY );
				layer.dx = newX - layer.x;
				layer.dy = newY - layer.y;
				if ( layer.restrictDragToAxis !== 'y' ) {
					layer.x = newX;
				}
				if ( layer.restrictDragToAxis !== 'x' ) {
					layer.y = newY;
				}

				// Trigger drag event
				_triggerLayerEvent( $canvas, data, layer, 'drag' );

				// Move groups with layer on drag
				for ( g = 0; g < dragGroups.length; g += 1 ) {

					groupName = dragGroups[ g ];
					group = data.layer.groups[ groupName ];
					if ( layer.groups && group ) {

						for ( l = 0; l < group.length; l += 1 ) {
							if ( group[ l ] !== layer ) {
								if ( layer.restrictDragToAxis !== 'y' && group[ l ].restrictDragToAxis !== 'y' ) {
									group[ l ].x += layer.dx;
								}
								if ( layer.restrictDragToAxis !== 'x' && group[ l ].restrictDragToAxis !== 'x' ) {
									group[ l ].y += layer.dy;
								}
							}
						}

					}

				}

			}

		} else if ( eventType === 'mouseup' || eventType === 'touchend' ) {
			// Detect when user stops dragging layer

			if ( drag.dragging ) {
				layer.dragging = FALSE;
				drag.dragging = FALSE;
				// Trigger dragstop event
				_triggerLayerEvent( $canvas, data, layer, 'dragstop' );
			}

			// Cancel dragging
			data.drag = {};

		}
	}


// List of CSS3 cursors that need to be prefixed
	css.cursors = [ 'grab', 'grabbing', 'zoom-in', 'zoom-out' ];

// Function to detect vendor prefix
// Modified version of David Walsh's implementation
// http://davidwalsh.name/vendor-prefix
	css.prefix = ( function () {
		try {
			var styles = getComputedStyle(document.documentElement, ''),
				pre = ( arraySlice
					.call(styles)
					.join('')
					.match(/-(moz|webkit|ms)-/) || ( styles.OLink === '' && ['', 'o'] )
				)[1];
			return '-' + pre + '-';
		} catch(e) {
			return '';
		}
	} )();

// Set cursor on canvas
	function _setCursor( $canvas, layer, eventType ) {
		var cursor;
		if ( layer.cursors ) {
			// Retrieve cursor from cursors object if it exists
			cursor = layer.cursors[ eventType ];
		}
		// Prefix any CSS3 cursor
		if ( $.inArray( cursor, css.cursors ) !== -1 ) {
			cursor = css.prefix + cursor;
		}
		// If cursor is defined
		if ( cursor ) {
			// Set canvas cursor
			$canvas.css( {
				cursor: cursor
			} );
		}
	}

// Reset cursor on canvas
	function _resetCursor( $canvas, data ) {
		$canvas.css( {
			cursor: data.cursor
		} );
	}

// Run the given event callback with the given arguments
	function _runEventCallback( $canvas, layer, eventType, callbacks, arg ) {
		// Prevent callback from firing recursively
		if ( callbacks[ eventType ] && layer._running && !layer._running[ eventType ] ) {
			// Signify the start of callback execution for this event
			layer._running[ eventType ] = TRUE;
			// Run event callback with the given arguments
			callbacks[ eventType ].call( $canvas[ 0 ], layer, arg );
			// Signify the end of callback execution for this event
			layer._running[ eventType ] = FALSE;
		}
	}

// Determine if the given layer can "legally" fire the given event
	function _layerCanFireEvent( layer, eventType ) {
		// If events are disable and if
		// layer is tangible or event is not tangible
		return ( !layer.disableEvents &&
		( !layer.intangible || $.inArray( eventType, tangibleEvents ) === -1 ) );
	}

// Trigger the given event on the given layer
	function _triggerLayerEvent( $canvas, data, layer, eventType, arg ) {
		// If layer can legally fire this event type
		if ( _layerCanFireEvent( layer, eventType ) ) {

			// Do not set a custom cursor on layer mouseout
			if ( eventType !== 'mouseout' ) {
				// Update cursor if one is defined for this event
				_setCursor( $canvas, layer, eventType );
			}

			// Trigger the user-defined event callback
			_runEventCallback( $canvas, layer, eventType, layer, arg );
			// Trigger the canvas-bound event hook
			_runEventCallback( $canvas, layer, eventType, data.eventHooks, arg );
			// Trigger the global event hook
			_runEventCallback( $canvas, layer, eventType, jCanvas.eventHooks, arg );

		}
	}

// Manually trigger a layer event
	$.fn.triggerLayerEvent = function ( layer, eventType ) {
		var $canvases = this, $canvas, e,
			data;

		for ( e = 0; e < $canvases.length; e += 1 ) {
			$canvas = $( $canvases[ e ] );
			data = _getCanvasData( $canvases[ e ] );
			layer = $canvas.getLayer( layer );
			if ( layer ) {
				_triggerLayerEvent( $canvas, data, layer, eventType );
			}
		}
		return $canvases;
	};

// Draw layer with the given ID
	$.fn.drawLayer = function drawLayer( layerId ) {
		var $canvases = this, e, ctx,
			$canvas, layer;

		for ( e = 0; e < $canvases.length; e += 1 ) {
			$canvas = $( $canvases[ e ] );
			ctx = _getContext( $canvases[ e ] );
			if (ctx) {
				layer = $canvas.getLayer( layerId );
				_drawLayer( $canvas, ctx, layer );
			}
		}
		return $canvases;
	};

// Draw all layers ( or, if given, only layers starting at an index )
	$.fn.drawLayers = function drawLayers( args ) {
		var $canvases = this, $canvas, e, ctx,
		// Internal parameters for redrawing the canvas
			params = args || {},
		// Other variables
			layers, layer, lastLayer, l, index, lastIndex,
			data, eventCache, eventType, isImageLayer;

		// The layer index from which to start redrawing the canvas
		index = params.index;
		if ( !index ) {
			index = 0;
		}

		for ( e = 0; e < $canvases.length; e += 1 ) {
			$canvas = $( $canvases[ e ] );
			ctx = _getContext( $canvases[ e ] );
			if ( ctx ) {

				data = _getCanvasData( $canvases[ e ] );

				// Clear canvas first unless otherwise directed
				if ( params.clear !== FALSE ) {
					$canvas.clearCanvas();
				}

				// Cache the layers array
				layers = data.layers;

				// Draw layers from first to last ( bottom to top )
				for ( l = index; l < layers.length; l += 1 ) {
					layer = layers[ l ];

					// Ensure layer index is up-to-date
					layer.index = l;

					// Prevent any one event from firing excessively
					if ( params.resetFire ) {
						layer._fired = FALSE;
					}
					// Draw layer
					_drawLayer( $canvas, ctx, layer, l + 1 );
					// Store list of previous masks for each layer
					layer._masks = data.transforms.masks.slice( 0 );

					// Allow image layers to load before drawing successive layers
					if ( layer._method === $.fn.drawImage && layer.visible ) {
						isImageLayer = true;
						break;
					}

				}

				// If layer is an image layer
				if ( isImageLayer ) {
					// Stop and wait for drawImage() to resume drawLayers()
					break;
				}

				// Store the latest
				lastIndex = l;

				// Get first layer that intersects with event coordinates
				layer = _getIntersectingLayer( data );

				eventCache = data.event;
				eventType = eventCache.type;

				// If jCanvas has detected a dragstart
				if ( data.drag.layer ) {
					// Handle dragging of layer
					_handleLayerDrag( $canvas, data, eventType );
				}

				// Manage mouseout event
				lastLayer = data.lastIntersected;
				if ( lastLayer !== NULL && layer !== lastLayer && lastLayer._hovered && !lastLayer._fired && !data.drag.dragging ) {

					data.lastIntersected = NULL;
					lastLayer._fired = TRUE;
					lastLayer._hovered = FALSE;
					_triggerLayerEvent( $canvas, data, lastLayer, 'mouseout' );
					_resetCursor( $canvas, data );

				}

				if ( layer ) {

					// Use mouse event callbacks if no touch event callbacks are given
					if ( !layer[ eventType ] ) {
						eventType = _getMouseEventName( eventType );
					}

					// Check events for intersecting layer
					if ( layer._event && layer.intersects ) {

						data.lastIntersected = layer;

						// Detect mouseover events
						if ( ( layer.mouseover || layer.mouseout || layer.cursors ) && !data.drag.dragging ) {

							if ( !layer._hovered && !layer._fired ) {

								// Prevent events from firing excessively
								layer._fired = TRUE;
								layer._hovered = TRUE;
								_triggerLayerEvent( $canvas, data, layer, 'mouseover' );

							}

						}

						// Detect any other mouse event
						if ( !layer._fired ) {

							// Prevent event from firing twice unintentionally
							layer._fired = TRUE;
							eventCache.type = NULL;

							_triggerLayerEvent( $canvas, data, layer, eventType );

						}

						// Use the mousedown event to start drag
						if ( layer.draggable && !layer.disableEvents && ( eventType === 'mousedown' || eventType === 'touchstart' ) ) {

							// Keep track of drag state
							data.drag.layer = layer;

						}

					}

				}

				// If cursor is not intersecting with any layer
				if ( layer === NULL && !data.drag.dragging ) {
					// Reset cursor to previous state
					_resetCursor( $canvas, data );
				}

				// If the last layer has been drawn
				if ( lastIndex === layers.length ) {

					// Reset list of intersecting layers
					data.intersecting.length = 0;
					// Reset transformation stack
					data.transforms = _cloneTransforms( baseTransforms );
					data.savedTransforms.length = 0;

				}

			}
		}
		return $canvases;
	};

// Add a jCanvas layer (internal)
	function _addLayer( canvas, params, args, method ) {
		var $canvas, data,
			layers, layer = ( params._layer ? args : params );

		// Store arguments object for later use
		params._args = args;

		// Convert all draggable drawings into jCanvas layers
		if ( params.draggable || params.dragGroups ) {
			params.layer = TRUE;
			params.draggable = TRUE;
		}

		// Determine the layer's type using the available information
		if ( !params._method ) {
			if ( method ) {
				params._method = method;
			} else if ( params.method ) {
				params._method = $.fn[ params.method ];
			} else if ( params.type ) {
				params._method = $.fn[ maps.drawings[ params.type ] ];
			} else {
				params._method = function () {};
			}
		}

		// If layer hasn't been added yet
		if ( params.layer && !params._layer ) {
			// Add layer to canvas

			$canvas = $( canvas );

			data = _getCanvasData( canvas );
			layers = data.layers;

			// Do not add duplicate layers of same name
			if ( layer.name === NULL || ( isString( layer.name ) && data.layer.names[ layer.name ] === UNDEFINED ) ) {

				// Convert number properties to numbers
				_coerceNumericProps( params );

				// Ensure layers are unique across canvases by cloning them
				layer = new jCanvasObject( params );
				layer.canvas = canvas;
				// Indicate that this is a layer for future checks
				layer.layer = TRUE;
				layer._layer = TRUE;
				layer._running = {};
				// If layer stores user-defined data
				if ( layer.data !== NULL ) {
					// Clone object
					layer.data = extendObject( {}, layer.data );
				} else {
					// Otherwise, create data object
					layer.data = {};
				}
				// If layer stores a list of associated groups
				if ( layer.groups !== NULL ) {
					// Clone list
					layer.groups = layer.groups.slice( 0 );
				} else {
					// Otherwise, create empty list
					layer.groups = [];
				}

				// Update layer group maps
				_updateLayerName( $canvas, data, layer );
				_updateLayerGroups( $canvas, data, layer );

				// Check for any associated jCanvas events and enable them
				_addLayerEvents( $canvas, data, layer );

				// Optionally enable drag-and-drop support and cursor support
				_enableDrag( $canvas, data, layer );

				// Copy _event property to parameters object
				params._event = layer._event;

				// Calculate width/height for text layers
				if ( layer._method === $.fn.drawText ) {
					$canvas.measureText( layer );
				}

				// Add layer to end of array if no index is specified
				if ( layer.index === NULL ) {
					layer.index = layers.length;
				}

				// Add layer to layers array at specified index
				layers.splice( layer.index, 0, layer );

				// Store layer on parameters object
				params._args = layer;

				// Trigger an 'add' event
				_triggerLayerEvent( $canvas, data, layer, 'add' );

			}

		} else if ( !params.layer ) {
			_coerceNumericProps( params );
		}

		return layer;
	}

// Add a jCanvas layer
	$.fn.addLayer = function addLayer( args ) {
		var $canvases = this, e, ctx,
			params;

		for ( e = 0; e < $canvases.length; e += 1 ) {
			ctx = _getContext( $canvases[ e ] );
			if ( ctx ) {

				params = new jCanvasObject( args );
				params.layer = TRUE;
				_addLayer( $canvases[ e ], params, args );

			}
		}
		return $canvases;
	};

	/* Animation API */

// Define properties used in both CSS and jCanvas
	css.props = [
		'width',
		'height',
		'opacity',
		'lineHeight'
	];
	css.propsObj = {};

// Hide/show jCanvas/CSS properties so they can be animated using jQuery
	function _showProps( obj ) {
		var cssProp, p;
		for ( p = 0; p < css.props.length; p += 1 ) {
			cssProp = css.props[ p ];
			obj[ cssProp ] = obj[ '_' + cssProp ];
		}
	}
	function _hideProps( obj, reset ) {
		var cssProp, p;
		for ( p = 0; p < css.props.length; p += 1 ) {
			cssProp = css.props[ p ];
			// Hide property using same name with leading underscore
			if ( obj[ cssProp ] !== UNDEFINED ) {
				obj[ '_' + cssProp ] = obj[ cssProp ];
				css.propsObj[ cssProp ] = TRUE;
				if ( reset ) {
					delete obj[ cssProp ];
				}
			}
		}
	}

// Evaluate property values that are functions
	function _parseEndValues( canvas, layer, endValues ) {
		var propName, propValue,
			subPropName, subPropValue;
		// Loop through all properties in map of end values
		for ( propName in endValues ) {
			if ( endValues.hasOwnProperty( propName ) ) {
				propValue = endValues[ propName ];
				// If end value is function
				if ( isFunction( propValue ) ) {
					// Call function and use its value as the end value
					endValues[ propName ] = propValue.call( canvas, layer, propName );
				}
				// If end value is an object
				if ( typeOf( propValue ) === 'object' && isPlainObject( propValue ) ) {
					// Prepare to animate properties in object
					for ( subPropName in propValue ) {
						if ( propValue.hasOwnProperty( subPropName ) ) {
							subPropValue = propValue[ subPropName ];
							// Store property's start value at top-level of layer
							if ( layer[ propName ] !== UNDEFINED ) {
								layer[ propName + '.' + subPropName ] = layer[ propName ][ subPropName ];
								// Store property's end value at top-level of end values map
								endValues[ propName + '.' + subPropName ] = subPropValue;
							}
						}
					}
					// Delete sub-property of object as it's no longer needed
					delete endValues[ propName ];
				}
			}
		}
		return endValues;
	}

// Remove sub-property aliases from layer object
	function _removeSubPropAliases( layer ) {
		var propName;
		for ( propName in layer ) {
			if ( layer.hasOwnProperty( propName ) ) {
				if ( propName.indexOf( '.' ) !== -1 ) {
					delete layer[ propName ];
				}
			}
		}
	}

// Convert a color value to an array of RGB values
	function _colorToRgbArray( color ) {
		var originalColor, elem,
			rgb = [],
			multiple = 1;

		// Deal with hexadecimal colors and color names
		if ( color.match( /^([a-z]+|#[0-9a-f]+)$/gi ) ) {
			// Deal with complete transparency
			if ( color === 'transparent' ) {
				color = 'rgba(0, 0, 0, 0)';
			}
			elem = document.head;
			originalColor = elem.style.color;
			elem.style.color = color;
			color = $.css( elem, 'color' );
			elem.style.color = originalColor;
		}
		// Parse RGB string
		if ( color.match( /^rgb/gi ) ) {
			rgb = color.match( /(\d+(\.\d+)?)/gi );
			// Deal with RGB percentages
			if ( color.match( /%/gi ) ) {
				multiple = 2.55;
			}
			rgb[ 0 ] *= multiple;
			rgb[ 1 ] *= multiple;
			rgb[ 2 ] *= multiple;
			// Ad alpha channel if given
			if ( rgb[ 3 ] !== UNDEFINED ) {
				rgb[ 3 ] = parseFloat( rgb[ 3 ] );
			} else {
				rgb[ 3 ] = 1;
			}
		}
		return rgb;
	}

// Animate a hex or RGB color
	function _animateColor( fx ) {
		var n = 3,
			i;
		// Only parse start and end colors once
		if ( typeOf( fx.start ) !== 'array' ) {
			fx.start = _colorToRgbArray( fx.start );
			fx.end = _colorToRgbArray( fx.end );
		}
		fx.now = [];

		// If colors are RGBA, animate transparency
		if ( fx.start[ 3 ] !== 1 || fx.end[ 3 ] !== 1 ) {
			n = 4;
		}

		// Calculate current frame for red, green, blue, and alpha
		for ( i = 0; i < n; i += 1 ) {
			fx.now[ i ] = fx.start[ i ] + ( fx.end[ i ] - fx.start[ i ] ) * fx.pos;
			// Only the red, green, and blue values must be integers
			if ( i < 3 ) {
				fx.now[ i ] = round( fx.now[ i ] );
			}
		}
		if ( fx.start[ 3 ] !== 1 || fx.end[ 3 ] !== 1 ) {
			// Only use RGBA if RGBA colors are given
			fx.now = 'rgba( ' + fx.now.join( ',' ) + ' )';
		} else {
			// Otherwise, animate as solid colors
			fx.now.slice( 0, 3 );
			fx.now = 'rgb( ' + fx.now.join( ',' ) + ' )';
		}
		// Animate colors for both canvas layers and DOM elements
		if ( fx.elem.nodeName ) {
			fx.elem.style[ fx.prop ] = fx.now;
		} else {
			fx.elem[ fx.prop ] = fx.now;
		}
	}

// Animate jCanvas layer
	$.fn.animateLayer = function animateLayer() {
		var $canvases = this, $canvas, e, ctx,
			args = arraySlice.call( arguments, 0 ),
			data, layer, props;

		// Deal with all cases of argument placement
		/*
		 0. layer name/index
		 1. properties
		 2. duration/options
		 3. easing
		 4. complete function
		 5. step function
		 */

		if ( typeOf( args[ 2 ] ) === 'object' ) {

			// Accept an options object for animation
			args.splice( 2, 0, args[ 2 ].duration || NULL );
			args.splice( 3, 0, args[ 3 ].easing || NULL );
			args.splice( 4, 0, args[ 4 ].complete || NULL );
			args.splice( 5, 0, args[ 5 ].step || NULL );

		} else {

			if ( args[ 2 ] === UNDEFINED ) {
				// If object is the last argument
				args.splice( 2, 0, NULL );
				args.splice( 3, 0, NULL );
				args.splice( 4, 0, NULL );
			} else if ( isFunction( args[ 2 ] ) ) {
				// If callback comes after object
				args.splice( 2, 0, NULL );
				args.splice( 3, 0, NULL );
			}
			if ( args[ 3 ] === UNDEFINED ) {
				// If duration is the last argument
				args[ 3 ] = NULL;
				args.splice( 4, 0, NULL );
			} else if ( isFunction( args[ 3 ] ) ) {
				// If callback comes after duration
				args.splice( 3, 0, NULL );
			}

		}

		// Run callback function when animation completes
		function complete( $canvas, data, layer ) {

			return function () {

				_showProps( layer );
				_removeSubPropAliases( layer );

				// Prevent multiple redraw loops
				if ( !data.animating || data.animated === layer ) {
					// Redraw layers on last frame
					$canvas.drawLayers();
				}

				// Signify the end of an animation loop
				layer._animating = FALSE;
				data.animating = FALSE;
				data.animated = NULL;

				// If callback is defined
				if ( args[ 4 ] ) {
					// Run callback at the end of the animation
					args[ 4 ].call( $canvas[ 0 ], layer );
				}

				_triggerLayerEvent( $canvas, data, layer, 'animateend' );

			};

		}

		// Redraw layers on every frame of the animation
		function step( $canvas, data, layer ) {

			return function ( now, fx ) {
				var parts, propName, subPropName,
					hidden = false;

				// If animated property has been hidden
				if ( fx.prop[ 0 ] === '_' ) {
					hidden = true;
					// Unhide property temporarily
					fx.prop = fx.prop.replace( '_', '' );
					layer[ fx.prop ] = layer[ '_' + fx.prop ];
				}

				// If animating property of sub-object
				if ( fx.prop.indexOf( '.' ) !== -1 ) {
					parts = fx.prop.split( '.' );
					propName = parts[ 0 ];
					subPropName = parts[ 1 ];
					if ( layer[ propName ] ) {
						layer[ propName ][ subPropName ] = fx.now;
					}
				}

				// Throttle animation to improve efficiency
				if ( layer._pos !== fx.pos ) {

					layer._pos = fx.pos;

					// Signify the start of an animation loop
					if ( !layer._animating && !data.animating ) {
						layer._animating = TRUE;
						data.animating = TRUE;
						data.animated = layer;
					}

					// Prevent multiple redraw loops
					if ( !data.animating || data.animated === layer ) {
						// Redraw layers for every frame
						$canvas.drawLayers();
					}

				}

				// If callback is defined
				if ( args[ 5 ] ) {
					// Run callback for each step of animation
					args[ 5 ].call( $canvas[ 0 ], now, fx, layer );
				}

				_triggerLayerEvent( $canvas, data, layer, 'animate', fx );

				// If property should be hidden during animation
				if ( hidden ) {
					// Hide property again
					fx.prop = '_' + fx.prop;
				}

			};

		}

		for ( e = 0; e < $canvases.length; e += 1 ) {
			$canvas = $( $canvases[ e ] );
			ctx = _getContext( $canvases[ e ] );
			if ( ctx ) {

				data = _getCanvasData( $canvases[ e ] );

				// If a layer object was passed, use it the layer to be animated
				layer = $canvas.getLayer( args[ 0 ] );

				// Ignore layers that are functions
				if ( layer && layer._method !== $.fn.draw ) {

					// Do not modify original object
					props = extendObject( {}, args[ 1 ] );

					props = _parseEndValues( $canvases[ e ], layer, props );

					// Bypass jQuery CSS Hooks for CSS properties ( width, opacity, etc. )
					_hideProps( props, TRUE );
					_hideProps( layer );

					// Fix for jQuery's vendor prefixing support, which affects how width/height/opacity are animated
					layer.style = css.propsObj;

					// Animate layer
					$( layer ).animate( props, {
						duration: args[ 2 ],
						easing: ( $.easing[ args[ 3 ] ] ? args[ 3 ] : NULL ),
						// When animation completes
						complete: complete( $canvas, data, layer ),
						// Redraw canvas for every animation frame
						step: step( $canvas, data, layer )
					} );
					_triggerLayerEvent( $canvas, data, layer, 'animatestart' );
				}

			}
		}
		return $canvases;
	};

// Animate all layers in a layer group
	$.fn.animateLayerGroup = function animateLayerGroup( groupId ) {
		var $canvases = this, $canvas, e,
			args = arraySlice.call( arguments, 0 ),
			group, l;
		for ( e = 0; e < $canvases.length; e += 1 ) {
			$canvas = $( $canvases[ e ] );
			group = $canvas.getLayerGroup( groupId );
			if ( group ) {

				// Animate all layers in the group
				for ( l = 0; l < group.length; l += 1 ) {

					// Replace first argument with layer
					args[ 0 ] = group[ l ];
					$canvas.animateLayer.apply( $canvas, args );

				}

			}
		}
		return $canvases;
	};

// Delay layer animation by a given number of milliseconds
	$.fn.delayLayer = function delayLayer( layerId, duration ) {
		var $canvases = this, $canvas, e,
			data, layer;
		duration = duration || 0;

		for ( e = 0; e < $canvases.length; e += 1 ) {
			$canvas = $( $canvases[ e ] );
			data = _getCanvasData( $canvases[ e ] );
			layer = $canvas.getLayer( layerId );
			// If layer exists
			if ( layer ) {
				// Delay animation
				$( layer ).delay( duration );
				_triggerLayerEvent( $canvas, data, layer, 'delay' );
			}
		}
		return $canvases;
	};

// Delay animation all layers in a layer group
	$.fn.delayLayerGroup = function delayLayerGroup( groupId, duration ) {
		var $canvases = this, $canvas, e,
			group, layer, l;
		duration = duration || 0;

		for ( e = 0; e < $canvases.length; e += 1 ) {
			$canvas = $( $canvases[ e ] );

			group = $canvas.getLayerGroup( groupId );
			// Delay all layers in the group
			if ( group ) {

				for ( l = 0; l < group.length; l += 1 ) {
					// Delay each layer in the group
					layer = group[ l ];
					$canvas.delayLayer( layer, duration );
				}

			}
		}
		return $canvases;
	};

// Stop layer animation
	$.fn.stopLayer = function stopLayer( layerId, clearQueue ) {
		var $canvases = this, $canvas, e,
			data, layer;

		for ( e = 0; e < $canvases.length; e += 1 ) {
			$canvas = $( $canvases[ e ] );
			data = _getCanvasData( $canvases[ e ] );
			layer = $canvas.getLayer( layerId );
			// If layer exists
			if ( layer ) {
				// Stop animation
				$( layer ).stop( clearQueue );
				_triggerLayerEvent( $canvas, data, layer, 'stop' );
			}
		}
		return $canvases;
	};

// Stop animation of all layers in a layer group
	$.fn.stopLayerGroup = function stopLayerGroup( groupId, clearQueue ) {
		var $canvases = this, $canvas, e,
			group, layer, l;

		for ( e = 0; e < $canvases.length; e += 1 ) {
			$canvas = $( $canvases[ e ] );

			group = $canvas.getLayerGroup( groupId );
			// Stop all layers in the group
			if ( group ) {

				for ( l = 0; l < group.length; l += 1 ) {
					// Stop each layer in the group
					layer = group[ l ];
					$canvas.stopLayer( layer, clearQueue );
				}

			}
		}
		return $canvases;
	};

// Enable animation for color properties
	function _supportColorProps( props ) {
		var p;
		for ( p = 0; p < props.length; p += 1 ) {
			$.fx.step[ props[ p ] ] = _animateColor;
		}
	}

// Enable animation for color properties
	_supportColorProps( [
		'color',
		'backgroundColor',
		'borderColor',
		'borderTopColor',
		'borderRightColor',
		'borderBottomColor',
		'borderLeftColor',
		'fillStyle',
		'outlineColor',
		'strokeStyle',
		'shadowColor'
	] );

	/* Event API */

// Map standard mouse events to touch events
	maps.touchEvents = {
		'mousedown': 'touchstart',
		'mouseup': 'touchend',
		'mousemove': 'touchmove'
	};
// Map standard touch events to mouse events
	maps.mouseEvents = {
		'touchstart': 'mousedown',
		'touchend': 'mouseup',
		'touchmove': 'mousemove'
	};

// Convert mouse event name to a corresponding touch event name ( if possible )
	function _getTouchEventName( eventName ) {
		// Detect touch event support
		if ( maps.touchEvents[ eventName ] ) {
			eventName = maps.touchEvents[ eventName ];
		}
		return eventName;
	}
// Convert touch event name to a corresponding mouse event name
	function _getMouseEventName( eventName ) {
		if ( maps.mouseEvents[ eventName ] ) {
			eventName = maps.mouseEvents[ eventName ];
		}
		return eventName;
	}

// Bind event to jCanvas layer using standard jQuery events
	function _createEvent( eventName ) {

		jCanvas.events[ eventName ] = function ( $canvas, data ) {
			var helperEventName, touchEventName, eventCache;

			// Retrieve canvas's event cache
			eventCache = data.event;

			// Both mouseover/mouseout events will be managed by a single mousemove event
			helperEventName = ( eventName === 'mouseover' || eventName === 'mouseout' ) ? 'mousemove' : eventName;
			touchEventName = _getTouchEventName( helperEventName );

			function eventCallback( event ) {
				// Cache current mouse position and redraw layers
				eventCache.x = event.offsetX;
				eventCache.y = event.offsetY;
				eventCache.type = helperEventName;
				eventCache.event = event;
				// Redraw layers on every trigger of the event
				$canvas.drawLayers( {
					resetFire: TRUE
				} );
				// Prevent default event behavior
				event.preventDefault();
			}

			// Ensure the event is not bound more than once
			if ( !data.events[ helperEventName ] ) {
				// Bind one canvas event which handles all layer events of that type
				if ( touchEventName !== helperEventName ) {
					$canvas.bind( helperEventName + '.jCanvas ' + touchEventName + '.jCanvas', eventCallback );
				} else {
					$canvas.bind( helperEventName + '.jCanvas', eventCallback );
				}
				// Prevent this event from being bound twice
				data.events[ helperEventName ] = TRUE;
			}
		};
	}
	function _createEvents( eventNames ) {
		var n;
		for ( n = 0; n < eventNames.length; n += 1 ) {
			_createEvent( eventNames[ n ] );
		}
	}
// Populate jCanvas events object with some standard events
	_createEvents( [
		'click',
		'dblclick',
		'mousedown',
		'mouseup',
		'mousemove',
		'mouseover',
		'mouseout',
		'touchstart',
		'touchmove',
		'touchend',
		'contextmenu'
	] );

// Check if event fires when a drawing is drawn
	function _detectEvents( canvas, ctx, params ) {
		var layer, data, eventCache, intersects,
			transforms, x, y, angle;

		// Use the layer object stored by the given parameters object
		layer = params._args;
		// Canvas must have event bindings
		if ( layer ) {

			data = _getCanvasData( canvas );
			eventCache = data.event;
			if ( eventCache.x !== NULL && eventCache.y !== NULL ) {
				// Respect user-defined pixel ratio
				x = eventCache.x * data.pixelRatio;
				y = eventCache.y * data.pixelRatio;
				// Determine if the given coordinates are in the current path
				intersects = ctx.isPointInPath( x, y ) || ( ctx.isPointInStroke && ctx.isPointInStroke( x, y ) );
			}
			transforms = data.transforms;

			// Allow callback functions to retrieve the mouse coordinates
			layer.eventX = eventCache.x;
			layer.eventY = eventCache.y;
			layer.event = eventCache.event;

			// Adjust coordinates to match current canvas transformation

			// Keep track of some transformation values
			angle = data.transforms.rotate;
			x = layer.eventX;
			y = layer.eventY;

			if ( angle !== 0 ) {
				// Rotate coordinates if coordinate space has been rotated
				layer._eventX = ( x * cos( -angle ) ) - ( y * sin( -angle ) );
				layer._eventY = ( y * cos( -angle ) ) + ( x * sin( -angle ) );
			} else {
				// Otherwise, no calculations need to be made
				layer._eventX = x;
				layer._eventY = y;
			}

			// Scale coordinates
			layer._eventX /= transforms.scaleX;
			layer._eventY /= transforms.scaleY;

			// If layer intersects with cursor
			if ( intersects ) {
				// Add it to a list of layers that intersect with cursor
				data.intersecting.push( layer );
			}
			layer.intersects = !!intersects;
		}
	}

// Normalize offsetX and offsetY for all browsers
	$.event.fix = function ( event ) {
		var offset, originalEvent, touches;

		event = jQueryEventFix.call( $.event, event );
		originalEvent = event.originalEvent;

		// originalEvent does not exist for manually-triggered events
		if ( originalEvent ) {

			touches = originalEvent.changedTouches;

			// If offsetX and offsetY are not supported, define them
			if ( event.pageX !== UNDEFINED && event.offsetX === UNDEFINED ) {
				offset = $( event.currentTarget ).offset();
				if ( offset ) {
					event.offsetX = event.pageX - offset.left;
					event.offsetY = event.pageY - offset.top;
				}
			} else if ( touches ) {
				// Enable offsetX and offsetY for mobile devices
				offset = $( event.currentTarget ).offset();
				if ( offset ) {
					event.offsetX = touches[ 0 ].pageX - offset.left;
					event.offsetY = touches[ 0 ].pageY - offset.top;
				}
			}

		}
		return event;
	};

	/* Drawing API */

// Map drawing names with their respective method names
	maps.drawings = {
		'arc': 'drawArc',
		'bezier': 'drawBezier',
		'ellipse': 'drawEllipse',
		'function': 'draw',
		'image': 'drawImage',
		'line': 'drawLine',
		'path': 'drawPath',
		'polygon': 'drawPolygon',
		'slice': 'drawSlice',
		'quadratic': 'drawQuadratic',
		'rectangle': 'drawRect',
		'text': 'drawText',
		'vector': 'drawVector',
		'save': 'saveCanvas',
		'restore': 'restoreCanvas',
		'rotate': 'rotateCanvas',
		'scale': 'scaleCanvas',
		'translate': 'translateCanvas'
	};

// Draws on canvas using a function
	$.fn.draw = function draw( args ) {
		var $canvases = this, $canvas, e, ctx,
			params = new jCanvasObject( args ),
			layer;

		// Draw using any other method
		if ( maps.drawings[ params.type ] && params.type !== 'function' ) {

			$canvases[ maps.drawings[ params.type ] ]( args );

		} else {

			for ( e = 0; e < $canvases.length; e += 1 ) {
				$canvas = $( $canvases[ e ] );
				ctx = _getContext( $canvases[ e ] );
				if ( ctx ) {

					params = new jCanvasObject( args );
					layer = _addLayer( $canvases[ e ], params, args, draw );
					if ( params.visible ) {

						if ( params.fn ) {
							// Call the given user-defined function
							params.fn.call( $canvases[ e ], ctx, params );
						}

					}

				}
			}

		}
		return $canvases;
	};

// Clears canvas
	$.fn.clearCanvas = function clearCanvas( args ) {
		var $canvases = this, e, ctx,
			params = new jCanvasObject( args ),
			layer;

		for ( e = 0; e < $canvases.length; e += 1 ) {
			ctx = _getContext( $canvases[ e ] );
			if ( ctx ) {

				if ( params.width === NULL || params.height === NULL ) {
					// Clear entire canvas if width/height is not given

					// Reset current transformation temporarily to ensure that the entire canvas is cleared
					ctx.save();
					ctx.setTransform( 1, 0, 0, 1, 0, 0 );
					ctx.clearRect( 0, 0, $canvases[ e ].width, $canvases[ e ].height );
					ctx.restore();

				} else {
					// Otherwise, clear the defined section of the canvas

					// Transform clear rectangle
					layer = _addLayer( $canvases[ e ], params, args, clearCanvas );
					_transformShape( $canvases[ e ], ctx, params, params.width, params.height );
					ctx.clearRect( params.x - ( params.width / 2 ), params.y - ( params.height / 2 ), params.width, params.height );
					// Restore previous transformation
					_restoreTransform( ctx, params );

				}

			}
		}
		return $canvases;
	};

	/* Transformation API */

// Restores canvas
	$.fn.saveCanvas = function saveCanvas( args ) {
		var $canvases = this, e, ctx,
			params, layer,
			data, i;

		for ( e = 0; e < $canvases.length; e += 1 ) {
			ctx = _getContext( $canvases[ e ] );
			if ( ctx ) {

				data = _getCanvasData( $canvases[ e ] );

				params = new jCanvasObject( args );
				layer = _addLayer( $canvases[ e ], params, args, saveCanvas );

				// Restore a number of times using the given count
				for ( i = 0; i < params.count; i += 1 ) {
					_saveCanvas( ctx, data );
				}

			}
		}
		return $canvases;
	};

// Restores canvas
	$.fn.restoreCanvas = function restoreCanvas( args ) {
		var $canvases = this, e, ctx,
			params, layer,
			data, i;

		for ( e = 0; e < $canvases.length; e += 1 ) {
			ctx = _getContext( $canvases[ e ] );
			if ( ctx ) {

				data = _getCanvasData( $canvases[ e ] );

				params = new jCanvasObject( args );
				layer = _addLayer( $canvases[ e ], params, args, restoreCanvas );

				// Restore a number of times using the given count
				for ( i = 0; i < params.count; i += 1 ) {
					_restoreCanvas( ctx, data );
				}

			}
		}
		return $canvases;
	};

// Rotates canvas (internal)
	function _rotateCanvas( ctx, params, transforms ) {

		// Get conversion factor for radians
		params._toRad = ( params.inDegrees ? ( PI / 180 ) : 1 );

		// Rotate canvas using shape as center of rotation
		ctx.translate( params.x, params.y );
		ctx.rotate( params.rotate * params._toRad );
		ctx.translate( -params.x, -params.y );

		// If transformation data was given
		if ( transforms ) {
			// Update transformation data
			transforms.rotate += ( params.rotate * params._toRad );
		}
	}

// Scales canvas (internal)
	function _scaleCanvas( ctx, params, transforms ) {

		// Scale both the x- and y- axis using the 'scale' property
		if ( params.scale !== 1 ) {
			params.scaleX = params.scaleY = params.scale;
		}

		// Scale canvas using shape as center of rotation
		ctx.translate( params.x, params.y );
		ctx.scale( params.scaleX, params.scaleY );
		ctx.translate( -params.x, -params.y );

		// If transformation data was given
		if ( transforms ) {
			// Update transformation data
			transforms.scaleX *= params.scaleX;
			transforms.scaleY *= params.scaleY;
		}
	}

// Translates canvas (internal)
	function _translateCanvas( ctx, params, transforms ) {

		// Translate both the x- and y-axis using the 'translate' property
		if ( params.translate ) {
			params.translateX = params.translateY = params.translate;
		}

		// Translate canvas
		ctx.translate( params.translateX, params.translateY );

		// If transformation data was given
		if ( transforms ) {
			// Update transformation data
			transforms.translateX += params.translateX;
			transforms.translateY += params.translateY;
		}
	}

// Rotates canvas
	$.fn.rotateCanvas = function rotateCanvas( args ) {
		var $canvases = this, e, ctx,
			params, layer,
			data;

		for ( e = 0; e < $canvases.length; e += 1 ) {
			ctx = _getContext( $canvases[ e ] );
			if ( ctx ) {

				data = _getCanvasData( $canvases[ e ] );

				params = new jCanvasObject( args );
				layer = _addLayer( $canvases[ e ], params, args, rotateCanvas );

				// Autosave transformation state by default
				if ( params.autosave ) {
					// Automatically save transformation state by default
					_saveCanvas( ctx, data );
				}
				_rotateCanvas( ctx, params, data.transforms );
			}

		}
		return $canvases;
	};

// Scales canvas
	$.fn.scaleCanvas = function scaleCanvas( args ) {
		var $canvases = this, e, ctx,
			params, layer,
			data;

		for ( e = 0; e < $canvases.length; e += 1 ) {
			ctx = _getContext( $canvases[ e ] );
			if ( ctx ) {

				data = _getCanvasData( $canvases[ e ] );

				params = new jCanvasObject( args );
				layer = _addLayer( $canvases[ e ], params, args, scaleCanvas );

				// Autosave transformation state by default
				if ( params.autosave ) {
					// Automatically save transformation state by default
					_saveCanvas( ctx, data );
				}
				_scaleCanvas( ctx, params, data.transforms );

			}
		}
		return $canvases;
	};

// Translates canvas
	$.fn.translateCanvas = function translateCanvas( args ) {
		var $canvases = this, e, ctx,
			params, layer,
			data;

		for ( e = 0; e < $canvases.length; e += 1 ) {
			ctx = _getContext( $canvases[ e ] );
			if ( ctx ) {

				data = _getCanvasData( $canvases[ e ] );

				params = new jCanvasObject( args );
				layer = _addLayer( $canvases[ e ], params, args, translateCanvas );

				// Autosave transformation state by default
				if ( params.autosave ) {
					// Automatically save transformation state by default
					_saveCanvas( ctx, data );
				}
				_translateCanvas( ctx, params, data.transforms );

			}
		}
		return $canvases;
	};

	/* Shape API */

// Draws rectangle
	$.fn.drawRect = function drawRect( args ) {
		var $canvases = this, e, ctx,
			params, layer,
			x1, y1,
			x2, y2,
			r, temp;

		for ( e = 0; e < $canvases.length; e += 1 ) {
			ctx = _getContext( $canvases[ e ] );
			if ( ctx ) {

				params = new jCanvasObject( args );
				layer = _addLayer( $canvases[ e ], params, args, drawRect );
				if ( params.visible ) {

					_transformShape( $canvases[ e ], ctx, params, params.width, params.height );
					_setGlobalProps( $canvases[ e ], ctx, params );

					ctx.beginPath();
					if ( params.width && params.height ) {
						x1 = params.x - ( params.width / 2 );
						y1 = params.y - ( params.height / 2 );
						r = abs( params.cornerRadius );
						// If corner radius is defined and is not zero
						if ( r ) {
							// Draw rectangle with rounded corners if cornerRadius is defined

							x2 = params.x + ( params.width / 2 );
							y2 = params.y + ( params.height / 2 );

							// Handle negative width
							if ( params.width < 0 ) {
								temp = x1;
								x1 = x2;
								x2 = temp;
							}
							// Handle negative height
							if ( params.height < 0 ) {
								temp = y1;
								y1 = y2;
								y2 = temp;
							}

							// Prevent over-rounded corners
							if ( ( x2 - x1 ) - ( 2 * r ) < 0 ) {
								r = ( x2 - x1 ) / 2;
							}
							if ( ( y2 - y1 ) - ( 2 * r ) < 0 ) {
								r = ( y2 - y1 ) / 2;
							}

							// Draw rectangle
							ctx.moveTo( x1 + r, y1 );
							ctx.lineTo( x2 - r, y1 );
							ctx.arc( x2 - r, y1 + r, r, 3 * PI / 2, PI * 2, FALSE );
							ctx.lineTo( x2, y2 - r );
							ctx.arc( x2 - r, y2 - r, r, 0, PI / 2, FALSE );
							ctx.lineTo( x1 + r, y2 );
							ctx.arc( x1 + r, y2 - r, r, PI / 2, PI, FALSE );
							ctx.lineTo( x1, y1 + r );
							ctx.arc( x1 + r, y1 + r, r, PI, 3 * PI / 2, FALSE );
							// Always close path
							params.closed = TRUE;

						} else {

							// Otherwise, draw rectangle with square corners
							ctx.rect( x1, y1, params.width, params.height );

						}
					}
					// Check for jCanvas events
					_detectEvents( $canvases[ e ], ctx, params );
					// Close rectangle path
					_closePath( $canvases[ e ], ctx, params );
				}
			}
		}
		return $canvases;
	};

// Retrieves a coterminal angle between 0 and 2pi for the given angle
	function _getCoterminal( angle ) {
		while ( angle < 0 ) {
			angle += ( 2 * PI );
		}
		return angle;
	}

// Retrieves the x-coordinate for the given angle in a circle
	function _getArcX( params, angle ) {
		return params.x + ( params.radius * cos( angle ) );
	}
// Retrieves the y-coordinate for the given angle in a circle
	function _getArcY( params, angle ) {
		return params.y + ( params.radius * sin( angle ) );
	}

// Draws arc (internal)
	function _drawArc( canvas, ctx, params, path ) {
		var x1, y1, x2, y2,
			x3, y3, x4, y4,
			offsetX, offsetY,
			diff;

		// Determine offset from dragging
		if ( params === path ) {
			offsetX = 0;
			offsetY = 0;
		} else {
			offsetX = params.x;
			offsetY = params.y;
		}

		// Convert default end angle to radians
		if ( !path.inDegrees && path.end === 360 ) {
			path.end = PI * 2;
		}

		// Convert angles to radians
		path.start *= params._toRad;
		path.end *= params._toRad;
		// Consider 0deg due north of arc
		path.start -= ( PI / 2 );
		path.end -= ( PI / 2 );

		// Ensure arrows are pointed correctly for CCW arcs
		diff = PI / 180;
		if ( path.ccw ) {
			diff *= -1;
		}

		// Calculate coordinates for start arrow
		x1 = _getArcX( path, path.start + diff );
		y1 = _getArcY( path, path.start + diff );
		x2 = _getArcX( path, path.start );
		y2 = _getArcY( path, path.start );

		_addStartArrow(
			canvas, ctx,
			params, path,
			x1, y1,
			x2, y2
		);

		// Draw arc
		ctx.arc( path.x + offsetX, path.y + offsetY, path.radius, path.start, path.end, path.ccw );

		// Calculate coordinates for end arrow
		x3 = _getArcX( path, path.end + diff );
		y3 = _getArcY( path, path.end + diff );
		x4 = _getArcX( path, path.end );
		y4 = _getArcY( path, path.end );

		_addEndArrow(
			canvas, ctx,
			params, path,
			x4, y4,
			x3, y3
		);
	}

// Draws arc or circle
	$.fn.drawArc = function drawArc( args ) {
		var $canvases = this, e, ctx,
			params, layer;

		for ( e = 0; e < $canvases.length; e += 1 ) {
			ctx = _getContext( $canvases[ e ] );
			if ( ctx ) {

				params = new jCanvasObject( args );
				layer = _addLayer( $canvases[ e ], params, args, drawArc );
				if ( params.visible ) {

					_transformShape( $canvases[ e ], ctx, params, params.radius * 2 );
					_setGlobalProps( $canvases[ e ], ctx, params );

					ctx.beginPath();
					_drawArc( $canvases[ e ], ctx, params, params );
					// Check for jCanvas events
					_detectEvents( $canvases[ e ], ctx, params );
					// Optionally close path
					_closePath( $canvases[ e ], ctx, params );

				}

			}
		}
		return $canvases;
	};

// Draws ellipse
	$.fn.drawEllipse = function drawEllipse( args ) {
		var $canvases = this, e, ctx,
			params, layer,
			controlW,
			controlH;

		for ( e = 0; e < $canvases.length; e += 1 ) {
			ctx = _getContext( $canvases[ e ] );
			if ( ctx ) {

				params = new jCanvasObject( args );
				layer = _addLayer( $canvases[ e ], params, args, drawEllipse );
				if ( params.visible ) {

					_transformShape( $canvases[ e ], ctx, params, params.width, params.height );
					_setGlobalProps( $canvases[ e ], ctx, params );

					// Calculate control width and height
					controlW = params.width * ( 4 / 3 );
					controlH = params.height;

					// Create ellipse using curves
					ctx.beginPath();
					ctx.moveTo( params.x, params.y - ( controlH / 2 ) );
					// Left side
					ctx.bezierCurveTo( params.x - ( controlW / 2 ), params.y - ( controlH / 2 ), params.x - ( controlW / 2 ), params.y + ( controlH / 2 ), params.x, params.y + ( controlH / 2 ) );
					// Right side
					ctx.bezierCurveTo( params.x + ( controlW / 2 ), params.y + ( controlH / 2 ), params.x + ( controlW / 2 ), params.y - ( controlH / 2 ), params.x, params.y - ( controlH / 2 ) );
					// Check for jCanvas events
					_detectEvents( $canvases[ e ], ctx, params );
					// Always close path
					params.closed = TRUE;
					_closePath( $canvases[ e ], ctx, params );

				}
			}
		}
		return $canvases;
	};

// Draws a regular ( equal-angled ) polygon
	$.fn.drawPolygon = function drawPolygon( args ) {
		var $canvases = this, e, ctx,
			params, layer,
			theta, dtheta, hdtheta,
			apothem,
			x, y, i;

		for ( e = 0; e < $canvases.length; e += 1 ) {
			ctx = _getContext( $canvases[ e ] );
			if ( ctx ) {

				params = new jCanvasObject( args );
				layer = _addLayer( $canvases[ e ], params, args, drawPolygon );
				if ( params.visible ) {

					_transformShape( $canvases[ e ], ctx, params, params.radius * 2 );
					_setGlobalProps( $canvases[ e ], ctx, params );

					// Polygon's central angle
					dtheta = ( 2 * PI ) / params.sides;
					// Half of dtheta
					hdtheta = dtheta / 2;
					// Polygon's starting angle
					theta = hdtheta + ( PI / 2 );
					// Distance from polygon's center to the middle of its side
					apothem = params.radius * cos( hdtheta );

					// Calculate path and draw
					ctx.beginPath();
					for ( i = 0; i < params.sides; i += 1 ) {

						// Draw side of polygon
						x = params.x + ( params.radius * cos( theta ) );
						y = params.y + ( params.radius * sin( theta ) );

						// Plot point on polygon
						ctx.lineTo( x, y );

						// Project side if chosen
						if ( params.concavity ) {
							// Sides are projected from the polygon's apothem
							x = params.x + ( ( apothem + ( -apothem * params.concavity ) ) * cos( theta + hdtheta ) );
							y = params.y + ( ( apothem + ( -apothem * params.concavity ) ) * sin( theta + hdtheta ) );
							ctx.lineTo( x, y );
						}

						// Increment theta by delta theta
						theta += dtheta;

					}
					// Check for jCanvas events
					_detectEvents( $canvases[ e ], ctx, params );
					// Always close path
					params.closed = TRUE;
					_closePath( $canvases[ e ], ctx, params );

				}
			}
		}
		return $canvases;
	};

// Draws pie-shaped slice
	$.fn.drawSlice = function drawSlice( args ) {
		var $canvases = this, $canvas, e, ctx,
			params, layer,
			angle, dx, dy;

		for ( e = 0; e < $canvases.length; e += 1 ) {
			$canvas = $( $canvases[ e ] );
			ctx = _getContext( $canvases[ e ] );
			if ( ctx ) {

				params = new jCanvasObject( args );
				layer = _addLayer( $canvases[ e ], params, args, drawSlice );
				if ( params.visible ) {

					_transformShape( $canvases[ e ], ctx, params, params.radius * 2 );
					_setGlobalProps( $canvases[ e ], ctx, params );

					// Perform extra calculations

					// Convert angles to radians
					params.start *= params._toRad;
					params.end *= params._toRad;
					// Consider 0deg at north of arc
					params.start -= ( PI / 2 );
					params.end -= ( PI / 2 );

					// Find positive equivalents of angles
					params.start = _getCoterminal( params.start );
					params.end = _getCoterminal( params.end );
					// Ensure start angle is less than end angle
					if ( params.end < params.start ) {
						params.end += ( 2 * PI );
					}

					// Calculate angular position of slice
					angle = ( ( params.start + params.end ) / 2 );

					// Calculate ratios for slice's angle
					dx = ( params.radius * params.spread * cos( angle ) );
					dy = ( params.radius * params.spread * sin( angle ) );

					// Adjust position of slice
					params.x += dx;
					params.y += dy;

					// Draw slice
					ctx.beginPath();
					ctx.arc( params.x, params.y, params.radius, params.start, params.end, params.ccw );
					ctx.lineTo( params.x, params.y );
					// Check for jCanvas events
					_detectEvents( $canvases[ e ], ctx, params );
					// Always close path
					params.closed = TRUE;
					_closePath( $canvases[ e ], ctx, params );

				}

			}
		}
		return $canvases;
	};

	/* Path API */

// Adds arrow to path using the given properties
	function _addArrow( canvas, ctx, params, path, x1, y1, x2, y2 ) {
		var leftX, leftY,
			rightX, rightY,
			offsetX, offsetY,
			angle;

		// If arrow radius is given and path is not closed
		if ( path.arrowRadius && !params.closed ) {

			// Calculate angle
			angle = atan2( ( y2 - y1 ), ( x2 - x1 ) );
			// Adjust angle correctly
			angle -= PI;
			// Calculate offset to place arrow at edge of path
			offsetX = ( params.strokeWidth * cos( angle ) );
			offsetY = ( params.strokeWidth * sin( angle ) );

			// Calculate coordinates for left half of arrow
			leftX = x2 + ( path.arrowRadius * cos( angle + ( path.arrowAngle / 2 ) ) );
			leftY = y2 + ( path.arrowRadius * sin( angle + ( path.arrowAngle / 2 ) ) );
			// Calculate coordinates for right half of arrow
			rightX = x2 + ( path.arrowRadius * cos( angle - ( path.arrowAngle / 2 ) ) );
			rightY = y2 + ( path.arrowRadius * sin( angle - ( path.arrowAngle / 2 ) ) );

			// Draw left half of arrow
			ctx.moveTo( leftX - offsetX, leftY - offsetY );
			ctx.lineTo( x2 - offsetX, y2 - offsetY );
			// Draw right half of arrow
			ctx.lineTo( rightX - offsetX, rightY - offsetY );

			// Visually connect arrow to path
			ctx.moveTo( x2 - offsetX, y2 - offsetY );
			ctx.lineTo( x2 + offsetX, y2 + offsetY );
			// Move back to end of path
			ctx.moveTo( x2, y2 );

		}
	}

// Optionally adds arrow to start of path
	function _addStartArrow( canvas, ctx, params, path, x1, y1, x2, y2 ) {
		if ( !path._arrowAngleConverted ) {
			path.arrowAngle *= params._toRad;
			path._arrowAngleConverted = TRUE;
		}
		if ( path.startArrow ) {
			_addArrow( canvas, ctx, params, path, x1, y1, x2, y2 );
		}
	}

// Optionally adds arrow to end of path
	function _addEndArrow( canvas, ctx, params, path, x1, y1, x2, y2 ) {
		if ( !path._arrowAngleConverted ) {
			path.arrowAngle *= params._toRad;
			path._arrowAngleConverted = TRUE;
		}
		if ( path.endArrow ) {
			_addArrow( canvas, ctx, params, path, x1, y1, x2, y2 );
		}
	}

// Draws line (internal)
	function _drawLine( canvas, ctx, params, path ) {
		var l,
			lx, ly;
		l = 2;
		_addStartArrow(
			canvas, ctx,
			params, path,
			path.x2 + params.x,
			path.y2 + params.y,
			path.x1 + params.x,
			path.y1 + params.y
		);
		if ( path.x1 !== UNDEFINED && path.y1 !== UNDEFINED ) {
			ctx.moveTo( path.x1 + params.x, path.y1 + params.y );
		}
		while ( TRUE ) {
			// Calculate next coordinates
			lx = path[ 'x' + l ];
			ly = path[ 'y' + l ];
			// If coordinates are given
			if ( lx !== UNDEFINED && ly !== UNDEFINED ) {
				// Draw next line
				ctx.lineTo( lx + params.x, ly + params.y );
				l += 1;
			} else {
				// Otherwise, stop drawing
				break;
			}
		}
		l -= 1;
		// Optionally add arrows to path
		_addEndArrow(
			canvas, ctx,
			params,
			path,
			path[ 'x' + ( l - 1 ) ] + params.x,
			path[ 'y' + ( l - 1 ) ] + params.y,
			path[ 'x' + l ] + params.x,
			path[ 'y' + l ] + params.y
		);
	}

// Draws line
	$.fn.drawLine = function drawLine( args ) {
		var $canvases = this, e, ctx,
			params, layer;

		for ( e = 0; e < $canvases.length; e += 1 ) {
			ctx = _getContext( $canvases[ e ] );
			if ( ctx ) {

				params = new jCanvasObject( args );
				layer = _addLayer( $canvases[ e ], params, args, drawLine );
				if ( params.visible ) {

					_transformShape( $canvases[ e ], ctx, params );
					_setGlobalProps( $canvases[ e ], ctx, params );

					// Draw each point
					ctx.beginPath();
					_drawLine( $canvases[ e ], ctx, params, params );
					// Check for jCanvas events
					_detectEvents( $canvases[ e ], ctx, params );
					// Optionally close path
					_closePath( $canvases[ e ], ctx, params );

				}

			}
		}
		return $canvases;
	};

// Draws quadratic curve (internal)
	function _drawQuadratic( canvas, ctx, params, path ) {
		var l,
			lx, ly,
			lcx, lcy;

		l = 2;

		_addStartArrow(
			canvas,
			ctx,
			params,
			path,
			path.cx1 + params.x,
			path.cy1 + params.y,
			path.x1 + params.x,
			path.y1 + params.y
		);

		if ( path.x1 !== UNDEFINED && path.y1 !== UNDEFINED ) {
			ctx.moveTo( path.x1 + params.x, path.y1 + params.y );
		}
		while ( TRUE ) {
			// Calculate next coordinates
			lx = path[ 'x' + l ];
			ly = path[ 'y' + l ];
			lcx = path[ 'cx' + ( l - 1 ) ];
			lcy = path[ 'cy' + ( l - 1 ) ];
			// If coordinates are given
			if ( lx !== UNDEFINED && ly !== UNDEFINED && lcx !== UNDEFINED && lcy !== UNDEFINED ) {
				// Draw next curve
				ctx.quadraticCurveTo( lcx + params.x, lcy + params.y, lx + params.x, ly + params.y );
				l += 1;
			} else {
				// Otherwise, stop drawing
				break;
			}
		}
		l -= 1;
		_addEndArrow(
			canvas,
			ctx,
			params,
			path,
			path[ 'cx' + ( l - 1 ) ] + params.x,
			path[ 'cy' + ( l - 1 ) ] + params.y,
			path[ 'x' + l ] + params.x,
			path[ 'y' + l ] + params.y
		);
	}

// Draws quadratic curve
	$.fn.drawQuadratic = function drawQuadratic( args ) {
		var $canvases = this, e, ctx,
			params, layer;

		for ( e = 0; e < $canvases.length; e += 1 ) {
			ctx = _getContext( $canvases[ e ] );
			if ( ctx ) {

				params = new jCanvasObject( args );
				layer = _addLayer( $canvases[ e ], params, args, drawQuadratic );
				if ( params.visible ) {

					_transformShape( $canvases[ e ], ctx, params );
					_setGlobalProps( $canvases[ e ], ctx, params );

					// Draw each point
					ctx.beginPath();
					_drawQuadratic( $canvases[ e ], ctx, params, params );
					// Check for jCanvas events
					_detectEvents( $canvases[ e ], ctx, params );
					// Optionally close path
					_closePath( $canvases[ e ], ctx, params );

				}
			}
		}
		return $canvases;
	};

// Draws Bezier curve (internal)
	function _drawBezier( canvas, ctx, params, path ) {
		var l, lc,
			lx, ly,
			lcx1, lcy1,
			lcx2, lcy2;

		l = 2;
		lc = 1;

		_addStartArrow(
			canvas,
			ctx,
			params,
			path,
			path.cx1 + params.x,
			path.cy1 + params.y,
			path.x1 + params.x,
			path.y1 + params.y
		);

		if ( path.x1 !== UNDEFINED && path.y1 !== UNDEFINED ) {
			ctx.moveTo( path.x1 + params.x, path.y1 + params.y );
		}
		while ( TRUE ) {
			// Calculate next coordinates
			lx = path[ 'x' + l ];
			ly = path[ 'y' + l ];
			lcx1 = path[ 'cx' + lc ];
			lcy1 = path[ 'cy' + lc ];
			lcx2 = path[ 'cx' + ( lc + 1 ) ];
			lcy2 = path[ 'cy' + ( lc + 1 ) ];
			// If next coordinates are given
			if ( lx !== UNDEFINED && ly !== UNDEFINED && lcx1 !== UNDEFINED && lcy1 !== UNDEFINED && lcx2 !== UNDEFINED && lcy2 !== UNDEFINED ) {
				// Draw next curve
				ctx.bezierCurveTo( lcx1 + params.x, lcy1 + params.y, lcx2 + params.x, lcy2 + params.y, lx + params.x, ly + params.y );
				l += 1;
				lc += 2;
			} else {
				// Otherwise, stop drawing
				break;
			}
		}
		l -= 1;
		lc -= 2;
		_addEndArrow(
			canvas,
			ctx,
			params,
			path,
			path[ 'cx' + ( lc + 1 ) ] + params.x,
			path[ 'cy' + ( lc + 1 ) ] + params.y,
			path[ 'x' + l ] + params.x,
			path[ 'y' + l ] + params.y
		);
	}

// Draws Bezier curve
	$.fn.drawBezier = function drawBezier( args ) {
		var $canvases = this, e, ctx,
			params, layer;

		for ( e = 0; e < $canvases.length; e += 1 ) {
			ctx = _getContext( $canvases[ e ] );
			if ( ctx ) {

				params = new jCanvasObject( args );
				layer = _addLayer( $canvases[ e ], params, args, drawBezier );
				if ( params.visible ) {

					_transformShape( $canvases[ e ], ctx, params );
					_setGlobalProps( $canvases[ e ], ctx, params );

					// Draw each point
					ctx.beginPath();
					_drawBezier( $canvases[ e ], ctx, params, params );
					// Check for jCanvas events
					_detectEvents( $canvases[ e ], ctx, params );
					// Optionally close path
					_closePath( $canvases[ e ], ctx, params );

				}
			}
		}
		return $canvases;
	};

// Retrieves the x-coordinate for the given vector angle and length
	function _getVectorX( params, angle, length ) {
		angle *= params._toRad;
		angle -= ( PI / 2 );
		return ( length * cos( angle ) );
	}
// Retrieves the y-coordinate for the given vector angle and length
	function _getVectorY( params, angle, length ) {
		angle *= params._toRad;
		angle -= ( PI / 2 );
		return ( length * sin( angle ) );
	}

// Draws vector (internal) #2
	function _drawVector( canvas, ctx, params, path ) {
		var l, angle, length,
			offsetX, offsetY,
			x, y,
			x2, y2,
			x3, y3,
			x4, y4;

		// Determine offset from dragging
		if ( params === path ) {
			offsetX = 0;
			offsetY = 0;
		} else {
			offsetX = params.x;
			offsetY = params.y;
		}

		l = 1;
		x = x2 = x3 = x4 = path.x + offsetX;
		y = y2 = y3 = y4 = path.y + offsetY;

		_addStartArrow(
			canvas, ctx,
			params, path,
			x + _getVectorX( params, path.a1, path.l1 ),
			y + _getVectorY( params, path.a1, path.l1 ),
			x,
			y
		);

		// The vector starts at the given ( x, y ) coordinates
		if ( path.x !== UNDEFINED && path.y !== UNDEFINED ) {
			ctx.moveTo( x, y );
		}
		while ( TRUE ) {

			angle = path[ 'a' + l ];
			length = path[ 'l' + l ];

			if ( angle !== UNDEFINED && length !== UNDEFINED ) {
				// Convert the angle to radians with 0 degrees starting at north
				// Keep track of last two coordinates
				x3 = x4;
				y3 = y4;
				// Compute ( x, y ) coordinates from angle and length
				x4 += _getVectorX( params, angle, length );
				y4 += _getVectorY( params, angle, length );
				// Store the second point
				if ( l === 1 ) {
					x2 = x4;
					y2 = y4;
				}
				ctx.lineTo( x4, y4 );
				l += 1;
			} else {
				// Otherwise, stop drawing
				break;
			}

		}
		_addEndArrow(
			canvas, ctx,
			params, path,
			x3, y3,
			x4, y4
		);
	}

// Draws vector
	$.fn.drawVector = function drawVector( args ) {
		var $canvases = this, e, ctx,
			params, layer;

		for ( e = 0; e < $canvases.length; e += 1 ) {
			ctx = _getContext( $canvases[ e ] );
			if ( ctx ) {

				params = new jCanvasObject( args );
				layer = _addLayer( $canvases[ e ], params, args, drawVector );
				if ( params.visible ) {

					_transformShape( $canvases[ e ], ctx, params );
					_setGlobalProps( $canvases[ e ], ctx, params );

					// Draw each point
					ctx.beginPath();
					_drawVector( $canvases[ e ], ctx, params, params );
					// Check for jCanvas events
					_detectEvents( $canvases[ e ], ctx, params );
					// Optionally close path
					_closePath( $canvases[ e ], ctx, params );

				}
			}
		}
		return $canvases;
	};

// Draws a path consisting of one or more subpaths
	$.fn.drawPath = function drawPath( args ) {
		var $canvases = this, e, ctx,
			params, layer,
			l, lp;

		for ( e = 0; e < $canvases.length; e += 1 ) {
			ctx = _getContext( $canvases[ e ] );
			if ( ctx ) {

				params = new jCanvasObject( args );
				layer = _addLayer( $canvases[ e ], params, args, drawPath );
				if ( params.visible ) {

					_transformShape( $canvases[ e ], ctx, params );
					_setGlobalProps( $canvases[ e ], ctx, params );

					ctx.beginPath();
					l = 1;
					while ( TRUE ) {
						lp = params[ 'p' + l ];
						if ( lp !== UNDEFINED ) {
							lp = new jCanvasObject( lp );
							if ( lp.type === 'line' ) {
								_drawLine( $canvases[ e ], ctx, params, lp );
							} else if ( lp.type === 'quadratic' ) {
								_drawQuadratic( $canvases[ e ], ctx, params, lp );
							} else if ( lp.type === 'bezier' ) {
								_drawBezier( $canvases[ e ], ctx, params, lp );
							} else if ( lp.type === 'vector' ) {
								_drawVector( $canvases[ e ], ctx, params, lp );
							} else if ( lp.type === 'arc' ) {
								_drawArc( $canvases[ e ], ctx, params, lp );
							}
							l += 1;
						} else {
							break;
						}
					}

					// Check for jCanvas events
					_detectEvents( $canvases[ e ], ctx, params );
					// Optionally close path
					_closePath( $canvases[ e ], ctx, params );

				}

			}
		}
		return $canvases;
	};

	/* Text API */

// Calculates font string and set it as the canvas font
	function _setCanvasFont( canvas, ctx, params ) {
		// Otherwise, use the given font attributes
		if ( !isNaN( Number( params.fontSize ) ) ) {
			// Give font size units if it doesn't have any
			params.fontSize += 'px';
		}
		// Set font using given font properties
		ctx.font = params.fontStyle + ' ' + params.fontSize + ' ' + params.fontFamily;
	}

// Measures canvas text
	function _measureText( canvas, ctx, params, lines ) {
		var originalSize, curWidth, l,
			propCache = caches.propCache;

		// Used cached width/height if possible
		if ( propCache.text === params.text && propCache.fontStyle === params.fontStyle && propCache.fontSize === params. fontSize && propCache.fontFamily === params.fontFamily && propCache.maxWidth === params.maxWidth && propCache.lineHeight === params.lineHeight ) {

			params.width = propCache.width;
			params.height = propCache.height;

		} else {
			// Calculate text dimensions only once

			// Calculate width of first line ( for comparison )
			params.width = ctx.measureText( lines[ 0 ] ).width;

			// Get width of longest line
			for ( l = 1; l < lines.length; l += 1 ) {

				curWidth = ctx.measureText( lines[ l ] ).width;
				// Ensure text's width is the width of its longest line
				if ( curWidth > params.width ) {
					params.width = curWidth;
				}

			}

			// Save original font size
			originalSize = canvas.style.fontSize;
			// Temporarily set canvas font size to retrieve size in pixels
			canvas.style.fontSize = params.fontSize;
			// Save text width and height in parameters object
			params.height = parseFloat( $.css( canvas, 'fontSize' ) ) * lines.length * params.lineHeight;
			// Reset font size to original size
			canvas.style.fontSize = originalSize;
		}
	}

// Wraps a string of text within a defined width
	function _wrapText( ctx, params ) {
		var allText = params.text,
		// Maximum line width ( optional )
			maxWidth = params.maxWidth,
		// Lines created by manual line breaks ( \n )
			manualLines = allText.split( '\n' ),
		// All lines created manually and by wrapping
			allLines = [],
		// Other variables
			lines, line, l,
			text, words, w;

		// Loop through manually-broken lines
		for ( l = 0; l < manualLines.length; l += 1 ) {

			text = manualLines[ l ];
			// Split line into list of words
			words = text.split( ' ' );
			lines = [];
			line = '';

			// If text is short enough initially
			// Or, if the text consists of only one word
			if ( words.length === 1 || ctx.measureText( text ).width < maxWidth ) {

				// No need to wrap text
				lines = [ text ];

			} else {

				// Wrap lines
				for ( w = 0; w < words.length; w += 1 ) {

					// Once line gets too wide, push word to next line
					if ( ctx.measureText( line + words[ w ] ).width > maxWidth ) {
						// This check prevents empty lines from being created
						if ( line !== '' ) {
							lines.push( line );
						}
						// Start new line and repeat process
						line = '';
					}
					// Add words to line until the line is too wide
					line += words[ w ];
					// Do not add a space after the last word
					if ( w !== ( words.length - 1 ) ) {
						line += ' ';
					}
				}
				// The last word should always be pushed
				lines.push( line );

			}
			// Remove extra space at the end of each line
			allLines = allLines.concat(
				lines
					.join( '\n' )
					.replace( /( (\n))|( $)/gi, '$2' )
					.split( '\n' )
			);

		}

		return allLines;
	}

// Draws text on canvas
	$.fn.drawText = function drawText( args ) {
		var $canvases = this, $canvas, e, ctx,
			params, layer,
			lines, line, l,
			fontSize, constantCloseness = 500,
			nchars, ch, c,
			x, y;

		for ( e = 0; e < $canvases.length; e += 1 ) {
			$canvas = $( $canvases[ e ] );
			ctx = _getContext( $canvases[ e ] );
			if ( ctx ) {

				params = new jCanvasObject( args );
				layer = _addLayer( $canvases[ e ], params, args, drawText );
				if ( params.visible ) {

					// Set text-specific properties
					ctx.textBaseline = params.baseline;
					ctx.textAlign = params.align;

					// Set canvas font using given properties
					_setCanvasFont( $canvases[ e ], ctx, params );

					if ( params.maxWidth !== NULL ) {
						// Wrap text using an internal function
						lines = _wrapText( ctx, params );
					} else {
						// Convert string of text to list of lines
						lines = params.text
							.toString()
							.split( '\n' );
					}

					// Calculate text's width and height
					_measureText( $canvases[ e ], ctx, params, lines );

					// If text is a layer
					if ( layer ) {
						// Copy calculated width/height to layer object
						layer.width = params.width;
						layer.height = params.height;
					}

					_transformShape( $canvases[ e ], ctx, params, params.width, params.height );
					_setGlobalProps( $canvases[ e ], ctx, params );

					// Adjust text position to accomodate different horizontal alignments
					x = params.x;
					if ( params.align === 'left' ) {
						if ( params.respectAlign ) {
							// Realign text to the left if chosen
							params.x += params.width / 2;
						} else {
							// Center text block by default
							x -= params.width / 2;
						}
					} else if ( params.align === 'right' ) {
						if ( params.respectAlign ) {
							// Realign text to the right if chosen
							params.x -= params.width / 2;
						} else {
							// Center text block by default
							x += params.width / 2;
						}
					}

					if ( params.radius ) {

						fontSize = parseFloat( params.fontSize );

						// Greater values move clockwise
						if ( params.letterSpacing === NULL ) {
							params.letterSpacing = fontSize / constantCloseness;
						}

						// Loop through each line of text
						for ( l = 0; l < lines.length; l += 1 ) {
							ctx.save();
							ctx.translate( params.x, params.y );
							line = lines[ l ];
							nchars = line.length;
							ctx.rotate( -( PI * params.letterSpacing * ( nchars - 1 ) ) / 2 );
							// Loop through characters on each line
							for ( c = 0; c < nchars; c += 1 ) {
								ch = line[ c ];
								// If character is not the first character
								if ( c !== 0 ) {
									// Rotate character onto arc
									ctx.rotate( PI * params.letterSpacing );
								}
								ctx.save();
								ctx.translate( 0, -params.radius );
								ctx.fillText( ch, 0, 0 );
								ctx.restore();
							}
							params.radius -= fontSize;
							params.letterSpacing += fontSize / ( constantCloseness * 2 * PI );
							ctx.restore();
						}

					} else {

						// Draw each line of text separately
						for ( l = 0; l < lines.length; l += 1 ) {
							line = lines[ l ];
							// Add line offset to center point, but subtract some to center everything
							y = params.y + ( l * params.height / lines.length ) - ( ( lines.length - 1 ) * params.height / lines.length ) / 2;

							ctx.shadowColor = params.shadowColor;

							// Fill & stroke text
							ctx.fillText( line, x, y );
							// Prevent extra shadow created by stroke ( but only when fill is present )
							if ( params.fillStyle !== 'transparent' ) {
								ctx.shadowColor = 'transparent';
							}
							if ( params.strokeWidth !== 0 ) {
								// Only stroke if the stroke is not 0
								ctx.strokeText( line, x, y );
							}

						}

					}

					// Adjust bounding box according to text baseline
					y = 0;
					if ( params.baseline === 'top' ) {
						y += params.height / 2;
					} else if ( params.baseline === 'bottom' ) {
						y -= params.height / 2;
					}

					// Detect jCanvas events
					if ( params._event ) {
						ctx.beginPath();
						ctx.rect(
							params.x - params.width / 2,
							params.y - params.height / 2 + y,
							params.width,
							params.height
						);
						_detectEvents( $canvases[ e ], ctx, params );
						// Close path and configure masking
						ctx.closePath();
					}
					_restoreTransform( ctx, params );

				}
			}
		}
		// Cache jCanvas parameters object for efficiency
		caches.propCache = params;
		return $canvases;
	};

// Measures text width/height using the given parameters
	$.fn.measureText = function measureText( args ) {
		var $canvases = this, ctx,
			params, lines;

		// Attempt to retrieve layer
		params = $canvases.getLayer( args );
		// If layer does not exist or if returned object is not a jCanvas layer
		if ( !params || ( params && !params._layer ) ) {
			params = new jCanvasObject( args );
		}

		ctx = _getContext( $canvases[ 0 ] );
		if ( ctx ) {

			// Set canvas font using given properties
			_setCanvasFont( $canvases[ 0 ], ctx, params );
			// Calculate width and height of text
			lines = _wrapText( ctx, params );
			_measureText( $canvases[ 0 ], ctx, params, lines );


		}

		return params;
	};

	/* Image API */

// Draws image on canvas
	$.fn.drawImage = function drawImage( args ) {
		var $canvases = this, canvas, e, ctx, data,
			params, layer,
			img, imgCtx, source,
			imageCache = caches.imageCache;

		// Draw image function
		function draw( canvas, ctx, data, params, layer ) {

			// If width and sWidth are not defined, use image width
			if ( params.width === NULL && params.sWidth === NULL ) {
				params.width = params.sWidth = img.width;
			}
			// If width and sHeight are not defined, use image height
			if ( params.height === NULL && params.sHeight === NULL ) {
				params.height = params.sHeight = img.height;
			}

			// Ensure image layer's width and height are accurate
			if ( layer ) {
				layer.width = params.width;
				layer.height = params.height;
			}

			// Only crop image if all cropping properties are given
			if ( params.sWidth !== NULL && params.sHeight !== NULL && params.sx !== NULL && params.sy !== NULL ) {

				// If width is not defined, use the given sWidth
				if ( params.width === NULL ) {
					params.width = params.sWidth;
				}
				// If height is not defined, use the given sHeight
				if ( params.height === NULL ) {
					params.height = params.sHeight;
				}

				// Optionally crop from top-left corner of region
				if ( params.cropFromCenter ) {
					params.sx += params.sWidth / 2;
					params.sy += params.sHeight / 2;
				}

				// Ensure cropped region does not escape image boundaries

				// Top
				if ( ( params.sy - ( params.sHeight / 2 ) ) < 0 ) {
					params.sy = ( params.sHeight / 2 );
				}
				// Bottom
				if ( ( params.sy + ( params.sHeight / 2 ) ) > img.height ) {
					params.sy = img.height - ( params.sHeight / 2 );
				}
				// Left
				if ( ( params.sx - ( params.sWidth / 2 ) ) < 0 ) {
					params.sx = ( params.sWidth / 2 );
				}
				// Right
				if ( ( params.sx + ( params.sWidth / 2 ) ) > img.width ) {
					params.sx = img.width - ( params.sWidth / 2 );
				}

				_transformShape( canvas, ctx, params, params.width, params.height );
				_setGlobalProps( canvas, ctx, params );

				// Draw image
				ctx.drawImage(
					img,
					params.sx - params.sWidth / 2,
					params.sy - params.sHeight / 2,
					params.sWidth,
					params.sHeight,
					params.x - params.width / 2,
					params.y - params.height / 2,
					params.width,
					params.height
				);

			} else {
				// Show entire image if no crop region is defined

				// Position/transform image if necessary
				_transformShape( canvas, ctx, params, params.width, params.height );

				// Draw image on canvas
				ctx.drawImage(
					img,
					params.x - params.width / 2,
					params.y - params.height / 2,
					params.width,
					params.height
				);

			}

			// Draw invisible rectangle to allow for events and masking
			ctx.beginPath();
			ctx.rect(
				params.x - params.width / 2,
				params.y - params.height / 2,
				params.width,
				params.height
			);
			// Check for jCanvas events
			_detectEvents( canvas, ctx, params );
			// Close path and configure masking
			ctx.closePath();
			_restoreTransform( ctx, params );
			_enableMasking( ctx, data, params );
		}
		// On load function
		function onload( canvas, ctx, data, params, layer ) {
			return function () {
				var $canvas = $( canvas );
				draw( canvas, ctx, data, params, layer );
				if ( params.layer ) {
					// Trigger 'load' event for layers
					_triggerLayerEvent( $canvas, data, layer, 'load' );
				} else if ( params.load ) {
					// Run 'load' callback for non-layers
					params.load.call( $canvas[ 0 ], layer );
				}
				// Continue drawing successive layers after this image layer has loaded
				if ( params.layer ) {
					// Store list of previous masks for each layer
					layer._masks = data.transforms.masks.slice( 0 );
					if ( params._next ) {
						// Draw successive layers
						$canvas.drawLayers( {
							clear: FALSE,
							resetFire: TRUE,
							index: params._next
						} );
					}
				}
			};
		}
		for ( e = 0; e < $canvases.length; e += 1 ) {
			canvas = $canvases[ e ];
			ctx = _getContext( $canvases[ e ] );
			if ( ctx ) {

				data = _getCanvasData( $canvases[ e ] );
				params = new jCanvasObject( args );
				layer = _addLayer( $canvases[ e ], params, args, drawImage );
				if ( params.visible ) {

					// Cache the given source
					source = params.source;

					imgCtx = source.getContext;
					if ( source.src || imgCtx ) {
						// Use image or canvas element if given
						img = source;
					} else if ( source ) {
						if ( imageCache[ source ] && imageCache[ source ].complete ) {
							// Get the image element from the cache if possible
							img = imageCache[ source ];
						} else {
							// Otherwise, get the image from the given source URL
							img = new Image();
							// If source URL is not a data URL
							if ( ! source.match( /^data:/i ) ) {
								// Set crossOrigin for this image
								img.crossOrigin = params.crossOrigin;
							}
							img.src = source;
							// Save image in cache for improved performance
							imageCache[ source ] = img;
						}
					}

					if ( img ) {
						if ( img.complete || imgCtx ) {
							// Draw image if already loaded
							onload( canvas, ctx, data, params, layer )();
						} else {
							// Otherwise, draw image when it loads
							img.onload = onload( canvas, ctx, data, params, layer );
							// Fix onload() bug in IE9
							img.src = img.src;
						}
					}

				}
			}
		}
		return $canvases;
	};

// Creates a canvas pattern object
	$.fn.createPattern = function createPattern( args ) {
		var $canvases = this, ctx,
			params,
			img, imgCtx,
			pattern, source;

		// Function to be called when pattern loads
		function onload() {
			// Create pattern
			pattern = ctx.createPattern( img, params.repeat );
			// Run callback function if defined
			if ( params.load ) {
				params.load.call( $canvases[ 0 ], pattern );
			}
		}

		ctx = _getContext( $canvases[ 0 ] );
		if ( ctx ) {

			params = new jCanvasObject( args );

			// Cache the given source
			source = params.source;

			// Draw when image is loaded ( if load() callback function is defined )

			if ( isFunction( source ) ) {
				// Draw pattern using function if given

				img = $( '<canvas />' )[ 0 ];
				img.width = params.width;
				img.height = params.height;
				imgCtx = _getContext( img );
				source.call( img, imgCtx );
				onload();

			} else {
				// Otherwise, draw pattern using source image

				imgCtx = source.getContext;
				if ( source.src || imgCtx ) {
					// Use image element if given
					img = source;
				} else {
					// Use URL if given to get the image
					img = new Image();
					// If source URL is not a data URL
					if ( ! source.match( /^data:/i ) ) {
						// Set crossOrigin for this image
						img.crossOrigin = params.crossOrigin;
					}
					img.src = source;
				}

				// Create pattern if already loaded
				if ( img.complete || imgCtx ) {
					onload();
				} else {
					img.onload = onload();
					// Fix onload() bug in IE9
					img.src = img.src;
				}

			}

		} else {

			pattern = NULL;

		}
		return pattern;
	};

// Creates a canvas gradient object
	$.fn.createGradient = function createGradient( args ) {
		var $canvases = this, ctx,
			params,
			gradient,
			stops = [], nstops,
			start, end,
			i, a, n, p;

		params = new jCanvasObject( args );
		ctx = _getContext( $canvases[ 0 ] );
		if ( ctx ) {

			// Gradient coordinates must be defined
			params.x1 = params.x1 || 0;
			params.y1 = params.y1 || 0;
			params.x2 = params.x2 || 0;
			params.y2 = params.y2 || 0;

			if ( params.r1 !== NULL && params.r2 !== NULL ) {
				// Create radial gradient if chosen
				gradient = ctx.createRadialGradient( params.x1, params.y1, params.r1, params.x2, params.y2, params.r2 );
			} else {
				// Otherwise, create a linear gradient by default
				gradient = ctx.createLinearGradient( params.x1, params.y1, params.x2, params.y2 );
			}

			// Count number of color stops
			for ( i = 1; params[ 'c' + i ] !== UNDEFINED; i += 1 ) {
				if ( params[ 's' + i ] !== UNDEFINED ) {
					stops.push( params[ 's' + i ] );
				} else {
					stops.push( NULL );
				}
			}
			nstops = stops.length;

			// Define start stop if not already defined
			if ( stops[ 0 ] === NULL ) {
				stops[ 0 ] = 0;
			}
			// Define end stop if not already defined
			if ( stops[ nstops - 1 ] === NULL ) {
				stops[ nstops - 1 ] = 1;
			}

			// Loop through color stops to fill in the blanks
			for ( i = 0; i < nstops; i += 1 ) {
				// A progression, in this context, is defined as all of the color stops between and including two known color stops

				if ( stops[ i ] !== NULL ) {
					// Start a new progression if stop is a number

					// Number of stops in current progression
					n = 1;
					// Current iteration in current progression
					p = 0;
					start = stops[ i ];

					// Look ahead to find end stop
					for ( a = ( i + 1 ); a < nstops; a += 1 ) {
						if ( stops[ a ] !== NULL ) {
							// If this future stop is a number, make it the end stop for this progression
							end = stops[ a ];
							break;
						} else {
							// Otherwise, keep looking ahead
							n += 1;
						}
					}

					// Ensure start stop is not greater than end stop
					if ( start > end ) {
						stops[ a ] = stops[ i ];
					}

				} else if ( stops[ i ] === NULL ) {
					// Calculate stop if not initially given
					p += 1;
					stops[ i ] = start + ( p * ( ( end - start ) / n ) );
				}
				// Add color stop to gradient object
				gradient.addColorStop( stops[ i ], params[ 'c' + ( i + 1 ) ] );
			}

		} else {
			gradient = NULL;
		}
		return gradient;
	};

// Manipulates pixels on the canvas
	$.fn.setPixels = function setPixels( args ) {
		var $canvases = this,
			canvas, e, ctx,
			params, layer,
			px,
			imgData, data, i, len;

		for ( e = 0; e < $canvases.length; e += 1 ) {
			canvas = $canvases[ e ];
			ctx = _getContext( canvas );
			if ( ctx ) {

				params = new jCanvasObject( args );
				layer = _addLayer( canvas, params, args, setPixels );
				_transformShape( $canvases[ e ], ctx, params, params.width, params.height );

				// Use entire canvas of x, y, width, or height is not defined
				if ( params.width === NULL || params.height === NULL ) {
					params.width = canvas.width;
					params.height = canvas.height;
					params.x = params.width / 2;
					params.y = params.height / 2;
				}

				if ( params.width !== 0 && params.height !== 0 ) {
					// Only set pixels if width and height are not zero

					imgData = ctx.getImageData( params.x - ( params.width / 2 ), params.y - ( params.height / 2 ), params.width, params.height );
					data = imgData.data;
					len = data.length;

					// Loop through pixels with the "each" callback function
					if ( params.each ) {
						for ( i = 0; i < len; i += 4 ) {
							px = {
								r: data[ i ],
								g: data[ i + 1 ],
								b: data[ i + 2 ],
								a: data[ i + 3 ]
							};
							params.each.call( canvas, px, params );
							data[ i ] = px.r;
							data[ i + 1 ] = px.g;
							data[ i + 2 ] = px.b;
							data[ i + 3 ] = px.a;
						}
					}
					// Put pixels on canvas
					ctx.putImageData( imgData, params.x - ( params.width / 2 ), params.y - ( params.height / 2 ) );
					// Restore transformation
					ctx.restore();

				}

			}
		}
		return $canvases;
	};

// Retrieves canvas image as data URL
	$.fn.getCanvasImage = function getCanvasImage( type, quality ) {
		var $canvases = this, canvas,
			dataURL = NULL;
		if ( $canvases.length !== 0 ) {
			canvas = $canvases[ 0 ];
			if ( canvas.toDataURL ) {
				// JPEG quality defaults to 1
				if ( quality === UNDEFINED ) {
					quality = 1;
				}
				dataURL = canvas.toDataURL( 'image/' + type, quality );
			}
		}
		return dataURL;
	};

// Scales canvas based on the device's pixel ratio
	$.fn.detectPixelRatio = function detectPixelRatio( callback ) {
		var $canvases = this,
			$canvas, canvas, e, ctx,
			devicePixelRatio, backingStoreRatio, ratio,
			oldWidth, oldHeight,
			data;

		for ( e = 0; e < $canvases.length; e += 1 ) {
			// Get canvas and its associated data
			canvas = $canvases[ e ];
			$canvas = $( $canvases[ e ] );
			ctx = _getContext( canvas );
			data = _getCanvasData( $canvases[ e ] );

			// If canvas has not already been scaled with this method
			if ( !data.scaled ) {

				// Determine device pixel ratios
				devicePixelRatio = window.devicePixelRatio || 1;
				backingStoreRatio = ctx.webkitBackingStorePixelRatio ||
				ctx.mozBackingStorePixelRatio ||
				ctx.msBackingStorePixelRatio ||
				ctx.oBackingStorePixelRatio ||
				ctx.backingStorePixelRatio || 1;

				// Calculate general ratio based on the two given ratios
				ratio = devicePixelRatio / backingStoreRatio;

				if ( ratio !== 1 ) {
					// Scale canvas relative to ratio

					// Get the current canvas dimensions for future use
					oldWidth = canvas.width;
					oldHeight = canvas.height;

					// Resize canvas relative to the determined ratio
					canvas.width = oldWidth * ratio;
					canvas.height = oldHeight * ratio;

					// Scale canvas back to original dimensions via CSS
					canvas.style.width = oldWidth + 'px';
					canvas.style.height = oldHeight + 'px';

					// Scale context to counter the manual scaling of canvas
					ctx.scale( ratio, ratio );

				}

				// Set pixel ratio on canvas data object
				data.pixelRatio = ratio;
				// Ensure that this method can only be called once for any given canvas
				data.scaled = TRUE;

				// Call the given callback function with the ratio as its only argument
				if ( callback ) {
					callback.call( canvas, ratio );
				}

			}

		}
		return $canvases;
	};

// Clears the jCanvas cache
	jCanvas.clearCache = function clearCache() {
		var cacheName;
		for ( cacheName in caches ) {
			if ( caches.hasOwnProperty( cacheName ) ) {
				caches[ cacheName ] = {};
			}
		}
	};

// Enable canvas feature detection with $.support
	$.support.canvas = ( $( '<canvas />' )[ 0 ].getContext !== UNDEFINED );

// Export jCanvas functions
	extendObject( jCanvas, {
		defaults: defaults,
		setGlobalProps: _setGlobalProps,
		transformShape: _transformShape,
		detectEvents: _detectEvents,
		closePath: _closePath,
		setCanvasFont: _setCanvasFont,
		measureText: _measureText
	} );
	$.jCanvas = jCanvas;
	$.jCanvasObject = jCanvasObject;

}));

/*! Copyright 2012, Ben Lin (http://dreamerslab.com/)
 * Licensed under the MIT License (LICENSE.txt).
 *
 * Version: 1.0.16
 *
 * Requires: jQuery >= 1.2.3
 */
(function(a){a.fn.addBack=a.fn.addBack||a.fn.andSelf;
    a.fn.extend({actual:function(b,l){if(!this[b]){throw'$.actual => The jQuery method "'+b+'" you called does not exist';}var f={absolute:false,clone:false,includeMargin:false};
        var i=a.extend(f,l);var e=this.eq(0);var h,j;if(i.clone===true){h=function(){var m="position: absolute !important; top: -1000 !important; ";e=e.clone().attr("style",m).appendTo("body");
        };j=function(){e.remove();};}else{var g=[];var d="";var c;h=function(){c=e.parents().addBack().filter(":hidden");d+="visibility: hidden !important; display: block !important; ";
            if(i.absolute===true){d+="position: absolute !important; ";}c.each(function(){var m=a(this);var n=m.attr("style");g.push(n);m.attr("style",n?n+";"+d:d);
            });};j=function(){c.each(function(m){var o=a(this);var n=g[m];if(n===undefined){o.removeAttr("style");}else{o.attr("style",n);}});};}h();var k=/(outer)/.test(b)?e[b](i.includeMargin):e[b]();
        j();return k;}});})(jQuery);


/*! noUiSlider - 7.0.9 - 2014-10-08 16:49:45 */
!function(){"use strict";function a(a){return a.split("").reverse().join("")}function b(a,b){return a.substring(0,b.length)===b}function c(a,b){return a.slice(-1*b.length)===b}function d(a,b,c){if((a[b]||a[c])&&a[b]===a[c])throw new Error(b)}function e(a){return"number"==typeof a&&isFinite(a)}function f(a,b){var c=Math.pow(10,b);return(Math.round(a*c)/c).toFixed(b)}function g(b,c,d,g,h,i,j,k,l,m,n,o){var p,q,r,s=o,t="",u="";return i&&(o=i(o)),e(o)?(b&&0===parseFloat(o.toFixed(b))&&(o=0),0>o&&(p=!0,o=Math.abs(o)),b!==!1&&(o=f(o,b)),o=o.toString(),-1!==o.indexOf(".")?(q=o.split("."),r=q[0],d&&(t=d+q[1])):r=o,c&&(r=a(r).match(/.{1,3}/g),r=a(r.join(a(c)))),p&&k&&(u+=k),g&&(u+=g),p&&l&&(u+=l),u+=r,u+=t,h&&(u+=h),m&&(u=m(u,s)),u):!1}function h(a,d,f,g,h,i,j,k,l,m,n,o){var p,q="";return n&&(o=n(o)),o&&"string"==typeof o?(k&&b(o,k)&&(o=o.replace(k,""),p=!0),g&&b(o,g)&&(o=o.replace(g,"")),l&&b(o,l)&&(o=o.replace(l,""),p=!0),h&&c(o,h)&&(o=o.slice(0,-1*h.length)),d&&(o=o.split(d).join("")),f&&(o=o.replace(f,".")),p&&(q+="-"),q+=o,q=q.replace(/[^0-9\.\-.]/g,""),""===q?!1:(q=Number(q),j&&(q=j(q)),e(q)?q:!1)):!1}function i(a){var b,c,e,f={};for(b=0;b<l.length;b+=1)if(c=l[b],e=a[c],void 0===e)f[c]="negative"!==c||f.negativeBefore?"mark"===c&&"."!==f.thousand?".":!1:"-";else if("decimals"===c){if(!(e>=0&&8>e))throw new Error(c);f[c]=e}else if("encoder"===c||"decoder"===c||"edit"===c||"undo"===c){if("function"!=typeof e)throw new Error(c);f[c]=e}else{if("string"!=typeof e)throw new Error(c);f[c]=e}return d(f,"mark","thousand"),d(f,"prefix","negative"),d(f,"prefix","negativeBefore"),f}function j(a,b,c){var d,e=[];for(d=0;d<l.length;d+=1)e.push(a[l[d]]);return e.push(c),b.apply("",e)}function k(a){return this instanceof k?void("object"==typeof a&&(a=i(a),this.to=function(b){return j(a,g,b)},this.from=function(b){return j(a,h,b)})):new k(a)}var l=["decimals","thousand","mark","prefix","postfix","encoder","decoder","negativeBefore","negative","edit","undo"];window.wNumb=k}(),function(a){"use strict";function b(b){return b instanceof a||a.zepto&&a.zepto.isZ(b)}function c(b,c){return"string"==typeof b&&0===b.indexOf("-inline-")?(this.method=c||"html",this.target=this.el=a(b.replace("-inline-","")||"<div/>"),!0):void 0}function d(b){if("string"==typeof b&&0!==b.indexOf("-")){this.method="val";var c=document.createElement("input");return c.name=b,c.type="hidden",this.target=this.el=a(c),!0}}function e(a){return"function"==typeof a?(this.target=!1,this.method=a,!0):void 0}function f(a,c){return b(a)&&!c?(a.is("input, select, textarea")?(this.method="val",this.target=a.on("change.liblink",this.changeHandler)):(this.target=a,this.method="html"),!0):void 0}function g(a,c){return b(a)&&("function"==typeof c||"string"==typeof c&&a[c])?(this.method=c,this.target=a,!0):void 0}function h(b,c,d){var e=this,f=!1;if(this.changeHandler=function(b){var c=e.formatInstance.from(a(this).val());return c===!1||isNaN(c)?(a(this).val(e.lastSetValue),!1):void e.changeHandlerMethod.call("",b,c)},this.el=!1,this.formatInstance=d,a.each(k,function(a,d){return f=d.call(e,b,c),!f}),!f)throw new RangeError("(Link) Invalid Link.")}function i(a){this.items=[],this.elements=[],this.origin=a}function j(b,c,d,e){0===b&&(b=this.LinkDefaultFlag),this.linkAPI||(this.linkAPI={}),this.linkAPI[b]||(this.linkAPI[b]=new i(this));var f=new h(c,d,e||this.LinkDefaultFormatter);f.target||(f.target=a(this)),f.changeHandlerMethod=this.LinkConfirm(b,f.el),this.linkAPI[b].push(f,f.el),this.LinkUpdate(b)}var k=[c,d,e,f,g];h.prototype.set=function(a){var b=Array.prototype.slice.call(arguments),c=b.slice(1);this.lastSetValue=this.formatInstance.to(a),c.unshift(this.lastSetValue),("function"==typeof this.method?this.method:this.target[this.method]).apply(this.target,c)},i.prototype.push=function(a,b){this.items.push(a),b&&this.elements.push(b)},i.prototype.reconfirm=function(a){var b;for(b=0;b<this.elements.length;b+=1)this.origin.LinkConfirm(a,this.elements[b])},i.prototype.remove=function(){var a;for(a=0;a<this.items.length;a+=1)this.items[a].target.off(".liblink");for(a=0;a<this.elements.length;a+=1)this.elements[a].remove()},i.prototype.change=function(a){if(this.origin.LinkIsEmitting)return!1;this.origin.LinkIsEmitting=!0;var b,c=Array.prototype.slice.call(arguments,1);for(c.unshift(a),b=0;b<this.items.length;b+=1)this.items[b].set.apply(this.items[b],c);this.origin.LinkIsEmitting=!1},a.fn.Link=function(b){var c=this;if(b===!1)return c.each(function(){this.linkAPI&&(a.map(this.linkAPI,function(a){a.remove()}),delete this.linkAPI)});if(void 0===b)b=0;else if("string"!=typeof b)throw new Error("Flag must be string.");return{to:function(a,d,e){return c.each(function(){j.call(this,b,a,d,e)})}}}}(window.jQuery||window.Zepto),function(a){"use strict";function b(b){return a.grep(b,function(c,d){return d===a.inArray(c,b)})}function c(a,b){return Math.round(a/b)*b}function d(a){return"number"==typeof a&&!isNaN(a)&&isFinite(a)}function e(a){var b=Math.pow(10,7);return Number((Math.round(a*b)/b).toFixed(7))}function f(a,b,c){a.addClass(b),setTimeout(function(){a.removeClass(b)},c)}function g(a){return Math.max(Math.min(a,100),0)}function h(b){return a.isArray(b)?b:[b]}function i(a,b){return 100/(b-a)}function j(a,b){return 100*b/(a[1]-a[0])}function k(a,b){return j(a,a[0]<0?b+Math.abs(a[0]):b-a[0])}function l(a,b){return b*(a[1]-a[0])/100+a[0]}function m(a,b){for(var c=1;a>=b[c];)c+=1;return c}function n(a,b,c){if(c>=a.slice(-1)[0])return 100;var d,e,f,g,h=m(c,a);return d=a[h-1],e=a[h],f=b[h-1],g=b[h],f+k([d,e],c)/i(f,g)}function o(a,b,c){if(c>=100)return a.slice(-1)[0];var d,e,f,g,h=m(c,b);return d=a[h-1],e=a[h],f=b[h-1],g=b[h],l([d,e],(c-f)*i(f,g))}function p(a,b,d,e){if(100===e)return e;var f,g,h=m(e,a);return d?(f=a[h-1],g=a[h],e-f>(g-f)/2?g:f):b[h-1]?a[h-1]+c(e-a[h-1],b[h-1]):e}function q(a,b,c){var e;if("number"==typeof b&&(b=[b]),"[object Array]"!==Object.prototype.toString.call(b))throw new Error("noUiSlider: 'range' contains invalid value.");if(e="min"===a?0:"max"===a?100:parseFloat(a),!d(e)||!d(b[0]))throw new Error("noUiSlider: 'range' value isn't numeric.");c.xPct.push(e),c.xVal.push(b[0]),e?c.xSteps.push(isNaN(b[1])?!1:b[1]):isNaN(b[1])||(c.xSteps[0]=b[1])}function r(a,b,c){return b?void(c.xSteps[a]=j([c.xVal[a],c.xVal[a+1]],b)/i(c.xPct[a],c.xPct[a+1])):!0}function s(a,b,c,d){this.xPct=[],this.xVal=[],this.xSteps=[d||!1],this.xNumSteps=[!1],this.snap=b,this.direction=c;var e,f=this;for(e in a)a.hasOwnProperty(e)&&q(e,a[e],f);f.xNumSteps=f.xSteps.slice(0);for(e in f.xNumSteps)f.xNumSteps.hasOwnProperty(e)&&r(Number(e),f.xNumSteps[e],f)}function t(a,b){if(!d(b))throw new Error("noUiSlider: 'step' is not numeric.");a.singleStep=b}function u(b,c){if("object"!=typeof c||a.isArray(c))throw new Error("noUiSlider: 'range' is not an object.");if(void 0===c.min||void 0===c.max)throw new Error("noUiSlider: Missing 'min' or 'max' in 'range'.");b.spectrum=new s(c,b.snap,b.dir,b.singleStep)}function v(b,c){if(c=h(c),!a.isArray(c)||!c.length||c.length>2)throw new Error("noUiSlider: 'start' option is incorrect.");b.handles=c.length,b.start=c}function w(a,b){if(a.snap=b,"boolean"!=typeof b)throw new Error("noUiSlider: 'snap' option must be a boolean.")}function x(a,b){if(a.animate=b,"boolean"!=typeof b)throw new Error("noUiSlider: 'animate' option must be a boolean.")}function y(a,b){if("lower"===b&&1===a.handles)a.connect=1;else if("upper"===b&&1===a.handles)a.connect=2;else if(b===!0&&2===a.handles)a.connect=3;else{if(b!==!1)throw new Error("noUiSlider: 'connect' option doesn't match handle count.");a.connect=0}}function z(a,b){switch(b){case"horizontal":a.ort=0;break;case"vertical":a.ort=1;break;default:throw new Error("noUiSlider: 'orientation' option is invalid.")}}function A(a,b){if(!d(b))throw new Error("noUiSlider: 'margin' option must be numeric.");if(a.margin=a.spectrum.getMargin(b),!a.margin)throw new Error("noUiSlider: 'margin' option is only supported on linear sliders.")}function B(a,b){if(!d(b))throw new Error("noUiSlider: 'limit' option must be numeric.");if(a.limit=a.spectrum.getMargin(b),!a.limit)throw new Error("noUiSlider: 'limit' option is only supported on linear sliders.")}function C(a,b){switch(b){case"ltr":a.dir=0;break;case"rtl":a.dir=1,a.connect=[0,2,1,3][a.connect];break;default:throw new Error("noUiSlider: 'direction' option was not recognized.")}}function D(a,b){if("string"!=typeof b)throw new Error("noUiSlider: 'behaviour' must be a string containing options.");var c=b.indexOf("tap")>=0,d=b.indexOf("drag")>=0,e=b.indexOf("fixed")>=0,f=b.indexOf("snap")>=0;a.events={tap:c||f,drag:d,fixed:e,snap:f}}function E(a,b){if(a.format=b,"function"==typeof b.to&&"function"==typeof b.from)return!0;throw new Error("noUiSlider: 'format' requires 'to' and 'from' methods.")}function F(b){var c,d={margin:0,limit:0,animate:!0,format:Y};return c={step:{r:!1,t:t},start:{r:!0,t:v},connect:{r:!0,t:y},direction:{r:!0,t:C},snap:{r:!1,t:w},animate:{r:!1,t:x},range:{r:!0,t:u},orientation:{r:!1,t:z},margin:{r:!1,t:A},limit:{r:!1,t:B},behaviour:{r:!0,t:D},format:{r:!1,t:E}},b=a.extend({connect:!1,direction:"ltr",behaviour:"tap",orientation:"horizontal"},b),a.each(c,function(a,c){if(void 0===b[a]){if(c.r)throw new Error("noUiSlider: '"+a+"' is required.");return!0}c.t(d,b[a])}),d.style=d.ort?"top":"left",d}function G(a,b,c){var d=a+b[0],e=a+b[1];return c?(0>d&&(e+=Math.abs(d)),e>100&&(d-=e-100),[g(d),g(e)]):[d,e]}function H(a){a.preventDefault();var b,c,d=0===a.type.indexOf("touch"),e=0===a.type.indexOf("mouse"),f=0===a.type.indexOf("pointer"),g=a;return 0===a.type.indexOf("MSPointer")&&(f=!0),a.originalEvent&&(a=a.originalEvent),d&&(b=a.changedTouches[0].pageX,c=a.changedTouches[0].pageY),(e||f)&&(f||void 0!==window.pageXOffset||(window.pageXOffset=document.documentElement.scrollLeft,window.pageYOffset=document.documentElement.scrollTop),b=a.clientX+window.pageXOffset,c=a.clientY+window.pageYOffset),g.points=[b,c],g.cursor=e,g}function I(b,c){var d=a("<div><div/></div>").addClass(X[2]),e=["-lower","-upper"];return b&&e.reverse(),d.children().addClass(X[3]+" "+X[3]+e[c]),d}function J(a,b,c){switch(a){case 1:b.addClass(X[7]),c[0].addClass(X[6]);break;case 3:c[1].addClass(X[6]);case 2:c[0].addClass(X[7]);case 0:b.addClass(X[6])}}function K(a,b,c){var d,e=[];for(d=0;a>d;d+=1)e.push(I(b,d).appendTo(c));return e}function L(b,c,d){return d.addClass([X[0],X[8+b],X[4+c]].join(" ")),a("<div/>").appendTo(d).addClass(X[1])}function M(b,c,d){function e(){return B[["width","height"][c.ort]]()}function i(a){var b,c=[D.val()];for(b=0;b<a.length;b+=1)D.trigger(a[b],c)}function j(a){return 1===a.length?a[0]:c.dir?a.reverse():a}function k(a){return function(b,c){D.val([a?null:c,a?c:null],!0)}}function l(b){var c=a.inArray(b,M);D[0].linkAPI&&D[0].linkAPI[b]&&D[0].linkAPI[b].change(I[c],C[c].children(),D)}function m(b,d){var e=a.inArray(b,M);return d&&d.appendTo(C[e].children()),c.dir&&c.handles>1&&(e=1===e?0:1),k(e)}function n(){var a,b;for(a=0;a<M.length;a+=1)this.linkAPI&&this.linkAPI[b=M[a]]&&this.linkAPI[b].reconfirm(b)}function o(a,b,d,e){return a=a.replace(/\s/g,V+" ")+V,b.on(a,function(a){return D.attr("disabled")?!1:D.hasClass(X[14])?!1:(a=H(a),a.calcPoint=a.points[c.ort],void d(a,e))})}function p(a,b){var c,d=b.handles||C,f=!1,g=100*(a.calcPoint-b.start)/e(),h=d[0][0]!==C[0][0]?1:0;c=G(g,b.positions,d.length>1),f=u(d[0],c[h],1===d.length),d.length>1&&(f=u(d[1],c[h?0:1],!1)||f),f&&i(["slide"])}function q(b){a("."+X[15]).removeClass(X[15]),b.cursor&&a("body").css("cursor","").off(V),T.off(V),D.removeClass(X[12]),i(["set","change"])}function r(b,c){1===c.handles.length&&c.handles[0].children().addClass(X[15]),b.stopPropagation(),o(W.move,T,p,{start:b.calcPoint,handles:c.handles,positions:[E[0],E[C.length-1]]}),o(W.end,T,q,null),b.cursor&&(a("body").css("cursor",a(b.target).css("cursor")),C.length>1&&D.addClass(X[12]),a("body").on("selectstart"+V,!1))}function s(b){var d,g=b.calcPoint,h=0;b.stopPropagation(),a.each(C,function(){h+=this.offset()[c.style]}),h=h/2>g||1===C.length?0:1,g-=B.offset()[c.style],d=100*g/e(),c.events.snap||f(D,X[14],300),u(C[h],d),i(["slide","set","change"]),c.events.snap&&r(b,{handles:[C[h]]})}function t(a){var b,c;if(!a.fixed)for(b=0;b<C.length;b+=1)o(W.start,C[b].children(),r,{handles:[C[b]]});a.tap&&o(W.start,B,s,{handles:C}),a.drag&&(c=B.find("."+X[7]).addClass(X[10]),a.fixed&&(c=c.add(B.children().not(c).children())),o(W.start,c,r,{handles:C}))}function u(a,b,d){var e=a[0]!==C[0][0]?1:0,f=E[0]+c.margin,h=E[1]-c.margin,i=E[0]+c.limit,j=E[1]-c.limit;return C.length>1&&(b=e?Math.max(b,f):Math.min(b,h)),d!==!1&&c.limit&&C.length>1&&(b=e?Math.min(b,i):Math.max(b,j)),b=F.getStep(b),b=g(parseFloat(b.toFixed(7))),b===E[e]?!1:(a.css(c.style,b+"%"),a.is(":first-child")&&a.toggleClass(X[17],b>50),E[e]=b,I[e]=F.fromStepping(b),l(M[e]),!0)}function v(a,b){var d,e,f;for(c.limit&&(a+=1),d=0;a>d;d+=1)e=d%2,f=b[e],null!==f&&f!==!1&&("number"==typeof f&&(f=String(f)),f=c.format.from(f),(f===!1||isNaN(f)||u(C[e],F.toStepping(f),d===3-c.dir)===!1)&&l(M[e]))}function w(a){if(D[0].LinkIsEmitting)return this;var b,d=h(a);return c.dir&&c.handles>1&&d.reverse(),c.animate&&-1!==E[0]&&f(D,X[14],300),b=C.length>1?3:1,1===d.length&&(b=1),v(b,d),i(["set"]),this}function x(){var a,b=[];for(a=0;a<c.handles;a+=1)b[a]=c.format.to(I[a]);return j(b)}function y(){return a(this).off(V).removeClass(X.join(" ")).empty(),delete this.LinkUpdate,delete this.LinkConfirm,delete this.LinkDefaultFormatter,delete this.LinkDefaultFlag,delete this.reappend,delete this.vGet,delete this.vSet,delete this.getCurrentStep,delete this.getInfo,delete this.destroy,d}function z(){var b=a.map(E,function(a,b){var c=F.getApplicableStep(a),d=I[b],e=c[2],f=d-c[2]>=c[1]?c[2]:c[0];return[[f,e]]});return j(b)}function A(){return d}var B,C,D=a(b),E=[-1,-1],F=c.spectrum,I=[],M=["lower","upper"].slice(0,c.handles);if(c.dir&&M.reverse(),b.LinkUpdate=l,b.LinkConfirm=m,b.LinkDefaultFormatter=c.format,b.LinkDefaultFlag="lower",b.reappend=n,D.hasClass(X[0]))throw new Error("Slider was already initialized.");B=L(c.dir,c.ort,D),C=K(c.handles,c.dir,B),J(c.connect,D,C),t(c.events),b.vSet=w,b.vGet=x,b.destroy=y,b.getCurrentStep=z,b.getOriginalOptions=A,b.getInfo=function(){return[F,c.style,c.ort]},D.val(c.start)}function N(a){if(!this.length)throw new Error("noUiSlider: Can't initialize slider on empty selection.");var b=F(a,this);return this.each(function(){M(this,b,a)})}function O(b){return this.each(function(){if(!this.destroy)return void a(this).noUiSlider(b);var c=a(this).val(),d=this.destroy(),e=a.extend({},d,b);a(this).noUiSlider(e),this.reappend(),d.start===e.start&&a(this).val(c)})}function P(){return this[0][arguments.length?"vSet":"vGet"].apply(this[0],arguments)}function Q(b,c,d,e){if("range"===c||"steps"===c)return b.xVal;if("count"===c){var f,g=100/(d-1),h=0;for(d=[];(f=h++*g)<=100;)d.push(f);c="positions"}return"positions"===c?a.map(d,function(a){return b.fromStepping(e?b.getStep(a):a)}):"values"===c?e?a.map(d,function(a){return b.fromStepping(b.getStep(b.toStepping(a)))}):d:void 0}function R(c,d,e,f){var g=c.direction,h={},i=c.xVal[0],j=c.xVal[c.xVal.length-1],k=!1,l=!1,m=0;return c.direction=0,f=b(f.slice().sort(function(a,b){return a-b})),f[0]!==i&&(f.unshift(i),k=!0),f[f.length-1]!==j&&(f.push(j),l=!0),a.each(f,function(b){var g,i,j,n,o,p,q,r,s,t,u=f[b],v=f[b+1];if("steps"===e&&(g=c.xNumSteps[b]),g||(g=v-u),u!==!1&&void 0!==v)for(i=u;v>=i;i+=g){for(n=c.toStepping(i),o=n-m,r=o/d,s=Math.round(r),t=o/s,j=1;s>=j;j+=1)p=m+j*t,h[p.toFixed(5)]=["x",0];q=a.inArray(i,f)>-1?1:"steps"===e?2:0,!b&&k&&(q=0),i===v&&l||(h[n.toFixed(5)]=[i,q]),m=n}}),c.direction=g,h}function S(b,c,d,e,f,g){function h(a,b){return["-normal","-large","-sub"][a&&f?f(b,a):a]}function i(a,c,d){return'class="'+c+" "+c+"-"+k+" "+c+h(d[1],d[0])+'" style="'+b+": "+a+'%"'}function j(a,b){d&&(a=100-a),l.append("<div "+i(a,"noUi-marker",b)+"></div>"),b[1]&&l.append("<div "+i(a,"noUi-value",b)+">"+g.to(b[0])+"</div>")}var k=["horizontal","vertical"][c],l=a("<div/>");return l.addClass("noUi-pips noUi-pips-"+k),a.each(e,j),l}var T=a(document),U=a.fn.val,V=".nui",W=window.navigator.pointerEnabled?{start:"pointerdown",move:"pointermove",end:"pointerup"}:window.navigator.msPointerEnabled?{start:"MSPointerDown",move:"MSPointerMove",end:"MSPointerUp"}:{start:"mousedown touchstart",move:"mousemove touchmove",end:"mouseup touchend"},X=["noUi-target","noUi-base","noUi-origin","noUi-handle","noUi-horizontal","noUi-vertical","noUi-background","noUi-connect","noUi-ltr","noUi-rtl","noUi-dragable","","noUi-state-drag","","noUi-state-tap","noUi-active","","noUi-stacking"];s.prototype.getMargin=function(a){return 2===this.xPct.length?j(this.xVal,a):!1},s.prototype.toStepping=function(a){return a=n(this.xVal,this.xPct,a),this.direction&&(a=100-a),a},s.prototype.fromStepping=function(a){return this.direction&&(a=100-a),e(o(this.xVal,this.xPct,a))},s.prototype.getStep=function(a){return this.direction&&(a=100-a),a=p(this.xPct,this.xSteps,this.snap,a),this.direction&&(a=100-a),a},s.prototype.getApplicableStep=function(a){var b=m(a,this.xPct),c=100===a?2:1;return[this.xNumSteps[b-2],this.xVal[b-c],this.xNumSteps[b-c]]},s.prototype.convert=function(a){return this.getStep(this.toStepping(a))};var Y={to:function(a){return a.toFixed(2)},from:Number};a.fn.val=function(b){function c(a){return a.hasClass(X[0])?P:U}if(void 0===b){var d=a(this[0]);return c(d).call(d)}var e=a.isFunction(b);return this.each(function(d){var f=b,g=a(this);e&&(f=b.call(this,d,g.val())),c(g).call(g,f)})},a.fn.noUiSlider=function(a,b){switch(a){case"step":return this[0].getCurrentStep();case"options":return this[0].getOriginalOptions()}return(b?O:N).call(this,a)},a.fn.noUiSlider_pips=function(b){var c=b.mode,d=b.density||1,e=b.filter||!1,f=b.values||!1,g=b.format||{to:Math.round},h=b.stepped||!1;return this.each(function(){var b=this.getInfo(),i=Q(b[0],c,f,h),j=R(b[0],d,c,i);return a(this).append(S(b[1],b[2],b[0].direction,j,e,g))})}}(window.jQuery||window.Zepto);

/* Modernizr 2.6.2 (Custom Build) | MIT & BSD
 * Build: http://modernizr.com/download/#-csstransforms-csstransforms3d-csstransitions-cssclasses-prefixed-teststyles-testprop-testallprops-prefixes-domprefixes
 */
window.Modernizr=function(a,b,c){function z(a){j.cssText=a}function A(a,b){return z(m.join(a+";")+(b||""))}function B(a,b){return typeof a===b}function C(a,b){return!!~(""+a).indexOf(b)}function D(a,b){for(var d in a){var e=a[d];if(!C(e,"-")&&j[e]!==c)return b=="pfx"?e:!0}return!1}function E(a,b,d){for(var e in a){var f=b[a[e]];if(f!==c)return d===!1?a[e]:B(f,"function")?f.bind(d||b):f}return!1}function F(a,b,c){var d=a.charAt(0).toUpperCase()+a.slice(1),e=(a+" "+o.join(d+" ")+d).split(" ");return B(b,"string")||B(b,"undefined")?D(e,b):(e=(a+" "+p.join(d+" ")+d).split(" "),E(e,b,c))}var d="2.6.2",e={},f=!0,g=b.documentElement,h="modernizr",i=b.createElement(h),j=i.style,k,l={}.toString,m=" -webkit- -moz- -o- -ms- ".split(" "),n="Webkit Moz O ms",o=n.split(" "),p=n.toLowerCase().split(" "),q={},r={},s={},t=[],u=t.slice,v,w=function(a,c,d,e){var f,i,j,k,l=b.createElement("div"),m=b.body,n=m||b.createElement("body");if(parseInt(d,10))while(d--)j=b.createElement("div"),j.id=e?e[d]:h+(d+1),l.appendChild(j);return f=["&#173;",'<style id="s',h,'">',a,"</style>"].join(""),l.id=h,(m?l:n).innerHTML+=f,n.appendChild(l),m||(n.style.background="",n.style.overflow="hidden",k=g.style.overflow,g.style.overflow="hidden",g.appendChild(n)),i=c(l,a),m?l.parentNode.removeChild(l):(n.parentNode.removeChild(n),g.style.overflow=k),!!i},x={}.hasOwnProperty,y;!B(x,"undefined")&&!B(x.call,"undefined")?y=function(a,b){return x.call(a,b)}:y=function(a,b){return b in a&&B(a.constructor.prototype[b],"undefined")},Function.prototype.bind||(Function.prototype.bind=function(b){var c=this;if(typeof c!="function")throw new TypeError;var d=u.call(arguments,1),e=function(){if(this instanceof e){var a=function(){};a.prototype=c.prototype;var f=new a,g=c.apply(f,d.concat(u.call(arguments)));return Object(g)===g?g:f}return c.apply(b,d.concat(u.call(arguments)))};return e}),q.csstransforms=function(){return!!F("transform")},q.csstransforms3d=function(){var a=!!F("perspective");return a&&"webkitPerspective"in g.style&&w("@media (transform-3d),(-webkit-transform-3d){#modernizr{left:9px;position:absolute;height:3px;}}",function(b,c){a=b.offsetLeft===9&&b.offsetHeight===3}),a},q.csstransitions=function(){return F("transition")};for(var G in q)y(q,G)&&(v=G.toLowerCase(),e[v]=q[G](),t.push((e[v]?"":"no-")+v));return e.addTest=function(a,b){if(typeof a=="object")for(var d in a)y(a,d)&&e.addTest(d,a[d]);else{a=a.toLowerCase();if(e[a]!==c)return e;b=typeof b=="function"?b():b,typeof f!="undefined"&&f&&(g.className+=" "+(b?"":"no-")+a),e[a]=b}return e},z(""),i=k=null,e._version=d,e._prefixes=m,e._domPrefixes=p,e._cssomPrefixes=o,e.testProp=function(a){return D([a])},e.testAllProps=F,e.testStyles=w,e.prefixed=function(a,b,c){return b?F(a,b,c):F(a,"pfx")},g.className=g.className.replace(/(^|\s)no-js(\s|$)/,"$1$2")+(f?" js "+t.join(" "):""),e}(this,this.document);
/*!
 * Shuffle.js by @Vestride
 * Categorize, sort, and filter a responsive grid of items.
 * Dependencies: jQuery 1.9+, Modernizr 2.6.2+
 * @license MIT license
 * @version 3.1.1
 */
!function(a){"function"==typeof define&&define.amd?define(["jquery","modernizr"],a):"object"==typeof exports?module.exports=a(require("jquery"),window.Modernizr):window.Shuffle=a(window.jQuery,window.Modernizr)}(function(a,b,c){"use strict";function d(a){return a?a.replace(/([A-Z])/g,function(a,b){return"-"+b.toLowerCase()}).replace(/^ms-/,"-ms-"):""}function e(b,c,d){var e,f,g,h=null,i=0;d=d||{};var j=function(){i=d.leading===!1?0:a.now(),h=null,g=b.apply(e,f),e=f=null};return function(){var k=a.now();i||d.leading!==!1||(i=k);var l=c-(k-i);return e=this,f=arguments,0>=l||l>c?(clearTimeout(h),h=null,i=k,g=b.apply(e,f),e=f=null):h||d.trailing===!1||(h=setTimeout(j,l)),g}}function f(a,b,c){for(var d=0,e=a.length;e>d;d++)if(b.call(c,a[d],d,a)==={})return}function g(b,c,d){return setTimeout(a.proxy(b,c),d)}function h(a){return Math.max.apply(Math,a)}function i(a){return Math.min.apply(Math,a)}function j(b){return a.isNumeric(b)?b:0}function k(a){var b,c,d=a.length;if(!d)return a;for(;--d;)c=Math.floor(Math.random()*(d+1)),b=a[c],a[c]=a[d],a[d]=b;return a}if("object"!=typeof b)throw new Error("Shuffle.js requires Modernizr.\nhttp://vestride.github.io/Shuffle/#dependencies");var l=b.prefixed("transition"),m=b.prefixed("transitionDelay"),n=b.prefixed("transitionDuration"),o={WebkitTransition:"webkitTransitionEnd",transition:"transitionend"}[l],p=b.prefixed("transform"),q=d(p),r=b.csstransforms&&b.csstransitions,s=b.csstransforms3d,t=!!window.getComputedStyle,u="shuffle",v="all",w="groups",x=1,y=.001,z=window.getComputedStyle||function(){},A=function(a,b){this.x=j(a),this.y=j(b)};A.equals=function(a,b){return a.x===b.x&&a.y===b.y};var B=function(){if(!t)return!1;var a=document.body||document.documentElement,b=document.createElement("div");b.style.cssText="width:10px;padding:2px;-webkit-box-sizing:border-box;box-sizing:border-box;",a.appendChild(b);var c=z(b,null).width,d="10px"===c;return a.removeChild(b),d}(),C=0,D=a(window),E=function(b,c){c=c||{},a.extend(this,E.options,c,E.settings),this.$el=a(b),this.element=b,this.unique="shuffle_"+C++,this._fire(E.EventType.LOADING),this._init(),g(function(){this.initialized=!0,this._fire(E.EventType.DONE)},this,16)};return E.EventType={LOADING:"loading",DONE:"done",LAYOUT:"layout",REMOVED:"removed"},E.ClassName={BASE:u,SHUFFLE_ITEM:"shuffle-item",FILTERED:"filtered",CONCEALED:"concealed"},E.options={group:v,speed:250,easing:"ease-out",itemSelector:"",sizer:null,gutterWidth:0,columnWidth:0,delimeter:null,buffer:0,columnThreshold:t?.01:.1,initialSort:null,throttle:e,throttleTime:300,sequentialFadeDelay:150,supported:r},E.settings={useSizer:!1,itemCss:{position:"absolute",top:0,left:0,visibility:"visible"},revealAppendedDelay:300,lastSort:{},lastFilter:v,enabled:!0,destroyed:!1,initialized:!1,_animations:[],_transitions:[],_isMovementCanceled:!1,styleQueue:[]},E.Point=A,E._getItemTransformString=function(a,b){return s?"translate3d("+a.x+"px, "+a.y+"px, 0) scale3d("+b+", "+b+", 1)":"translate("+a.x+"px, "+a.y+"px) scale("+b+")"},E._getNumberStyle=function(b,c,d){if(t){d=d||z(b,null);var e=E._getFloat(d[c]);return B||"width"!==c?B||"height"!==c||(e+=E._getFloat(d.paddingTop)+E._getFloat(d.paddingBottom)+E._getFloat(d.borderTopWidth)+E._getFloat(d.borderBottomWidth)):e+=E._getFloat(d.paddingLeft)+E._getFloat(d.paddingRight)+E._getFloat(d.borderLeftWidth)+E._getFloat(d.borderRightWidth),e}return E._getFloat(a(b).css(c))},E._getFloat=function(a){return j(parseFloat(a))},E._getOuterWidth=function(a,b){var c=z(a,null),d=E._getNumberStyle(a,"width",c);if(b){var e=E._getNumberStyle(a,"marginLeft",c),f=E._getNumberStyle(a,"marginRight",c);d+=e+f}return d},E._getOuterHeight=function(a,b){var c=z(a,null),d=E._getNumberStyle(a,"height",c);if(b){var e=E._getNumberStyle(a,"marginTop",c),f=E._getNumberStyle(a,"marginBottom",c);d+=e+f}return d},E._skipTransition=function(a,b,c){var d=a.style[n];a.style[n]="0ms",b.call(c);var e=a.offsetWidth;e=null,a.style[n]=d},E.prototype._init=function(){this.$items=this._getItems(),this.sizer=this._getElementOption(this.sizer),this.sizer&&(this.useSizer=!0),this.$el.addClass(E.ClassName.BASE),this._initItems(),D.on("resize."+u+"."+this.unique,this._getResizeFunction());var a=this.$el.css(["position","overflow"]),b=E._getOuterWidth(this.element);this._validateStyles(a),this._setColumns(b),this.shuffle(this.group,this.initialSort),this.supported&&g(function(){this._setTransitions(),this.element.style[l]="height "+this.speed+"ms "+this.easing},this)},E.prototype._getResizeFunction=function(){var b=a.proxy(this._onResize,this);return this.throttle?this.throttle(b,this.throttleTime):b},E.prototype._getElementOption=function(a){return"string"==typeof a?this.$el.find(a)[0]||null:a&&a.nodeType&&1===a.nodeType?a:a&&a.jquery?a[0]:null},E.prototype._validateStyles=function(a){"static"===a.position&&(this.element.style.position="relative"),"hidden"!==a.overflow&&(this.element.style.overflow="hidden")},E.prototype._filter=function(a,b){a=a||this.lastFilter,b=b||this.$items;var c=this._getFilteredSets(a,b);return this._toggleFilterClasses(c.filtered,c.concealed),this.lastFilter=a,"string"==typeof a&&(this.group=a),c.filtered},E.prototype._getFilteredSets=function(b,c){var d=a(),e=a();return b===v?d=c:f(c,function(c){var f=a(c);this._doesPassFilter(b,f)?d=d.add(f):e=e.add(f)},this),{filtered:d,concealed:e}},E.prototype._doesPassFilter=function(b,c){if(a.isFunction(b))return b.call(c[0],c,this);var d=c.data(w),e=this.delimeter&&!a.isArray(d)?d.split(this.delimeter):d;return a.inArray(b,e)>-1},E.prototype._toggleFilterClasses=function(a,b){a.removeClass(E.ClassName.CONCEALED).addClass(E.ClassName.FILTERED),b.removeClass(E.ClassName.FILTERED).addClass(E.ClassName.CONCEALED)},E.prototype._initItems=function(a){a=a||this.$items,a.addClass([E.ClassName.SHUFFLE_ITEM,E.ClassName.FILTERED].join(" ")),a.css(this.itemCss).data("point",new A).data("scale",x)},E.prototype._updateItemCount=function(){this.visibleItems=this._getFilteredItems().length},E.prototype._setTransition=function(a){a.style[l]=q+" "+this.speed+"ms "+this.easing+", opacity "+this.speed+"ms "+this.easing},E.prototype._setTransitions=function(a){a=a||this.$items,f(a,function(a){this._setTransition(a)},this)},E.prototype._setSequentialDelay=function(a){this.supported&&f(a,function(a,b){a.style[m]="0ms,"+(b+1)*this.sequentialFadeDelay+"ms"},this)},E.prototype._getItems=function(){return this.$el.children(this.itemSelector)},E.prototype._getFilteredItems=function(){return this.$items.filter("."+E.ClassName.FILTERED)},E.prototype._getConcealedItems=function(){return this.$items.filter("."+E.ClassName.CONCEALED)},E.prototype._getColumnSize=function(b,c){var d;return d=a.isFunction(this.columnWidth)?this.columnWidth(b):this.useSizer?E._getOuterWidth(this.sizer):this.columnWidth?this.columnWidth:this.$items.length>0?E._getOuterWidth(this.$items[0],!0):b,0===d&&(d=b),d+c},E.prototype._getGutterSize=function(b){var c;return c=a.isFunction(this.gutterWidth)?this.gutterWidth(b):this.useSizer?E._getNumberStyle(this.sizer,"marginLeft"):this.gutterWidth},E.prototype._setColumns=function(a){var b=a||E._getOuterWidth(this.element),c=this._getGutterSize(b),d=this._getColumnSize(b,c),e=(b+c)/d;Math.abs(Math.round(e)-e)<this.columnThreshold&&(e=Math.round(e)),this.cols=Math.max(Math.floor(e),1),this.containerWidth=b,this.colWidth=d},E.prototype._setContainerSize=function(){this.$el.css("height",this._getContainerSize())},E.prototype._getContainerSize=function(){return h(this.positions)},E.prototype._fire=function(a,b){this.$el.trigger(a+"."+u,b&&b.length?b:[this])},E.prototype._resetCols=function(){var a=this.cols;for(this.positions=[];a--;)this.positions.push(0)},E.prototype._layout=function(a,b){f(a,function(a){this._layoutItem(a,!!b)},this),this._processStyleQueue(),this._setContainerSize()},E.prototype._layoutItem=function(b,c){var d=a(b),e=d.data(),f=e.point,g=e.scale,h={width:E._getOuterWidth(b,!0),height:E._getOuterHeight(b,!0)},i=this._getItemPosition(h);A.equals(f,i)&&g===x||(e.point=i,e.scale=x,this.styleQueue.push({$item:d,point:i,scale:x,opacity:c?0:1,skipTransition:c||0===this.speed,callfront:function(){c||d.css("visibility","visible")},callback:function(){c&&d.css("visibility","hidden")}}))},E.prototype._getItemPosition=function(a){for(var b=this._getColumnSpan(a.width,this.colWidth,this.cols),c=this._getColumnSet(b,this.cols),d=this._getShortColumn(c,this.buffer),e=new A(Math.round(this.colWidth*d),Math.round(c[d])),f=c[d]+a.height,g=this.cols+1-c.length,h=0;g>h;h++)this.positions[d+h]=f;return e},E.prototype._getColumnSpan=function(a,b,c){var d=a/b;return Math.abs(Math.round(d)-d)<this.columnThreshold&&(d=Math.round(d)),Math.min(Math.ceil(d),c)},E.prototype._getColumnSet=function(a,b){if(1===a)return this.positions;for(var c=b+1-a,d=[],e=0;c>e;e++)d[e]=h(this.positions.slice(e,e+a));return d},E.prototype._getShortColumn=function(a,b){for(var c=i(a),d=0,e=a.length;e>d;d++)if(a[d]>=c-b&&a[d]<=c+b)return d;return 0},E.prototype._shrink=function(b){var c=b||this._getConcealedItems();f(c,function(b){var c=a(b),d=c.data();d.scale!==y&&(d.scale=y,this.styleQueue.push({$item:c,point:d.point,scale:y,opacity:0,callback:function(){c.css("visibility","hidden")}}))},this)},E.prototype._onResize=function(){if(this.enabled&&!this.destroyed){var a=E._getOuterWidth(this.element);a!==this.containerWidth&&this.update()}},E.prototype._getStylesForTransition=function(a){var b={opacity:a.opacity};return this.supported?b[p]=E._getItemTransformString(a.point,a.scale):(b.left=a.point.x,b.top=a.point.y),b},E.prototype._transition=function(b){var c=this._getStylesForTransition(b);this._startItemAnimation(b.$item,c,b.callfront||a.noop,b.callback||a.noop)},E.prototype._startItemAnimation=function(b,c,d,e){function f(b){b.target===b.currentTarget&&(a(b.target).off(o,f),g._removeTransitionReference(h),e())}var g=this,h={$element:b,handler:f};if(d(),!this.initialized)return b.css(c),void e();if(this.supported)b.css(c),b.on(o,f),this._transitions.push(h);else{var i=b.stop(!0).animate(c,this.speed,"swing",e);this._animations.push(i.promise())}},E.prototype._processStyleQueue=function(b){this.isTransitioning&&this._cancelMovement();var c=a();f(this.styleQueue,function(a){a.skipTransition?this._styleImmediately(a):(c=c.add(a.$item),this._transition(a))},this),c.length>0&&this.initialized&&this.speed>0?(this.isTransitioning=!0,this.supported?this._whenCollectionDone(c,o,this._movementFinished):this._whenAnimationsDone(this._movementFinished)):b||g(this._layoutEnd,this),this.styleQueue.length=0},E.prototype._cancelMovement=function(){this.supported?f(this._transitions,function(a){a.$element.off(o,a.handler)}):(this._isMovementCanceled=!0,this.$items.stop(!0),this._isMovementCanceled=!1),this._transitions.length=0,this.isTransitioning=!1},E.prototype._removeTransitionReference=function(b){var c=a.inArray(b,this._transitions);c>-1&&this._transitions.splice(c,1)},E.prototype._styleImmediately=function(a){E._skipTransition(a.$item[0],function(){a.$item.css(this._getStylesForTransition(a))},this)},E.prototype._movementFinished=function(){this.isTransitioning=!1,this._layoutEnd()},E.prototype._layoutEnd=function(){this._fire(E.EventType.LAYOUT)},E.prototype._addItems=function(a,b,c){this._initItems(a),this._setTransitions(a),this.$items=this._getItems(),this._shrink(a),f(this.styleQueue,function(a){a.skipTransition=!0}),this._processStyleQueue(!0),b?this._addItemsToEnd(a,c):this.shuffle(this.lastFilter)},E.prototype._addItemsToEnd=function(a,b){var c=this._filter(null,a),d=c.get();this._updateItemCount(),this._layout(d,!0),b&&this.supported&&this._setSequentialDelay(d),this._revealAppended(d)},E.prototype._revealAppended=function(b){g(function(){f(b,function(b){var c=a(b);this._transition({$item:c,opacity:1,point:c.data("point"),scale:x})},this),this._whenCollectionDone(a(b),o,function(){a(b).css(m,"0ms"),this._movementFinished()})},this,this.revealAppendedDelay)},E.prototype._whenCollectionDone=function(b,c,d){function e(b){b.target===b.currentTarget&&(a(b.target).off(c,e),f++,f===g&&(h._removeTransitionReference(i),d.call(h)))}var f=0,g=b.length,h=this,i={$element:b,handler:e};b.on(c,e),this._transitions.push(i)},E.prototype._whenAnimationsDone=function(b){a.when.apply(null,this._animations).always(a.proxy(function(){this._animations.length=0,this._isMovementCanceled||b.call(this)},this))},E.prototype.shuffle=function(a,b){this.enabled&&(a||(a=v),this._filter(a),this._updateItemCount(),this._shrink(),this.sort(b))},E.prototype.sort=function(a){if(this.enabled){this._resetCols();var b=a||this.lastSort,c=this._getFilteredItems().sorted(b);this._layout(c),this.lastSort=b}},E.prototype.update=function(a){this.enabled&&(a||this._setColumns(),this.sort())},E.prototype.layout=function(){this.update(!0)},E.prototype.appended=function(a,b,c){this._addItems(a,b===!0,c!==!1)},E.prototype.disable=function(){this.enabled=!1},E.prototype.enable=function(a){this.enabled=!0,a!==!1&&this.update()},E.prototype.remove=function(b){function c(){b.remove(),this.$items=this._getItems(),this._updateItemCount(),this._fire(E.EventType.REMOVED,[b,this]),b=null}b.length&&b.jquery&&(this._toggleFilterClasses(a(),b),this._shrink(b),this.sort(),this.$el.one(E.EventType.LAYOUT+"."+u,a.proxy(c,this)))},E.prototype.destroy=function(){D.off("."+this.unique),this.$el.removeClass(u).removeAttr("style").removeData(u),this.$items.removeAttr("style").removeData("point").removeData("scale").removeClass([E.ClassName.CONCEALED,E.ClassName.FILTERED,E.ClassName.SHUFFLE_ITEM].join(" ")),this.$items=null,this.$el=null,this.sizer=null,this.element=null,this._transitions=null,this.destroyed=!0},a.fn.shuffle=function(b){var c=Array.prototype.slice.call(arguments,1);return this.each(function(){var d=a(this),e=d.data(u);e?"string"==typeof b&&e[b]&&e[b].apply(e,c):(e=new E(this,b),d.data(u,e))})},a.fn.sorted=function(b){var d=a.extend({},a.fn.sorted.defaults,b),e=this.get(),f=!1;return e.length?d.randomize?k(e):(a.isFunction(d.by)&&e.sort(function(b,e){if(f)return 0;var g=d.by(a(b)),h=d.by(a(e));return g===c&&h===c?(f=!0,0):h>g||"sortFirst"===g||"sortLast"===h?-1:g>h||"sortLast"===g||"sortFirst"===h?1:0}),f?this.get():(d.reverse&&e.reverse(),e)):[]},a.fn.sorted.defaults={reverse:!1,by:null,randomize:!1},E});


/* viewport selectors in jquery (http://www.appelsiini.net/projects/viewport) */

(function($){$.belowthefold=function(element,settings){var fold=$(window).height()+$(window).scrollTop();return fold<=$(element).offset().top-settings.threshold;};$.abovethetop=function(element,settings){var top=$(window).scrollTop();return top>=$(element).offset().top+$(element).height()-settings.threshold;};$.rightofscreen=function(element,settings){var fold=$(window).width()+$(window).scrollLeft();return fold<=$(element).offset().left-settings.threshold;};$.leftofscreen=function(element,settings){var left=$(window).scrollLeft();return left>=$(element).offset().left+$(element).width()-settings.threshold;};$.inviewport=function(element,settings){return!$.rightofscreen(element,settings)&&!$.leftofscreen(element,settings)&&!$.belowthefold(element,settings)&&!$.abovethetop(element,settings);};$.extend($.expr[':'],{"below-the-fold":function(a,i,m){return $.belowthefold(a,{threshold:0});},"above-the-top":function(a,i,m){return $.abovethetop(a,{threshold:0});},"left-of-screen":function(a,i,m){return $.leftofscreen(a,{threshold:0});},"right-of-screen":function(a,i,m){return $.rightofscreen(a,{threshold:0});},"in-viewport":function(a,i,m){return $.inviewport(a,{threshold:0});}});})(jQuery);

/** x and y from 0 to 1 **/
jQuery.fn.isOnScreen = function(x, y){

    if(x == null || typeof x == 'undefined') x = 1;
    if(y == null || typeof y == 'undefined') y = 1;

    var win = jQuery(window);

    var viewport = {
        top : win.scrollTop(),
        left : win.scrollLeft()
    };
    viewport.right = viewport.left + win.width();
    viewport.bottom = viewport.top + win.height();

    var height = this.outerHeight();
    var width = this.outerWidth();

    if(!width || !height){
        return false;
    }

    var bounds = this.offset();
    bounds.right = bounds.left + width;
    bounds.bottom = bounds.top + height;

    var visible = (!(viewport.right < bounds.left || viewport.left > bounds.right || viewport.bottom < bounds.top || viewport.top > bounds.bottom));

    if(!visible){
        return false;
    }

    var deltas = {
        top : Math.min( 1, ( bounds.bottom - viewport.top ) / height),
        bottom : Math.min(1, ( viewport.bottom - bounds.top ) / height),
        left : Math.min(1, ( bounds.right - viewport.left ) / width),
        right : Math.min(1, ( viewport.right - bounds.left ) / width)
    };

    return (deltas.left * deltas.right) >= x && (deltas.top * deltas.bottom) >= y;
};