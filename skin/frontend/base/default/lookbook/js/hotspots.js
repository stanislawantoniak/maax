jQuery.extend({
    setHotspots : function(slide, hotspots) {
        if (!hotspots) return;
        
        jQuery.each(hotspots, function(i, hotspot) {
            if (!document.getElementById(hotspot.id)) {
                // scales
                var imgwidth = slide.width();
                var scale    = imgwidth/hotspot.imgW;
                if(typeof hotspot.imgW == 'undefined' || hotspot.imgW === null) {
                    scale = 1;
                    hotspot.imgH = slide.height();
                }
                
                // main sizes, hs - hotspot
                var offsetH = parseInt((hotspot.imgH*scale - slide.height())/2);
                var hs_left   = hotspot.left / (hotspot.imgW / 100);
                var hs_top    = hotspot.top / (hotspot.imgH / 100);
                var hs_width  = hotspot.width / (hotspot.imgW / 100);
                var hs_height = hotspot.height / (hotspot.imgH / 100);
                
                var hotspotHTML = '<div class="hotspot" id="'+hotspot.id+'" \n\
                    style="left:' + hs_left + '%; top:' + hs_top + '%; \n\
                    width:' + hs_width + '%; height:' + hs_height + '%;">\n\
                    ' + hotspot.text + '</div>';
                slide.append(hotspotHTML);
                
                var infoblock = slide.find('#'+hotspot.id +' .product-info');
                var infowidth = parseInt(infoblock.actual('outerWidth'));
                var hspt_width_hf = parseInt(hotspot.width * scale / 2);
                var leftposition = hotspot.left * scale + hspt_width_hf + 7;
                infoblock.find('.info-icon').css('left', hspt_width_hf + 'px');

                if (((leftposition + infowidth + 10) > imgwidth) 
                  && (leftposition > (imgwidth - leftposition)))
                {
                    if (jQuery.browser.msie && jQuery.browser.version == '8.0') {
                        if (leftposition - 5 < infowidth) {
                            infoblock.css('width', leftposition - 20 + 'px');
                            infowidth = infoblock.width();
                        }
                        infoblock.css('left', '50%');
                        infoblock.css('margin-left', '-' + infowidth - 2 * parseInt(infoblock.css('padding-left')) + 'px');
                    } else {
                        infoblock.css('left', '');
                        infoblock.css('right', '50%');
                    }

                    if (leftposition - 5 < infowidth) {
                        infoblock.css('width', leftposition - 20 + 'px');
                        infowidth = infoblock.width();
                    }
                } else {
                    infoblock.css('left', '50%');
                    if ((imgwidth - leftposition - 5) < infowidth) {
                        infoblock.css('width', imgwidth - leftposition - 20 + 'px');
                        infowidth = infoblock.width();
                    }
                }

                var imgheight = parseInt(slide.height());
                var infoheight = parseInt(infoblock.actual('outerHeight'));
                var hspt_height_hf = parseInt(hotspot.height * scale / 2);
                var topposition = hotspot.top * scale + hspt_height_hf;
                if (((topposition + infoheight + 5) > imgheight) && (topposition > (imgheight - topposition)))
                {
                    if (jQuery.browser.msie && jQuery.browser.version == '8.0') {
                        if (topposition - 5 < infoheight) {
                            infoblock.css('height', topposition - 10 + 'px');
                            infoheight = infoblock.height();
                        }
                        infoblock.css('top', '50%');
                        infoblock.css('margin-top', '-' + infoheight - 2 * parseInt(infoblock.css('padding-top')) + 'px');
                    } else {
                        infoblock.css('top', '');
                        infoblock.css('bottom', '50%');
                    }
                    
                    if (topposition - 5 < infoheight) {
                        infoblock.css('top', '50%');
                        infoblock.css('height', topposition - 10 + 'px');
                        infoheight = infoblock.height();
                    }
                } else {
                    infoblock.css('top', '50%');
                    if ((imgheight - topposition - 5) < infoheight) {
                        infoblock.css('height', imgheight - topposition - 10 + 'px');
                        infoheight = infoblock.height();
                    }
                }
                
                //set position for hotspot-icon
                var icon = slide.find('#'+hotspot.id +' .hotspot-icon');
                icon.on('load', function() {
                    icon.css('left', '50%');
                    icon.css('top', '50%');
                    icon.css('margin-left', '-' + icon.actual('width') / 2 + 'px');
                    icon.css('margin-top', '-' + icon.actual('height') / 2 + 'px');
                });
            }
      });
    }
});
  
jQuery(document).ready(function() {
    jQuery('.product-info a').on('click touchend', function(e) {
        var el = jQuery(this);
        var link = el.attr('href');
        window.location = link;
    });
    /******************************/
    if ("ontouchstart" in document.documentElement) {
        jQuery(document).on('touchstart', 'body', function(e) {
            jQuery(".hotspot").removeClass('hover');
        });
        jQuery(document).on('touchstart', '.hotspot', function(e) {
            jQuery(this).addClass('hover');
            e.stopPropagation();
        });
        jQuery(document).on('touchstart', '.hotspot .product-info', function(event) {
            event.stopPropagation();
        });
    }
    /******************************/
});              