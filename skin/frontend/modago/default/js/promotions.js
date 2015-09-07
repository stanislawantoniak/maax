Mall.promotions = function (couponId) {
    Mall.promotions.populatePromotionContent(couponId);

    jQuery("#myPromotionsModal").on("hide.bs.modal", function () {
        Mall.promotions.clearPromotionContent();
    });
};

/**
 * Populate promotion popup content
 * @param couponId
 */
Mall.promotions.populatePromotionContent = function (couponId) {
    var promoItem = jQuery("#mypromotions-list .promo_item[data-couponid=" + couponId + "]");

    if (promoItem.length) {

        var promoName = promoItem.find(".promo_name").html();

        var modal = jQuery("#myPromotionsModal");
        modal.find(".promo-name").html(promoName);

        var logoImg = document.createElement("img");
        logoImg.setAttribute("src", promoItem.find(".promo_popup_data").data("logo"));
        logoImg.setAttribute("alt", promoName);
        modal.find(".promo-logo-wrapper").html(logoImg);

        var bannerImg = document.createElement("img");
        bannerImg.setAttribute("src", promoItem.find(".promo_popup_data").data("banner"));
        bannerImg.setAttribute("alt", promoName);
        modal.find(".promo-banner-wrapper").html(bannerImg);


        modal.find(".promo-description").html(promoItem.find(".promo_popup_data").data("description"));
        modal.find(".promo-expiration span").html(promoItem.find(".promo_popup_data").data("term"));
        modal.find(".promo-code span").html(promoItem.find(".promo_popup_data").data("code"));

        modal.find(".promo-link a").attr("href",promoItem.find(".promo_popup_data").data("url"));
        modal.find(".promo-pdf a").attr("href",promoItem.find(".promo_popup_data").data("pdf"));
    }
}


/**
 * Clear promotion popup content
 */
Mall.promotions.clearPromotionContent = function () {
    var modal = jQuery("#myPromotionsModal");
    modal.find(".promo-name").html("");
    modal.find(".promo-logo-wrapper").html("");


    modal.find(".promo-description").html("");
    modal.find(".promo-expiration span").html("");
    modal.find(".promo-code span").html("");

    modal.find(".promo-link a").attr("href", "");
    modal.find(".promo-pdf a").attr("href", "");
}

Mall.promotions.openModal = function () {
    jQuery("#myPromotionsModal").modal();
}
