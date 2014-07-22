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

;(function(b){var a={init:function(c){return this.each(function(){a.destroy.call(this);this.opt=b.extend(true,{},b.fn.raty.defaults,c);var e=b(this),g=["number","readOnly","score","scoreName"];a._callback.call(this,g);if(this.opt.precision){a._adjustPrecision.call(this);}this.opt.number=a._between(this.opt.number,0,this.opt.numberMax);this.opt.path=this.opt.path||"";if(this.opt.path&&this.opt.path.slice(this.opt.path.length-1,this.opt.path.length)!=="/"){this.opt.path+="/";}this.stars=a._createStars.call(this);this.score=a._createScore.call(this);a._apply.call(this,this.opt.score);var f=this.opt.space?4:0,d=this.opt.width||(this.opt.number*this.opt.size+this.opt.number*f);if(this.opt.cancel){this.cancel=a._createCancel.call(this);d+=(this.opt.size+f);}if(this.opt.readOnly){a._lock.call(this);}else{e.css("cursor","pointer");a._binds.call(this);}if(this.opt.width!==false){e.css("width",d);}a._target.call(this,this.opt.score);e.data({settings:this.opt,raty:true});});},_adjustPrecision:function(){this.opt.targetType="score";this.opt.half=true;},_apply:function(c){if(c&&c>0){c=a._between(c,0,this.opt.number);this.score.val(c);}a._fill.call(this,c);if(c){a._roundStars.call(this,c);}},_between:function(e,d,c){return Math.min(Math.max(parseFloat(e),d),c);},_binds:function(){if(this.cancel){a._bindCancel.call(this);}a._bindClick.call(this);a._bindOut.call(this);a._bindOver.call(this);},_bindCancel:function(){a._bindClickCancel.call(this);a._bindOutCancel.call(this);a._bindOverCancel.call(this);},_bindClick:function(){var c=this,d=b(c);c.stars.on("click.raty",function(e){c.score.val((c.opt.half||c.opt.precision)?d.data("score"):this.alt);if(c.opt.click){c.opt.click.call(c,parseFloat(c.score.val()),e);}});},_bindClickCancel:function(){var c=this;c.cancel.on("click.raty",function(d){c.score.removeAttr("value");if(c.opt.click){c.opt.click.call(c,null,d);}});},_bindOut:function(){var c=this;b(this).on("mouseleave.raty",function(d){var e=parseFloat(c.score.val())||undefined;a._apply.call(c,e);a._target.call(c,e,d);if(c.opt.mouseout){c.opt.mouseout.call(c,e,d);}});},_bindOutCancel:function(){var c=this;c.cancel.on("mouseleave.raty",function(d){b(this).attr("src",c.opt.path+c.opt.cancelOff);if(c.opt.mouseout){c.opt.mouseout.call(c,c.score.val()||null,d);}});},_bindOverCancel:function(){var c=this;c.cancel.on("mouseover.raty",function(d){b(this).attr("src",c.opt.path+c.opt.cancelOn);c.stars.attr("src",c.opt.path+c.opt.starOff);a._target.call(c,null,d);if(c.opt.mouseover){c.opt.mouseover.call(c,null);}});},_bindOver:function(){var c=this,d=b(c),e=c.opt.half?"mousemove.raty":"mouseover.raty";c.stars.on(e,function(g){var h=parseInt(this.alt,10);if(c.opt.half){var f=parseFloat((g.pageX-b(this).offset().left)/c.opt.size),j=(f>0.5)?1:0.5;h=h-1+j;a._fill.call(c,h);if(c.opt.precision){h=h-j+f;}a._roundStars.call(c,h);d.data("score",h);}else{a._fill.call(c,h);}a._target.call(c,h,g);if(c.opt.mouseover){c.opt.mouseover.call(c,h,g);}});},_callback:function(c){for(i in c){if(typeof this.opt[c[i]]==="function"){this.opt[c[i]]=this.opt[c[i]].call(this);}}},_createCancel:function(){var e=b(this),c=this.opt.path+this.opt.cancelOff,d=b("<img />",{src:c,alt:"x",title:this.opt.cancelHint,"class":"raty-cancel"});if(this.opt.cancelPlace=="left"){e.prepend("&#160;").prepend(d);}else{e.append("&#160;").append(d);}return d;},_createScore:function(){return b("<input />",{type:"hidden",name:this.opt.scoreName}).appendTo(this);},_createStars:function(){var e=b(this);for(var c=1;c<=this.opt.number;c++){var f=a._getHint.call(this,c),d=(this.opt.score&&this.opt.score>=c)?"starOn":"starOff";d=this.opt.path+this.opt[d];b("<img />",{src:d,alt:c,title:f}).appendTo(this);if(this.opt.space){e.append((c<this.opt.number)?"&#160;":"");}}return e.children("img");},_error:function(c){b(this).html(c);b.error(c);},_fill:function(d){var m=this,e=0;for(var f=1;f<=m.stars.length;f++){var g=m.stars.eq(f-1),l=m.opt.single?(f==d):(f<=d);if(m.opt.iconRange&&m.opt.iconRange.length>e){var j=m.opt.iconRange[e],h=j.on||m.opt.starOn,c=j.off||m.opt.starOff,k=l?h:c;if(f<=j.range){g.attr("src",m.opt.path+k);}if(f==j.range){e++;}}else{var k=l?"starOn":"starOff";g.attr("src",this.opt.path+this.opt[k]);}}},_getHint:function(d){var c=this.opt.hints[d-1];return(c==="")?"":(c||d);},_lock:function(){var d=parseInt(this.score.val(),10),c=d?a._getHint.call(this,d):this.opt.noRatedMsg;b(this).data("readonly",true).css("cursor","").attr("title",c);this.score.attr("readonly","readonly");this.stars.attr("title",c);if(this.cancel){this.cancel.hide();}},_roundStars:function(e){var d=(e-Math.floor(e)).toFixed(2);if(d>this.opt.round.down){var c="starOn";if(this.opt.halfShow&&d<this.opt.round.up){c="starHalf";}else{if(d<this.opt.round.full){c="starOff";}}this.stars.eq(Math.ceil(e)-1).attr("src",this.opt.path+this.opt[c]);}},_target:function(f,d){if(this.opt.target){var e=b(this.opt.target);if(e.length===0){a._error.call(this,"Target selector invalid or missing!");}if(this.opt.targetFormat.indexOf("{score}")<0){a._error.call(this,'Template "{score}" missing!');}var c=d&&d.type=="mouseover";if(f===undefined){f=this.opt.targetText;}else{if(f===null){f=c?this.opt.cancelHint:this.opt.targetText;}else{if(this.opt.targetType=="hint"){f=a._getHint.call(this,Math.ceil(f));}else{if(this.opt.precision){f=parseFloat(f).toFixed(1);}}if(!c&&!this.opt.targetKeep){f=this.opt.targetText;}}}if(f){f=this.opt.targetFormat.toString().replace("{score}",f);}if(e.is(":input")){e.val(f);}else{e.html(f);}}},_unlock:function(){b(this).data("readonly",false).css("cursor","pointer").removeAttr("title");this.score.removeAttr("readonly","readonly");for(var c=0;c<this.opt.number;c++){this.stars.eq(c).attr("title",a._getHint.call(this,c+1));}if(this.cancel){this.cancel.css("display","");}},cancel:function(c){return this.each(function(){if(b(this).data("readonly")!==true){a[c?"click":"score"].call(this,null);this.score.removeAttr("value");}});},click:function(c){return b(this).each(function(){if(b(this).data("readonly")!==true){a._apply.call(this,c);if(!this.opt.click){a._error.call(this,'You must add the "click: function(score, evt) { }" callback.');}this.opt.click.call(this,c,{type:"click"});a._target.call(this,c);}});},destroy:function(){return b(this).each(function(){var d=b(this),c=d.data("raw");if(c){d.off(".raty").empty().css({cursor:c.style.cursor,width:c.style.width}).removeData("readonly");}else{d.data("raw",d.clone()[0]);}});},getScore:function(){var d=[],c;b(this).each(function(){c=this.score.val();d.push(c?parseFloat(c):undefined);});return(d.length>1)?d:d[0];},readOnly:function(c){return this.each(function(){var d=b(this);if(d.data("readonly")!==c){if(c){d.off(".raty").children("img").off(".raty");a._lock.call(this);}else{a._binds.call(this);a._unlock.call(this);}d.data("readonly",c);}});},reload:function(){return a.set.call(this,{});},score:function(){return arguments.length?a.setScore.apply(this,arguments):a.getScore.call(this);},set:function(c){return this.each(function(){var e=b(this),f=e.data("settings"),d=b.extend({},f,c);e.raty(d);});},setScore:function(c){return b(this).each(function(){if(b(this).data("readonly")!==true){a._apply.call(this,c);a._target.call(this,c);}});}};b.fn.raty=function(c){if(a[c]){return a[c].apply(this,Array.prototype.slice.call(arguments,1));}else{if(typeof c==="object"||!c){return a.init.apply(this,arguments);}else{b.error("Method "+c+" does not exist!");}}};b.fn.raty.defaults={cancel:false,cancelHint:"Cancel this rating!",cancelOff:"cancel-off.png",cancelOn:"cancel-on.png",cancelPlace:"left",click:undefined,half:false,halfShow:true,hints:["bad","poor","regular","good","gorgeous"],iconRange:undefined,mouseout:undefined,mouseover:undefined,noRatedMsg:"Not rated yet!",number:5,numberMax:20,path:"",precision:false,readOnly:false,round:{down:0.25,full:0.6,up:0.76},score:undefined,scoreName:"score",single:false,size:16,space:true,starHalf:"star-half.png",starOff:"star-off.png",starOn:"star-on.png",target:undefined,targetFormat:"{score}",targetKeep:false,targetText:"",targetType:"hint",width:undefined};})(jQuery);

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

/**
* @license Input Mask plugin for jquery
* http://github.com/RobinHerbots/jquery.inputmask
* Copyright (c) 2010 - 2014 Robin Herbots
* Licensed under the MIT license (http://www.opensource.org/licenses/mit-license.php)
* Version: 0.0.0
*/

(function ($) {
    if ($.fn.inputmask === undefined) {

        //helper functions
        function isInputEventSupported(eventName) {
            var el = document.createElement('input'),
                eventName = 'on' + eventName,
                isSupported = (eventName in el);
            if (!isSupported) {
                el.setAttribute(eventName, 'return;');
                isSupported = typeof el[eventName] == 'function';
            }
            el = null;
            return isSupported;
        }

        function resolveAlias(aliasStr, options, opts) {
            var aliasDefinition = opts.aliases[aliasStr];
            if (aliasDefinition) {
                if (aliasDefinition.alias) resolveAlias(aliasDefinition.alias, undefined, opts); //alias is another alias
                $.extend(true, opts, aliasDefinition); //merge alias definition in the options
                $.extend(true, opts, options); //reapply extra given options
                return true;
            }
            return false;
        }

        function generateMaskSet(opts, multi) {
            var ms = [];

            function analyseMask(mask) {
                var tokenizer = /(?:[?*+]|\{[0-9\+\*]+(?:,[0-9\+\*]*)?\})\??|[^.?*+^${[]()|\\]+|./g,
                    escaped = false;

                function maskToken(isGroup, isOptional, isQuantifier, isAlternator) {
                    this.matches = [];
                    this.isGroup = isGroup || false;
                    this.isOptional = isOptional || false;
                    this.isQuantifier = isQuantifier || false;
                    this.isAlternator = isAlternator || false;
                    this.quantifier = { min: 1, max: 1 };
                };

                //test definition => {fn: RegExp/function, cardinality: int, optionality: bool, newBlockMarker: bool, casing: null/upper/lower, def: definitionSymbol, placeholder: placeholder, mask: real maskDefinition}
                function insertTestDefinition(mtoken, element, position) {
                    var maskdef = opts.definitions[element];
                    var newBlockMarker = mtoken.matches.length == 0;
                    position = position != undefined ? position : mtoken.matches.length;
                    if (maskdef && !escaped) {
                        var prevalidators = maskdef["prevalidator"], prevalidatorsL = prevalidators ? prevalidators.length : 0;
                        for (var i = 1; i < maskdef.cardinality; i++) {
                            var prevalidator = prevalidatorsL >= i ? prevalidators[i - 1] : [], validator = prevalidator["validator"], cardinality = prevalidator["cardinality"];
                            mtoken.matches.splice(position++, 0, { fn: validator ? typeof validator == 'string' ? new RegExp(validator) : new function () { this.test = validator; } : new RegExp("."), cardinality: cardinality ? cardinality : 1, optionality: mtoken.isOptional, newBlockMarker: newBlockMarker, casing: maskdef["casing"], def: maskdef["definitionSymbol"] || element, placeholder: maskdef["placeholder"], mask: element });
                        }
                        mtoken.matches.splice(position++, 0, { fn: maskdef.validator ? typeof maskdef.validator == 'string' ? new RegExp(maskdef.validator) : new function () { this.test = maskdef.validator; } : new RegExp("."), cardinality: maskdef.cardinality, optionality: mtoken.isOptional, newBlockMarker: newBlockMarker, casing: maskdef["casing"], def: maskdef["definitionSymbol"] || element, placeholder: maskdef["placeholder"], mask: element });
                    } else {
                        mtoken.matches.splice(position++, 0, { fn: null, cardinality: 0, optionality: mtoken.isOptional, newBlockMarker: newBlockMarker, casing: null, def: element, placeholder: undefined, mask: element });
                        escaped = false;
                    }
                }

                var currentToken = new maskToken(),
                    match,
                    m,
                    openenings = [],
                    maskTokens = [],
                    openingToken;

                while (match = tokenizer.exec(mask)) {
                    m = match[0];
                    switch (m.charAt(0)) {
                        case opts.optionalmarker.end:
                            // optional closing
                        case opts.groupmarker.end:
                            // Group closing
                            openingToken = openenings.pop();
                            if (openenings.length > 0) {
                                openenings[openenings.length - 1]["matches"].push(openingToken);
                            } else {
                                currentToken.matches.push(openingToken);
                            }
                            break;
                        case opts.optionalmarker.start:
                            // optional opening
                            openenings.push(new maskToken(false, true));
                            break;
                        case opts.groupmarker.start:
                            // Group opening
                            openenings.push(new maskToken(true));
                            break;
                        case opts.quantifiermarker.start:
                            //Quantifier
                            var quantifier = new maskToken(false, false, true);

                            m = m.replace(/[{}]/g, "");
                            var mq = m.split(","),
                                mq0 = isNaN(mq[0]) ? mq[0] : parseInt(mq[0]),
                                mq1 = mq.length == 1 ? mq0 : (isNaN(mq[1]) ? mq[1] : parseInt(mq[1]));
                            if (mq1 == "*" || mq1 == "+") {
                                mq0 = mq1 == "*" ? 0 : 1;
                            }
                            quantifier.quantifier = { min: mq0, max: mq1 };
                            if (openenings.length > 0) {
                                var matches = openenings[openenings.length - 1]["matches"];
                                match = matches.pop();
                                if (!match["isGroup"]) {
                                    var groupToken = new maskToken(true);
                                    groupToken.matches.push(match);
                                    match = groupToken;
                                }
                                matches.push(match);
                                matches.push(quantifier);
                            } else {
                                match = currentToken.matches.pop();
                                if (!match["isGroup"]) {
                                    var groupToken = new maskToken(true);
                                    groupToken.matches.push(match);
                                    match = groupToken;
                                }
                                currentToken.matches.push(match);
                                currentToken.matches.push(quantifier);
                            }
                            break;
                        case opts.escapeChar:
                            escaped = true;
                            break;
                        case opts.alternatormarker:
                            var alternator = new maskToken(false, false, false, true);
                            if (openenings.length > 0) {
                                var matches = openenings[openenings.length - 1]["matches"];
                                match = matches.pop();
                                alternator.matches.push(match);
                                openenings.push(alternator);
                            } else {
                                match = currentToken.matches.pop();
                                alternator.matches.push(match);
                                openenings.push(alternator);
                            }
                            break;
                        default:
                            if (openenings.length > 0) {
                                insertTestDefinition(openenings[openenings.length - 1], m);
                                var lastToken = openenings[openenings.length - 1];
                                if (lastToken["isAlternator"]) {
                                    openingToken = openenings.pop();
                                    for (var mndx = 0; mndx < openingToken.matches.length; mndx++) {
                                        openingToken.matches[mndx].isGroup = false; //don't mark alternate groups as group
                                    }
                                    if (openenings.length > 0) {
                                        openenings[openenings.length - 1]["matches"].push(openingToken);
                                    } else {
                                        currentToken.matches.push(openingToken);
                                    }
                                }
                            } else {
                                if (currentToken.matches.length > 0) {
                                    var lastMatch = currentToken.matches[currentToken.matches.length - 1];
                                    if (lastMatch["isGroup"]) { //this is not a group but a normal mask => convert
                                        lastMatch.isGroup = false;
                                        insertTestDefinition(lastMatch, opts.groupmarker.start, 0);
                                        insertTestDefinition(lastMatch, opts.groupmarker.end);
                                    }
                                }
                                insertTestDefinition(currentToken, m);
                            }
                    }
                }

                if (openenings.length > 0) {
                    var lastToken = openenings[openenings.length - 1];
                    if (lastToken["isAlternator"]) {
                        for (var mndx = 0; mndx < lastToken.matches.length; mndx++) {
                            lastToken.matches[mndx].isGroup = false; //don't mark alternate groups as group
                        }
                    }
                    currentToken.matches = currentToken.matches.concat(openenings);
                }
                if (currentToken.matches.length > 0) {
                    var lastMatch = currentToken.matches[currentToken.matches.length - 1];
                    if (lastMatch["isGroup"]) { //this is not a group but a normal mask => convert
                        lastMatch.isGroup = false;
                        insertTestDefinition(lastMatch, opts.groupmarker.start, 0);
                        insertTestDefinition(lastMatch, opts.groupmarker.end);
                    }
                    maskTokens.push(currentToken);
                }

                //console.log(JSON.stringify(maskTokens));
                return maskTokens;
            }

            function generateMask(mask, metadata) {
                if (opts.numericInput && opts.multi !== true) { //TODO FIX FOR DYNAMIC MASKS WITH QUANTIFIERS
                    mask = mask.split('').reverse();
                    for (var ndx = 0; ndx < mask.length; ndx++) {
                        if (mask[ndx] == opts.optionalmarker.start)
                            mask[ndx] = opts.optionalmarker.end;
                        else if (mask[ndx] == opts.optionalmarker.end)
                            mask[ndx] = opts.optionalmarker.start;
                        else if (mask[ndx] == opts.groupmarker.start)
                            mask[ndx] = opts.groupmarker.end;
                        else if (mask[ndx] == opts.groupmarker.end)
                            mask[ndx] = opts.groupmarker.start;
                    }
                    mask = mask.join('');
                }
                if (mask == undefined || mask == "")
                    return undefined;
                else {
                    if (opts.repeat > 0 || opts.repeat == "*" || opts.repeat == "+") {
                        var repeatStart = opts.repeat == "*" ? 0 : (opts.repeat == "+" ? 1 : opts.repeat);
                        mask = opts.groupmarker.start + mask + opts.groupmarker.end + opts.quantifiermarker.start + repeatStart + "," + opts.repeat + opts.quantifiermarker.end;
                    }
                    if ($.inputmask.masksCache[mask] == undefined) {
                        $.inputmask.masksCache[mask] = {
                            "mask": mask,
                            "maskToken": analyseMask(mask),
                            "validPositions": {},
                            "_buffer": undefined,
                            "buffer": undefined,
                            "tests": {},
                            "metadata": metadata
                        };
                    }

                    return $.extend(true, {}, $.inputmask.masksCache[mask]);
                }
            }

            if ($.isFunction(opts.mask)) { //allow mask to be a preprocessing fn - should return a valid mask
                opts.mask = opts.mask.call(this, opts);
            }
            if ($.isArray(opts.mask)) {
                if (multi) {
                    $.each(opts.mask, function (ndx, msk) {
                        if (msk["mask"] != undefined) {
                            ms.push(generateMask(msk["mask"].toString(), msk));
                        } else {
                            ms.push(generateMask(msk.toString()));
                        }
                    });
                } else {
                    var altMask = "(" + opts.mask.join(")|(") + ")";
                    ms = generateMask(altMask);
                }
            } else {
                if (opts.mask.length == 1 && opts.greedy == false && opts.repeat != 0) {
                    opts.placeholder = "";
                } //hide placeholder with single non-greedy mask
                if (opts.mask["mask"] != undefined) {
                    ms = generateMask(opts.mask["mask"].toString(), opts.mask);
                } else {
                    ms = generateMask(opts.mask.toString());
                }
            }
            return ms;
        }

        var msie1x = typeof ScriptEngineMajorVersion === "function"
                ? ScriptEngineMajorVersion() //IE11 detection
                : new Function("/*@cc_on return @_jscript_version; @*/")() >= 10, //conditional compilation from mickeysoft trick
            ua = navigator.userAgent,
            iphone = ua.match(new RegExp("iphone", "i")) !== null,
            android = ua.match(new RegExp("android.*safari.*", "i")) !== null,
            androidchrome = ua.match(new RegExp("android.*chrome.*", "i")) !== null,
            androidfirefox = ua.match(new RegExp("android.*firefox.*", "i")) !== null,
            kindle = /Kindle/i.test(ua) || /Silk/i.test(ua) || /KFTT/i.test(ua) || /KFOT/i.test(ua) || /KFJWA/i.test(ua) || /KFJWI/i.test(ua) || /KFSOWI/i.test(ua) || /KFTHWA/i.test(ua) || /KFTHWI/i.test(ua) || /KFAPWA/i.test(ua) || /KFAPWI/i.test(ua),
            PasteEventType = isInputEventSupported('paste') ? 'paste' : isInputEventSupported('input') ? 'input' : "propertychange";

        //if (androidchrome) {
        //    var browser = navigator.userAgent.match(new RegExp("chrome.*", "i")),
        //        version = parseInt(new RegExp(/[0-9]+/).exec(browser));
        //    androidchrome32 = (version == 32);
        //}

        //masking scope
        //actionObj definition see below
        function maskScope(actionObj, maskset, opts) {
            var isRTL = false,
                valueOnFocus,
                $el,
                skipKeyPressEvent = false, //Safari 5.1.x - modal dialog fires keypress twice workaround
                skipInputEvent = false, //skip when triggered from within inputmask
                ignorable = false,
                maxLength;

            //maskset helperfunctions


            function getMaskTemplate(baseOnInput, minimalPos, includeInput) {
                minimalPos = minimalPos || 0;
                var maskTemplate = [], ndxIntlzr, pos = 0, test, testPos;
                do {
                    if (baseOnInput === true && getMaskSet()['validPositions'][pos]) {
                        var validPos = getMaskSet()['validPositions'][pos];
                        test = validPos["match"];
                        ndxIntlzr = validPos["locator"].slice();
                        maskTemplate.push(test["fn"] == null ? test["def"] : (includeInput === true ? validPos["input"] : test["placeholder"] || opts.placeholder.charAt(pos % opts.placeholder.length)));
                    } else {
                        if (minimalPos > pos) {
                            var testPositions = getTests(pos, ndxIntlzr, pos - 1);
                            testPos = testPositions[0];
                        } else {
                            testPos = getTestTemplate(pos, ndxIntlzr, pos - 1);
                        }
                        test = testPos["match"];
                        ndxIntlzr = testPos["locator"].slice();
                        maskTemplate.push(test["fn"] == null ? test["def"] : test["placeholder"] || opts.placeholder.charAt(pos % opts.placeholder.length));
                    }
                    pos++;
                } while ((maxLength == undefined || pos - 1 < maxLength) && test["fn"] != null || (test["fn"] == null && test["def"] != "") || minimalPos >= pos);
                maskTemplate.pop(); //drop the last one which is empty
                return maskTemplate;
            }

            function getMaskSet() {
                return maskset;
            }

            function resetMaskSet(soft) {
                var maskset = getMaskSet();
                maskset["buffer"] = undefined;
                maskset["tests"] = {};
                if (soft !== true) {
                    maskset["_buffer"] = undefined;
                    maskset["validPositions"] = {};
                    maskset["p"] = -1;
                }
            }

            function getLastValidPosition(closestTo) {
                var maskset = getMaskSet(), lastValidPosition = -1, valids = maskset["validPositions"];
                if (closestTo == undefined) closestTo = -1;
                var before = lastValidPosition, after = lastValidPosition;
                for (var posNdx in valids) {
                    var psNdx = parseInt(posNdx);
                    if (closestTo == -1 || valids[psNdx]["match"].fn != null) {
                        if (psNdx < closestTo) before = psNdx;
                        if (psNdx >= closestTo) after = psNdx;
                    }
                }
                lastValidPosition = (closestTo - before) > 1 || after < closestTo ? before : after;
                return lastValidPosition;
            }

            function setValidPosition(pos, validTest, fromSetValid) {
                if (opts.insertMode && getMaskSet()["validPositions"][pos] != undefined && fromSetValid == undefined) {
                    //reposition & revalidate others
                    var positionsClone = $.extend(true, {}, getMaskSet()["validPositions"]), lvp = getLastValidPosition(), i;
                    for (i = pos; i <= lvp; i++) { //clear selection
                        delete getMaskSet()["validPositions"][i];
                    }
                    getMaskSet()["validPositions"][pos] = validTest;
                    var valid = true;
                    for (i = pos; i <= lvp ; i++) {
                        var t = positionsClone[i];
                        if (t != undefined) {
                            var j = t["match"].fn == null ? i + 1 : seekNext(i);
                            if (positionCanMatchDefinition(j, t["match"].def)) {
                                valid = valid && isValid(j, t["input"], true, true) !== false;
                            } else valid = false;
                        }
                        if (!valid) break;
                    }

                    if (!valid) {
                        getMaskSet()["validPositions"] = $.extend(true, {}, positionsClone);
                        return false;
                    }
                } else
                    getMaskSet()["validPositions"][pos] = validTest;

                return true;
            }

            function stripValidPositions(start, end) {
                var i, startPos = start, lvp;
                for (i = start; i < end; i++) { //clear selection
                    //TODO FIXME BETTER CHECK
                    delete getMaskSet()["validPositions"][i];
                }

                for (i = end ; i <= getLastValidPosition() ;) {
                    var t = getMaskSet()["validPositions"][i];
                    var s = getMaskSet()["validPositions"][startPos];
                    if (t != undefined && s == undefined) {
                        if (positionCanMatchDefinition(startPos, t.match.def) && isValid(startPos, t["input"], true) !== false) {
                            delete getMaskSet()["validPositions"][i];
                            i++;
                        }
                        startPos++;
                    } else i++;
                }
                lvp = getLastValidPosition();
                //catchup
                while (lvp > 0 && (getMaskSet()["validPositions"][lvp] == undefined || getMaskSet()["validPositions"][lvp].match.fn == null)) {
                    delete getMaskSet()["validPositions"][lvp];
                    lvp--;
                }
                resetMaskSet(true);
            }

            function getTestTemplate(pos, ndxIntlzr, tstPs) {
                var testPositions = getTests(pos, ndxIntlzr, tstPs), testPos;
                for (var ndx = 0; ndx < testPositions.length; ndx++) {
                    testPos = testPositions[ndx];
                    if (opts.greedy || (testPos["match"] && (testPos["match"].optionality === false || testPos["match"].newBlockMarker === false) && testPos["match"].optionalQuantifier !== true)) {
                        break;
                    }
                }

                return testPos;
            }
            function getTest(pos) {
                if (getMaskSet()['validPositions'][pos]) {
                    return getMaskSet()['validPositions'][pos]["match"];
                }
                return getTests(pos)[0]["match"];
            }
            function positionCanMatchDefinition(pos, def) {
                var valid = false, tests = getTests(pos);
                for (var tndx = 0; tndx < tests.length; tndx++) {
                    if (tests[tndx]["match"] && tests[tndx]["match"].def == def) {
                        valid = true;
                        break;
                    }
                }
                return valid;
            };
            function getTests(pos, ndxIntlzr, tstPs) {
                var maskTokens = getMaskSet()["maskToken"], testPos = ndxIntlzr ? tstPs : 0, ndxInitializer = ndxIntlzr || [0], matches = [], insertStop = false;
                function ResolveTestFromToken(maskToken, ndxInitializer, loopNdx, quantifierRecurse) { //ndxInitilizer contains a set of indexes to speedup searches in the mtokens

                    function handleMatch(match, loopNdx, quantifierRecurse) {
                        if (testPos == pos && match.matches == undefined) {
                            matches.push({ "match": match, "locator": loopNdx.reverse() });
                            return true;
                        } else if (match.matches != undefined) {
                            if (match.isGroup && quantifierRecurse !== true) { //when a group pass along to the quantifier
                                match = handleMatch(maskToken.matches[tndx + 1], loopNdx);
                                if (match) return true;
                            } else if (match.isOptional) {
                                var optionalToken = match;
                                match = ResolveTestFromToken(match, ndxInitializer, loopNdx, quantifierRecurse);
                                if (match) {
                                    var latestMatch = matches[matches.length - 1]["match"];
                                    var isFirstMatch = $.inArray(latestMatch, optionalToken.matches) == 0;
                                    if (isFirstMatch) {
                                        insertStop = true; //insert a stop for non greedy
                                    }
                                    testPos = pos; //match the position after the group
                                }
                            } else if (match.isAlternator) {
                                var alternateToken = match;
                                var currentMatches = matches.slice(), malternate1, malternate2, loopNdxCnt = loopNdx.length;
                                var altIndex = ndxInitializer.length > 0 ? ndxInitializer.shift() : -1;
                                if (altIndex == -1) {
                                    var currentPos = testPos;
                                    matches = [];
                                    match = ResolveTestFromToken(alternateToken.matches[0], ndxInitializer.slice(), [0].concat(loopNdx), quantifierRecurse);
                                    malternate1 = matches.slice();
                                    testPos = currentPos;
                                    matches = [];
                                    match = ResolveTestFromToken(alternateToken.matches[1], ndxInitializer, [1].concat(loopNdx), quantifierRecurse);
                                    malternate2 = matches.slice();
                                    //fuzzy merge matches
                                    matches = [];
                                    for (var ndx1 = 0; ndx1 < malternate1.length; ndx1++) {
                                        var altMatch = malternate1[ndx1]; currentMatches.push(altMatch);
                                        for (var ndx2 = 0; ndx2 < malternate2.length; ndx2++) {
                                            var altMatch2 = malternate2[ndx2];
                                            //verify equality
                                            if (altMatch.match.mask == altMatch2.match.mask) {
                                                malternate2.splice(ndx2, 1);
                                                altMatch.locator[loopNdxCnt] = -1;
                                                break;
                                            }
                                        }
                                    }
                                    matches = currentMatches.concat(malternate2);

                                } else {
                                    match = handleMatch(alternateToken.matches[altIndex], [altIndex].concat(loopNdx), quantifierRecurse);
                                }
                                if (match) return true;
                            } else if (match.isQuantifier && quantifierRecurse !== true) {
                                var qt = match;
                                opts.greedy = opts.greedy && isFinite(qt.quantifier.max); //greedy must be off when * or + is used (always!!)
                                for (var qndx = (ndxInitializer.length > 0 && quantifierRecurse !== true) ? ndxInitializer.shift() : 0; (qndx < (isNaN(qt.quantifier.max) ? qndx + 1 : qt.quantifier.max)) && testPos <= pos; qndx++) {
                                    var tokenGroup = maskToken.matches[$.inArray(qt, maskToken.matches) - 1];
                                    match = handleMatch(tokenGroup, [qndx].concat(loopNdx), true);
                                    if (match) {
                                        //get latest match
                                        var latestMatch = matches[matches.length - 1]["match"];
                                        latestMatch.optionalQuantifier = qndx > qt.quantifier.min - 1;
                                        var isFirstMatch = $.inArray(latestMatch, tokenGroup.matches) == 0;

                                        if (isFirstMatch) { //search for next possible match
                                            if (qndx > qt.quantifier.min - 1) {
                                                insertStop = true;
                                                testPos = pos; //match the position after the group
                                                break; //stop quantifierloop
                                            } else return true;
                                        } else {
                                            return true;
                                        }
                                    }
                                }
                            } else {
                                match = ResolveTestFromToken(match, ndxInitializer, loopNdx, quantifierRecurse);
                                if (match)
                                    return true;
                            }
                        } else testPos++;
                    }

                    for (var tndx = (ndxInitializer.length > 0 ? ndxInitializer.shift() : 0) ; tndx < maskToken.matches.length; tndx++) {
                        if (maskToken.matches[tndx]["isQuantifier"] !== true) {
                            var match = handleMatch(maskToken.matches[tndx], [tndx].concat(loopNdx), quantifierRecurse);
                            if (match && testPos == pos) {
                                return match;
                            } else if (testPos > pos) {
                                break;
                            }
                        }
                    }
                }

                //if (disableCache !== true && getMaskSet()['tests'][pos] && !getMaskSet()['validPositions'][pos]) {
                //    return getMaskSet()['tests'][pos];
                //}
                if (ndxIntlzr == undefined) {
                    var previousPos = pos - 1, test;
                    while ((test = getMaskSet()['validPositions'][previousPos]) == undefined && previousPos > -1) {
                        previousPos--;
                    }
                    if (test != undefined && previousPos > -1) {
                        testPos = previousPos;
                        ndxInitializer = test["locator"].slice();
                    } else {
                        previousPos = pos - 1;
                        while ((test = getMaskSet()['tests'][previousPos]) == undefined && previousPos > -1) {
                            previousPos--;
                        }
                        if (test != undefined && previousPos > -1) {
                            testPos = previousPos;
                            ndxInitializer = test[0]["locator"].slice();
                        }
                    }
                }
                for (var mtndx = ndxInitializer.shift() ; mtndx < maskTokens.length; mtndx++) {
                    var match = ResolveTestFromToken(maskTokens[mtndx], ndxInitializer, [mtndx]);
                    if ((match && testPos == pos) || testPos > pos) {
                        break;
                    }
                }
                if (matches.length == 0 || insertStop)
                    matches.push({ "match": { fn: null, cardinality: 0, optionality: true, casing: null, def: "" }, "locator": [] });

                getMaskSet()['tests'][pos] = matches;
                //console.log(pos + " - " + JSON.stringify(matches));
                return matches;
            }

            function getBufferTemplate() {
                if (getMaskSet()['_buffer'] == undefined) {
                    //generate template
                    getMaskSet()["_buffer"] = getMaskTemplate(false, 1);
                }
                return getMaskSet()['_buffer'];
            }

            function getBuffer() {
                if (getMaskSet()['buffer'] == undefined) {
                    getMaskSet()['buffer'] = getMaskTemplate(true, getLastValidPosition(), true);
                }
                return getMaskSet()['buffer'];
            }

            function refreshFromBuffer(start, end) {
                var buffer = getBuffer().slice(); //work on clone
                if (start === true) {
                    resetMaskSet();
                    start = 0;
                    end = buffer.length;
                } else {
                    for (var i = start; i < end; i++) {
                        delete getMaskSet()["validPositions"][i];
                        delete getMaskSet()["tests"][i];
                    }
                }

                for (var i = start; i < end; i++) {
                    if (buffer[i] != opts.skipOptionalPartCharacter) {
                        isValid(i, buffer[i], true, true);
                    }
                }
            }

            function casing(elem, test) {
                switch (test.casing) {
                    case "upper":
                        elem = elem.toUpperCase();
                        break;
                    case "lower":
                        elem = elem.toLowerCase();
                        break;
                }

                return elem;
            }

            function isValid(pos, c, strict, fromSetValid) { //strict true ~ no correction or autofill
                strict = strict === true; //always set a value to strict to prevent possible strange behavior in the extensions 

                function _isValid(position, c, strict, fromSetValid) {
                    var rslt = false;
                    $.each(getTests(position), function (ndx, tst) {
                        var test = tst["match"];
                        var loopend = c ? 1 : 0, chrs = '', buffer = getBuffer();
                        for (var i = test.cardinality; i > loopend; i--) {
                            chrs += getBufferElement(position - (i - 1));
                        }
                        if (c) {
                            chrs += c;
                        }

                        //return is false or a json object => { pos: ??, c: ??} or true
                        rslt = test.fn != null ?
                            test.fn.test(chrs, getMaskSet(), position, strict, opts)
                            : (c == test["def"] || c == opts.skipOptionalPartCharacter) && test["def"] != "" ? //non mask
                            { c: test["def"], pos: position }
                            : false;

                        if (rslt !== false) {
                            var elem = rslt.c != undefined ? rslt.c : c;
                            elem = (elem == opts.skipOptionalPartCharacter && test["fn"] === null) ? test["def"] : elem;

                            var validatedPos = position;
                            if (rslt["remove"] != undefined) { //remove position
                                stripValidPositions(rslt["remove"], rslt["remove"] + 1);
                            }

                            if (rslt["refreshFromBuffer"]) {
                                var refresh = rslt["refreshFromBuffer"];
                                strict = true;
                                refreshFromBuffer(refresh === true ? refresh : refresh["start"], refresh["end"]);
                                if (rslt.pos == undefined && rslt.c == undefined) {
                                    rslt.pos = getLastValidPosition();
                                    return false;//breakout if refreshFromBuffer && nothing to insert
                                }
                                validatedPos = rslt.pos != undefined ? rslt.pos : position;
                                if (validatedPos != position) {
                                    rslt = $.extend(rslt, isValid(validatedPos, elem, true)); //revalidate new position strict
                                    return false;
                                }

                            } else if (rslt !== true && rslt.pos != undefined && rslt["pos"] != position) { //their is a position offset
                                validatedPos = rslt["pos"];
                                refreshFromBuffer(position, validatedPos);
                                if (validatedPos != position) {
                                    rslt = $.extend(rslt, isValid(validatedPos, elem, true)); //revalidate new position strict
                                    return false;
                                }
                            }

                            if (rslt != true && rslt.pos == undefined && rslt.c == undefined) {
                                return false; //breakout if nothing to insert
                            }

                            if (ndx > 0) {
                                resetMaskSet(true);
                            }

                            if (!setValidPosition(validatedPos, $.extend({}, tst, { "input": casing(elem, test) }), fromSetValid))
                                rslt = false;
                            return false; //break from $.each
                        }
                    });

                    return rslt;
                }

                //Check for a nonmask before the pos
                var buffer = getBuffer();
                for (var pndx = pos - 1; pndx > -1; pndx--) {
                    if (getMaskSet()["validPositions"][pndx] && getMaskSet()["validPositions"][pndx].fn == null)
                        break;
                    else if ((!isMask(pndx) || buffer[pndx] != getPlaceholder(pndx)) && getTests(pndx).length > 1) {
                        _isValid(pndx, buffer[pndx], true);
                        break;
                    }
                }

                var maskPos = pos;
                if (maskPos >= getMaskLength()) {
                   // console.log("try alternate match");
                    return false;
                }
                var result = _isValid(maskPos, c, strict, fromSetValid);
                if (!strict && result === false) {
                    var currentPosValid = getMaskSet()["validPositions"][maskPos];
                    if (currentPosValid && currentPosValid["match"].fn == null && (currentPosValid["match"].def == c || c == opts.skipOptionalPartCharacter)) {
                        result = { "caret": seekNext(maskPos) };
                    } else if ((opts.insertMode || getMaskSet()["validPositions"][seekNext(maskPos)] == undefined) && !isMask(maskPos)) { //does the input match on a further position?
                        for (var nPos = maskPos + 1, snPos = seekNext(maskPos) ; nPos <= snPos; nPos++) {
                            result = _isValid(nPos, c, strict, fromSetValid);
                            if (result !== false) {
                                maskPos = nPos;
                                break;
                            }
                        }
                    }
                }

                if (result === true) result = { "pos": maskPos };
                return result;
            }

            function isMask(pos) {
                var test = getTest(pos);
                return test.fn != null ? test.fn : false;
            }

            function getMaskLength() {
                var maskLength;
                maxLength = $el.prop('maxLength');
                if (maxLength == -1) maxLength = undefined; /* FF sets no defined max length to -1 */
                if (opts.greedy == false) {
                    var pos, lvp = getLastValidPosition(), testPos = getMaskSet()["validPositions"][lvp],
                        ndxIntlzr = testPos != undefined ? testPos["locator"].slice() : undefined;
                    for (pos = lvp + 1; testPos == undefined || (testPos["match"]["fn"] != null || (testPos["match"]["fn"] == null && testPos["match"]["def"] != "")) ; pos++) {
                        testPos = getTestTemplate(pos, ndxIntlzr, pos - 1);
                        ndxIntlzr = testPos["locator"].slice();
                    }
                    maskLength = pos;
                } else
                    maskLength = getBuffer().length;

                return (maxLength == undefined || maskLength < maxLength) ? maskLength : maxLength;
            }

            function seekNext(pos) {
                var maskL = getMaskLength();
                if (pos >= maskL) return maskL;
                var position = pos;
                while (++position < maskL && !isMask(position) && (opts.nojumps !== true || opts.nojumpsThreshold > position)) {
                }
                return position;
            }

            function seekPrevious(pos) {
                var position = pos;
                if (position <= 0) return 0;

                while (--position > 0 && !isMask(position)) {
                };
                return position;
            }

            function getBufferElement(position) {
                return getMaskSet()["validPositions"][position] == undefined ? getPlaceholder(position) : getMaskSet()["validPositions"][position]["input"];
            }

            function writeBuffer(input, buffer, caretPos) {
                input._valueSet(buffer.join(''));
                if (caretPos != undefined) {
                    caret(input, caretPos);
                }
            }

            function getPlaceholder(pos, test) {
                test = test || getTest(pos);
                return test["placeholder"] || (test["fn"] == null ? test["def"] : opts.placeholder.charAt(pos % opts.placeholder.length));
            }

            function checkVal(input, writeOut, strict, nptvl, intelliCheck) {
                var inputValue = nptvl != undefined ? nptvl.slice() : truncateInput(input._valueGet()).split('');
                resetMaskSet();
                if (writeOut) input._valueSet(""); //initial clear
                $.each(inputValue, function (ndx, charCode) {
                    if (intelliCheck === true) {
                        var p = getMaskSet()["p"],
                            lvp = p == -1 ? p : seekPrevious(p),
                            pos = lvp == -1 ? ndx : seekNext(lvp);
                        if ($.inArray(charCode, getBufferTemplate().slice(lvp + 1, pos)) == -1) {
                            keypressEvent.call(input, undefined, true, charCode.charCodeAt(0), false, strict, ndx);
                        }
                    } else {
                        keypressEvent.call(input, undefined, true, charCode.charCodeAt(0), false, strict, ndx);
                        strict = strict || (ndx > 0 && ndx > getMaskSet()["p"]);
                    }
                });
                if (writeOut) {
                    var keypressResult = opts.onKeyPress.call(this, undefined, getBuffer(), 0, opts);
                    handleOnKeyResult(input, keypressResult);
                    writeBuffer(input, getBuffer(), $(input).is(":focus") ? seekNext(getLastValidPosition(0)) : undefined);
                }
            }

            function escapeRegex(str) {
                return $.inputmask.escapeRegex.call(this, str);
            }

            function truncateInput(inputValue) {
                return inputValue.replace(new RegExp("(" + escapeRegex(getBufferTemplate().join('')) + ")*$"), "");
            }
            function unmaskedvalue($input) {
                if ($input.data('_inputmask') && !$input.hasClass('hasDatepicker')) {
                    var umValue = [], vps = getMaskSet()["validPositions"];
                    for (var pndx in vps) {
                        if (vps[pndx]["match"] && vps[pndx]["match"].fn != null) {
                            umValue.push(vps[pndx]["input"]);
                        }
                    }
                    var unmaskedValue = (isRTL ? umValue.reverse() : umValue).join('');
                    var bufferValue = (isRTL ? getBuffer().reverse() : getBuffer()).join('');
                    if ($.isFunction(opts.onUnMask)) {
                        unmaskedValue = opts.onUnMask.call($input, bufferValue, unmaskedValue, opts);
                    }
                    return unmaskedValue;
                } else {
                    return $input[0]._valueGet();
                }
            }
            function TranslatePosition(pos) {
                if (isRTL && typeof pos == 'number' && (!opts.greedy || opts.placeholder != "")) {
                    var bffrLght = getBuffer().length;
                    pos = bffrLght - pos;
                }
                return pos;
            }
            function caret(input, begin, end) {
                var npt = input.jquery && input.length > 0 ? input[0] : input, range;
                if (typeof begin == 'number') {
                    begin = TranslatePosition(begin);
                    end = TranslatePosition(end);
                    end = (typeof end == 'number') ? end : begin;

                    //store caret for multi scope
                    var data = $(npt).data('_inputmask') || {};
                    data["caret"] = { "begin": begin, "end": end };
                    $(npt).data('_inputmask', data);

                    if (!$(npt).is(":visible")) {
                        return;
                    }

                    npt.scrollLeft = npt.scrollWidth;
                    if (opts.insertMode == false && begin == end) end++; //set visualization for insert/overwrite mode
                    if (npt.setSelectionRange) {
                        npt.selectionStart = begin;
                        npt.selectionEnd = end;

                    } else if (npt.createTextRange) {
                        range = npt.createTextRange();
                        range.collapse(true);
                        range.moveEnd('character', end);
                        range.moveStart('character', begin);
                        range.select();
                    }
                } else {
                    var data = $(npt).data('_inputmask');
                    if (!$(npt).is(":visible") && data && data["caret"] != undefined) {
                        begin = data["caret"]["begin"];
                        end = data["caret"]["end"];
                    } else if (npt.setSelectionRange) {
                        begin = npt.selectionStart;
                        end = npt.selectionEnd;
                    } else if (document.selection && document.selection.createRange) {
                        range = document.selection.createRange();
                        begin = 0 - range.duplicate().moveStart('character', -100000);
                        end = begin + range.text.length;
                    }
                    begin = TranslatePosition(begin);
                    end = TranslatePosition(end);
                    return { "begin": begin, "end": end };
                }
            }
            function determineLastRequiredPosition(returnDefinition) {
                var buffer = getBuffer(), bl = buffer.length,
                   pos, lvp = getLastValidPosition(), positions = {},
                   ndxIntlzr = getMaskSet()["validPositions"][lvp] != undefined ? getMaskSet()["validPositions"][lvp]["locator"].slice() : undefined, testPos;
                for (pos = lvp + 1; pos < buffer.length; pos++) {
                    testPos = getTestTemplate(pos, ndxIntlzr, pos - 1);
                    ndxIntlzr = testPos["locator"].slice();
                    positions[pos] = $.extend(true, {}, testPos);
                }

                for (pos = bl - 1; pos > lvp; pos--) {
                    testPos = positions[pos]["match"];
                    if ((testPos.optionality || testPos.optionalQuantifier) && buffer[pos] == getPlaceholder(pos, testPos)) {
                        bl--;
                    } else break;
                }
                return returnDefinition ? { "l": bl, "def": positions[bl] ? positions[bl]["match"] : undefined } : bl;
            }
            function clearOptionalTail(input) {
                var buffer = getBuffer(), tmpBuffer = buffer.slice();
                var rl = determineLastRequiredPosition();
                tmpBuffer.length = rl;
                writeBuffer(input, tmpBuffer);
            }
            function isComplete(buffer) { //return true / false / undefined (repeat *)
                if ($.isFunction(opts.isComplete)) return opts.isComplete.call($el, buffer, opts);
                if (opts.repeat == "*") return undefined;
                var complete = false, lrp = determineLastRequiredPosition(true), aml = seekPrevious(lrp["l"]), lvp = getLastValidPosition();

                if (lvp == aml) {
                    if (lrp["def"] == undefined || lrp["def"].newBlockMarker || lrp["def"].optionalQuantifier) {
                        complete = true;
                        for (var i = 0; i <= aml; i++) {
                            var mask = isMask(i);
                            if ((mask && (buffer[i] == undefined || buffer[i] == getPlaceholder(i))) || (!mask && buffer[i] != getPlaceholder(i))) {
                                complete = false;
                                break;
                            }
                        }
                    }
                }
                return complete;
            }

            function isSelection(begin, end) {
                return isRTL ? (begin - end) > 1 || ((begin - end) == 1 && opts.insertMode) :
                (end - begin) > 1 || ((end - begin) == 1 && opts.insertMode);
            }

            function installEventRuler(npt) {
                var events = $._data(npt).events;

                $.each(events, function (eventType, eventHandlers) {
                    $.each(eventHandlers, function (ndx, eventHandler) {
                        if (eventHandler.namespace == "inputmask") {
                            if (eventHandler.type != "setvalue") {
                                var handler = eventHandler.handler;
                                eventHandler.handler = function (e) {
                                    if (this.readOnly || this.disabled)
                                        e.preventDefault;
                                    else
                                        return handler.apply(this, arguments);
                                };
                            }
                        }
                    });
                });
            }

            function patchValueProperty(npt) {

                function PatchValhook(type) {
                    if ($.valHooks[type] == undefined || $.valHooks[type].inputmaskpatch != true) {
                        var valueGet = $.valHooks[type] && $.valHooks[type].get ? $.valHooks[type].get : function (elem) { return elem.value; };
                        var valueSet = $.valHooks[type] && $.valHooks[type].set ? $.valHooks[type].set : function (elem, value) {
                            elem.value = value;
                            return elem;
                        };

                        $.valHooks[type] = {
                            get: function (elem) {
                                var $elem = $(elem);
                                if ($elem.data('_inputmask')) {
                                    if ($elem.data('_inputmask')['opts'].autoUnmask)
                                        return $elem.inputmask('unmaskedvalue');
                                    else {
                                        var result = valueGet(elem),
                                            inputData = $elem.data('_inputmask'),
                                            maskset = inputData['maskset'],
                                            bufferTemplate = maskset['_buffer'];
                                        bufferTemplate = bufferTemplate ? bufferTemplate.join('') : '';
                                        return result != bufferTemplate ? result : '';
                                    }
                                } else return valueGet(elem);
                            },
                            set: function (elem, value) {
                                var $elem = $(elem), inputData = $elem.data('_inputmask'), result;
                                if (inputData) {
                                    result = valueSet(elem, $.isFunction(inputData['opts'].onBeforeMask) ? inputData['opts'].onBeforeMask.call(el, value, inputData['opts']) : value);
                                    $elem.triggerHandler('setvalue.inputmask');
                                } else {
                                    result = valueSet(elem, value);
                                }
                                return result;
                            },
                            inputmaskpatch: true
                        };
                    }
                }

                var valueProperty;
                if (Object.getOwnPropertyDescriptor)
                    valueProperty = Object.getOwnPropertyDescriptor(npt, "value");
                if (valueProperty && valueProperty.get) {
                    if (!npt._valueGet) {
                        var valueGet = valueProperty.get;
                        var valueSet = valueProperty.set;
                        npt._valueGet = function () {
                            return isRTL ? valueGet.call(this).split('').reverse().join('') : valueGet.call(this);
                        };
                        npt._valueSet = function (value) {
                            valueSet.call(this, isRTL ? value.split('').reverse().join('') : value);
                        };

                        Object.defineProperty(npt, "value", {
                            get: function () {
                                var $self = $(this), inputData = $(this).data('_inputmask');
                                if (inputData) {
                                    return inputData['opts'].autoUnmask ? $self.inputmask('unmaskedvalue') : (valueGet.call(this) != getBufferTemplate().join('') ? valueGet.call(this) : '');
                                } else return valueGet.call(this);
                            },
                            set: function (value) {
                                var inputData = $(this).data('_inputmask');
                                if (inputData) {
                                    valueSet.call(this, $.isFunction(inputData['opts'].onBeforeMask) ? inputData['opts'].onBeforeMask.call(el, value, inputData['opts']) : value);
                                    $(this).triggerHandler('setvalue.inputmask');
                                } else {
                                    valueSet.call(this, value);
                                }
                            }
                        });
                    }
                } else if (document.__lookupGetter__ && npt.__lookupGetter__("value")) {
                    if (!npt._valueGet) {
                        var valueGet = npt.__lookupGetter__("value");
                        var valueSet = npt.__lookupSetter__("value");
                        npt._valueGet = function () {
                            return isRTL ? valueGet.call(this).split('').reverse().join('') : valueGet.call(this);
                        };
                        npt._valueSet = function (value) {
                            valueSet.call(this, isRTL ? value.split('').reverse().join('') : value);
                        };

                        npt.__defineGetter__("value", function () {
                            var $self = $(this), inputData = $(this).data('_inputmask');
                            if (inputData) {
                                return inputData['opts'].autoUnmask ? $self.inputmask('unmaskedvalue') : (valueGet.call(this) != getBufferTemplate().join('') ? valueGet.call(this) : '');
                            } else return valueGet.call(this);
                        });
                        npt.__defineSetter__("value", function (value) {
                            var inputData = $(this).data('_inputmask');
                            if (inputData) {
                                valueSet.call(this, $.isFunction(inputData['opts'].onBeforeMask) ? inputData['opts'].onBeforeMask.call(el, value, inputData['opts']) : value);
                                $(this).triggerHandler('setvalue.inputmask');
                            } else {
                                valueSet.call(this, value);
                            }
                        });
                    }
                } else {
                    if (!npt._valueGet) {
                        npt._valueGet = function () { return isRTL ? this.value.split('').reverse().join('') : this.value; };
                        npt._valueSet = function (value) { this.value = isRTL ? value.split('').reverse().join('') : value; };
                    }
                    PatchValhook(npt.type);
                }
            }

            function handleRemove(input, k, pos) {
                if (opts.numericInput || isRTL) {
                    if (k == opts.keyCode.BACKSPACE)
                        k = opts.keyCode.DELETE;
                    else if (k == opts.keyCode.DELETE)
                        k = opts.keyCode.BACKSPACE;

                    if (isRTL) {
                        var pend = pos.end;
                        pos.end = pos.begin;
                        pos.begin = pend;
                    }
                }

                if (k == opts.keyCode.BACKSPACE && pos.end - pos.begin <= 1)
                    pos.begin = seekPrevious(pos.begin);
                else if (k == opts.keyCode.DELETE && pos.begin == pos.end)
                    pos.end++;

                stripValidPositions(pos.begin, pos.end);
                var firstMaskedPos = getLastValidPosition(pos.begin);
                if (firstMaskedPos < pos.begin) {
                    getMaskSet()["p"] = seekNext(firstMaskedPos);
                } else {
                    getMaskSet()["p"] = pos.begin;
                }
            }

            function handleOnKeyResult(input, keyResult, caretPos) {
                if (keyResult && keyResult["refreshFromBuffer"]) {
                    var refresh = keyResult["refreshFromBuffer"];
                    refreshFromBuffer(refresh === true ? refresh : refresh["start"], refresh["end"]);

                    resetMaskSet(true);
                    if (caretPos != undefined) {
                        writeBuffer(input, getBuffer());
                        caret(input, keyResult.caret || caretPos.begin, keyResult.caret || caretPos.end);
                    }
                }
            }

            function keydownEvent(e) {
                //Safari 5.1.x - modal dialog fires keypress twice workaround
                skipKeyPressEvent = false;
                var input = this, $input = $(input), k = e.keyCode, pos = caret(input);

                //backspace, delete, and escape get special treatment
                if (k == opts.keyCode.BACKSPACE || k == opts.keyCode.DELETE || (iphone && k == 127) || e.ctrlKey && k == 88) { //backspace/delete
                    e.preventDefault(); //stop default action but allow propagation
                    if (k == 88) valueOnFocus = getBuffer().join('');
                    handleRemove(input, k, pos);
                    writeBuffer(input, getBuffer(), getMaskSet()["p"]);
                    if (input._valueGet() == getBufferTemplate().join(''))
                        $input.trigger('cleared');

                    if (opts.showTooltip) { //update tooltip
                        $input.prop("title", getMaskSet()["mask"]);
                    }
                } else if (k == opts.keyCode.END || k == opts.keyCode.PAGE_DOWN) { //when END or PAGE_DOWN pressed set position at lastmatch
                    setTimeout(function () {
                        var caretPos = seekNext(getLastValidPosition());
                        if (!opts.insertMode && caretPos == getMaskLength() && !e.shiftKey) caretPos--;
                        caret(input, e.shiftKey ? pos.begin : caretPos, caretPos);
                    }, 0);
                } else if ((k == opts.keyCode.HOME && !e.shiftKey) || k == opts.keyCode.PAGE_UP) { //Home or page_up
                    caret(input, 0, e.shiftKey ? pos.begin : 0);
                } else if (k == opts.keyCode.ESCAPE || (k == 90 && e.ctrlKey)) { //escape && undo
                    checkVal(input, true, false, valueOnFocus.split(''));
                    $input.click();
                } else if (k == opts.keyCode.INSERT && !(e.shiftKey || e.ctrlKey)) { //insert
                    opts.insertMode = !opts.insertMode;
                    caret(input, !opts.insertMode && pos.begin == getMaskLength() ? pos.begin - 1 : pos.begin);
                } else if (opts.insertMode == false && !e.shiftKey) {
                    if (k == opts.keyCode.RIGHT) {
                        setTimeout(function () {
                            var caretPos = caret(input);
                            caret(input, caretPos.begin);
                        }, 0);
                    } else if (k == opts.keyCode.LEFT) {
                        setTimeout(function () {
                            var caretPos = caret(input);
                            caret(input, isRTL ? caretPos.begin + 1 : caretPos.begin - 1);
                        }, 0);
                    }
                }

                var currentCaretPos = caret(input);
                var keydownResult = opts.onKeyDown.call(this, e, getBuffer(), currentCaretPos.begin, opts);
                handleOnKeyResult(input, keydownResult, currentCaretPos);
                ignorable = $.inArray(k, opts.ignorables) != -1;
            }

            function keypressEvent(e, checkval, k, writeOut, strict, ndx) {
                //Safari 5.1.x - modal dialog fires keypress twice workaround
                if (k == undefined && skipKeyPressEvent) return false;
                skipKeyPressEvent = true;

                var input = this, $input = $(input);

                e = e || window.event;
                var k = checkval ? k : (e.which || e.charCode || e.keyCode);

                if (checkval !== true && (!(e.ctrlKey && e.altKey) && (e.ctrlKey || e.metaKey || ignorable))) {
                    return true;
                } else {
                    if (k) {
                        //special treat the decimal separator
                        if (checkval !== true && k == 46 && e.shiftKey == false && opts.radixPoint == ",") k = 44;

                        var pos, forwardPosition, c = String.fromCharCode(k);
                        if (checkval) {
                            var pcaret = strict ? ndx : getLastValidPosition() + 1;
                            pos = { begin: pcaret, end: pcaret };
                        } else {
                            pos = caret(input);
                        }

                        //should we clear a possible selection??
                        var isSlctn = isSelection(pos.begin, pos.end);
                        if (isSlctn) {
                            getMaskSet()["undoPositions"] = $.extend(true, {}, getMaskSet()["validPositions"]); //init undobuffer for recovery when not valid
                            handleRemove(input, opts.keyCode.DELETE, pos);
                            if (!opts.insertMode) { //preserve some space
                                opts.insertMode = !opts.insertMode;
                                setValidPosition(pos.begin, strict);
                                opts.insertMode = !opts.insertMode;
                            }
                            isSlctn = !opts.multi;
                        }

                        getMaskSet()["writeOutBuffer"] = true;
                        var p = isRTL && !isSlctn ? pos.end : pos.begin;
                        var valResult = isValid(p, c, strict);
                        if (valResult !== false) {
                            if (valResult !== true) {
                                p = valResult.pos != undefined ? valResult.pos : p; //set new position from isValid
                                c = valResult.c != undefined ? valResult.c : c; //set new char from isValid
                            }
                            resetMaskSet(true);
                            if (valResult.caret != undefined)
                                forwardPosition = valResult.caret;
                            else {
                                var vps = getMaskSet()["validPositions"];
                                if (vps[p + 1] != undefined && getTests(p + 1, vps[p].locator.slice(), p).length > 1)
                                    forwardPosition = p + 1;
                                else
                                    forwardPosition = seekNext(p);
                            }
                            getMaskSet()["p"] = forwardPosition; //needed for checkval
                        }

                        if (writeOut !== false) {
                            var self = this;
                            setTimeout(function () { opts.onKeyValidation.call(self, valResult, opts); }, 0);
                            if (getMaskSet()["writeOutBuffer"] && valResult !== false) {
                                var buffer = getBuffer();
                                writeBuffer(input, buffer, checkval ? undefined : opts.numericInput ? seekPrevious(forwardPosition) : forwardPosition);
                                if (checkval !== true) {
                                    setTimeout(function () { //timeout needed for IE
                                        if (isComplete(buffer) === true)
                                            $input.trigger("complete");
                                        skipInputEvent = true;
                                        $input.trigger("input");
                                    }, 0);
                                }
                            } else if (isSlctn) {
                                getMaskSet()["buffer"] = undefined;
                                getMaskSet()["validPositions"] = getMaskSet()["undoPositions"];
                            }
                        } else if (isSlctn) {
                            getMaskSet()["buffer"] = undefined;
                            getMaskSet()["validPositions"] = getMaskSet()["undoPositions"];
                        }


                        if (opts.showTooltip) { //update tooltip
                            $input.prop("title", getMaskSet()["mask"]);
                        }

                        //needed for IE8 and below
                        if (e && checkval != true) {
                            e.preventDefault ? e.preventDefault() : e.returnValue = false;

                            var currentCaretPos = caret(input);
                            var keypressResult = opts.onKeyPress.call(this, e, getBuffer(), currentCaretPos.begin, opts);
                            handleOnKeyResult(input, keypressResult, currentCaretPos);
                        }
                        var temp;
                        for (var i in getMaskSet().validPositions) {
                            temp += " " + i;
                        }
                    }
                }
            }
            function keyupEvent(e) {
                var $input = $(this), input = this, k = e.keyCode, buffer = getBuffer();

                var currentCaretPos = caret(input);
                var keyupResult = opts.onKeyUp.call(this, e, buffer, currentCaretPos.begin, opts);
                handleOnKeyResult(input, keyupResult, currentCaretPos);
                if (k == opts.keyCode.TAB && opts.showMaskOnFocus) {
                    if ($input.hasClass('focus-inputmask') && input._valueGet().length == 0) {
                        resetMaskSet();
                        buffer = getBuffer();
                        writeBuffer(input, buffer);
                        caret(input, 0);
                        valueOnFocus = getBuffer().join('');
                    } else {
                        writeBuffer(input, buffer);
                        caret(input, TranslatePosition(0), TranslatePosition(getMaskLength()));
                    }
                }
            }
            function pasteEvent(e) {
                if (skipInputEvent === true && e.type == "input") {
                    skipInputEvent = false;
                    return true;
                }

                var input = this, $input = $(input), inputValue = input._valueGet();
                //paste event for IE8 and lower I guess ;-)
                if (e.type == "propertychange" && input._valueGet().length <= getMaskLength()) {
                    return true;
                } else if (e.type == "paste") {
                    if (window.clipboardData && window.clipboardData.getData) { // IE
                        inputValue = window.clipboardData.getData('Text');
                    } else if (e.originalEvent && e.originalEvent.clipboardData && e.originalEvent.clipboardData.getData) {
                        inputValue = e.originalEvent.clipboardData.getData('text/plain');
                    }
                }

                var pasteValue = $.isFunction(opts.onBeforePaste) ? opts.onBeforePaste.call(input, inputValue, opts) : inputValue;
                checkVal(input, true, false, pasteValue.split(''), true);
                $input.click();
                if (isComplete(getBuffer()) === true)
                    $input.trigger("complete");

                return false;
            }
            function mobileInputEvent(e) {
                if (skipInputEvent === true && e.type == "input") {
                    skipInputEvent = false;
                    return true;
                }
                var input = this;

                //backspace in chrome32 only fires input event - detect & treat
                var caretPos = caret(input),
                    currentValue = input._valueGet();

                currentValue = currentValue.replace(new RegExp("(" + escapeRegex(getBufferTemplate().join('')) + ")*"), "");
                //correct caretposition for chrome
                if (caretPos.begin > currentValue.length) {
                    caret(input, currentValue.length);
                    caretPos = caret(input);
                }
                if ((getBuffer().length - currentValue.length) == 1 && currentValue.charAt(caretPos.begin) != getBuffer()[caretPos.begin]
                    && currentValue.charAt(caretPos.begin + 1) != getBuffer()[caretPos.begin]
                    && !isMask(caretPos.begin)) {
                    e.keyCode = opts.keyCode.BACKSPACE;
                    keydownEvent.call(input, e);
                }
                e.preventDefault();
            }

            function mask(el) {
                $el = $(el);
                if ($el.is(":input") && $el.attr("type") != "number") {
                    //store tests & original buffer in the input element - used to get the unmasked value
                    $el.data('_inputmask', {
                        'maskset': maskset,
                        'opts': opts,
                        'isRTL': false
                    });

                    //show tooltip
                    if (opts.showTooltip) {
                        $el.prop("title", getMaskSet()["mask"]);
                    }

                    patchValueProperty(el);

                    if (el.dir == "rtl" || opts.rightAlign)
                        $el.css("text-align", "right");

                    if (el.dir == "rtl" || opts.numericInput) {
                        el.dir = "ltr";
                        $el.removeAttr("dir");
                        var inputData = $el.data('_inputmask');
                        inputData['isRTL'] = true;
                        $el.data('_inputmask', inputData);
                        isRTL = true;
                    }

                    //unbind all events - to make sure that no other mask will interfere when re-masking
                    $el.unbind(".inputmask");
                    $el.removeClass('focus-inputmask');
                    //bind events
                    $el.closest('form').bind("submit", function () { //trigger change on submit if any
                        if (valueOnFocus != getBuffer().join('')) {
                            $el.change();
                        }
                        if (opts.autoUnmask && opts.removeMaskOnSubmit) {
                            $el.inputmask("remove");
                        }
                    }).bind('reset', function () {
                        setTimeout(function () {
                            $el.trigger("setvalue");
                        }, 0);
                    });
                    $el.bind("mouseenter.inputmask", function () {
                        var $input = $(this), input = this;
                        if (!$input.hasClass('focus-inputmask') && opts.showMaskOnHover) {
                            if (input._valueGet() != getBuffer().join('')) {
                                writeBuffer(input, getBuffer());
                            }
                        }
                    }).bind("blur.inputmask", function () {
                        var $input = $(this), input = this;
                        if ($input.data('_inputmask')) {
                            var nptValue = input._valueGet(), buffer = getBuffer();
                            $input.removeClass('focus-inputmask');
                            if (valueOnFocus != getBuffer().join('')) {
                                $input.change();
                            }
                            if (opts.clearMaskOnLostFocus && nptValue != '') {
                                if (nptValue == getBufferTemplate().join(''))
                                    input._valueSet('');
                                else { //clearout optional tail of the mask
                                    clearOptionalTail(input);
                                }
                            }
                            if (isComplete(buffer) === false) {
                                $input.trigger("incomplete");
                                if (opts.clearIncomplete) {
                                    resetMaskSet();
                                    if (opts.clearMaskOnLostFocus)
                                        input._valueSet('');
                                    else {
                                        buffer = getBufferTemplate().slice();
                                        writeBuffer(input, buffer);
                                    }
                                }
                            }
                        }
                    }).bind("focus.inputmask", function () {
                        var $input = $(this), input = this, nptValue = input._valueGet();
                        if (opts.showMaskOnFocus && !$input.hasClass('focus-inputmask') && (!opts.showMaskOnHover || (opts.showMaskOnHover && nptValue == ''))) {
                            if (input._valueGet() != getBuffer().join('')) {
                                writeBuffer(input, getBuffer(), seekNext(getLastValidPosition()));
                            }
                        }
                        $input.addClass('focus-inputmask');
                        valueOnFocus = getBuffer().join('');
                    }).bind("mouseleave.inputmask", function () {
                        var $input = $(this), input = this;
                        if (opts.clearMaskOnLostFocus) {
                            if (!$input.hasClass('focus-inputmask') && input._valueGet() != $input.attr("placeholder")) {
                                if (input._valueGet() == getBufferTemplate().join('') || input._valueGet() == '')
                                    input._valueSet('');
                                else { //clearout optional tail of the mask
                                    clearOptionalTail(input);
                                }
                            }
                        }
                    }).bind("click.inputmask", function () {
                        var input = this;
                        if ($(input).is(":focus")) {
                            setTimeout(function () {
                                var selectedCaret = caret(input);
                                if (selectedCaret.begin == selectedCaret.end) {
                                    var clickPosition = isRTL ? TranslatePosition(selectedCaret.begin) : selectedCaret.begin,
                                        lvp = getLastValidPosition(clickPosition),
                                        lastPosition = seekNext(lvp);
                                    if (clickPosition < lastPosition) {
                                        if (isMask(clickPosition))
                                            caret(input, clickPosition);
                                        else caret(input, seekNext(clickPosition));
                                    } else
                                        caret(input, lastPosition);
                                }
                            }, 0);
                        }
                    }).bind('dblclick.inputmask', function () {
                        var input = this;
                        setTimeout(function () {
                            caret(input, 0, seekNext(getLastValidPosition()));
                        }, 0);
                    }).bind(PasteEventType + ".inputmask dragdrop.inputmask drop.inputmask", pasteEvent
                    ).bind('setvalue.inputmask', function () {
                        var input = this;
                        checkVal(input, true);
                        valueOnFocus = getBuffer().join('');
                    }).bind('complete.inputmask', opts.oncomplete
                    ).bind('incomplete.inputmask', opts.onincomplete
                    ).bind('cleared.inputmask', opts.oncleared);

                    $el.bind("keydown.inputmask", keydownEvent
                    ).bind("keypress.inputmask", keypressEvent
                    ).bind("keyup.inputmask", keyupEvent);

                    if (android || androidfirefox || androidchrome || kindle) {
                        if (PasteEventType == "input") {
                            $el.unbind(PasteEventType + ".inputmask");
                        }
                        $el.bind("input.inputmask", mobileInputEvent);
                    }

                    if (msie1x)
                        $el.bind("input.inputmask", pasteEvent);

                    //apply mask
                    var initialValue = $.isFunction(opts.onBeforeMask) ? opts.onBeforeMask.call(el, el._valueGet(), opts) : el._valueGet();
                    checkVal(el, true, false, initialValue.split(''), true);
                    valueOnFocus = getBuffer().join('');
                    // Wrap document.activeElement in a try/catch block since IE9 throw "Unspecified error" if document.activeElement is undefined when we are in an IFrame.
                    var activeElement;
                    try {
                        activeElement = document.activeElement;
                    } catch (e) {
                    }
                    if (isComplete(getBuffer()) === false) {
                        if (opts.clearIncomplete)
                            resetMaskSet();
                    }
                    if (opts.clearMaskOnLostFocus) {
                        if (getBuffer().join('') == getBufferTemplate().join('')) {
                            el._valueSet('');
                        } else {
                            clearOptionalTail(el);
                        }
                    } else {
                        writeBuffer(el, getBuffer());
                    }
                    if (activeElement === el) { //position the caret when in focus
                        $el.addClass('focus-inputmask');
                        caret(el, seekNext(getLastValidPosition()));
                    }

                    installEventRuler(el);
                }
            }

            //action object
            if (actionObj != undefined) {
                switch (actionObj["action"]) {
                    case "isComplete":
                        $el = $(actionObj["el"]);
                        maskset = $el.data('_inputmask')['maskset'];
                        opts = $el.data('_inputmask')['opts'];
                        return isComplete(actionObj["buffer"]);
                    case "unmaskedvalue":
                        $el = actionObj["$input"];
                        maskset = $el.data('_inputmask')['maskset'];
                        opts = $el.data('_inputmask')['opts'];
                        isRTL = actionObj["$input"].data('_inputmask')['isRTL'];
                        return unmaskedvalue(actionObj["$input"]);
                    case "mask":
                        valueOnFocus = getBuffer().join('');
                        mask(actionObj["el"]);
                        break;
                    case "format":
                        $el = $({});
                        $el.data('_inputmask', {
                            'maskset': maskset,
                            'opts': opts,
                            'isRTL': opts.numericInput
                        });
                        if (opts.numericInput) {
                            isRTL = true;
                        }
                        var valueBuffer = actionObj["value"].split('');
                        checkVal($el, false, false, isRTL ? valueBuffer.reverse() : valueBuffer, true);
                        return isRTL ? getBuffer().reverse().join('') : getBuffer().join('');
                    case "isValid":
                        $el = $({});
                        $el.data('_inputmask', {
                            'maskset': maskset,
                            'opts': opts,
                            'isRTL': opts.numericInput
                        });
                        if (opts.numericInput) {
                            isRTL = true;
                        }
                        var valueBuffer = actionObj["value"].split('');
                        checkVal($el, false, true, isRTL ? valueBuffer.reverse() : valueBuffer);
                        var buffer = getBuffer();
                        var rl = determineLastRequiredPosition();
                        buffer.length = rl;

                        return isComplete(buffer) && actionObj["value"] == buffer.join('');
                    case "getemptymask":
                        $el = $(actionObj["el"]);
                        maskset = $el.data('_inputmask')['maskset'];
                        opts = $el.data('_inputmask')['opts'];
                        return getBufferTemplate();
                    case "remove":
                        var el = actionObj["el"];
                        $el = $(el);
                        maskset = $el.data('_inputmask')['maskset'];
                        opts = $el.data('_inputmask')['opts'];
                        //writeout the unmaskedvalue
                        el._valueSet(unmaskedvalue($el));
                        //unbind all events
                        $el.unbind(".inputmask");
                        $el.removeClass('focus-inputmask');
                        //clear data
                        $el.removeData('_inputmask');
                        //restore the value property
                        var valueProperty;
                        if (Object.getOwnPropertyDescriptor)
                            valueProperty = Object.getOwnPropertyDescriptor(el, "value");
                        if (valueProperty && valueProperty.get) {
                            if (el._valueGet) {
                                Object.defineProperty(el, "value", {
                                    get: el._valueGet,
                                    set: el._valueSet
                                });
                            }
                        } else if (document.__lookupGetter__ && el.__lookupGetter__("value")) {
                            if (el._valueGet) {
                                el.__defineGetter__("value", el._valueGet);
                                el.__defineSetter__("value", el._valueSet);
                            }
                        }
                        try { //try catch needed for IE7 as it does not supports deleting fns
                            delete el._valueGet;
                            delete el._valueSet;
                        } catch (e) {
                            el._valueGet = undefined;
                            el._valueSet = undefined;

                        }
                        break;
                }
            }
        };

        $.inputmask = {
            //options default
            defaults: {
                placeholder: "_",
                optionalmarker: { start: "[", end: "]" },
                quantifiermarker: { start: "{", end: "}" },
                groupmarker: { start: "(", end: ")" },
                alternatormarker: "|",
                escapeChar: "\\",
                mask: null,
                oncomplete: $.noop, //executes when the mask is complete
                onincomplete: $.noop, //executes when the mask is incomplete and focus is lost
                oncleared: $.noop, //executes when the mask is cleared
                repeat: 0, //repetitions of the mask: * ~ forever, otherwise specify an integer
                greedy: true, //true: allocated buffer for the mask and repetitions - false: allocate only if needed
                autoUnmask: false, //automatically unmask when retrieving the value with $.fn.val or value if the browser supports __lookupGetter__ or getOwnPropertyDescriptor
                removeMaskOnSubmit: true, //remove the mask before submitting the form.  Use in combination with autoUnmask: true
                clearMaskOnLostFocus: true,
                insertMode: true, //insert the input or overwrite the input
                clearIncomplete: false, //clear the incomplete input on blur
                aliases: {}, //aliases definitions => see jquery.inputmask.extensions.js
                alias: null,
                onKeyUp: $.noop, //callback to implement autocomplete on certain keys for example
                onKeyPress: $.noop, //callback to implement autocomplete on certain keys for example
                onKeyDown: $.noop, //callback to implement autocomplete on certain keys for example
                onBeforeMask: undefined, //executes before masking the initial value to allow preprocessing of the initial value.  args => initialValue, opts => return processedValue
                onBeforePaste: undefined, //executes before masking the pasted value to allow preprocessing of the pasted value.  args => pastedValue, opts => return processedValue
                onUnMask: undefined, //executes after unmasking to allow postprocessing of the unmaskedvalue.  args => maskedValue, unmaskedValue, opts
                showMaskOnFocus: true, //show the mask-placeholder when the input has focus
                showMaskOnHover: true, //show the mask-placeholder when hovering the empty input
                onKeyValidation: $.noop, //executes on every key-press with the result of isValid. Params: result, opts
                skipOptionalPartCharacter: " ", //a character which can be used to skip an optional part of a mask
                showTooltip: false, //show the activemask as tooltip
                numericInput: false, //numericInput input direction style (input shifts to the left while holding the caret position)
                rightAlign: false, //align to the right
                //numeric basic properties
                radixPoint: "", //".", // | ","
                //numeric basic properties
                nojumps: false, //do not jump over fixed parts in the mask
                nojumpsThreshold: 0, //start nojumps as of
                definitions: {
                    '9': {
                        validator: "[0-9]",
                        cardinality: 1,
                        definitionSymbol: "*"
                    },
                    'a': {
                        validator: "[A-Za-z\u0410-\u044F\u0401\u0451]",
                        cardinality: 1,
                        definitionSymbol: "*"
                    },
                    '*': {
                        validator: "[A-Za-z\u0410-\u044F\u0401\u04510-9]",
                        cardinality: 1
                    }
                },
                keyCode: {
                    ALT: 18, BACKSPACE: 8, CAPS_LOCK: 20, COMMA: 188, COMMAND: 91, COMMAND_LEFT: 91, COMMAND_RIGHT: 93, CONTROL: 17, DELETE: 46, DOWN: 40, END: 35, ENTER: 13, ESCAPE: 27, HOME: 36, INSERT: 45, LEFT: 37, MENU: 93, NUMPAD_ADD: 107, NUMPAD_DECIMAL: 110, NUMPAD_DIVIDE: 111, NUMPAD_ENTER: 108,
                    NUMPAD_MULTIPLY: 106, NUMPAD_SUBTRACT: 109, PAGE_DOWN: 34, PAGE_UP: 33, PERIOD: 190, RIGHT: 39, SHIFT: 16, SPACE: 32, TAB: 9, UP: 38, WINDOWS: 91
                },
                //specify keycodes which should not be considered in the keypress event, otherwise the preventDefault will stop their default behavior especially in FF
                ignorables: [8, 9, 13, 19, 27, 33, 34, 35, 36, 37, 38, 39, 40, 45, 46, 93, 112, 113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 123],
                isComplete: undefined //override for isComplete - args => buffer, opts - return true || false
            },
            masksCache: {},
            escapeRegex: function (str) {
                var specials = ['/', '.', '*', '+', '?', '|', '(', ')', '[', ']', '{', '}', '\\'];
                return str.replace(new RegExp('(\\' + specials.join('|\\') + ')', 'gim'), '\\$1');
            },
            format: function (value, options) {
                var opts = $.extend(true, {}, $.inputmask.defaults, options);
                resolveAlias(opts.alias, options, opts);
                return maskScope({ "action": "format", "value": value }, generateMaskSet(opts), opts);
            },
            isValid: function (value, options) {
                var opts = $.extend(true, {}, $.inputmask.defaults, options);
                resolveAlias(opts.alias, options, opts);
                return maskScope({ "action": "isValid", "value": value }, generateMaskSet(opts), opts);
            }
        };

        $.fn.inputmask = function (fn, options, targetScope, targetData, msk) {
            targetScope = targetScope || maskScope;
            targetData = targetData || "_inputmask";
            function importAttributeOptions(npt, opts) {
                var $npt = $(npt);
                for (var option in opts) {
                    var optionData = $npt.data("inputmask-" + option.toLowerCase());
                    if (optionData != undefined)
                        opts[option] = optionData;
                }
                return opts;
            }
            var opts = $.extend(true, {}, $.inputmask.defaults, options),
                maskset;

            if (typeof fn === "string") {
                switch (fn) {
                    case "mask":
                        //resolve possible aliases given by options
                        resolveAlias(opts.alias, options, opts);
                        maskset = generateMaskSet(opts, targetScope !== maskScope);
                        if (maskset.length == 0) { return this; }

                        return this.each(function () {
                            targetScope({ "action": "mask", "el": this }, $.extend(true, {}, maskset), importAttributeOptions(this, opts));
                        });
                    case "unmaskedvalue":
                        var $input = $(this);
                        if ($input.data(targetData)) {
                            return targetScope({ "action": "unmaskedvalue", "$input": $input });
                        } else return $input.val();
                    case "remove":
                        return this.each(function () {
                            var $input = $(this);
                            if ($input.data(targetData)) {
                                targetScope({ "action": "remove", "el": this });
                            }
                        });
                    case "getemptymask": //return the default (empty) mask value, usefull for setting the default value in validation
                        if (this.data(targetData)) {
                            return targetScope({ "action": "getemptymask", "el": this });
                        }
                        else return "";
                    case "hasMaskedValue": //check wheter the returned value is masked or not; currently only works reliable when using jquery.val fn to retrieve the value 
                        return this.data(targetData) ? !this.data(targetData)['opts'].autoUnmask : false;
                    case "isComplete":
                        if (this.data(targetData)) {
                            return targetScope({ "action": "isComplete", "buffer": this[0]._valueGet().split(''), "el": this });
                        } else return true;
                    case "getmetadata": //return mask metadata if exists
                        if (this.data(targetData)) {
                            maskset = this.data(targetData)['maskset'];
                            return maskset['metadata'];
                        }
                        else return undefined;
                    case "_detectScope":
                        resolveAlias(opts.alias, options, opts);
                        if (msk != undefined && !resolveAlias(msk, options, opts) && $.inArray(msk, ["mask", "unmaskedvalue", "remove", "getemptymask", "hasMaskedValue", "isComplete", "getmetadata", "_detectScope"]) == -1) {
                            opts.mask = msk;
                        }
                        if ($.isFunction(opts.mask)) {
                            opts.mask = opts.mask.call(this, opts);
                        }
                        return $.isArray(opts.mask);
                    default:
                        resolveAlias(opts.alias, options, opts);
                        //check if the fn is an alias
                        if (!resolveAlias(fn, options, opts)) {
                            //maybe fn is a mask so we try
                            //set mask
                            opts.mask = fn;
                        }
                        maskset = generateMaskSet(opts, targetScope !== maskScope);
                        if (maskset == undefined) { return this; }
                        return this.each(function () {
                            targetScope({ "action": "mask", "el": this }, $.extend(true, {}, maskset), importAttributeOptions(this, opts));
                        });
                }
            } else if (typeof fn == "object") {
                opts = $.extend(true, {}, $.inputmask.defaults, fn);

                resolveAlias(opts.alias, fn, opts); //resolve aliases
                maskset = generateMaskSet(opts, targetScope !== maskScope);
                if (maskset == undefined) { return this; }
                return this.each(function () {
                    targetScope({ "action": "mask", "el": this }, $.extend(true, {}, maskset), importAttributeOptions(this, opts));
                });
            } else if (fn == undefined) {
                //look for data-inputmask atribute - the attribute should only contain optipns
                return this.each(function () {
                    var attrOptions = $(this).attr("data-inputmask");
                    if (attrOptions && attrOptions != "") {
                        try {
                            attrOptions = attrOptions.replace(new RegExp("'", "g"), '"');
                            var dataoptions = $.parseJSON("{" + attrOptions + "}");
                            $.extend(true, dataoptions, options);
                            opts = $.extend(true, {}, $.inputmask.defaults, dataoptions);
                            resolveAlias(opts.alias, dataoptions, opts);
                            opts.alias = undefined;
                            $(this).inputmask("mask", opts, targetScope);
                        } catch (ex) { } //need a more relax parseJSON
                    }
                });
            }
        };
    }
})(jQuery);
