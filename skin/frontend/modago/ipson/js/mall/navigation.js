Mall.Navigation = {
    currentCategoryId: [],
    highlighted: false,

    init: function() {
        var self = this;
        jQuery("#navigation a[data-catids]").each(function(index, value) {
            if (!self.highlighted) {
                var arr = jQuery(this).attr('data-catids').split(',');
                for (i = 0; i < arr.length; i++) {
                    if (self.currentCategoryId.indexOf(arr[i]) != -1) {
                        jQuery(this).addClass('forceActive');
                        jQuery(this).attr('data-flagForceActive', 1);
                        self.highlighted = true;
                        break;
                    }
                }
            }
        });
        self.attachOnChangeDesktopSubmenu();
    },

    destroy: function() {
        jQuery("#navigation a[data-catids]").each(function(index, value) {
            if (self.highlighted) {
                jQuery(this).removeClass('forceActive');
                jQuery(this).removeAttr('data-flagForceActive');
                self.highlighted = false;
            }
        });
    },

    /**
     * When click in main category from desktop navigation menu
     * highlighted element (current category context) should temporary be disabled
     */
    attachOnChangeDesktopSubmenu: function() {
        var customToggle = function () {
            if (jQuery('#nav_desc a').hasClass('active')) {
                jQuery('#nav_desc a[data-flagForceActive="1"]').removeClass('forceActive');
            } else {
                jQuery('#nav_desc a[data-flagForceActive="1"]').addClass('forceActive');
            }
        };
        jQuery(document).delegate('#nav_desc a'  , 'click', customToggle);
        jQuery(document).delegate('.closeSubMenu', 'click', customToggle);
    }
};

jQuery(document).ready(function() {
    Mall.Navigation.init();
});