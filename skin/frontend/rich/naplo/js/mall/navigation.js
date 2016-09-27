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

            var li = jQuery(this).parents("li");
            var liOffset = li.offset().left;
            var liOffsetTop = li.offset().top;

            var left = liOffset + 10;

            if (jQuery('#nav_desc a').hasClass('active')) {
                jQuery("#clone_submenu").css({
                    'top': (liOffsetTop + 45),
                    'left': left
                });
                jQuery('#nav_desc a[data-flagForceActive="1"]').removeClass('forceActive');
            } else {
                jQuery("#clone_submenu").css({
                    'top': 0
                });

                jQuery('#nav_desc a[data-flagForceActive="1"]').addClass('forceActive');
            }
        };

        var cloneMenuPosition = function () {
            if((typeof jQuery("#clone_submenu .clone_submenu-content").html() == "undefined") || jQuery("#clone_submenu .clone_submenu-content").html().length == 0){
                return;
            }


            var li = jQuery('#nav_desc a.active').parents("li");
            var liOffset = li.offset().left;
            var liOffsetTop = li.offset().top;

            var left = liOffset + 10;

            jQuery("#clone_submenu").css({
                'top': (liOffsetTop + 45),
                'left': left
            });
        };


        jQuery(document).delegate('#nav_desc a'  , 'click', customToggle);
        jQuery(window).resize(function(){cloneMenuPosition();});
    }
};

jQuery(document).ready(function() {
    Mall.Navigation.init();
});