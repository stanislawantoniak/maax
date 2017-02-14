Mall.upsell = {
    _height: 0,
    _boxCount: 0,
    _realBoxCount: 0,
    _actualBoxCount: 0,
    _animationBox: '',
    _animationBoxContent: '',
    _toShowItem: '',
    _toHideItem: '',
    _lock: false,
    init :function() {
        jQuery(".product_list_widget_item").mouseenter(function(event) {
            event.stopPropagation();
            Mall.upsell.showItem(jQuery(this));
            
        });
        jQuery(".watch_more_links .watch_more").click(function(event) {
            event.stopPropagation();
            if (Mall.upsell._lock == true) return false;
            if (Mall.upsell._actualBoxCount < Mall.upsell._realBoxCount - Mall.upsell._boxCount) {
                var collection = jQuery(".product_list_widget_item");
                var firstItem = collection.eq(Mall.upsell._actualBoxCount);
                var nextItem = collection.eq(Mall.upsell._actualBoxCount+Mall.upsell._boxCount);
                self = Mall.upsell;
                self._lock = true;
                self._toShowItem = nextItem;
                self._toHideItem = firstItem;
                self.moveAnimationBox(-190);            
                Mall.upsell._actualBoxCount = Mall.upsell._actualBoxCount+1;
            }
            return false;            
        });
        jQuery(".box-up-sell .watch_more_up").click(function(event) {
            event.stopPropagation();
            if (Mall.upsell._lock == true) return false;
            if (Mall.upsell._actualBoxCount > 0) {
                var collection = jQuery(".product_list_widget_item");
                var lastItem = collection.eq(Mall.upsell._actualBoxCount+Mall.upsell._boxCount-1);
                var nextItem = collection.eq(Mall.upsell._actualBoxCount-1);
                self = Mall.upsell;
                self._lock = true;
                self._toShowItem = nextItem;
                self._toHideItem = lastItem;
                self.moveAnimationBox(190);            
                Mall.upsell._actualBoxCount = Mall.upsell._actualBoxCount-1;
            }
             
            return false;            
        });
        var upsellHeight = jQuery("#product_content").outerHeight();
        this._animationBox = jQuery(".upsell_content");
        this._animationBoxContent = jQuery(".upsell_content_scroll");
        var itemHeight = 190;
        Mall.upsell._boxCount = Math.floor(upsellHeight/itemHeight)-1;
        if (Mall.upsell._boxCount<1) {
            Mall.upsell._boxCount = 1;
        }                
//            Mall.upsell._boxCount = 2; // test
        var showMore = false;
        jQuery(".product_list_widget_item").each(function() {
                if (Mall.upsell._realBoxCount<Mall.upsell._boxCount) {
                    jQuery(this).show();
                } else {
                    showMore = true;
                }
                Mall.upsell._realBoxCount = Mall.upsell._realBoxCount + 1;
        });
        Mall.upsell._height = this._animationBox.height();
        this._animationBox.css('height',Mall.upsell._height);
        if (showMore) {
            jQuery(".box-up-sell .more_block").show();
        }
    },
    moveAnimationBox: function(position) {
        this._animationBox.css('overflow','hidden');
        this._toShowItem.show();      
        if (position >0) {
            self._animationBoxContent.css('top',-position);
            position = 0;
        }  
        
        this._animationBoxContent.animate({top:position},400,function() {
            self = Mall.upsell;
            self._toHideItem.hide();
            self._animationBoxContent.css('top',0);
            self._animationBox.css('overflow','normal');    
            self._lock = false;
        });
    },
    /**
     * show sizes options after mouseenter
     */


    showItem: function (obj) {
        var pid = obj.data('pid');        
        var sizesArea = obj.find('.sizes');
        sizesArea.addClass('sizeborder');
        sizesArea.html('<i class="fa fa-spinner fa-spin"></i>');        
        jQuery.ajax({
            method: 'POST',
            url: 'orbacommon/ajax_upsell/getInfo',
            data: {pid:pid},
            success: function (data) {
                sizesArea.removeClass('sizeborder');
                sizesArea.html(data);
                Mall.upsell.fixOptions(sizesArea);
                sizesArea.find('.spinner').hide();
            }
        });
    },
    
    /**
     * draw diagonals on unavailable and resize boxes
     */

    fixOptions: function(area) {
        area.find("label").each(function() {
            var obj = jQuery(this).find('span');
            var wSizeLabel = jQuery.trim(obj.text()).length;
            var size = Mall.product.getTextWidth(obj)+10;
            if(wSizeLabel >= 4) {                
                jQuery(this).css({width:size+ 'px'});
                jQuery(this).closest('label').children('span').css({width:size+ 'px'});
            }            
            if (jQuery(this).hasClass('no-size')) {
                // diagonal
                elFilterSizeWidth = obj.width();
                elFilterSizeHeight = obj.height();;
                obj.append('<canvas class="diagonal" width="'+elFilterSizeWidth+'" height="'+elFilterSizeHeight+'"></canvas>');
            }
        });
    }
};

jQuery(document).ready(function () {
    "use strict";
    jQuery.extend(Mall.upsell, Mall.translate.ext);
    Mall.upsell.init();
});