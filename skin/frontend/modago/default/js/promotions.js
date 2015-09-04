Mall.promotions = function (couponId) {
    console.log(couponId);
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
        var htmlContent = Mall.promotions.promotionContentTemplate();

        jQuery.tmpl(htmlContent,
            {
                "Name": promoItem.find(".promo_name").html(),
                "Description": promoItem.find(".promo_popup_data").data("description"),
                "Term": promoItem.find(".promo_popup_data").data("term"),
                "Code": promoItem.find(".promo_popup_data").data("code"),
                "PDF": promoItem.find(".promo_popup_data").data("pdf")
            }
        ).appendTo("#myPromotionsModal .modal-body");


        Mall.promotions.openModal();
    }
}

/**
 * Template for promotion popup content
 * @returns {string}
 */
Mall.promotions.promotionContentTemplate = function () {
    return "<h2>" + Mall.translate.__('your-discount') + ":</h2>" +
        "<h3>${Name}</h3>" +
        "<div>${Description}</div>" +
        "<div>" + Mall.translate.__('your-promo-expiration-date') + " ${Term}.</div>" +
        "<div>" + Mall.translate.__('your-promo-code') + ": ${Code}</div>" +
        "<div><a href='${PDF}' download='promotion-conditions'>" + Mall.translate.__('promo-conditions') + "</a></div>";
}

/**
 * Clear promotion popup content
 */
Mall.promotions.clearPromotionContent = function () {
    jQuery("#myPromotionsModal .modal-body").html("");
}

Mall.promotions.openModal = function () {
    jQuery("#myPromotionsModal").modal();
}
