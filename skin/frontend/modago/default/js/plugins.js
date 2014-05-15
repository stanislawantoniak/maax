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


