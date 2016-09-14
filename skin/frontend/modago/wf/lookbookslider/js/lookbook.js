jQuery(document).ready(function () {
    Mall.lookbook.init();
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

