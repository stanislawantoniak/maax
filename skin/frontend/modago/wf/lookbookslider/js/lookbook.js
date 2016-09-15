jQuery(document).ready(function () {
    Mall.lookbook.init();

    // jQuery('body').tooltip({
    //     selector: '[rel=tooltip]',
    //     placement: function (tip, element) {
    //         var offset = jQuery(element).offset();
    //
    //         if (offset.left > (jQuery(window).width() - 300)) {
    //             return "left";
    //         }
    //         return "right";
    //     },
    //     title: function () {
    //         var hotspot = jQuery(this).parents(".hotspot");
    //         return '<div class="class="product-info">' + hotspot.find(".product-info").html() + '</div>';
    //     },
    //     html: true
    // });
});


Mall.lookbook = {
    init: function () {
        Mall.lookbook.iconsManage();
    },
    iconsManage: function () {
        var iconMinus = Mall.reg.get("lookbookslider_minus_icon"),
            iconPlus = Mall.reg.get("lookbookslider_plus_icon");

        jQuery(".lookbookslider-container").delegate(".cycle-slideshow .hotspot", {
            hover: function () {
                jQuery(this).find("img.hotspot-icon").attr("src", iconMinus);
            },
            mouseleave: function () {
                jQuery(this).find("img.hotspot-icon").attr("src", iconPlus);
            }
        });
    }
};

