Mall.Navigation = {
    currentCategoryId: [],
    highligted: false,

    init: function() {
        var self = this;
        jQuery("#navigation a[data-catids]").each(function(index, value) {
            if (!self.highligted) {
                var arr = jQuery(this).attr('data-catids').split(',');
                for (i = 0; i < arr.length; i++) {
                    if (self.currentCategoryId.indexOf(arr[i]) != -1) {
                        //jQuery(this).addClass('forceActive'); //todo
                        jQuery(this).css('background', 'red');
                        self.highligted = true;
                        break;
                    }
                }
            }
        });
    },

    destroy: function() {
        jQuery("#navigation a[data-catids]").each(function(index, value) {
            if (self.highligted) {
                //jQuery(this).removeClass('forceActive'); //todo
                jQuery(this).css('background', '');
                self.highligted = false;
            }
        });
    },
};

jQuery(document).ready(function() {
    Mall.Navigation.init();
});