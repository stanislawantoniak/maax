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

 