var Config = {
	path : {
		recentlyViewed : '/orbacommon/ajax_product/get_recently_viewed',
		ajaxLoader : '/skin/frontend/modago/default/images/ajax-loader.gif',
		averageRating : {
			averageRatingPath 		: '/skin/frontend/modago/default/images/raty',
			averageRatingStarOff 	: 'star-off-big-custom.png',
			averageRatingStarOn 	: 'star-on-big-custom.png',
			averageRatingStarHalf 	: 'star-half-big-custom.png'
		},
		ratyNote : {
			ratyNotePath 		: '/skin/frontend/modago/default/images/raty',
			ratyNoteStarOff 	: 'star-off-custom.png',
			ratyNoteStarOn 		: 'star-on-custom.png'
			//ratyNoteStarHalf 	: 'star-half-custom.png'
		},
		ratings : {
			ratingsPath 		: '/skin/frontend/modago/default/images/raty',
			ratingsStarOff 		: 'star-off-custom.png',
			ratingsStarOn 		: 'star-on-custom.png'
			//ratingsStarHalf 	: 'star-half-custom.png'
		},
		commentRating : {
			commentRatingPath 			: '/skin/frontend/modago/default/images/raty',
			commentRatingStarOff 		: 'star-off-custom.png',
			commentRatingStarOn 		: 'star-on-custom.png'
			//commentRatingStarHalf 		: 'star-half-custom.png'
		},
		averageNoteClient : {
			averageNoteClientPath 			: '/skin/frontend/modago/default/images/raty',
			averageNoteClientStarOff 		: 'star-small-off.png',
			averageNoteClientStarOn 		: 'star-small.png'
			//averageNoteClientStarHalf 		: 'star--small-half-custom.png'
		},
        heartLike: "/skin/frontend/modago/default/images/heart.png",
        heartLiked: "/skin/frontend/modago/default/images/heart-like.png"
	},
    url: {
        customer_email_exists: window.location.protocol + "//"
            + window.location.host + '/checkout/singlepage/checkExistingAccount',
        address: {
            remove: "",
            save: "/customer/address/saveAjax",
            get: ""
        }
    }
};

