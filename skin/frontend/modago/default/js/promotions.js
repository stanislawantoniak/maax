Mall.promotions = function (couponId) {
	Mall.promotions.jsCopySupported = document.queryCommandSupported('copy');

    Mall.promotions.populatePromotionContent(couponId);

    jQuery("#myPromotionsModal").on("hide.bs.modal", function () {
        Mall.promotions.clearPromotionContent();
    });
};

Mall.promotions.jsCopySupported = false;

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

        if(promoItem.find(".promo_popup_data").data("banner").length > 0){
            var bannerImg = document.createElement("img");
            bannerImg.setAttribute("src", promoItem.find(".promo_popup_data").data("banner"));
            bannerImg.setAttribute("alt", promoName);
            modal.find(".promo-banner-wrapper").html(bannerImg);
        }

	    var code = promoItem.find(".promo_popup_data").data("code");

        modal.find(".promo-description").html(promoItem.find(".promo_popup_data").data("description"));
        modal.find(".promo-expiration span").html(promoItem.find(".promo_popup_data").data("term"));
        modal.find(".promo-code span").html(code);

        modal.find(".promo-link a").attr("href",promoItem.find(".promo_popup_data").data("url"));

	    var copy = modal.find(".promo-code-copy"),
		    copySuccess = modal.find('.promo-code-copy-success').hide(),
		    copyError = modal.find('.promo-code-copy-error').hide();

	    if(!Mall.promotions.jsCopySupported) {
		    copy.hide();
	    } else {
		    copy.click(function() {
			    Mall.promotions.copyTextToClipboard(code,copyError,copySuccess);
		    });
	    }

        if(promoItem.find(".promo_popup_data").data("pdf").length > 0){
            modal.find(".promo-pdf a").attr("href",promoItem.find(".promo_popup_data").data("pdf"));
        } else {
            modal.find(".promo-pdf a").hide();
        }

    }
};


/**
 * Clear promotion popup content
 */
Mall.promotions.clearPromotionContent = function () {
    var modal = jQuery("#myPromotionsModal");
    modal.find(".promo-name").html("");
    modal.find(".promo-logo-wrapper").html("");
    modal.find(".promo-banner-wrapper").html("");


    modal.find(".promo-description").html("");
    modal.find(".promo-expiration span").html("");
    modal.find(".promo-code span").html("");

    modal.find(".promo-link a").attr("href", "");
    modal.find(".promo-pdf a").attr("href", "").show();
};

Mall.promotions.openModal = function () {
    jQuery("#myPromotionsModal").modal();
};

Mall.promotions.copyTextToClipboard = function(text,error,success) {
	if(Mall.promotions.jsCopySupported) {
		error = typeof error != 'undefined' && error.length ? error : false;
		success = typeof success != 'undefined' && success.length ? success : false;

		var textArea = document.createElement("textarea");

		textArea.style.position = 'fixed';
		textArea.style.top = 0;
		textArea.style.left = 0;
		textArea.style.width = '2em';
		textArea.style.height = '2em';
		textArea.style.padding = 0;
		textArea.style.border = 'none';
		textArea.style.outline = 'none';
		textArea.style.boxShadow = 'none';
		textArea.style.background = 'transparent';

		textArea.value = text;

		document.body.appendChild(textArea);

		textArea.select();

		try {
			var successful = document.execCommand('copy');
		} catch (err) {
			successful = false;
		}

		if(successful && success) {
			error.hide();
			success.show();
		} else if(!successful && error) {
			success.hide();
			error.show();
		}

		document.body.removeChild(textArea);
	}
};