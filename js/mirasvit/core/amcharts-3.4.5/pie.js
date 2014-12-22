AmCharts.AmPieChart=AmCharts.Class({inherits:AmCharts.AmSlicedChart,construct:function(e){this.type="pie";AmCharts.AmPieChart.base.construct.call(this,e);this.cname="AmPieChart";this.pieBrightnessStep=30;this.minRadius=10;this.depth3D=0;this.startAngle=90;this.angle=this.innerRadius=0;this.startRadius="500%";this.pullOutRadius="20%";this.labelRadius=20;this.labelText="[[title]]: [[percents]]%";this.balloonText="[[title]]: [[percents]]% ([[value]])\n[[description]]";this.previousScale=1;AmCharts.applyTheme(this,
e,this.cname)},drawChart:function(){AmCharts.AmPieChart.base.drawChart.call(this);var e=this.chartData;if(AmCharts.ifArray(e)){if(0<this.realWidth&&0<this.realHeight){AmCharts.VML&&(this.startAlpha=1);var g=this.startDuration,c=this.container,a=this.updateWidth();this.realWidth=a;var h=this.updateHeight();this.realHeight=h;var d=AmCharts.toCoordinate,k=d(this.marginLeft,a),b=d(this.marginRight,a),q=d(this.marginTop,h)+this.getTitleHeight(),l=d(this.marginBottom,h),u,v,f,s=AmCharts.toNumber(this.labelRadius),
r=this.measureMaxLabel();this.labelText&&this.labelsEnabled||(s=r=0);u=void 0===this.pieX?(a-k-b)/2+k:d(this.pieX,this.realWidth);v=void 0===this.pieY?(h-q-l)/2+q:d(this.pieY,h);f=d(this.radius,a,h);f||(a=0<=s?a-k-b-2*r:a-k-b,h=h-q-l,f=Math.min(a,h),h<a&&(f/=1-this.angle/90,f>a&&(f=a)),h=AmCharts.toCoordinate(this.pullOutRadius,f),f=(0<=s?f-1.8*(s+h):f-1.8*h)/2);f<this.minRadius&&(f=this.minRadius);h=d(this.pullOutRadius,f);q=AmCharts.toCoordinate(this.startRadius,f);d=d(this.innerRadius,f);d>=f&&
(d=f-1);l=AmCharts.fitToBounds(this.startAngle,0,360);0<this.depth3D&&(l=270<=l?270:90);l-=90;a=f-f*this.angle/90;for(k=0;k<e.length;k++)if(b=e[k],!0!==b.hidden&&0<b.percents){var m=360*b.percents/100,r=Math.sin((l+m/2)/180*Math.PI),w=-Math.cos((l+m/2)/180*Math.PI)*(a/f),p=this.outlineColor;p||(p=b.color);p={fill:b.color,stroke:p,"stroke-width":this.outlineThickness,"stroke-opacity":this.outlineAlpha,"fill-opacity":this.alpha};b.url&&(p.cursor="pointer");p=AmCharts.wedge(c,u,v,l,m,f,a,d,this.depth3D,
p,this.gradientRatio,b.pattern);this.addEventListeners(p,b);b.startAngle=l;e[k].wedge=p;0<g&&(this.chartCreated||p.setAttr("opacity",this.startAlpha));b.ix=r;b.iy=w;b.wedge=p;b.index=k;if(this.labelsEnabled&&this.labelText&&b.percents>=this.hideLabelsPercent){var n=l+m/2,m=s;isNaN(b.labelRadius)||(m=b.labelRadius);var z=u+r*(f+m),B=v+w*(f+m),x,t=0;if(0<=m){var y;90>=n&&0<=n?(y=0,x="start",t=8):90<=n&&180>n?(y=1,x="start",t=8):180<=n&&270>n?(y=2,x="end",t=-8):270<=n&&360>n&&(y=3,x="end",t=-8);b.labelQuarter=
y}else x="middle";var n=this.formatString(this.labelText,b),A=b.labelColor;A||(A=this.color);n=AmCharts.text(c,n,A,this.fontFamily,this.fontSize,x);n.translate(z+1.5*t,B);b.tx=z+1.5*t;b.ty=B;0<=m?p.push(n):this.freeLabelsSet.push(n);b.label=n;b.tx=z;b.tx2=z+t;b.tx0=u+r*f;b.ty0=v+w*f}m=d+(f-d)/2;b.pulled&&(m+=this.pullOutRadiusReal);b.balloonX=r*m+u;b.balloonY=w*m+v;b.startX=Math.round(r*q);b.startY=Math.round(w*q);b.pullX=Math.round(r*h);b.pullY=Math.round(w*h);this.graphsSet.push(p);(0===b.alpha||
0<g&&!this.chartCreated)&&p.hide();l+=360*b.percents/100}0<s&&!this.labelRadiusField&&this.arrangeLabels();this.pieXReal=u;this.pieYReal=v;this.radiusReal=f;this.innerRadiusReal=d;0<s&&this.drawTicks();this.initialStart();this.setDepths()}(e=this.legend)&&e.invalidateSize()}else this.cleanChart();this.dispDUpd();this.chartCreated=!0},setDepths:function(){var e=this.chartData,g;for(g=0;g<e.length;g++){var c=e[g],a=c.wedge,c=c.startAngle;0<=c&&180>c?a.toFront():180<=c&&a.toBack()}},arrangeLabels:function(){var e=
this.chartData,g=e.length,c,a;for(a=g-1;0<=a;a--)c=e[a],0!==c.labelQuarter||c.hidden||this.checkOverlapping(a,c,0,!0,0);for(a=0;a<g;a++)c=e[a],1!=c.labelQuarter||c.hidden||this.checkOverlapping(a,c,1,!1,0);for(a=g-1;0<=a;a--)c=e[a],2!=c.labelQuarter||c.hidden||this.checkOverlapping(a,c,2,!0,0);for(a=0;a<g;a++)c=e[a],3!=c.labelQuarter||c.hidden||this.checkOverlapping(a,c,3,!1,0)},checkOverlapping:function(e,g,c,a,h){var d,k,b=this.chartData,q=b.length,l=g.label;if(l){if(!0===a)for(k=e+1;k<q;k++)b[k].labelQuarter==
c&&(d=this.checkOverlappingReal(g,b[k],c))&&(k=q);else for(k=e-1;0<=k;k--)b[k].labelQuarter==c&&(d=this.checkOverlappingReal(g,b[k],c))&&(k=0);!0===d&&100>h&&(d=g.ty+3*g.iy,g.ty=d,l.translate(g.tx2,d),this.checkOverlapping(e,g,c,a,h+1))}},checkOverlappingReal:function(e,g,c){var a=!1,h=e.label,d=g.label;e.labelQuarter!=c||e.hidden||g.hidden||!d||(h=h.getBBox(),c={},c.width=h.width,c.height=h.height,c.y=e.ty,c.x=e.tx,e=d.getBBox(),d={},d.width=e.width,d.height=e.height,d.y=g.ty,d.x=g.tx,AmCharts.hitTest(c,
d)&&(a=!0));return a}});