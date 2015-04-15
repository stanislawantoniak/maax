var prevW = -1, prevH = -1, lastToggle = 0;
jQuery.noConflict();
(function( $ ) {
	$(function() {
		var reviewForm = $('#review-form'),
			reviewSummaryTable = reviewForm.find('.review-summary-table');
		reviewForm.on('click', 'input[type="text"], textarea', function(){

			var valid = false;
			reviewSummaryTable.find('input[type="hidden"]').each(function(){
				if($(this).val() == '')
				{
					reviewSummaryTable.find('.error.hidden').removeClass('hidden');
					return valid = false;
				} else {
					reviewSummaryTable.find('.error').addClass('hidden');
					return valid = true;
				}
			});
			return valid;
		});
		reviewSummaryTable.on('click', 'img', function(){
			jQuery("#stars").valid();
			var valid = false;
			reviewSummaryTable.find('input[type="hidden"]').each(function(){
				if($(this).val() == '')
				{
					reviewSummaryTable.find('.error.hidden').removeClass('hidden');
					return valid = false;
				} else {
					reviewSummaryTable.find('.error').addClass('hidden');
					return valid = true;
				}
			});
			return valid;
		});

		/////////////////////////////////////// Validator Form ////////////////////////////////////////////

		jQuery.validator.addMethod("stars", function() {
			var valid = false;
			reviewSummaryTable.find('input[type="hidden"]').each(function(){
				if($(this).val() == '')
				{
					reviewSummaryTable.find('.error.hidden').removeClass('hidden');
					return valid = false;
				} else {
					reviewSummaryTable.find('.error').addClass('hidden');
					return valid = true;
				}
			});
			return valid;
		}, "");
		reviewForm.each(function () {

			$(this).validate({
				success: "valid",
				focusInvalid: false,
				errorElement: "span",
				onfocusout: function (element) {
					$(element).valid();
				},
				onsubmit: true,
				rules: {
					title: {
						required:true
					},
					czy_polecasz_produkt : {
						required:true,
						maxlengh:1
					},
					stars: {
						stars: true
					}
				},

				messages: {

				},
				ignore: "#cart-form",
				ignoreTitle: true,
				highlight: function(element) {
					var we = $(element).innerWidth()+25;
					$(element).closest("div").addClass('has-error has-feedback').removeClass('has-success');
					$(element).closest("div").find('.form-ico-times').remove();
					$(element).closest("div").not( ".form-checkbox" ).not( ".form-radio" ).append('<i style="left:'+we+'px; right:auto" class="form-ico-times form-control-feedback "></i>');
					$(element).closest("div").find('.form-ico-checked').remove();
				},
				unhighlight: function(element) {
					var we = $(element).innerWidth()+25;
					$(element).closest("div").removeClass('has-error').addClass('has-success has-feedback');
					$(element).closest("div").find('.form-ico-checked').remove();
					$(element).closest("div").append('<i style="left:'+we+'px; right:auto" class="form-ico-checked form-control-feedback"></i>');
					$(element).closest("div").find('.form-ico-times').remove();
				},
				errorPlacement: function(error, element) {
					if (element.attr("type") == "checkbox" ){
						$(element).closest('div').append(error)
					} else if (element.attr("type") == "radio") {
						$(element).closest('div').append(error)
					}else {
						error.insertAfter(element)
					}
				}
			});
		});
		$('#question-form-mobile,#question-form').submit(function () {
			if ($(this).valid()) {
				addFormSpinner($(this));
			}
		});

		function addFormSpinner(form) {
			var submitButton = form.find('button[type=submit]');
			submitButton.prop("disabled", true);
			submitButton.find('i').addClass('fa fa-spinner fa-spin');
		}

		var tableFooterGroup = $('.table-footer-group');
		tableFooterGroup.on('click', '.deliver_info', function(e){
			var _w = $(window).innerWidth();
			if (_w <=767) {
				e.preventDefault();
				$(this).closest('.table-footer-group').find('.conditions_shipping').toggle(50);
			}
		});
		var triggerConditionsShipping = $('#trigger_conditions_shipping');
		tableFooterGroup.on('click', '.trigger_conditions_shipping', function(e){
			var _w = $(window).innerWidth();
			var html;
			if (_w >=768) {
				e.preventDefault();
				html = $(this)
					.closest('.table-footer-group')
					.next('.table-footer-group')
					.find('.conditions_shipping').html();

			} else {
				html = $(this)
					.closest('.table-row')
					.next('.conditions_shipping')
					.html();
			}
			
			//triggerConditionsShipping.modal('show');
			triggerConditionsShipping.on('shown.bs.modal', function () {
				triggerConditionsShipping.find('.modal-body').html('');
				triggerConditionsShipping.find('.modal-body').append('<div class="panel panel-default">' + html + '</div>')
			})

		});
		triggerConditionsShipping.on('hide.bs.modal', function () {
			triggerConditionsShipping.find('.modal-body').html('');
		});

		$(window).on('load resize', function(){
			var link = $('.forgot-password');
			$.each( link, function() {
				var p = $( "#pass" );
				var pw = p.innerWidth();

				var position = parseInt(pw - 108);
				$(this).css({
					left:position+'px'
				});
			});
		});

		$('.hint').not('input').tooltip({
			placement: function() {
				var viewport = $(window).innerWidth();

				if (viewport > 768) {
					return "right";
				} else {
					return "top";
				}
			}
		});



		$.ajaxSetup ({
			// Disable caching of AJAX responses
			cache: false
		});

		/* ===================== invoice data ================== */

		$('#invoice_vat').on('click', function(){

			var firm_name = $('#firm_name').val(),
				street = $('#street').val(),
				zip_code = $('#zip_code').val(),
				city = $('#city').val();

			var invoice_data_firm_name = '',
				invoice_data_street = '',
				invoice_data_zip_code = '',
				invoice_data_city = '';

			if(this.checked) {
				$('#invoice_data').css({display:'block'});
				$('#invoice_data_firm_name').val(firm_name);
				$('#invoice_data_street').val(street);
				$('#invoice_data_zip_code').val(zip_code);
				$('#invoice_data_city').val(city);
			} else {
				$('#invoice_data').css({display:'none'});
			}
		});

		/* ==================== KUPON RABATOWY =============== */
		$('.form_discount_voucher').hide();
		var discountVoucher = $('#discount_voucher');
		discountVoucher.on('click', '.info_discount_voucher', function(e){
			e.preventDefault();
			$('.form_discount_voucher').show(50);
		});
		discountVoucher.on('click', 'input[type="submit"]', function(e){
			e.preventDefault();
			$('.form_discount_voucher').hide(50);
			$('.coupon-list').show(50);
		});

		/* ===================== checkout ===================== */
		widthWindow = $(window).width();
		var podziekowanieNieudaneViewSummary = $('.podziekowanie_nieudane_view_summary');
		if (widthWindow <= 767) {
			podziekowanieNieudaneViewSummary.closest('.block_info_order').find('.adres_dostawy').hide();
			podziekowanieNieudaneViewSummary.closest('.block_info_order').next('.table-summary-product').hide();
			podziekowanieNieudaneViewSummary.closest('.panel-body').next('.panel-footer').hide();
		}

		podziekowanieNieudaneViewSummary.on('click', function(e){
			e.preventDefault();
			var txt = $(this).closest('.block_info_order').next('.table-summary-product').is(':visible') ? 'Rozwiń szczegóły' : 'Zwiń szczegóły';
			var pf = $(this).closest('.panel-body').next('.panel-footer');
			$(this).children('span').text(txt);
			$(this).toggleClass('open');
			$(this).find('i').toggleClass('bullet-strzalka-up bullet-strzalka-down');
			$(this).closest('.block_info_order').find('.adres_dostawy').toggle();
			$(this).closest('.block_info_order').next('.table-summary-product').toggle();
			$(this).closest('.panel-body').next('.panel-footer').toggle();
		});

		var default_pay_bank = $('.default_pay input[name=pay_method]');

		$('body').find('.default_pay input[name=pay_method]:checked').closest('.panel').addClass('selected');

		$(default_pay_bank).on('change', function(){
			$(default_pay_bank).closest('.panel').removeClass('selected');
			$(this).closest('.panel').addClass('selected');
			$('.selected_bank').hide();
			$(this).closest('.form-group').next('.selected_bank').show();
		});


		var stara_cena = jQuery('.table-summary-product .cena .stara_cena');
		var hide_panel = $('.node-type-summary-delivery-payment header h2');

		$('#podsumowanie_popup').modal('show');


		var _modal = $('[data-toggle="modal"]');
		_modal.each(function(){

			var  _modalTarget = $(this).data('target');


			$(_modalTarget).on('show.bs.modal', function (e) {
				var backdrop =  $('#sb-site').find('.modal-backdrop');
				if (backdrop.length == 0) {
					$('#sb-site').append('<div class="modal-backdrop fade in"></div>');
				}
			});
			$(_modalTarget).on('shown.bs.modal', function () {
				$('html').find('body > .modal-backdrop').remove();
			});
			$(_modalTarget).on('hidden.bs.modal', function () {
				$('html').find('.modal-backdrop').remove();
			});
		});

		$('.popover-dismiss').popover({
			trigger:'hover'
		});

		$('.open-details-order-info').on('click',function(e){
			e.preventDefault();
			$(this).toggleClass('current');
			var txt = $(this).closest('.col-md-6').next('.block-addres').is(':visible') ? 'rozwiń szczegóły' : 'zwiń szczegóły';
			$(this).text(txt);
			$(this).closest('.col-md-6').next('.block-addres').toggle(50);
			$(this).closest('.block_info_order').next('.table-summary-product').toggle(50);
		});

		$('#show-table-summary-product').on('click', function(){
			$(this).closest('.main').toggleClass('opened-panel-group');
			$(this).find('i').toggleClass('bullet-strzalka-down bullet-strzalka-up');
			if(!$(this).closest('.main').hasClass('opened-panel-group')){
				$('#order-show-products').removeClass('hidden').addClass("visible-xs");
			}else{
				$('#order-show-products').addClass('hidden').removeClass("visible-xs");
			}
		});

		$('#zwin_produkty').on('click',function(){
			$(this).closest('.main').removeClass('opened-panel-group');
			$('#order-show-products').removeClass('hidden').addClass("visible-xs");
			hide_panel.find('i').removeClass('bullet-strzalka-up').addClass('bullet-strzalka-down');
		});

		$('#pokaz_produkty').on('click',function(){
			$(this).closest('.main').toggleClass('opened-panel-group');
			$('#order-show-products').addClass('hidden').removeClass("visible-xs");
			hide_panel.find('i').removeClass('bullet-strzalka-down').addClass('bullet-strzalka-up');
		});

		$('.sidebar-secound').on('click', 'h2.open', function(){
			var ww = $(window).innerWidth();
			if(ww <=767) {
				$(this).closest('header').next('.panel-group').toggle(50);
				$(this).closest('header').toggleClass('open');
				$(this).find('i').toggleClass('bullet-strzalka-down bullet-strzalka-up')
			}
		});

		$(window).on('load', function() {
			var ww = $(window).innerWidth();
			if(ww <=767 && ww >=481) {
				$('#checkout.podsumowanie #content-main').find('.main').addClass('opened-panel-group');
				$('#checkout.podsumowanie #content-main').find('.title-section').find('i').removeClass('bullet-strzalka-down').addClass('bullet-strzalka-up');
			} else if (ww >=768) {
				$('#checkout.podsumowanie #content-main').find('#order-show-products').addClass('hidden');
			}
		});

		var $window = $(window);
		var $wWOryginal = $(window).innerWidth();
		$window.on('resize', function () {

			var widthWindow = $(window).innerWidth();
			var zwin_produkty = $('#zwin_produkty');
			var pokaz_produkty = $('#pokaz_produkty');
			if(widthWindow != $wWOryginal) {
				if (widthWindow <= 767) {
					var arrow = podziekowanieNieudaneViewSummary.closest('.block_info_order').is(':hidden') ? 'bullet-strzalka-up test' : 'bullet-strzalka-down test';

					podziekowanieNieudaneViewSummary.find('i').attr('class', arrow)
					podziekowanieNieudaneViewSummary.closest('.block_info_order').find('.adres_dostawy').hide();
					podziekowanieNieudaneViewSummary.closest('.block_info_order').next('.table-summary-product').hide();

					if(podziekowanieNieudaneViewSummary.hasClass('open')) {
						podziekowanieNieudaneViewSummary.find('i').removeClass('bullet-strzalka-down').addClass('bullet-strzalka-up')
						podziekowanieNieudaneViewSummary.closest('.block_info_order').next('.table-summary-product').css({display:'block'})
						podziekowanieNieudaneViewSummary.closest('.block_info_order').find('.adres_dostawy').css({display:'block'})
					}

				} else {
					$('.adres_dostawy').show();
					podziekowanieNieudaneViewSummary.closest('.block_info_order').find('.adres_dostawy').show();
					podziekowanieNieudaneViewSummary.closest('.block_info_order').next('.table-summary-product').show();
					podziekowanieNieudaneViewSummary.closest('.panel-body').next('.panel-footer').show();

					var sidebarSecoundHeader = $('.sidebar-secound').find('header');
					sidebarSecoundHeader.removeClass('open');
					sidebarSecoundHeader.next('.panel-group').show();
					$('#checkout.podsumowanie #content-main').find('#order-show-products').addClass('hidden');
					podziekowanieNieudaneViewSummary.removeClass('open');
				}

				if (widthWindow < 768 && widthWindow > 481 ) {
					$('#checkout.podsumowanie').find('.main').addClass('opened-panel-group');
				} else {
					$('#checkout.podsumowanie').find('.main').removeClass('opened-panel-group');
				}

				if(widthWindow >=481) {
					hide_panel.find('i').removeClass('bullet-strzalka-down').addClass('bullet-strzalka-up');
					hide_panel.closest('.panel-group').next('.panel-group').show(50);
					$('.open-details-order-info').closest('.block_info_order').next('.table-summary-product').show(50);
					$('.title-section').next('.panel-group').show(50);
				}
			}
		});


		/* ================= end:// checkout ================= */
		var widthWindow = $(window).innerWidth();
		var kolumnaPrawa =  $("div.col-01").innerHeight();
		var kolumnaLewa = $("div.col-02").innerHeight();
		if (widthWindow >= 748) {
			if (kolumnaLewa > kolumnaPrawa) {
				$("div.col-01").css({'height' : kolumnaLewa});
			} else {
				$("div.col-02").css({'height' : kolumnaPrawa});
			}
		}
		$( window ).resize(function() {
			var widthWindow = $(window).innerWidth();
			var col01 = $("div.col-01"), col02 = $("div.col-02");
			col01.css({'height' : 'auto'});
			col02.css({'height' : 'auto'});
			var kolumnaPrawa =  col01.innerHeight();
			var kolumnaLewa = col02.innerHeight();
			if (widthWindow >= 748) {
				if (kolumnaLewa > kolumnaPrawa) {
					col01.css({'height' : kolumnaLewa});
				} else {
					col02.css({'height' : kolumnaPrawa});
				}
			} else {
				col01.css({'height' : 'auto'});
				col02.css({'height' : 'auto'});
			}
		});

		var productGallery = $("#product-gallery");
		var sync1 = productGallery.find("#sync1");
		var sync2 = productGallery.find("#sync2");

		sync1.rwdCarousel({
			singleItem : true,
			slideSpeed : 1000,
			navigation: true,
			pagination:true,
			afterAction : syncPosition,
			responsiveRefreshRate : 200,
			mouseDrag:false,
			rewindNav : false,
			itemsScaleUp:true

		});

		sync2.rwdCarousel({
			items : 1,
			pagination:false,
			navigation: false,
			touchDrag: false,
			mouseDrag:false,
			afterInit : function(el){
				el.find(".rwd-item").eq(0).addClass("synced");
				var sync2Item = $('#sync2 .rwd-item');
				var sync2ItemLength = sync2Item.length;
				if (sync2ItemLength <= 4 ) {
					$('#product-gallery #wrapper-sync2 .up').addClass('disabled');
					$('#product-gallery #wrapper-sync2 .down').addClass('disabled');
					//console.log(sync2ItemLength)
				}
			}
		});


		function syncPosition(){
			var current = this.currentItem;
			$("#product-gallery #sync2")
				.find(".rwd-item")
				.removeClass("synced")
				.eq(current)
				.addClass("synced")
			if($("#product-gallery #sync2").data("rwdCarousel") !== undefined){
				center(current)
			}
		}

		$("#sync2").on("click", ".rwd-item", function(e){
			e.preventDefault();
			var number = $(this).data("rwdItem");
			sync1.trigger("rwd.goTo",number);
		});

		$('#product-gallery #wrapper-sync2 .up').addClass('disabled');

		$('#product-gallery #wrapper-sync2').on('click', '.up', function(event) {
			event.preventDefault();

			var item = $('#product-gallery #wrapper-sync2').find('.rwd-item');
			var itemHeight = item.height()+10;
			var position = parseInt($('#product-gallery #wrapper-sync2 .rwd-wrapper').css('margin-top'));
			var wrapp = $(this).closest('#product-gallery #wrapper-sync2').find('.rwd-wrapper');
			var sumItem = itemHeight * (item.length-5);
			wrapp.filter(':not(:animated)').animate({
				'margin-top': '+='+itemHeight
			});

			if (position == '-'+itemHeight) {
				$('#product-gallery #wrapper-sync2 .up').addClass('disabled');
			}
			if (position != '-'+sumItem) {
				$('#product-gallery #wrapper-sync2 .down').removeClass('disabled');
			}
		});

		$('#product-gallery #wrapper-sync2').on('click', '.down', function(event) {
			event.preventDefault();

			var item = $('#product-gallery #wrapper-sync2').find('.rwd-item');
			var itemHeight = item.height()+10;
			var position =  parseInt($('#product-gallery #wrapper-sync2 .rwd-wrapper').css('margin-top'));
			var wrapp = $(this).closest('#product-gallery #wrapper-sync2').find('.rwd-wrapper');

			var wrappHeight = wrapp.height();
			var sumItem = itemHeight * (item.length-5);
			wrapp.filter(':not(:animated)').animate({
				'margin-top': '-='+itemHeight
			});
			if (position != '-'+itemHeight) {
				$('#product-gallery #wrapper-sync2 .up').removeClass('disabled');
			}
			if (position == '-'+sumItem) {
				$('#product-gallery #wrapper-sync2 .down').addClass('disabled');
			}
		});

		function center(number){
			//was doing completely nothing
		}

		flagProduct();
		function flagProduct() {
			var sync1 = $('#sync1').find('.rwd-item');
			sync1.each(function(i){
				var flags = $(this).find('a');
				var flag = flags.data('flags');
				flags.append('<i class="flag '+flag+'"></i>');
			});

		}

		function flagProductGallery() {
			var sync3 = $('#sync3').find('.rwd-item');
			sync3.each(function(i){
				var flags3 = $(this).find('.inner-item');
				var flag3 = flags3.data('flags');

				if ($(this).find('.flag').length == 0) {
					$(this).find('.inner-item').append('<i class="flag '+flag3+'"></i>');
				}
			});

		}

// COLOR CAROUSEL
		var rwd_color = $("#rwd-color");
		rwd_color.rwdCarousel({
			items:5,
			navigation : true,
			pagination: false,
			itemsScaleUp: false,
			responsive: false,
			rewindNav : false
		});


		function galeriaProduktu(){
// start
			var lightbox = $("#lightbox");
			var sync3 = lightbox.find("#sync3");
			var sync4 = lightbox.find("#sync4");
			var wrapperSync4 = $('#wrapper-sync4');

			sync4.rwdCarousel({
				items : 1,
				pagination:false,
				navigation: false,
				mouseDrag:false,
				touchDrag: false,
				transitionStyle: "fade",
				afterInit : function(el){
					el.find(".rwd-item").eq(0).addClass("synced");
					var sync4Item = sync4.find('.rwd-item');
					var sync4ItemLength = sync4Item.length;
					if (sync4ItemLength <= 4 ) {
						wrapperSync4.find('.up').addClass('disabled');
						wrapperSync4.find('.down').addClass('disabled');
					}
				}
			});

			sync3.rwdCarousel({
				singleItem : true,
				navigation: true,
				pagination:true,
				afterAction : syncPosition2,
				responsiveRefreshRate : 200,
				mouseDrag:true,
				rewindNav : false,
				itemsScaleUp:true,
				transitionStyle : "fade",
				slideSpeed:10,
				afterMove: function() {
					var body = $("#inner-lightbox");
					var top = body.scrollTop();
					if(top!=0) {
						body.animate({ scrollTop: 0 }, "slow");
					}
				},
				afterInit:function(elem) {
					var that = this;
					that.rwdControls.prependTo(elem);
					lightbox.find('.inner-item').css({visibility:'hidden'});
					lightbox.find('#galeria-lightbox #sync3 .rwd-wrapper-outer').prepend('<div class="loader" style="text-align:center;"><img src="'+Config.path.ajaxLoader+'" /></div>')
					imagesLoaded( document.querySelector('#galeria-lightbox #sync3 .rwd-item .item'), function( instance ) {

						setTimeout(function(){
							$('#lightbox').find('.inner-item').css({visibility:'visible'})
						}, 500);

						$('#galeria-lightbox #sync3 .rwd-item').each(function(index, el) {
							var numberProduct = $('#sync1').data("rwdCarousel").rwd.visibleItems;
							$('#sync4').find( ".rwd-item" ).eq( numberProduct ).trigger("click");




							flagProductGallery();
							var img = $(this).find('img');
							var a = $(this).find('img');
							var aH = a.height();
							var b = $(this).find('img');
							var bW = b.width();
							var c = $(window).height();
							var d = lightbox.find('#hl').innerHeight();
							var contentHeight = c-d-90;
							var imgWidthLoad = $(this).find('img').data('width');
							a.css({
								height: contentHeight,
								width: 'auto'
							});
							// ustawienie szerokości contenera dla przeskalowanego zdjęcia
							var innerItem = a.innerWidth();
							var innerItemHeight = a.height();
							a.closest('.inner-item').css('width', innerItem );
							// Ukrycie button zoom
							if (innerItem > imgWidthLoad) {
								$(this).find('.zoom_plus').hide();
								$(this).find('.zoom_minus').hide();

							}
							lightbox.find('.rwd-buttons').width(innerItem+'px')
							lightbox.find('.rwd-prev, .rwd-next').css({
								top: innerItemHeight/2
							});

							var aImage = $(this).find('img');
							var aImageWidth = parseInt(aImage.css('width'));

							$('.zoom_minus').on('click', function(event) {
								var windowWidth = $(window).innerWidth();
								a.css({
									height: contentHeight,
									width: 'auto'
								});
								if (windowWidth <= 1023) {
									lightbox.find('.rwd-buttons').show();
								}
								a.closest('.inner-item').css({'width': innerItem, 'margin': '0 auto' });
								$('.rwd-buttons').width(aImageWidth);
								$(this).closest('.rwd-wrapper').find('.rwd-item').each(function(){
									$(this).find('.zoom_minus').addClass('full disabled').removeClass('full').addClass('disabled');
									$(this).find('.zoom_plus').removeClass('full disabled');
								});
							});

							$('#lightbox').find('.loader').remove();
						});
					});
				}
			});

			$('.zoom_plus').on('click', function(event) {
				event.preventDefault();
				$('#sync3 .rwd-item').each(function(){
					var img = $(this).find('img');
					var imgScaleWidth = parseInt(img.width());
					var imgWidth   =    $(this).find('img').data('width');
					var divWidth   = parseInt($(this).find('.item').css("width"));
					$(img).width(divWidth);
					$('.rwd-buttons').width(imgScaleWidth);
					$(img).css("height", 'auto');


					if (imgWidth < divWidth) {
						$(img).width(imgWidth+'px'); //Set the width to the div's width
						$(this).find('.inner-item').css({width:imgWidth,margin:'0 auto'});
						$(img).css("height", 'auto');
					} else if (imgWidth >= divWidth) {
						$(img).width('100%') //Set the width to the div's width
						$(this).find('.inner-item').css({width:'100%',margin:'0 auto'});
						$(img).css("height", 'auto');

					} else if (imgScaleWidth > imgWidth){
					}
					$(this).find('.zoom_plus').addClass('full disabled');
					$(this).find('.zoom_minus').addClass('full').removeClass('disabled');
				})

			});

			function syncPosition2(el){
				var current = this.currentItem;
				$("#sync4")
					.find(".rwd-item")
					.removeClass("synced")
					.eq(current)
					.addClass("synced");
			}

			$('#sync3').on('click', '.rwd-next', function(e){
				e.preventDefault();

				var current = $('#sync4 .rwd-item').index($('.synced'));
				var hri = $("#sync4").find('.rwd-item').height();


				var currentLength = $("#sync4").find('.rwd-item').length;
				console.log('Current: ' +  this.currentItem + '|| Height Item: ' + hri + '|| Length: ' + currentLength);
				if(current >= 4 && current <= currentLength) {

					$("#sync4 .rwd-wrapper").animate({
						marginTop: '-=98px'
					});
					if (current >= 4) {
						$("#wrapper-sync4").find('.up').removeClass('disabled')
					} else {
						$("#wrapper-sync4").find('.up').addClass('disabled')
					}
					if (current === currentLength-1) {
						$("#wrapper-sync4").find('.down').addClass('disabled')
					} else {
						$("#wrapper-sync4").find('.down').removeClass('disabled')
					}
				}
			});


			$("#sync4").on("click", ".rwd-item", function(e){
				e.preventDefault();
				var number = $(this).data("rwdItem");
				sync1.trigger("rwd.goTo",number);
			});

			function center(number){
				//did nothing at all
			}

			$("#lightbox #sync4").on("click", ".rwd-item", function(e){
				e.preventDefault();
				var number = $(this).data("rwdItem");
				$("#lightbox #sync4").find('.rwd-item').removeClass("synced");
				$(this).addClass("synced");
				sync3.trigger("rwd.goTo",number);
			});

			$('#lightbox #wrapper-sync4 .up').addClass('disabled');

			$('#lightbox #wrapper-sync4').on('click', '.up', function(event) {
				event.preventDefault();

				var item = $('#lightbox #wrapper-sync4').find('.rwd-item');
				var itemHeight = item.height()+10;
				var position = parseInt($('#lightbox #wrapper-sync4 .rwd-wrapper').css('margin-top'));
				var wrapp = $(this).closest('#lightbox #wrapper-sync4').find('.rwd-wrapper');
				var sumItem = itemHeight * (item.length-5);
				wrapp.filter(':not(:animated)').animate({
					'margin-top': '+='+itemHeight
				});

				if (position == '-'+itemHeight) {
					$('#lightbox #wrapper-sync4 .up').addClass('disabled');
				}
				if (position != '-'+sumItem) {
					$('#lightbox #wrapper-sync4 .down').removeClass('disabled');
				}

			});

			$('#lightbox #wrapper-sync4').on('click', '.down', function(event) {
				event.preventDefault();

				var item = $('#lightbox #wrapper-sync4').find('.rwd-item');
				var itemHeight = item.height()+10;
				var position =  parseInt($('#lightbox #wrapper-sync4 .rwd-wrapper').css('margin-top'));
				var wrapp = $(this).closest('#lightbox #wrapper-sync4').find('.rwd-wrapper');

				wrapp.filter(':not(:animated)').animate({
					'margin-top': '-='+itemHeight
				});
				if (position != '-'+itemHeight) {
					$('#lightbox #wrapper-sync4 .up').removeClass('disabled');
				}
				if (position == '-'+sumItem) {
					$('#lightbox #wrapper-sync4 .down').addClass('disabled');
				}
			});
		}

// Show Top Layer - Gallery
		$(window).on('resize', function(){
			var widthWindow = $(window).width();
			var lupa = $('#product-gallery #sync1').find('.view_lupa');
			if (widthWindow < 767) {
				lupa.hide();
			} else {
				lupa.show();
			}
		});
		if (widthWindow >= 767) {
			showGalleryProduct();
		}
		function showGalleryProduct() {
			$('#product-gallery #sync1').on('click', 'a', function(event) {
				event.preventDefault();
				var widthWindow = $(window).width();
				if (widthWindow >= 767) {

					$( "#lightbox .bl" ).html($("#galeria-lightbox-wr").html());

					$('#lightbox').css({display:'block'});

					$('body').addClass('lightbox');
					galeriaProduktu();
					$('body').addClass('lightbox');
				}
			});
		}

		var lightbox = $('#lightbox');
		if (lightbox) {
			lightbox.on('click', '#remove-lightbox', function(event) {
				event.preventDefault();
				$(this).parents('#lightbox').hide();
				$('body').removeClass('lightbox');
				var carousel = $('.bl').find("#galeria-lightbox");//.connected-carousels
				carousel.remove();
				//window.location.reload(true)
			});
		}

		function equalizeHeights() {
			var heights = new Array();
			$('.node-type-list #items-product .item').each(function() {
				heights.push($(this).height());
			});
			var min = Math.min.apply( Math, heights );
			var itemsProduct = $('#items-product');
			var con = itemsProduct.innerHeight();
			itemsProduct.not('.list-shop-product').css('height', 'auto');
			itemsProduct.not('.list-shop-product').css('height', con-min);
		}

		helperForm();
		function helperForm() {
			$('input.orders_someone_else').on('click',  function(event) {
				$(this).closest('form').find('div.orders_someone_else').toggleClass('hidden');
			});
		}

		var intFrameWidth = window.innerWidth;
		if (intFrameWidth <= 767) {
			$('.toggle-xs').find('.main').hide();
			$('.block-complementary-product.toggle-xs').find('.main').show();
		} else {
			$('.toggle-xs').find('.main').show();
		}

		$('#product-gallery .stage a').on('click', '.selector', function(event) {
			event.preventDefault();
			$('#product_gallery').modal('hide');
		});

		shippingHelper();
		openFormReview();

		function openFormReview() {
			var footerComments = $('.footer_comments');
			var wrapperFormReview = $('#block-review-form');

			$('.viewFormComments').on('click', function(event) {
				event.preventDefault();var intFrameWidth = window.innerWidth;
				wrapperFormReview.slideToggle(200, function(){
					var animeOffset = $("#block-review-form").offset().top - 80;
					$('html, body').animate({
						scrollTop: animeOffset
					}, 800);
				});

			});
		}

		$(".dropdown-menu li a").click(function(){
			event.preventDefault();
			var selText = $(this).text();
			$(this).parents('.btn-group').find('.dropdown-toggle').html(selText+' <span class="caret"></span>');
		});

		function shippingHelper() {
			var tableWrapper = $('.tableWrapper'),
				tableCell = $('.tableWrapper .table-cell'),
				oldPrice = tableCell.find('.product_price').children('span'),
				oldPriceWidth = oldPrice.width() + 12;
			oldPrice.css({
				'margin-left': '-'+oldPriceWidth+'px'
			});
		}

		var itemProduct = $('.box_listing_product');
		itemProduct.on('mouseenter', '.like', function(event) {
			event.preventDefault();
			var textLike;
			if ($(this).hasClass('liked')) {
				textLike = 'Dodane do ulubionych';
			} else {
				textLike = 'Dodaj do ulubionych';
			}
			$(this).find('.toolLike').show().text(textLike);
		});
		itemProduct.on('mouseleave mouseup', '.like', function(event) {
			event.preventDefault();
			$(this).find('.toolLike').hide().text('');
		});
		itemProduct.on('mousedown', '.like', function(event) {
			event.preventDefault();
			$(this).find('img:visible').animate({transform: 'scale(1.2)'}, 200);
		});
		itemProduct.on('mouseup', '.like', function(event) {
			event.preventDefault();
			$(this).find('img:visible').animate({transform: 'scale(1.0)'}, 200)
		});
		itemProduct.on('mousedown', '.liked', function(event) {
			event.preventDefault();
			var textLike = 'Usunięte z ulubionych';
			$(this).find('.toolLike').show().text(textLike);
		});

		init();
		showSubMenuMobile();

		$(this).find(':disabled').next('.sbHolder').addClass('sbHolderDisabled');

		$('#collapseOne').collapse({'toggle': false});
		$('#collapseTwo').collapse({'toggle': false});
		$('#collapseThree').collapse({'toggle': false});

		$('.panel-collapse')
			.on('shown.bs.collapse', function () {
				jQuery(this).prev().find('i').toggleClass('bullet-strzalka-down bullet-strzalka-up');
			})
			.on('hidden.bs.collapse', function () {
				jQuery(this).prev().find('i').toggleClass('bullet-strzalka-down bullet-strzalka-up');
			});

		$('.toggle-xs').on('click', '.title_section', function(event) {

			var intFrameWidth = window.innerWidth;
			if(intFrameWidth < 768) {
				event.preventDefault();
				var self = $(this);
				self.closest('.section').find('.main, .rwdCarousel').slideToggle({
					complete: function() {
						self.closest('.section').attr('data-mobiletoggle', !$(this).closest('.section').data('mobiletoggle'));
						var i = self.find('i');
						var diff = $.now() - lastToggle;
						if(diff > 100) {
							i.toggleClass("bullet-strzalka-up bullet-strzalka-down")
							lastToggle = $.now();
						}
					}
				});
			}
		});

		jQuery.fn.resize_delayed = function ( func, duration ){
			this.resize(function() {
				clearTimeout( window.____resize_delayed );
				window.____resize_delayed = setTimeout( func, duration);
			});
		};

		$(window).on("resize", function() {

			var intFrameWidth = window.innerWidth;
			if($(window).width() != prevW) {
				if (intFrameWidth > 767) {
					$('.toggle-xs').each(function(index, el) {
						$(this).find('.main').show();
						$(this).find('#rwd-complementary-product').show();
					});
				} else {
					$(".toggle-xs[data-mobiletoggle='false']").find('.main').hide();
					$('.block-complementary-product.toggle-xs').find('.main').show();
				}
			}
			prevW = $(window).width();
			prevH = $(window).height();
		});

		$('.toggleMenu').click(function(event) {
			event.preventDefault();
			var screenHeight = $(window).height(),
				body = $('body'),
				htmlBody = $('html,body');
			body.addClass('sb-open');
			htmlBody.addClass('noscroll');
			$('#sb-site').addClass('open');
			$('.sb-slidebar').addClass('sb-active');
			body.append('<div class="noscroll" style="width:100%; height:'+screenHeight+'px"></div>');
			if(typeof Mall.listing != 'undefined') {
				Mall.listing.positionFilters();
			}
		});

		closeHamburgerMenu = function(event) {
			event.preventDefault();
			var body = $('body'),
				htmlBody = $('html,body');
			body.removeClass('sb-open');
			htmlBody.removeClass('noscroll');
			$('#sb-site').removeClass('open');
			$('.sb-slidebar').removeClass('sb-active').find('.sb-submenu-active').removeClass('sb-submenu-active');
			body.find('.noscroll').remove();
			if(typeof Mall.listing != 'undefined') {
				Mall.listing.positionFilters();
			}
		};

		$('.closeSlidebar').click(closeHamburgerMenu);

		 $(document).mouseup(function(e) {
			var container = $("body > .sb-slidebar");
			if(container.is(":visible") && !container.is(e.target) && container.has(e.target).length === 0){
				closeHamburgerMenu(e);
			}
		});


// Slidebars Submenus
		$('.sb-toggle-submenu').off('click').on('click', function() {
			$submenu = $(this).parent().children('.sb-submenu');
			$(this).add($submenu).toggleClass('sb-submenu-active'); // Toggle active class.

			if ($submenu.hasClass('sb-submenu-active')) {
				$submenu.slideDown(200);
			} else {
				$submenu.slideUp(200);
			}
		});


// HELPER MODAL LOGO SALLER
		$('#seller_description').on('shown.bs.modal', function (e) {
			var sallerlogo = $('.logo_salles').find('img');
			var sallerlogoHeight = sallerlogo.height();

			sallerlogo.css({
				'margin-top' : sallerlogoHeight+'px'
			});

		})


// RATY{ path: 'assets/images' }
		$('#average_rating').raty({
			path: Config.path.averageRating.averageRatingPath,
			starOff : Config.path.averageRating.averageRatingStarOff,
			starOn  : Config.path.averageRating.averageRatingStarOn,
			starHalf  : Config.path.averageRating.averageRatingStarHalf,
			size   : 22,
			readOnly: true,
			hints: ['zły', 'słaby', 'satysfakcjonujący', 'dobry', 'świetny'],
			half     : true,
			number: function() {
				return $(this).attr('data-number');
			},
			score: function() {
				return $(this).attr('data-score');
			}
		});

		$('.raty_note dd div').raty({
			path: Config.path.ratyNote.ratyNotePath,
			starOff : Config.path.ratyNote.ratyNoteStarOff,
			starOn  : Config.path.ratyNote.ratyNoteStarOn,
			hints: ['zły', 'słaby', 'satysfakcjonujący', 'dobry', 'świetny'],
			readOnly: true,
			size   : 17,

			number: function() {
				return $(this).attr('data-number');
			},
			score: function() {
				return $(this).attr('data-score');
			}
		});

		$('.ratings tr td div').raty({
			path: Config.path.ratings.ratingsPath,
			starOff : Config.path.ratings.ratingsStarOff,
			starOn  : Config.path.ratings.ratingsStarOn,
			hints: ['zły', 'słaby', 'satysfakcjonujący', 'dobry', 'świetny'],
			size   : 17,
			scoreName: function() {
				return $(this).attr("data-score-name");
			},

			number: function() {
				return $(this).attr('data-number');
			},
			score: function() {
				return $(this).attr('data-score');
			}
		});

		$('.comment_rating').raty({
			path: Config.path.commentRating.commentRatingPath,
			starOff : Config.path.commentRating.commentRatingStarOff,
			starOn  : Config.path.commentRating.commentRatingStarOn,
			size   : 17,
			readOnly: true,
			hints: ['zły', 'słaby', 'satysfakcjonujący', 'dobry', 'świetny'],
			number: function() {
				return $(this).attr('data-number');
			},
			score: function() {
				return $(this).attr('data-score');
			}
		});
		$('#average_note_client .note').raty({
			path: Config.path.averageNoteClient.averageNoteClientPath,
			starOff : Config.path.averageNoteClient.averageNoteClientStarOff,
			starOn  : Config.path.averageNoteClient.averageNoteClientStarOn,
			size   : 13,
			readOnly: true,
			hints: ['zły', 'słaby', 'satysfakcjonujący', 'dobry', 'świetny'],
			number: function() {
				return $(this).attr('data-number');
			},
			score: function() {
				return $(this).attr('data-score');
			}
		});

// RATING
		$('body').find('.rating').each(function(index, el) {
			var rating = $(this).data('percent');
			$(this).children('span').animate({width:rating+'%'}, 1000);

		});


// SCROLL
		$('.scrollTo').on('click', function(event) {
			event.preventDefault();
			var $a = $(this);
			var $b = $a.attr('href');
			var $c = $($b);
			var $d = parseInt($c.offset().top);
			var $e = parseInt($('header').height());
			var $f = $d - $e;

			$('html, body').animate({
				scrollTop: parseInt($f)
			}, 1000, "easeOutExpo");
		});

//FUNCTION & INIT

		function init(){
			recentlyViewed();               // POKAZ SLAJDÓW
			responsJcarousel();             // POKAZ SLAJDÓW
			// MENU MOBILE
			activeMenu();                   // ZAZNACZENIE AKTYWNEJ POZYCJI MENU
			cloneMenu();                    // CLONOWANIE MENU
			linkCloseMenu();                // ZAMKNIĘCIE SUBMENU
			closeMenu();                    // ZAMKNIĘCIE MENU
			filterPrice();
			filterStyleSelect();
			filterNoteClient();
			filterType();
			filterRecommendedProducts();
		}



		function filterRecommendedProducts() {
			var filterRecommendedProducts = $('#filter_recommended_products');
			filterRecommendedProducts.on('click', ':checkbox', function(event) {
				filterRecommendedProducts.find('.clear').removeClass('hidden');

				var filterRecommendedProductsLenght = filterRecommendedProducts.find(':checked').length;
				if (filterRecommendedProductsLenght >= 1) {
					filterRecommendedProducts.find('.action').removeClass('hidden');
				} else {
					filterRecommendedProducts.find('.action').addClass('hidden');
				}
			});
			filterRecommendedProducts.on('click', '.clear', function() {
				$(this).closest('.action').addClass('hidden');
			});
		}

		function filterType() {
			var filterType = $('.filter-type');
			filterType.on('click', ':checkbox', function() {
				filterType.find('.clear').removeClass('hidden');

				var filterTypeLenght = filterType.find(':checked').length;
				if (filterTypeLenght >= 1) {
					filterType.find('.action').removeClass('hidden');
				} else {
					filterType.find('.action').addClass('hidden');
				}
			});
			filterType.on('click', '.clear', function() {
				$(this).closest('.action').addClass('hidden');
			});

		}

		function filterNoteClient() {
			var filterNoteClient = $('#note_client');
			filterNoteClient.on('click', ':checkbox', function(event) {
				filterNoteClient.find('.clear').removeClass('hidden');

				var filterNoteClientLenght = filterNoteClient.find(':checked').length;
				if (filterNoteClientLenght >= 1) {
					filterNoteClient.find('.action').removeClass('hidden');
				} else {
					filterNoteClient.find('.action').addClass('hidden');
				};
			});
			filterNoteClient.on('click', '.clear', function(event) {
				$(this).closest('.action').addClass('hidden');
			});
		}

		function filterStyleSelect() {
			var filterStyleSelect = $('#filter_style.select');
			var filterStyleCheckbox = $('#filter_style.checkbox');
			filterStyleSelect.on('change', 'select', function(event) {

				var filterStyleSelectVal = $(this).val();
				if (filterStyleSelectVal !='' || filterStyleSelectVal != 'undefined') {
					filterStyleSelect.find('.clear').removeClass('hidden');
				} else {
					filterStyleSelect.find('.clear').addClass('hidden');
				}
			});

			filterStyleCheckbox.on('click', ':checkbox', function(event) {
				var filterStyleCheckboxLenght = filterStyleCheckbox.find(':checked').length;
				if (filterStyleCheckboxLenght >= 1) {
					filterStyleCheckbox.find('.action.clear').removeClass('hidden');
				} else {
					filterStyleCheckbox.find('.action.clear').addClass('hidden');
				}
			});

			filterStyleSelect.on('click', '.clear', function(event) {
				filterStyleSelect.find('select').val('');
				filterStyleSelect.find('.sbSelector').text('');
				$(this).addClass('hidden');

			});
			filterStyleCheckbox.on('click', '.clear', function(event) {
				$(this).closest('.action').addClass('hidden');

			});

		}

		function filterPrice() {
			var filterPrice = $('#filter_price');

			filterPrice.on('click', ':checkbox', function(event) {
				filterPrice.find('.clear').removeClass('hidden');

				var filterPriceLenght = $('#filter_price').find(':checked').length;
				if (filterPriceLenght >= 1) {
					filterPrice.find('.action').removeClass('hidden');
				} else {
					filterPrice.find('.action').addClass('hidden');
				}
			});

			filterPrice.on('keyup', '#zakres_min, #zakres_max', function(event) {
				var filterPriceValueMin = filterPrice.find('#zakres_min').val();
				var filterPriceValueMax = filterPrice.find('#zakres_max').val();
				if (filterPriceValueMin.length >=1 || filterPriceValueMax.length >=1 ) {
					filterPrice.find('.action').removeClass('hidden');
				} else {
					filterPrice.find('.action').addClass('hidden');
				}
			});

			filterPrice.on('click', '.clear', function(event) {
				$(this).closest('.action').addClass('hidden');
			});

		}

// MENU MOBILE

		function showSubMenuMobile(){
			var mobileMenu = $('#nav_mobile > li > a,#shop_nav_mobile > li > a');

			//aby w navigation mozna bylo dac klikalnego linka
			//wystarczy dodac do anchor'a class="clickable"
			mobileMenu = $(mobileMenu).filter(function( index ) {
				return !$(this ).hasClass('clickable');
			});

			mobileMenu.on('click', function(event) {
				event.preventDefault();
				//$(this).closest(mobileMenu).find('.open').removeClass('open');
				var ico = $(this).find('i');
				if (ico.hasClass('fa-chevron-down') || ico.hasClass('fa-chevron-up')) {
					$(this).find('i').toggleClass( 'fa-chevron-down fa-chevron-up' );
				}


				$(this).next('ul').toggleClass('open');
				if($(this).closest(mobileMenu).find('.open').length > 1) {
					$(this).closest(mobileMenu).find('.open').removeClass('open');
					$(this).next('ul').toggleClass('open');
				}

			});


		}

// CLONOWANIE MENU

		function cloneMenu() {
			var navSubClone = $('#nav_desc');
			var containerCloneMenu = $('#clone_submenu .container-fluid');
			if (navSubClone.css('visibility') == 'visible') {
				navSubClone.on('click', 'a', function(event) {
					//aby w navigation mozna bylo dac klikalnego linka
					//wystarczy dodac do anchor'a class="clickable"
					if(!$(this).hasClass('clickable')) {
						event.preventDefault();
						if ($(this).hasClass('children')) {
							containerCloneMenu.html('');
							$(this).next('ul').clone().appendTo(containerCloneMenu).css('width', '100%').slideDown(300);
						} else {
							containerCloneMenu.html('');
						}
						if(jQuery('body').hasClass('filter-sidebar')) {
							Mall.listing.positionFilters();
						}
					}

				});
			}
		}

// ZAZNACZENIE AKTYWNEJ POZYCJI MENU
		function activeMenu() {
			$('#nav_desc').on('click', 'a', function(event) {
				if(!$(this).hasClass('clickable')){
					event.preventDefault();
					$(this).closest('#nav_desc').find('.active').removeClass('active');
					$(this).closest('#nav_desc').find('.fa-caret-up').removeClass('fa-caret-up');
					$(this).find('i').addClass('fa-caret-up');
					$(this).addClass('active');
				}

			});
		}

// ZAMKNIĘCIE SUBMENU
		function closeMenu() {
			var containerCloneMenu = $('#clone_submenu .container-fluid');
			containerCloneMenu.on('click', '.closeSubMenu', function(event) {
				event.preventDefault();
				$(this).closest('ul').remove();
				$('#nav_desc').find('.fa-caret-up').removeClass('fa-caret-up');
				$("html, body").animate({ scrollTop: 0 }, "slow");
                $('#nav_desc a').removeClass('active');
				if(jQuery('body').hasClass('filter-sidebar')) {
					Mall.listing.positionFilters();
				}
			});
		}
		function linkCloseMenu() {
			var containerCloneMenu = $('#nav_desc');
			containerCloneMenu.on('click', '.active', function(event) {
				event.preventDefault();
				$('#clone_submenu').find('ul').remove();
				$('#nav_desc').find('.active').removeClass('active');
				$('#nav_desc').find('.fa-caret-up').removeClass('fa-caret-up');
				$("html, body").animate({ scrollTop: 0 }, "slow");
				if(jQuery('body').hasClass('filter-sidebar')) {
					Mall.listing.positionFilters();
				}
			});
		}

// POKAZY SLAJDÓW
		var rwd_banners = $("#rwd-banners .rwd-carousel");

		rwd_banners.rwdCarousel({
			items : 3, //10 items above 1000px browser width
			itemsDesktop : [1000,3], //5 items between 1000px and 901px
			itemsDesktopSmall : [900,2], // betweem 900px and 601px
			itemsTablet: [600,2], //2 items between 600 and 0
			itemsMobile : [480,1], // itemsMobile disabled - inherit from itemsTablet option
			pagination : false,
			itemsScaleUp:true,
			rewindNav : false,
			navigation: true,
			navigationText: [
				"<i class='fa fa-chevron-left'></i>",
				"<i class='fa fa-chevron-right'></i>"
			]
		});

		// Custom Navigation Events
		var rwdBanners = $("#rwd-banners");
		rwdBanners.find(".next").click(function(){
			rwd_banners.trigger('rwd.next');
		});
		rwdBanners.find(".prev").click(function(){
			rwd_banners.trigger('rwd.prev');
		});
		rwdBanners.find(".play").click(function(){
			rwd_banners.trigger('rwd.play',1000); //rwd.play event accept autoPlay speed as second parameter
		});
		rwdBanners.find(".stop").click(function(){
			rwd_banners.trigger('rwd.stop');
		});

		var rwd_inspiration = $("#rwd-inspiration .rwd-carousel");

		rwd_inspiration.rwdCarousel({
			items : 5, //10 items above 1000px browser width
			itemsDesktop : [1000,4], //5 items between 1000px and 901px
			itemsDesktopSmall : [900,3], // betweem 900px and 601px
			itemsTablet: [600,3], //2 items between 600 and 0
			itemsMobile : [480,1], // itemsMobile disabled - inherit from itemsTablet option
			pagination : false,
			navigation: true,
			navigationText: ['<i class="fa fa-chevron-left"></i>','<i class="fa fa-chevron-right"></i>'],
			rewindNav : false,
			itemsScaleUp:true
		});

		// Custom Navigation Events
		var rwdInspiration = $("#rwd-inspiration");
		rwdInspiration.find(".next").click(function(){
			rwd_inspiration.trigger('rwd.next');
		});
		rwdInspiration.find(".prev").click(function(){
			rwd_inspiration.trigger('rwd.prev');
		});
		rwdInspiration.find(".play").click(function(){
			rwd_inspiration.trigger('rwd.play',1000); //rwd.play event accept autoPlay speed as second parameter
		});
		rwdInspiration.find(".stop").click(function(){
			rwd_inspiration.trigger('rwd.stop');
		});

		var rwd_complementary_product = $("#rwd-complementary-product .rwd-carousel");

		rwd_complementary_product.rwdCarousel({
			items : 5, //10 items above 1000px browser width
			itemsDesktop : [1000,4], //5 items between 1000px and 901px
			itemsDesktopSmall : [900,3], // betweem 900px and 601px
			itemsTablet: [600,3], //2 items between 600 and 0
			itemsMobile : [480,3], // itemsMobile disabled - inherit from itemsTablet option
			pagination : false,
			navigation: true,
			rewindNav : false,
			itemsScaleUp:false,
			afterUpdate: function(){
				var imgHeight = rwd_complementary_product.find('img').height()/2;
				var imgHeightplus = rwd_complementary_product.find('img').height()/2-20;
				rwd_complementary_product.next('.customNavigation').find('.prev').css({top:imgHeight+'px'});
				rwd_complementary_product.next('.customNavigation').find('.next').css({top:imgHeight+'px'});
				rwd_complementary_product.find('.rwd-controls').find('.rwd-prev').css({top:imgHeightplus+'px'});
				rwd_complementary_product.find('.rwd-controls').find('.rwd-next').css({top:imgHeightplus+'px'});

				Mall.rwdCarousel.alignComplementaryProductsPrices(this);
			},
			afterInit:function(){
				imagesLoaded( document.querySelector('#rwd-complementary-product'), function( instance ) {
					var imgHeight = rwd_complementary_product.find('img').height()/2;
					var imgHeightplus = rwd_complementary_product.find('img').height()/2-20;
					rwd_complementary_product.next('.customNavigation').find('.prev').css({top:imgHeight+'px'});
					rwd_complementary_product.next('.customNavigation').find('.next').css({top:imgHeight+'px'});
					rwd_complementary_product.find('.rwd-controls').find('.rwd-prev').css({top:imgHeightplus+'px'});
					rwd_complementary_product.find('.rwd-controls').find('.rwd-next').css({top:imgHeightplus+'px'});
				});
				Mall.rwdCarousel.alignComplementaryProductsPrices(this);
			}
		});

		function responsJcarousel() {
			var jcarousel = $('#complementary_product .jcarousel');
			jcarousel
				.on('jcarousel:reload jcarousel:create', function () {})
				.jcarousel({
					wrap: 'circular',
					visible: 6
				});

			$('.jcarousel-control-prev')
				.jcarouselControl({
					target: '-=1'
				});

			$('.jcarousel-control-next')
				.jcarouselControl({
					target: '+=1'
				});
		}

		function recentlyViewed() {
			$('.recently-viewed .rv').jcarousel({
				wrap: 'circular',
				visible:5
			});

			$('.rv-control-prev')
				.on('jcarouselcontrol:active', function() {
					$(this).removeClass('inactive');
				})
				.on('jcarouselcontrol:inactive', function() {
					$(this).addClass('inactive');
				})
				.jcarouselControl({

					target: '-=1'
				});

			$('.rv-control-next')
				.on('jcarouselcontrol:active', function() {
					$(this).removeClass('inactive');
				})
				.on('jcarouselcontrol:inactive', function() {
					$(this).addClass('inactive');
				})
				.jcarouselControl({
					target: '+=1'
				});

			$('.rv-pagination')
				.on('jcarouselpagination:active', 'a', function() {
					$(this).addClass('active');
				})
				.on('jcarouselpagination:inactive', 'a', function() {
					$(this).removeClass('active');
				})
				.jcarouselPagination();
		}



		/* =============================== CAROUSEL GALLERY Product ================================= */
		var connector = function(itemNavigation, carouselStage) {
			return carouselStage.jcarousel('items').eq(itemNavigation.index());
		};


		// Setup the carousels. Adjust the options for both carousels here.
		var carouselStage = $('.carousel-stage').jcarousel();
		var carouselNavigation = $('.carousel-navigation').jcarousel();

		// We loop through the items of the navigation carousel and set it up
		// as a control for an item from the stage carousel.
		carouselNavigation.jcarousel('items').each(function() {
			var item = $(this);
			//item.append('<div class="shadow"></div>')
			// This is where we actually connect to items.
			var target = connector(item, carouselStage);

			item
				.on('jcarouselcontrol:active', function() {
					carouselNavigation.jcarousel('scrollIntoView', this);
					item.addClass('active');
				})
				.on('jcarouselcontrol:inactive', function() {
					item.removeClass('active');
				})
				.jcarouselControl({
					target: target,
					carousel: carouselStage
				});


			// Setup controls for the stage carousel
			$('.prev-stage')
				.on('jcarouselcontrol:inactive', function() {
					$(this).addClass('inactive');
				})
				.on('jcarouselcontrol:active', function() {
					$(this).removeClass('inactive');
				})
				.jcarouselControl({
					target: '-=1'
				});

			$('.next-stage')
				.on('jcarouselcontrol:inactive', function() {
					$(this).addClass('inactive');
				})
				.on('jcarouselcontrol:active', function() {
					$(this).removeClass('inactive');
				})
				.jcarouselControl({
					target: '+=1'
				});

			// Setup controls for the navigation carousel
			$('.prev-navigation')
				.on('jcarouselcontrol:inactive', function() {
					$(this).addClass('inactive');
				})
				.on('jcarouselcontrol:active', function() {
					$(this).removeClass('inactive');
				})
				.jcarouselControl({
					target: '-=1'
				});

			$('.next-navigation')
				.on('jcarouselcontrol:inactive', function() {
					$(this).addClass('inactive');
				})
				.on('jcarouselcontrol:active', function() {
					$(this).removeClass('inactive');
				})
				.jcarouselControl({
					target: '+=1'
				});
			$('.jcarousel-pagination')
				.on('jcarouselpagination:active', 'a', function() {
					$(this).addClass('active');
				})
				.on('jcarouselpagination:inactive', 'a', function() {
					$(this).removeClass('active');
				})
				.jcarouselPagination();
		});
		/* =============================== END::// CAROUSEL GALLERY Product ================================= */
		var connector = function(itemNavigation, carouselStage) {
			return carouselStage.jcarousel('items').eq(itemNavigation.index());
		};
		var zoomGallery = $('#zoom_gallery');
		carouselStage = zoomGallery.find('.carousel-stage').jcarousel();
		carouselNavigation = zoomGallery.find('.carousel-navigation').jcarousel();

		carouselStage.on('jcarousel:reload jcarousel:create', function () {
			var width = carouselStage.innerWidth();
			carouselStage.jcarousel('items').css('width', width + 'px');
		});

		// We loop through the items of the navigation carousel and set it up
		// as a control for an item from the stage carousel.
		carouselNavigation.jcarousel('items').each(function() {
			var item = $(this);
			var target = connector(item, carouselStage);

			item
				.on('jcarouselcontrol:active', function() {
					carouselNavigation.jcarousel('scrollIntoView', this);
					item.addClass('active');
				})
				.on('jcarouselcontrol:inactive', function() {
					item.removeClass('active');
				})
				.jcarouselControl({
					target: target,
					carousel: carouselStage
				});


			// Setup controls for the stage carousel
			zoomGallery.find('.prev-stage')
				.on('jcarouselcontrol:inactive', function() {
					$(this).addClass('inactive');
				})
				.on('jcarouselcontrol:active', function() {
					$(this).removeClass('inactive');
				})
				.jcarouselControl({
					target: '-=1'
				});

			zoomGallery.find('.next-stage')
				.on('jcarouselcontrol:inactive', function() {
					$(this).addClass('inactive');
				})
				.on('jcarouselcontrol:active', function() {
					$(this).removeClass('inactive');
				})
				.jcarouselControl({
					target: '+=1'
				});

			$(".carousel-stage").touchwipe({
				wipeLeft: function() {
					$(".carousel-stage").jcarousel('scroll', '+=1');
					alert("right");
				},
				wipeRight: function() {
					$(".carousel-stage").jcarousel('scroll', '-=1');
					alert("left");
				},
				min_move_x: 100,
				min_move_y: 100,
				preventDefaultEvents: false

			});

			// Setup controls for the navigation carousel
			zoomGallery.find('.prev-navigation')
				.on('jcarouselcontrol:inactive', function() {
					$(this).addClass('inactive');
				})
				.on('jcarouselcontrol:active', function() {
					$(this).removeClass('inactive');
				})
				.jcarouselControl({
					target: '-=1'
				});

			zoomGallery.find('.next-navigation')
				.on('jcarouselcontrol:inactive', function() {
					$(this).addClass('inactive');
				})
				.on('jcarouselcontrol:active', function() {
					$(this).removeClass('inactive');
				})
				.jcarouselControl({
					target: '+=1'
				});
			zoomGallery.find('.jcarousel-pagination')
				.on('jcarouselpagination:active', 'a', function() {
					$(this).addClass('active');
				})
				.on('jcarouselpagination:inactive', 'a', function() {
					$(this).removeClass('active');
				})
				.jcarouselPagination();
		});


		/* =============================== CAROUSEL GALLERY Product MODAL ================================= */

		connector = function(itemNavigation, carouselStage) {
			return carouselStage.jcarousel('items').eq(itemNavigation.index());
		};
		$(function() {
			// Setup the carousels. Adjust the options for both carousels here.
			var carouselStage      = $('.carousel-stage-modd').jcarousel();
			var carouselNavigation = $('.carousel-navigation-modd').jcarousel();

			// We loop through the items of the navigation carousel and set it up
			// as a control for an item from the stage carousel.
			carouselNavigation.jcarousel('items').each(function() {
				var item = $(this);

				// This is where we actually connect to items.
				var target = connector(item, carouselStage);

				item
					.on('jcarouselcontrol:active', function() {
						carouselNavigation.jcarousel('scrollIntoView', this);
						item.addClass('active');
					})
					.on('jcarouselcontrol:inactive', function() {
						item.removeClass('active');
					})
					.jcarouselControl({
						target: target,
						carousel: carouselStage
					});
			});

			// Setup controls for the stage carousel
			$('.prev-stage-modd')
				.on('jcarouselcontrol:inactive', function() {
					$(this).addClass('inactive');
				})
				.on('jcarouselcontrol:active', function() {
					$(this).removeClass('inactive');
				})
				.jcarouselControl({
					target: '-=1'
				});

			$('.next-stage-modd')
				.on('jcarouselcontrol:inactive', function() {
					$(this).addClass('inactive');
				})
				.on('jcarouselcontrol:active', function() {
					$(this).removeClass('inactive');
				})
				.jcarouselControl({
					target: '+=1'
				});

			// Setup controls for the navigation carousel
			$('.prev-navigation-modd')
				.on('jcarouselcontrol:inactive', function() {
					$(this).addClass('inactive');
				})
				.on('jcarouselcontrol:active', function() {
					$(this).removeClass('inactive');
				})
				.jcarouselControl({
					target: '-=1'
				});

			$('.next-navigation-modd')
				.on('jcarouselcontrol:inactive', function() {
					$(this).addClass('inactive');
				})
				.on('jcarouselcontrol:active', function() {
					$(this).removeClass('inactive');
				})
				.jcarouselControl({
					target: '+=1'
				});
		});
		/* =============================== END::// CAROUSEL GALLERY Product MODAL ================================= */

	});
})(jQuery);