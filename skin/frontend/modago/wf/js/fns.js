var prevW = -1, prevH = -1, lastToggle = 0;
jQuery.noConflict();
(function( $ ) {
	$(function() {

		function addFormSpinner(form) {
			var submitButton = form.find('button[type=submit]');
			submitButton.prop("disabled", true);
			submitButton.find('i').addClass('fa fa-spinner fa-spin');
		}

		$('#question-form-mobile,#question-form,#review-form').submit(function () {
			if ($(this).valid()) {
				addFormSpinner($(this));
			}
		});

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
		if ($('.hint').not('input').tooltip) {

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
		}

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
		if ($('#podsumowanie_popup').modal) {
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
		
		}
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

		$('.sidebar-second').on('click', 'h2.open', function(){
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

					var sidebarSecoundHeader = $('.sidebar-second').find('header');
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
		var kolumnaPrawa =  $("div.col-01").outerHeight();
		var kolumnaLewa = $("div.col-02").outerHeight();
		if (widthWindow >= 768) {
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
			var kolumnaPrawa =  col01.outerHeight();
			var kolumnaLewa = col02.outerHeight();
			if (widthWindow >= 768) {
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

		openFormReview();
        shippingHelper();

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

		init();
		showSubMenuMobile();

		$(this).find(':disabled').next('.sbHolder').addClass('sbHolderDisabled');

		if ($('#collapseOne').collapse) $('#collapseOne').collapse({'toggle': false});
		if ($('#collapseTwo').collapse) $('#collapseTwo').collapse({'toggle': false});
		if ($('#collapseThree').collapse) $('#collapseThree').collapse({'toggle': false});

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
            jQuery('#link_menu').toggleClass('not-open');
			body.addClass('sb-open');
			htmlBody.addClass('noscroll');
			$('#sb-site').addClass('open');
			$('.sb-slidebar').addClass('sb-active');
			body.append('<div class="noscroll closeHamburgerMenu" style="width:100%; height:'+screenHeight+'px"></div>');
			if(typeof Mall.listing != 'undefined') {
				Mall.listing.positionFilters();
			}
			jQuery('.closeHamburgerMenu').click(closeHamburgerMenu);
			jQuery(window).swipe(Mall.swipeOptions);
		});

		closeHamburgerMenu = function(event) {
			event.preventDefault();
			var body = $('body'),
				htmlBody = $('html,body');
			body.removeClass('sb-open');
			htmlBody.removeClass('noscroll');
			$('#sb-site').removeClass('open');
			$('.sb-slidebar').removeClass('sb-active').find('.sb-submenu-active').removeClass('sb-submenu-active');
			jQuery('.closeHamburgerMenu').off('click');
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

		$('.sb-toggle-submenu').on('click', function() {
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
					$(this).find('i').toggleClass('fa-angle-up fa-angle-down');
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

		var rwd_complementary_product = $("#rwd-complementary-product .rwd-carousel");

		rwd_complementary_product.rwdCarousel({
			items : 5, //10 items above 1000px browser width
			itemsDesktop : [1000,4], //5 items between 1000px and 901px
			itemsDesktopSmall : [900,3], // betweem 900px and 601px
			itemsTablet: [600,3], //2 items between 600 and 0
			itemsMobile : [480,2], // itemsMobile disabled - inherit from itemsTablet option
			pagination : false,
			navigation: true,
			rewindNav : false,
			itemsScaleUp:false,
			navigationText: ['<div class="owl-arrow owl-prev"></div>','<div class="owl-arrow owl-next"></div>'],
			//afterUpdate: function(){
			//	var imgHeight = rwd_complementary_product.find('img').height()/2;
			//	var imgHeightplus = rwd_complementary_product.find('img').height()/2-20;
			//	rwd_complementary_product.next('.customNavigation').find('.prev').css({top:imgHeight+'px'});
			//	rwd_complementary_product.next('.customNavigation').find('.next').css({top:imgHeight+'px'});
			//	rwd_complementary_product.find('.rwd-controls').find('.rwd-prev').css({top:imgHeightplus+'px'});
			//	rwd_complementary_product.find('.rwd-controls').find('.rwd-next').css({top:imgHeightplus+'px'});
            //
			//	Mall.rwdCarousel.alignComplementaryProductsPrices(this);
			//},
			//afterInit:function(){
			//	imagesLoaded( document.querySelector('#rwd-complementary-product'), function( instance ) {
			//		var imgHeight = rwd_complementary_product.find('img').height()/2;
			//		var imgHeightplus = rwd_complementary_product.find('img').height()/2-20;
			//		rwd_complementary_product.next('.customNavigation').find('.prev').css({top:imgHeight+'px'});
			//		rwd_complementary_product.next('.customNavigation').find('.next').css({top:imgHeight+'px'});
			//		rwd_complementary_product.find('.rwd-controls').find('.rwd-prev').css({top:imgHeightplus+'px'});
			//		rwd_complementary_product.find('.rwd-controls').find('.rwd-next').css({top:imgHeightplus+'px'});
			//	});
			//	Mall.rwdCarousel.alignComplementaryProductsPrices(this);
			//}
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

	});
})(jQuery);