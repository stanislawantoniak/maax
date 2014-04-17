/*!
 * Time picker for pickadate.js v3.1.4
 * http://amsul.github.io/pickadate.js/time.htm
 */
(function(){function e(e,t){var i=this,r=e.$node.data("value");i.settings=t,i.queue={interval:"i",min:"measure create",max:"measure create",now:"now create",select:"parse create validate",highlight:"create validate",view:"create validate",disable:"flipItem",enable:"flipItem"},i.item={},i.item.interval=t.interval||30,i.item.disable=(t.disable||[]).slice(0),i.item.enable=-function(e){return e[0]===!0?e.shift():-1}(i.item.disable),i.set("min",t.min).set("max",t.max).set("now").set("select",r||e.$node[0].value||i.item.min,{format:r?t.formatSubmit:t.format}),i.key={40:1,38:-1,39:1,37:-1,go:function(e){i.set("highlight",i.item.highlight.pick+e*i.item.interval,{interval:e*i.item.interval}),this.render()}},e.on("render",function(){var r=e.$root.children(),n=r.find("."+t.klass.viewset);n.length?r[0].scrollTop=~~(n.position().top-2*n[0].clientHeight):console.warn("Nothing to viewset with",i.item.view)}).on("open",function(){e.$root.find("button").attr("disable",!1)}).on("close",function(){e.$root.find("button").attr("disable",!0)})}var t=24,i=60,r=12,n=t*i;e.prototype.set=function(e,t,i){var r=this;return r.item["enable"==e?"disable":"flip"==e?"enable":e]=r.queue[e].split(" ").map(function(n){return t=r[n](e,t,i)}).pop(),"select"==e?r.set("highlight",r.item.select,i):"highlight"==e?r.set("view",r.item.highlight,i):"interval"==e?r.set("min",r.item.min,i).set("max",r.item.max,i):("flip"==e||"min"==e||"max"==e||"disable"==e||"enable"==e)&&r.item.select&&r.item.highlight&&("min"==e&&r.set("max",r.item.max,i),r.set("select",r.item.select,i).set("highlight",r.item.highlight,i)),r},e.prototype.get=function(e){return this.item[e]},e.prototype.create=function(e,r,o){var a=this;return r=void 0===r?e:r,Picker._.isObject(r)&&Picker._.isInteger(r.pick)?r=r.pick:Array.isArray(r)?r=+r[0]*i+ +r[1]:Picker._.isInteger(r)||(r=a.now(e,r,o)),"max"==e&&a.item.min.pick>r&&(r+=n),r=a.normalize(r,o),{hour:~~(t+r/i)%t,mins:(i+r%i)%i,time:(n+r)%n,pick:r}},e.prototype.now=function(e,t){var r=new Date,n=r.getHours()*i+r.getMinutes();return Picker._.isInteger(t)?t+="min"==e&&0>t&&0===n?2:1:t=1,t*this.item.interval+n},e.prototype.normalize=function(e){return e-((0>e?this.item.interval:0)+e%this.item.interval)},e.prototype.measure=function(e,r,n){var o=this;return r?r===!0||Picker._.isInteger(r)?r=o.now(e,r,n):Picker._.isObject(r)&&Picker._.isInteger(r.pick)&&(r=o.normalize(r.pick,n)):r="min"==e?[0,0]:[t-1,i-1],r},e.prototype.validate=function(e,t,i){var r=this,n=i&&i.interval?i.interval:r.item.interval;return r.disabled(t)&&(t=r.shift(t,n)),t=r.scope(t),r.disabled(t)&&(t=r.shift(t,-1*n)),t},e.prototype.disabled=function(e){var t=this,i=t.item.disable.filter(function(i){return Picker._.isInteger(i)?e.hour==i:Array.isArray(i)?e.pick==t.create(i).pick:void 0}).length;return-1===t.item.enable?!i:i},e.prototype.shift=function(e,t){for(var i=this;i.disabled(e)&&(e=i.create(e.pick+=t||i.item.interval),!(e.pick<=i.item.min.pick||e.pick>=i.item.max.pick)););return e},e.prototype.scope=function(e){var t=this.item.min.pick,i=this.item.max.pick;return this.create(e.pick>i?i:t>e.pick?t:e)},e.prototype.parse=function(e,t,r){var n=this,o={};if(!t||Picker._.isInteger(t)||Array.isArray(t)||Picker._.isDate(t)||Picker._.isObject(t)&&Picker._.isInteger(t.pick))return t;if(!r||!r.format)throw"Need a formatting option to parse this..";return n.formats.toArray(r.format).map(function(e){var i=n.formats[e],r=i?Picker._.trigger(i,n,[t,o]):e.replace(/^!/,"").length;i&&(o[e]=t.substr(0,r)),t=t.substr(r)}),+o.i+i*(+(o.H||o.HH)||+(o.h||o.hh)%12+(/^p/i.test(o.A||o.a)?12:0))},e.prototype.formats={h:function(e,t){return e?Picker._.digits(e):t.hour%r||r},hh:function(e,t){return e?2:Picker._.lead(t.hour%r||r)},H:function(e,t){return e?Picker._.digits(e):""+t.hour},HH:function(e,t){return e?Picker._.digits(e):Picker._.lead(t.hour)},i:function(e,t){return e?2:Picker._.lead(t.mins)},a:function(e,t){return e?4:n/2>t.time%n?"a.m.":"p.m."},A:function(e,t){return e?2:n/2>t.time%n?"AM":"PM"},toArray:function(e){return e.split(/(h{1,2}|H{1,2}|i|a|A|!.)/g)},toString:function(e,t){var i=this;return i.formats.toArray(e).map(function(e){return Picker._.trigger(i.formats[e],i,[0,t])||e.replace(/^!/,"")}).join("")}},e.prototype.flipItem=function(e,t){var i=this,r=i.item.disable,n=-1===i.item.enable;return"flip"==t?i.item.enable=n?1:-1:!n&&"enable"==e||n&&"disable"==e?r=i.removeDisabled(r,t):(!n&&"disable"==e||n&&"enable"==e)&&(r=i.addDisabled(r,t)),r},e.prototype.addDisabled=function(e,t){var i=this;return t.map(function(t){i.filterDisabled(e,t).length||e.push(t)}),e},e.prototype.removeDisabled=function(e,t){var i=this;return t.map(function(t){e=i.filterDisabled(e,t,1)}),e},e.prototype.filterDisabled=function(e,t,i){var r=Array.isArray(t);return e.filter(function(e){var n=!r&&t===e||r&&Array.isArray(e)&&""+t==""+e;return i?!n:n})},e.prototype.i=function(e,t){return Picker._.isInteger(t)&&t>0?t:this.item.interval},e.prototype.nodes=function(e){var t=this,i=t.settings,r=t.item.select,n=t.item.highlight,o=t.item.view,a=t.item.disable;return Picker._.node("ul",Picker._.group({min:t.item.min.pick,max:t.item.max.pick,i:t.item.interval,node:"li",item:function(e){return e=t.create(e),[Picker._.trigger(t.formats.toString,t,[Picker._.trigger(i.formatLabel,t,[e])||i.format,e]),function(s,c){return r&&r.pick==c&&s.push(i.klass.selected),n&&n.pick==c&&s.push(i.klass.highlighted),o&&o.pick==c&&s.push(i.klass.viewset),a&&t.disabled(e)&&s.push(i.klass.disabled),s.join(" ")}([i.klass.listItem],e.pick),"data-pick="+e.pick]}})+Picker._.node("li",Picker._.node("button",i.clear,i.klass.buttonClear,"data-clear=1"+(e?"":" disable"))),i.klass.list)},e.defaults=function(e){return{clear:"Clear",format:"h:i A",interval:30,klass:{picker:e+" "+e+"--time",holder:e+"__holder",list:e+"__list",listItem:e+"__list-item",disabled:e+"__list-item--disabled",selected:e+"__list-item--selected",highlighted:e+"__list-item--highlighted",viewset:e+"__list-item--viewset",now:e+"__list-item--now",buttonClear:e+"__button--clear"}}}(Picker.klasses().picker),Picker.extend("pickatime",e)})();