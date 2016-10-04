var Config = {
	path : {
		recentlyViewed : '/orbacommon/ajax_product/get_recently_viewed',
		ajaxLoader : '/skin/frontend/base/default/images/ajax-loader.gif',
		averageRating : {
			averageRatingPath 		: '/skin/frontend/base/default/images/svg',
			averageRatingStarOff 	: 'star_no.svg',
			averageRatingStarOn 	: 'star_yes.svg',
			averageRatingStarHalf 	: 'star_half.svg'
		},
		ratyNote : {
			ratyNotePath 		: '/skin/frontend/base/default/images/svg',
			ratyNoteStarOff 	: 'star_no.svg',
			ratyNoteStarOn 		: 'star_yes.svg'
			//ratyNoteStarHalf 	: 'star_half.svg'
		},
		ratings : {
			ratingsPath 		: '/skin/frontend/base/default/images/svg',
			ratingsStarOff 		: 'star_no.svg',
			ratingsStarOn 		: 'star_yes.svg'
			//ratingsStarHalf 	: 'star_half.svg'
		},
		commentRating : {
			commentRatingPath 			: '/skin/frontend/base/default/images/svg',
			commentRatingStarOff 		: 'star_no.svg',
			commentRatingStarOn 		: 'star_yes.svg'
			//commentRatingStarHalf 		: 'star_half.svg'
		},
		averageNoteClient : {
			averageNoteClientPath 			: '/skin/frontend/base/default/images/svg',
			averageNoteClientStarOff 		: 'star_no.svg',
			averageNoteClientStarOn 		: 'star_yes.svg'
			//averageNoteClientStarHalf 		: 'star_half.svg'
		},
        heartLike: "/skin/frontend/base/default/images/svg/unlike.svg",
        heartLiked: "/skin/frontend/base/default/images/svg/like.svg"
	},
    url: {
        customer_email_exists: window.location.protocol + "//"
            + window.location.host + '/checkout/singlepage/checkExistingAccount',
        address: {
            remove: "/customer/address/deleteAjax",
            save: "/customer/address/saveAjax",
            get: ""
        },
		zip_validate: window.location.protocol + "//"
		+ window.location.host + '/checkout/singlepage/checkZip'
    }
};

