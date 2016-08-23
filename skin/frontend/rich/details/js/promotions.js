Mall.promotions = function (couponId) {
	Mall.promotions.jsCopySupported = document.queryCommandSupported('copy');

    Mall.promotions.populatePromotionContent(couponId);

    jQuery("#myPromotionsModal").on("hide.bs.modal", function () {
        Mall.promotions.clearPromotionContent();
    });
};

Mall.promotions.initNotLogged = function() {
	if(jQuery('.mypromotions-not-logged-in').length) {

		Mall.promotions.setListHeight();

		jQuery(window).resize(Mall.promotions.setListHeight);
	}
};

Mall.promotions.setListHeight = function() {
	var height = jQuery('.mypromotions-modal:visible').outerHeight(),
		target = jQuery('#mypromotions-list');

	target.css('height',height + 'px');
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

	    var copy = modal.find(".promo-code-copy");
	    if(copy.length) {
		    Mall.promotions.copybuttonText = false;
		    Mall.promotions.copybuttonTimer = false;

		    if (Mall.promotions.copybuttonText) {
			    copy.find('a').text(Mall.promotions.copybuttonText);
		    }

		    if (!Mall.promotions.jsCopySupported) {
			    copy.hide();
		    } else {
			    copy.find('a').click(function () {
				    var result = Mall.promotions.copyTextToClipboard(code);
				    if (result) {
					    var me = jQuery(this),
						    copiedText = me.data('copied');

					    if (!Mall.promotions.copybuttonText) {
						    Mall.promotions.copybuttonText = me.text();
					    }

					    me.text(copiedText);

					    if (Mall.promotions.copybuttonTimer) {
						    clearInterval(Mall.promotions.copybuttonTimer);
					    }

					    Mall.promotions.copybuttonTimer = setTimeout(function () {
						    me.text(Mall.promotions.copybuttonText);
					    }, 3000)
				    }

			    });
		    }
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

Mall.promotions.copyTextToClipboard = function(text) {
	if(Mall.promotions.jsCopySupported) {

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

		document.body.removeChild(textArea);

		return successful;
	}
};

Mall.promotions.moveMsgsBlock = function() {
	var block = jQuery('.page-messages-block');
	if(block.length) {
		var header = jQuery('section#main').find('header#header-main');
		if(header.length) {
			block.insertAfter(header);
		}
	}
};

Mall.promotions.showPersistentRegister = function(e) {
	jQuery('#mypromotions-persistent-modal').addClass('hidden');
	jQuery('.mypromotions-persistent-register').removeClass('hidden');
	jQuery(window).resize();
	if(typeof e != 'undefined') {
		e.preventDefault();
	}
	return false;
};

jQuery(document).ready(function() {
	if(jQuery('body').hasClass('mypromotions-index-index')) {
		Mall.promotions.moveMsgsBlock();
		jQuery('.mypromotions-cms-persistent').find('.mypromotions-cms-text-register').click(Mall.promotions.showPersistentRegister);
	}
});