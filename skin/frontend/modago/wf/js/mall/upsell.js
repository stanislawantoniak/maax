Mall.upsell = {
    init :function() {
        jQuery(".product_list_widget_item").mouseenter(function(event) {
            event.stopPropagation();
            Mall.upsell.showItem(jQuery(this));
            
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