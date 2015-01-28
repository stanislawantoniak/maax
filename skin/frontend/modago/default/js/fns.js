var prevW = -1, prevH = -1, lastToggle;
jQuery.noConflict();
(function( $ ) {
    $(function() {
        // Validate Raty
        //appearance_rating
        //$('#appearance_rating').find('input').attr('required', true);
        $('#review-form').on('click', 'input[type="text"], textarea', function(){

            var valueH = $(".review-summary-table").find('input[type="hidden"]').val();
            var valid = false;
            $(".review-summary-table").find('input[type="hidden"]').each(function(){
                if($(this).val() == '')
                {
                    $('#review-form').find('.review-summary-table').find('.error.hidden').removeClass('hidden');
                    return valid = false;
                } else {
                    $('#review-form').find('.review-summary-table').find('.error').addClass('hidden');
                    return valid = true;
                }
            });
            return valid;
            //var emptyValue = $(".review-summary-table.ratings").find('input[value='']').length
            console.log(valueH)
        });
        $(".review-summary-table").on('click', 'img', function(){
            jQuery("#stars").valid();
            var valid = false;
            $(".review-summary-table").find('input[type="hidden"]').each(function(){
                if($(this).val() == '')
                {
                    $('#review-form').find('.review-summary-table').find('.error.hidden').removeClass('hidden');
                    return valid = false;
                } else {
                    $('#review-form').find('.review-summary-table').find('.error').addClass('hidden');
                    return valid = true;
                }
            });
            return valid;
        });

        /////////////////////////////////////// Validator Form ////////////////////////////////////////////



        /////////////////////////////////////////
        //jQuery.validator.addMethod("zipcode", function(value, element) {
        //    return this.optional(element) || /^[0-9]{2}(-[0-9]{3})?$/.test(value);
        //}, "Please provide a valid zipcode.");
        //jQuery.validator.addMethod("nip", function(value, element) {
        //    return this.optional(element) || /^((\d{3}[- ]\d{3}[- ]\d{2}[- ]\d{2})|(\d{3}[- ]\d{2}[- ]\d{2}[- ]\d{3}))$/.test(value);
        //}, "Please provide a valid nip.");
        jQuery.validator.addMethod("stars", function(value, element) {
            var valid = false;
            $(".review-summary-table").find('input[type="hidden"]').each(function(){
                if($(this).val() == '')
                {
                    $('#review-form').find('.review-summary-table').find('.error.hidden').removeClass('hidden');
                    return valid = false;
                } else {
                    $('#review-form').find('.review-summary-table').find('.error').addClass('hidden');
                    return valid = true;
                }
            });
            return valid;
        }, "");
        $("#review-form, #question-form-mobile").each(function () {

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
                highlight: function(element, errorClass, validClass) {
                    var we = $(element).innerWidth()+25;
                    var el = $(element).attr('type');
                    $(element).closest("div").addClass('has-error has-feedback').removeClass('has-success');
                    $(element).closest("div").find('.form-ico-times').remove();

                    $(element).closest("div").not( ".form-checkbox" ).not( ".form-radio" ).append('<i style="left:'+we+'px; right:auto" class="form-ico-times form-control-feedback "></i>');

                    $(element).closest("div").find('.form-ico-checked').remove();
                },
                unhighlight: function(element, errorClass, validClass) {
                    var we = $(element).innerWidth()+25;
                    $(element).closest("div").removeClass('has-error').addClass('has-success has-feedback');
                    $(element).closest("div").find('.form-ico-checked').remove();
                    //if (element.attr("type") != "checkbox"){

                    $(element).closest("div").append('<i style="left:'+we+'px; right:auto" class="form-ico-checked form-control-feedback"></i>');
                    //}
                    $(element).closest("div").find('.form-ico-times').remove();
                },
                errorPlacement: function(error, element) {
                    if (element.attr("type") == "checkbox" ){
                        $(element).closest('div').append(error)
                        //error.prepend(element).hide().slideToggle(300);
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
            submitButton.prop("disabled", "disabled");
            submitButton.find('i').addClass('fa fa-spinner fa-spin');
        }
        $('.table-footer-group').on('click', '.deliver_info', function(e){
            var _w = $(window).innerWidth();
            if (_w <=767) {
                e.preventDefault();
                $(this).closest('.table-footer-group').find('.conditions_shipping').toggle(50);
            }
        });
        $('.table-footer-group').on('click', '.trigger_conditions_shipping', function(e){
            var _w = $(window).innerWidth();
            if (_w >=768) {
                e.preventDefault();
                var html = $(this)
                    .closest('.table-footer-group')
                    .next('.table-footer-group')
                    .find('.conditions_shipping').html();
                console.log(html);
            } else {
                var html = $(this)
                    .closest('.table-row')
                    .next('.conditions_shipping')
                    .html();
            }
            $('#trigger_conditions_shipping').modal('show');
            $('#trigger_conditions_shipping').on('shown.bs.modal', function (e) {
                $('#trigger_conditions_shipping').find('.modal-body').html('');
                $('#trigger_conditions_shipping').find('.modal-body').append('<div class="panel panel-default">' + html + '</div>')
            })

        });
        $('#trigger_conditions_shipping').on('hide.bs.modal', function (e) {
            $('#trigger_conditions_shipping').find('.modal-body').html('');
        });



        $(window).on('load resize', function(){
            var link = $('.forgot-password');
            $.each( link, function( key, value ) {
                var p = $( "#pass" );
                var pw = p.innerWidth();
                var offset = p.offset();

                var position = parseInt(pw - 108);
                $(this).css({
                    left:position+'px'
                });
            });
        });






        $('.hint').not('input').tooltip({
            placement: function(a, element) {
                var position = $(element).parent().position();
                var viewport = $(window).innerWidth();

                if (viewport > 768) {
                    return "right";
                }
                if (viewport <= 767) {
                    return "top";
                }
                return "right";
            }
        })



        $.ajaxSetup ({
            // Disable caching of AJAX responses
            cache: false
        });


        $(document).ajaxComplete(function(event, xhr, settings) {
            dropDownSelectListAjax();

//            var scroll = $('body').find('.mCustomScrollbar');
//            if (scroll.length >= 1) {
//                scroll.mCustomScrollbar({
//                    setHeight:100,
//                    theme:"dark-thick",
//                    scrollButtons:{
//                        enable:true
//                    }
//                });
//            };
//          var sliderRange = $( "#slider-range" );
//          if (sliderRange.length >= 1) {
//          $( "#slider-range" ).slider({
//                  range: true,
//                  min: 0,
//                  max: 500,
//                  values: [ 75, 300 ],
//                  slide: function(event, ui) {
//                      $("#zakres_min").val(ui.values[0]);
//                      $("#zakres_max").val(ui.values[1]);
//
//                  }
//
//
//                });
//
//               $("#zakres_min").val($("#slider-range").slider("values", 0));
//               $("#zakres_max").val($("#slider-range").slider("values", 1));
//               $('#slider-range').on('click', 'a', function(event) {
//                  var checkSlider = $('#checkSlider').find('input');
//                  if (!checkSlider.is(':checked')) {
//                    checkSlider.prop('checked', true);
//                    $('#filter_price').find('.action').removeClass('hidden');
//                  }
//                });
//
//
//          };
        });






        /*
         var zipcode = ['#zip_code'];
         $.each(zipcode, function(index, value) {
         var value = $(value);
         value.keydown(function(event) {
         if (value.val().length == 2) {
         event.target.value = event.target.value + "-";
         } else if (value.val().length > 5 && (event.keyCode != 46) && event.keyCode != 8 && event.keyCode != 9 && event.keyCode != 27 && event.keyCode != 13 && !(event.keyCode >= 35 && event.keyCode <= 39)) {
         return false;
         } else if (value.val().length > 2) {
         // Jesli wieksze niz 2, ale nie ma myslnikato dostawiamy - lag fix
         if (value.val().split('-').length == 1) {
         value.val(value.val().substring(0, 2) + '-' + value.val().substring(2, 5));
         }
         }
         // Allow: backspace, delete, tab, escape, and enter
         if (event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 27 || event.keyCode == 13 ||
         // Allow: Ctrl+A
         (event.keyCode == 65 && event.ctrlKey === true) ||
         // Allow: home, end, left, right
         (event.keyCode >= 35 && event.keyCode <= 39)) {
         return;
         }
         else {
         // Ensure that it is a number and stop the keypress
         if (event.shiftKey || (event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105 )) {
         event.preventDefault();
         }
         }
         });
         });

         var vatzipcode = ['#vat_zip_code'];
         $.each(vatzipcode, function(index, value) {
         var value = $(value);
         value.keydown(function(event) {
         if (value.val().length == 2) {
         event.target.value = event.target.value + "-";
         } else if (value.val().length > 5 && (event.keyCode != 46) && event.keyCode != 8 && event.keyCode != 9 && event.keyCode != 27 && event.keyCode != 13 && !(event.keyCode >= 35 && event.keyCode <= 39)) {
         return false;
         } else if (value.val().length > 2) {
         // Jesli wieksze niz 2, ale nie ma myslnikato dostawiamy - lag fix
         if (value.val().split('-').length == 1) {
         value.val(value.val().substring(0, 2) + '-' + value.val().substring(2, 5));
         }
         }
         // Allow: backspace, delete, tab, escape, and enter
         if (event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 27 || event.keyCode == 13 ||
         // Allow: Ctrl+A
         (event.keyCode == 65 && event.ctrlKey === true) ||
         // Allow: home, end, left, right
         (event.keyCode >= 35 && event.keyCode <= 39)) {
         return;
         }
         else {
         // Ensure that it is a number and stop the keypress
         if (event.shiftKey || (event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105 )) {
         event.preventDefault();
         }
         }
         });
         });



         ////////////////////////////////////////////////////////////////////



         // assumes a print function is defined
         var anyString = "8761058000";

         // Displays
         console.log(anyString.substring(0,3));
         console.log(anyString.substring(3,6));
         console.log(anyString.substring(6,8));
         console.log(anyString.substring(8,10));

         */



        /* ===================== invoice data ================== */

        $('#invoice_vat').on('click', function(){

            var firm_name = $('#firm_name').val(),
                street = $('#street').val(),
                zip_code = $('#zip_code').val(),
                city = $('#city').val();

            //console.log (firm_name +' |'+ street +' |'+ zip_code +' |'+ city)

            var invoice_data_firm_name = '',
                invoice_data_street = '',
                invoice_data_zip_code = '',
                invoice_data_city = '';

            if(this.checked) {
                $('#invoice_data').css({display:'block'});
                $('#invoice_data_firm_name').val(firm_name)
                $('#invoice_data_street').val(street)
                $('#invoice_data_zip_code').val(zip_code)
                $('#invoice_data_city').val(city)
            } else {
                $('#invoice_data').css({display:'none'});
            }
        });

        /* ==================== KUPON RABATOWY =============== */
        $('.form_discount_voucher').hide();
//$('.coupon-list').hide();
        $('#discount_voucher').on('click', '.info_discount_voucher', function(e){
            e.preventDefault();
            $('.form_discount_voucher').show(50);
        });
        $('#discount_voucher').on('click', 'input[type="submit"]', function(e){
            e.preventDefault();
            $('.form_discount_voucher').hide(50);
            $('.coupon-list').show(50);

        });

        /* ===================== checkout ===================== */
        var widthWindow = $(window).width();
        if (widthWindow <= 767) {//91
            $('.podziekowanie_nieudane_view_summary').closest('.block_info_order').find('.adres_dostawy').hide();
            $('.podziekowanie_nieudane_view_summary').closest('.block_info_order').next('.table-summary-product').hide();
            $('.podziekowanie_nieudane_view_summary').closest('.panel-body').next('.panel-footer').hide();

        }


        $('.podziekowanie_nieudane_view_summary').on('click', function(e){
            e.preventDefault();
            var txt = $(this).closest('.block_info_order').next('.table-summary-product').is(':visible') ? 'Rozwiń szczegóły' : 'Zwiń szczegóły';
            var pf = $(this).closest('.panel-body').next('.panel-footer');
            $(this).children('span').text(txt);
            $(this).toggleClass('open');
            $(this).find('i').toggleClass('bullet-strzalka-up bullet-strzalka-down')
            $(this).closest('.block_info_order').find('.adres_dostawy').toggle();
            $(this).closest('.block_info_order').next('.table-summary-product').toggle();
            $(this).closest('.panel-body').next('.panel-footer').toggle();
            if(pf.is(':visible')) {
                //pf.css({display:'none'})
            }
        });



//
//        var invoice = $('#invoice input[type=checkbox]');
//        var block_invoice = $('#block_invoice');
//        if ($('#invoice_this').is(':checked')) {
//            block_invoice.show(50);
//        };
//
//        var change_address = $('.change_address');
//        change_address.on('click', function(e){
//            e.preventDefault();
//            $(this).closest('.panel').find('.panel-body').find('.panel').each(function(key, value){
//                $(this).toggle(50);
//            });
//            var txt = $(this).is('.open') ? 'zmień adres' : ' zwiń listę adresów';
//            $(this).text(txt).toggleClass('open');
//        });




        var devault_pay_bank = $('.default_pay input[name=pay_method]');
        /*devault_pay_bank.each(function(index){
         console.log(index);
         if (devault_pay_bank.prop('checked', true)) {
         $(this).closest('.panel').addClass('selected');
         };
         });*/

        $('body').find('.default_pay input[name=pay_method]:checked').closest('.panel').addClass('selected');


        $(devault_pay_bank).on('change', function(){
            $(devault_pay_bank).closest('.panel').removeClass('selected');
            $(this).closest('.panel').addClass('selected');
            $('.selected_bank').hide();
            $(this).closest('.form-group').next('.selected_bank').show();
        });
//        $('#invoice').on('change', 'input', function(){
//            block_invoice.toggle();
//        });

        //var view_default_pay = $('#view_default_pay');
        //var view_block_default_pay = $('.default_pay > .panel > .panel-body > .panel');
        //var form_group_default_pay = $('.form-group.default_pay');
        //view_default_pay.on('click', function(e){
        //    var txt = $(this).closest('.panel').children('.panel-body').find('.panel-default').is(':visible') ? 'zmień sposób płatności' : 'porzuć zmianę';
        //    e.preventDefault();
        //    view_block_default_pay.toggle();
        //    form_group_default_pay.closest('.row').css({marginBottom:'15px'});
        //    $(this).text(txt);
        //    //$(this).closest('.panel-footer').hide();
        //});

        var stara_cena = jQuery('.table-summary-product .cena .stara_cena');
        var stara_cena_szer = stara_cena.innerWidth();

//        stara_cena.css({
//            marginLeft:-stara_cena_szer+20
//        });


// panels


        var hide_panel = $('.node-type-summary-delivery-payment header h2');
        hide_panel.on('click',function(){
            //$(this).closest('header').toggleClass('hide_panel_group');
            //$(this).closest('header').next('.panel-group').toggle(50);
            //$(this).find('i').toggleClass('bullet-strzalka-up bullet-strzalka-down')
        });




        $('#podsumowanie_popup').modal('show');


        var _modal = $('[data-toggle="modal"]');
        _modal.each(function(){

            var  _modalTarget = $(this).data('target');


            $(_modalTarget).on('show.bs.modal', function (e) {
                var backdrop =  $('#sb-site').find('.modal-backdrop');
                if (backdrop.length == 0) {
                    $('#sb-site').append('<div class="modal-backdrop fade in"></div>');
                };

            });
            $(_modalTarget).on('shown.bs.modal', function (e) {
                $('html').find('body > .modal-backdrop').remove();
            });
            $(_modalTarget).on('hidden.bs.modal', function (e) {
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
            var txt = $(this).closest('.main').hasClass('opened-panel-group') ? 'bullet-strzalka-up' : 'bullet-strzalka-down';
            $(this).find('i').toggleClass('bullet-strzalka-down bullet-strzalka-up');
			
			if(!$(this).closest('.main').hasClass('opened-panel-group')){
				  $('#order-show-products').removeClass('hidden').addClass("visible-xs");
			}else{
				  $('#order-show-products').addClass('hidden').removeClass("visible-xs");
			}
          
            /*if ($('#order-show-products').is(':visible')) {
             $('#order-show-products').hide();
             $('#zmien_produkty').show(50);
             } else {
             $('#order-show-products').show();
             $('#zmien_produkty').hide(50);
             };*/
        });

        $('#zwin_produkty').on('click',function(){
            $(this).closest('.main').removeClass('opened-panel-group');
            $('#order-show-products').removeClass('hidden').addClass("visible-xs");
            //$(this).closest('.panel-group').hide(50);
            //$('#order-show-products').show();
			//console.log("Pokaz pozak");
            hide_panel.find('i').removeClass('bullet-strzalka-up').addClass('bullet-strzalka-down');
        });

        $('#pokaz_produkty').on('click',function(){
            $(this).closest('.main').toggleClass('opened-panel-group');
            $('#order-show-products').addClass('hidden').removeClass("visible-xs");
            //$('#summary-panel-product').show(50);
            //$('#order-show-products').hide();
			//console.log("Pokaz schowaj");
            hide_panel.find('i').removeClass('bullet-strzalka-down').addClass('bullet-strzalka-up');
        });

        $('.sidebar-secound').on('click', 'h2.open', function(){
            var ww = $(window).innerWidth();
            if(ww <=767) {
                $(this).closest('header').next('.panel-group').toggle(50);
                $(this).closest('header').toggleClass('open');
                var arrow = $(this).closest('header').hasClass('open') ? 'bullet-strzalka-down' : 'bullet-strzalka-up';
                $(this).find('i').toggleClass('bullet-strzalka-down bullet-strzalka-up')

            }
        });

        $(window).on('load', function() {
            var ww = $(window).innerWidth();
            if(ww <=767 && ww >=481) {
                $('#checkout.podsumowanie #content-main').find('.main').addClass('opened-panel-group');
                //$('#checkout.podsumowanie #content-main').find('#order-show-products').removeClass('hidden');
                $('#checkout.podsumowanie #content-main').find('.title-section').find('i').removeClass('bullet-strzalka-down').addClass('bullet-strzalka-up');
            } else {

            }
            if (ww >=768) {
                $('#checkout.podsumowanie #content-main').find('#order-show-products').addClass('hidden');
            }
        });

//$('#order-show-products').hide(50);
        var widthWindowshowproducts = $(window).innerWidth();
        if (widthWindowshowproducts<=480) {
            //$('.title-section').next('.panel-group').hide(50);
            //$('#order-show-products').show(50);
            //$('#summary-panel-product').hide(50);
            var openGroup = hide_panel.closest('header').next('.panel-group');
            //if (openGroup.is(':visible')) {
            //$('.title-section:not(".hide_panel_group")').find('i').removeClass('bullet-strzalka-up').addClass('bullet-strzalka-down');
            //};
            //$('.title-section:not(".hide_panel_group")').find('i').removeClass('bullet-strzalka-up').addClass('bullet-strzalka-down');
        };

        var $window = $(window);
        var $wWOryginal = $(window).innerWidth();
        $window.on('resize', function () {

            var widthWindow = $(window).innerWidth();
            var zwin_produkty = $('#zwin_produkty');
            var pokaz_produkty = $('#pokaz_produkty');
            var orderShowProducts = $('#order-show-products');
            if(widthWindow != $wWOryginal) {
                if (widthWindow <= 767) {
                    var arrow = $('.podziekowanie_nieudane_view_summary').closest('.block_info_order').is(':hidden') ? 'bullet-strzalka-up test' : 'bullet-strzalka-down test';

                    $('.podziekowanie_nieudane_view_summary').find('i').attr('class', arrow)
                    $('.podziekowanie_nieudane_view_summary').closest('.block_info_order').find('.adres_dostawy').hide();
                    $('.podziekowanie_nieudane_view_summary').closest('.block_info_order').next('.table-summary-product').hide();
                    //$('.podziekowanie_nieudane_view_summary').closest('.panel-body').next('.panel-footer').hide();

                    if($('.podziekowanie_nieudane_view_summary').hasClass('open')) {
                        $('.podziekowanie_nieudane_view_summary.open').find('i').removeClass('bullet-strzalka-down').addClass('bullet-strzalka-up')
                        $('.podziekowanie_nieudane_view_summary.open').closest('.block_info_order').next('.table-summary-product').css({display:'block'})
                        $('.podziekowanie_nieudane_view_summary.open').closest('.block_info_order').find('.adres_dostawy').css({display:'block'})
                    }

                } else {
                    $('.adres_dostawy').show();
                    $('.podziekowanie_nieudane_view_summary').closest('.block_info_order').find('.adres_dostawy').show();
                    $('.podziekowanie_nieudane_view_summary').closest('.block_info_order').next('.table-summary-product').show();
                    $('.podziekowanie_nieudane_view_summary').closest('.panel-body').next('.panel-footer').show();
                    $('.sidebar-secound').find('header').removeClass('open');
                    $('.sidebar-secound').find('header').next('.panel-group').show();
                    $('#checkout.podsumowanie #content-main').find('#order-show-products').addClass('hidden');
                    $('.podziekowanie_nieudane_view_summary').removeClass('open');
                };

                if (widthWindow < 768 && widthWindow > 481 ) {
                    $('#checkout.podsumowanie').find('.main').addClass('opened-panel-group');
                } else {
                    $('#checkout.podsumowanie').find('.main').removeClass('opened-panel-group');
                };

                if (zwin_produkty.is(":visible")) {
                    //orderShowProducts.hide(50)
                } else  {
                    // orderShowProducts.show(50)
                }


                if(widthWindow >=481) {

                    hide_panel.find('i').removeClass('bullet-strzalka-down').addClass('bullet-strzalka-up');
                    hide_panel.closest('.panel-group').next('.panel-group').show(50);
                    $('.open-details-order-info').closest('.block_info_order').next('.table-summary-product').show(50);
                    $('.title-section').next('.panel-group').show(50);
                    //$('.hide_panel_group').next('.panel-group').show(50);
                    //$('#order-show-products').hide(50);
                    //$('#zmien_produkty').show(50);
                }
                if(widthWindow <=480) {
                    //if(!isAndroid) {}
                    //$('#order-show-products').show(50).off();
                    //$('.title-section:not(.hide_panel_group)').next('.panel-group').one().hide(50);
                    //$('.title-section.hide_panel_group').next('.panel-group').show(50);
                    //$('.title-section:not(".hide_panel_group")').find('i').removeClass('bullet-strzalka-up').addClass('bullet-strzalka-down');
                }
            }
        });
        $window.on('resize', function () {
            if(widthWindow != $wWOryginal) {
                if (widthWindow >= 768) {}
            }
        });


        /* ================= end:// checkout ================= */

////// Wyrównanie kolumn
        var widthWindow = $(window).innerWidth();
        var kolumnaPrawa =  $("div.col-01").innerHeight();
        var kolumnaLewa = $("div.col-02").innerHeight();
        if (widthWindow >= 768) {
            if (kolumnaLewa > kolumnaPrawa) {
                $("div.col-01").css({'height' : kolumnaLewa});
            } else {
                $("div.col-02").css({'height' : kolumnaPrawa});
            };
        };
        $( window ).resize(function() {
            var widthWindow = $(window).innerWidth();
            var kolumnaPrawa =  $("div.col-01").innerHeight();
            var kolumnaLewa = $("div.col-02").innerHeight();
            if (widthWindow >= 768) {
                if (kolumnaLewa > kolumnaPrawa) {
                    $("div.col-01").css({'height' : kolumnaLewa});
                } else {
                    $("div.col-02").css({'height' : kolumnaPrawa});
                };
            } else {
                $("div.col-01").css({'height' : 'auto'});
                $("div.col-02").css({'height' : 'auto'});
            }
        });


////////////////////////////////////////////////////////////////////


        var sync1 = $("#product-gallery #sync1");
        var sync2 = $("#product-gallery #sync2");

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
                };
            }
        });


        function syncPosition(el){
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
            var itemH = parseInt(item.height());
            var itemHeight = item.height()+10;
            var position = parseInt($('#product-gallery #wrapper-sync2 .rwd-wrapper').css('margin-top'));
            var wrapp = $(this).closest('#product-gallery #wrapper-sync2').find('.rwd-wrapper');
            var wrappHeight = wrapp.height();
            var sumItem = itemHeight * (item.length-5);
            wrapp.filter(':not(:animated)').animate({
                'margin-top': '+='+itemHeight
            });

            if (position == '-'+itemHeight) {
                $('#product-gallery #wrapper-sync2 .up').addClass('disabled');
            };
            if (position != '-'+sumItem) {
                $('#product-gallery #wrapper-sync2 .down').removeClass('disabled');
            };

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
            };
            if (position == '-'+sumItem) {
                $('#product-gallery #wrapper-sync2 .down').addClass('disabled');
            };


        });

        function center(number){
            var sync2visible = sync2.data("rwdCarousel").rwd.visibleItems;
            var num = number;
            var found = false;
            for(var i in sync2visible){
                if(num === sync2visible[i]){
                    var found = true;
                }
            }

            if(found===false){
                if(num>sync2visible[sync2visible.length-1]){
                    //sync2.trigger("rwd.goTo", num - sync2visible.length+2)
                }else{
                    if(num - 1 === -1){
                        num = 0;
                    }
                    // sync2.trigger("rwd.goTo", num);
                }
            } else if(num === sync2visible[sync2visible.length-1]){
                // sync2.trigger("rwd.goTo", sync2visible[1])
            } else if(num === sync2visible[0]){
                // sync2.trigger("rwd.goTo", num-1)
            }

        }
        flagProduct();
//flagProductGallery();
        function flagProduct() {
            var sync1 = $('#sync1 .rwd-item');
            sync1.each(function( i, l ){
                var flags = $(this).find('a');
                var flag = flags.data('flags');
                flags.append('<i class="flag '+flag+'"></i>');
            });

        }

        function flagProductGallery() {
            var sync3 = $('#sync3 .rwd-item');
            sync3.each(function( i, l ){
                var flags3 = $(this).find('.inner-item');
                var flag3 = flags3.data('flags');

                if ($(this).find('.flag').length == 0) {
                    $(this).find('.inner-item').append('<i class="flag '+flag3+'"></i>');
                };
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


////////////////////////////////////////////////////////////////////
        function galeriaProduktu(){
// start
            var sync3 = $("#lightbox #sync3");
            var sync4 = $("#lightbox #sync4");

            sync4.rwdCarousel({
                items : 1,
                pagination:false,
                navigation: false,
                mouseDrag:false,
                touchDrag: false,
                transitionStyle: "fade",
                afterAction : function() {

                },
                afterMove: function() {

                },

                afterInit : function(el){
                    el.find(".rwd-item").eq(0).addClass("synced");

                    var sync4Item = $('#sync4 .rwd-item');
                    var sync4ItemLength = sync4Item.length;
                    if (sync4ItemLength <= 4 ) {
                        $('#wrapper-sync4 .up').addClass('disabled');
                        $('#wrapper-sync4 .down').addClass('disabled');
                    };


                }
            });
            sync3.rwdCarousel({
                singleItem : true,
                slideSpeed : 1000,
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
                    //var number_trigger = $(this).closest('.rwd-item').data("currentItem");
                    //console.log(number_trigger)
                    //$('#sync3').trigger("rwd.jumpTo",number_trigger);
                    var body = $("#inner-lightbox");
                    var top = body.scrollTop();
                    if(top!=0) {
                        $("#inner-lightbox").animate({ scrollTop: 0 }, "slow");
                    }
                },
                afterInit:function(elem) {
                    var that = this
                    that.rwdControls.prependTo(elem)
                    $('#lightbox').find('.inner-item').css({visibility:'hidden'});
                    $('#lightbox').find('#galeria-lightbox #sync3 .rwd-wrapper-outer').prepend('<div class="loader" style="text-align:center;"><img src="'+Config.path.ajaxLoader+'" /></div>')
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
                            var aW = a.width()
                            var aH = a.height();
                            var b = $(this).find('img')
                            var bW = b.width();
                            var ratio = Math.round(aH/bW*100)/100;
                            var c = $(window).height();
                            var d = $('#lightbox #hl').innerHeight();
                            var contentHeight = c-d-90;
                            var widthRatio = Math.round(bW/ratio*100)/100;
                            var widthWindow = $(window).innerWidth();
                            var imgWidthLoad   =  $(this).find('img').data('width');
                            var imgHeightLoad  =  $(this).find('img').data('height');
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

                            };
                            $('#lightbox .rwd-buttons').width(innerItem+'px')
                            $('#lightbox .rwd-buttons .rwd-prev, #lightbox .rwd-buttons .rwd-next').css({
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
                                    $('#lightbox .rwd-buttons').show();
                                };
                                a.closest('.inner-item').css({'width': innerItem, 'margin': '0 auto' });
                                ///$(this).removeClass('full').addClass('disabled');
                                ///$(this).prev('.zoom_plus').removeClass('full disabled');
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
                    console.log($(this).length)
                    var imgScaleWidth = parseInt(img.width());
                    var imgWidth   =    $(this).find('img').data('width');
                    var imgHeight  =  $(this).find('img').data('height');
                    var divWidth   = $(this).find('.item').css("width"),
                        divWidth = parseInt(divWidth);
                    var divHeight  = $(this).find('.item').css("height") ;
                    $(img).width(divWidth)
                    $('.rwd-buttons').width(imgScaleWidth);
                    $(img).css("height", 'auto');


                    if (imgWidth < divWidth) {
                        $(img).width(imgWidth+'px') //Set the width to the div's width
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

            /*
             $('#product-gallery #sync1').on('click', 'a', function(event) {
             event.preventDefault();

             $( "#lightbox .bl" ).html($("#galeria-lightbox-wr").html());

             setTimeout(function(){
             //$('#lightbox').css({display:'block'}).click();
             galeriaProduktu();
             }, 1500);




             $('body').addClass('lightbox');






             //center(numberProduct)
             //if (numberProduct) {
             //  console.log(numberProduct);
             //};

             });
             */
            updateCarousel();
            function updateCarousel(el){
                //var current = $("#sync4").find('.synced').index();


            }


            function syncPosition2(el){
                var current = this.currentItem;
                var hri = $("#sync4").find('.rwd-item').height();
                var currentLength = $("#sync4").find('.rwd-item').length;



                $("#sync4")
                    .find(".rwd-item")
                    .removeClass("synced")
                    .eq(current)
                    .addClass("synced")
                if($("#sync4").data("rwdCarousel") !== undefined){
                    //center(current)
                }



            }
            $('#sync3').on('click', '.rwd-next', function(e){
                e.preventDefault();

                var current = $('#sync4 .rwd-item').index($('.synced'));
                var number = $('#sync4').data("rwdItem");
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
                var sync4visible = sync4.data("rwdCarousel").rwd.visibleItems;
                var num = number;
                var found = false;
                for(var i in sync4visible){
                    if(num === sync4visible[i]){
                        //var found = true;
                    }
                }

                if(found===false){
                    if(num>sync4visible[sync4visible.length-1]){
                        //sync4.trigger("rwd.goTo", num - sync4visible.length+2)
                    }else{
                        if(num - 1 === -1){
                            //  num = 0;
                        }
                        // sync2.trigger("rwd.goTo", num);
                    }
                } else if(num === sync4visible[sync4visible.length-1]){
                    //sync4.trigger("rwd.goTo", sync4visible[1])
                } else if(num === sync4visible[0]){
                    //sync4.trigger("rwd.goTo", num-1)
                }

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
                var itemH = parseInt(item.height());
                var itemHeight = item.height()+10;
                var position = parseInt($('#lightbox #wrapper-sync4 .rwd-wrapper').css('margin-top'));
                var wrapp = $(this).closest('#lightbox #wrapper-sync4').find('.rwd-wrapper');
                var wrappHeight = wrapp.height();
                var sumItem = itemHeight * (item.length-5);
                wrapp.filter(':not(:animated)').animate({
                    'margin-top': '+='+itemHeight
                });

                if (position == '-'+itemHeight) {
                    $('#lightbox #wrapper-sync4 .up').addClass('disabled');
                };
                if (position != '-'+sumItem) {
                    $('#lightbox #wrapper-sync4 .down').removeClass('disabled');
                };

            });

            $('#lightbox #wrapper-sync4').on('click', '.down', function(event) {
                event.preventDefault();

                var item = $('#lightbox #wrapper-sync4').find('.rwd-item');
                var itemHeight = item.height()+10;
                var position =  parseInt($('#lightbox #wrapper-sync4 .rwd-wrapper').css('margin-top'));
                var wrapp = $(this).closest('#lightbox #wrapper-sync4').find('.rwd-wrapper');

                var wrappHeight = wrapp.height();
                var sumItem = itemHeight * (item.length-7);
                wrapp.filter(':not(:animated)').animate({
                    'margin-top': '-='+itemHeight
                });
                if (position != '-'+itemHeight) {
                    $('#lightbox #wrapper-sync4 .up').removeClass('disabled');
                };
                if (position == '-'+sumItem) {
                    $('#lightbox #wrapper-sync4 .down').addClass('disabled');
                };


            });


// end
        }
////////////////////////////////////////////////////////////////////


// Show Top Layer - Gallery
        $(window).on('resize', function(){
            var widthWindow = $(window).width();
            var lupa = $('#product-gallery #sync1').find('.view_lupa');
            if (widthWindow < 767) {
                lupa.hide();
            } else {
                lupa.show();
            };
        })
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

                    //$('#lightbox').find('.inner-item').css({visibility:'hidden'})

                    setTimeout(function(){
                        //$('#lightbox').find('.inner-item').css({visibility:'visible'})
                    }, 500);
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
        };
//*/

//////////////////////////////////////////////////////////////////


//        $("label img").on("click", function() {
//            $("#" + $(this).parents("label").attr("for")).click();
//			return;
//        });
//equalizeHeights();
        var flags_container = $('.carousel-stage');
        if (flags_container.length > 0 ) {

        };
//
//if ($('body').hasClass('node-type-list')) {
////  equalizeHeights();
//  var container = document.querySelector('.jsMasonryContent');
//  notifElem = document.querySelector('#notification');
//    imagesLoaded( document.querySelector('#items-product'), function( instance ) {
//        var msnry = new Masonry( container, {
//         itemSelector: '.item',
//         isResizable: true
//      });
//        msnry.on( 'layoutComplete', function( msnryInstance, laidOutItems ) {
////          equalizeHeights()
//        });
////        equalizeHeights()
//
//    });
//
//
//
//
//
//
//};



        function equalizeHeights() {
            var heights = new Array();
            $('.node-type-list #items-product .item').each(function() {
                heights.push($(this).height());
            });
            var min = Math.min.apply( Math, heights );
            var con = $('#items-product').innerHeight();
            $('#items-product').not('.list-shop-product').css('height', 'auto');
            $('#items-product').not('.list-shop-product').css('height', con-min);
        }




        /*

         if ($('body').hasClass('node-type-list')) {
         imagesLoaded( document.querySelector('#items-product'), function( instance ) {
         var $containerContent = $('.jsMasonryContent');
         // initialize
         $containerContent.masonry({
         itemSelector: '.item',
         isResizable: true
         });

         var jsMasonryContent = $('.jsMasonryContent').height(),
         jsMasonryContent = jsMasonryContent-250;
         $('.jsMasonryContent').css('height', jsMasonryContent);

         });

         };

         */


        $(window).smartresize(function(){

        });
        helperForm();
        function helperForm() {

            var thisEl = $('')
            var hiddenEl = $('.form-group.orders_someone_else');

            $('input.orders_someone_else').on('click',  function(event) {
                //event.preventDefault();
                /* Act on the event */
                $(this).closest('form').find('div.orders_someone_else').toggleClass('hidden');
                //hiddenEl.show();
            });


        }


        var footerHeight = 0,
            footerTop = 0,
            $footer = $("#footer");
        positionFooter();
        function positionFooter() {
            var  sbsite = $('#sb-site').innerHeight();
            footerHeight = $footer.innerHeight();


            if ( (sbsite) < $(window).innerHeight()) {
                $footer.css({
                    position: "fixed",
                    bottom: "0px",
                    left:"0px",
                    width:"100%"
                });
                $('#sb-site').addClass('fixed');
                $('body').css({
                    paddingBottom: footerHeight
                });

            } else if($('body').hasClass('noscroll')) {
                $footer.css({
                    position: "static"
                });
                $('#sb-site').removeClass('fixed');
                $('#sb-site').addClass('static');
                $('body').css({
                    paddingBottom: 0
                });
            } else {
                $footer.css({
                    position: "static"
                });
                $('#sb-site').removeClass('fixed');
                $('#sb-site').addClass('static');
                $('body').css({
                    paddingBottom: 0
                });
            }

        }

        $(window)
            .scroll(positionFooter)
            .resize(positionFooter)

        $('.faq-content .panel, #navigation').click(function(){
            positionFooter();
        })

        var intFrameWidth = window.innerWidth;
        if (intFrameWidth <= 767) {
            $('.toggle-xs').find('.main').hide();
            $('.block-complementary-product.toggle-xs').find('.main').show();
        } else {
            $('.toggle-xs').find('.main').show();
        };

        $('#product-gallery .stage a').on('click', '.selector', function(event) {
            event.preventDefault();
            $('#product_gallery').modal('hide');
        });






        //formHint();
        shippingHelper();
        visibleBtnClearFilterSize();
        openFormReview();
        listinghelper();

        function openFormReview() {
            var footerComments = $('.footer_comments');
            var openFormReview = footerComments.find('.btn');
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

        function dropDownSelectListAjax() {
//            var headList = $('.button-select.ajax').not(".filter-droplist");
//            var listSelect = $('.dropdown-select ul').not(".filter-droplist");
//            headList.on('click', function(event) {
//                event.preventDefault();
//                $(this).next('.dropdown-select').stop(true).slideToggle(200);
//            });
//            listSelect.on('click', 'a', function(event) {
//                event.preventDefault();
//                var thisVal = $(this).html();
//                $(this).closest('.select-group').find('.button-select').html(thisVal+'<span class="down"></span>');
//                $(this).closest('.dropdown-select').slideUp(200);
//            });
//            $(document).click(function(e) {
//                if (!$(e.target).parents().andSelf().is('.select-group')) {
//                    $(".dropdown-select").slideUp(200);
//                }
//            });

        }

        $(document).ready(function () {
            $(".menu-5columns dt a, .menu-5columns dt span,.menu-5columns dd a").each(function (i, item) {

                if ($(item).textWidth() >= 152) {
                    $(item).addClass("long-wrap");
                }
            })
        })

        $.fn.textWidth = function (text, font) {
            if (!$.fn.textWidth.fakeEl) $.fn.textWidth.fakeEl = $('<span>').hide().appendTo(document.body);
            $.fn.textWidth.fakeEl.text(text || this.val() || this.text()).css('font', font || this.css('font'));
            return $.fn.textWidth.fakeEl.width();
        };


//        var scroll = $('body').find('.mCustomScrollbar');
//        if (scroll.length >= 1) {
//            scroll.mCustomScrollbar({
////          setHeight:100,
//                theme:"dark-thick",
//                scrollButtons:{
//                    enable:true
//                }
//            });
//        };


        function listinghelper() {

            var listProducts = $('#items-product');
            var listItemsProducts = listProducts.children('.item');
            listItemsProducts.each(function(index, el) {


                var children = $(this).children('.box_listing_product');
                var widthThis = children.innerWidth()-15;

                var childrenPrice = children.find('.col-price').innerWidth();
                var childrenLike = children.find('.like').innerWidth();

                var widthBlock = parseInt(childrenPrice + childrenLike);

                var widthThisHalf = parseInt(widthThis/2);

                if (widthBlock < widthThis) {

                };
                if (widthBlock > 0 && //To prevent using this script before products loaded
                    widthBlock > widthThis) {
                    if (childrenPrice > widthThisHalf) {
                        $(this).find('.price').addClass('price-two-line');
                    } else {
                        $(this).find('.price').removeClass('price-two-line');
                    };
                    if (childrenLike > widthThisHalf) {
                        $(this).find('.price').addClass('like-two-line');
                    } else {
                        $(this).find('.price').removeClass('like-two-line');
                    };
                };
            });



        }


        function shippingHelper() {
            var tableWrapper = $('.tableWrapper'),
                tableCell = $('.tableWrapper .table-cell'),
                oldPrice = tableCell.find('.product_price').children('span'),
                oldPriceWidth = oldPrice.width() + 12;
            oldPrice.css({
                'margin-left': '-'+oldPriceWidth+'px',
            });



        }
        //$('input[type=text],input[type=email],input[type=password],textarea ').tooltip({
        //    placement: function(a, element) {
        //        var position = $(element).parent().position();
        //        var viewport = $(window).innerWidth();
        //
        //        if (viewport > 768) {
        //            return "right";
        //        }
        //        if (viewport <= 767) {
        //            return "top";
        //        }
        //        return "right";
        //    },
        //    trigger: "focus"
        //    //container: 'body'
        //});
        //$('input[type=text],input[type=email],input[type=password],textarea ').focus(function(){
        //    $('.tooltip.top').css({
        //        right:0+'px',
        //        left:'auto'
        //    })
        //})


        /*function formHint() {
         var form = $('form');
         form.on('click', 'input[type="text"].hint, input[type="email"].hint, input[type="password"].hint', function(event) {
         event.preventDefault();

         var elHint = $(this).data('hint');

         var html = '';
         html += '<span class="topHint">';
         html += elHint ;
         html += '</span>';

         $(this).after(html);
         var htmlHint = $(this).closest('.form-group').find('.topHint');
         var htmlHintWidth = htmlHint.width()+38;

         htmlHint.css({
         right: '-'+htmlHintWidth+'px'
         });

         });
         form.on('blur', 'input[type="text"].hint, input[type="email"].hint, input[type="password"].hint', function(event) {
         event.preventDefault();
         var htmlHint = $(this).closest('.form-group').find('.topHint');
         htmlHint.remove();
         });
         } */

        var itemProduct = $('.box_listing_product');
        itemProduct.on('click', '.like', function(event) {
            event.preventDefault();
            /* Act on the event */
            var itemProductId = $(this).data('idproduct');
        });
        itemProduct.on('mouseenter', '.like', function(event) {
            event.preventDefault();
            if ($(this).hasClass('liked')) {
                var textLike = 'Dodane do ulubionych';
            } else {
                var textLike = 'Dodaj do ulubionych';
            };
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



        var intFrameWidth = window.innerWidth;
        init();
        showSubMenuMobile();
        initScrollBarFilterMarka();
        initScrollBarFilterStyle();

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
		                self.find('i').toggleClass('bullet-strzalka-down bullet-strzalka-up');
		                var i = self.find('i');
		                if($.now() - lastToggle > 100) {
			                lastToggle = $.now();
			                if (i.hasClass('bullet-strzalka-down')) {
				                i.removeClass('bullet-strzalka-down').addClass('bullet-strzalka-up');
			                } else {
				                i.removeClass('bullet-strzalka-up').addClass('bullet-strzalka-down');
			                }
		                }
	                }
                });
            };
        });




        jQuery.fn.resize_delayed = function ( func, duration ){
            this.resize(function() {
                clearTimeout( window.____resize_delayed );
                window.____resize_delayed = setTimeout( func, duration);
            });
        };

        // USAGE:
        /* jQuery( window ).resize_delayed( function (){

         jsMasonryContentHeight();
         function jsMasonryContentHeight() {
         var jsMasonryContent = 0;
         var jsMasonryContent = $('.jsMasonryContent').height(),
         jsMasonryContentH = jsMasonryContent;
         $('.jsMasonryContent').css('height', jsMasonryContentH-300);
         }

         }, 500 );*/

        $(window).on("resize", function() {

            var intFrameWidth = window.innerWidth;
            var widthChanged = false, heightChanged = false;
            if($(window).width() != prevW) {
                listinghelper();

                if (intFrameWidth > 767) {
                    $('.toggle-xs').each(function(index, el) {
                        $(this).find('.main').show();

                    });
                    $('.toggle-xs').each(function(index, el) {
                        $(this).find('#rwd-complementary-product').show();
                    });
                    // $('body.node-type-view-product').find('.section').find('.title_section').closest('header').next('div').css({
                    //   display: 'block'
                    // });

                } else {

                    $(".toggle-xs[data-mobiletoggle='false']").find('.main').hide();
                    $('.block-complementary-product.toggle-xs').find('.main').show();
                    //$('body.node-type-view-product').find('.section').find('.title_section').closest('header').children('h2').children('i').removeClass('bullet-strzalka-up').addClass('bullet-strzalka-down')
                    //$('body.node-type-view-product').find('.section').find('.title_section').closest('header').next('div').css({
                    //  display: 'none'
                    //});
                };
            }
            prevW = $(window).width();
            prevH = $(window).height();
        });









        $('#header').on('click', '.toggleMenu', function(event) {
            event.preventDefault(); var screenWidth = $(window).width();
            var screenHeight = $(window).height();
            var docHeight = $(window).innerHeight();
            $('body').toggleClass('sb-open noscroll');
            $('#sb-site').toggleClass('open');
            $('.sb-slidebar').toggleClass('sb-active');
            $('body').addClass('noscroll').append('<div class="noscroll" style="width:100%; height:'+screenHeight+'px"></div>');
            $('#sb-site').css('height', docHeight);
        });

        $('.sb-slidebar').on('click', '.closeSlidebar', function(event) {
            event.preventDefault();
            $('body').removeClass('sb-open noscroll');
            $('#sb-site').removeClass('open');
            $('.sb-slidebar').removeClass('sb-active');
            $('body').find('.noscroll').remove();
        });

        $(document).mouseup(function (e){
            var container = $(".sb-slidebar");
            if(container.is(":visible")){

                if (!container.is(e.target) && container.has(e.target).length === 0) {
                    //hide here
                    $('#sb-site').removeClass('open');
                    $('.sb-slidebar').removeClass('sb-active');
                    $('body').removeClass('sb-open noscroll');
                    $('body').find('.noscroll').remove();
                }
            }
        });



        //control sidebar
        if ($('body').hasClass('filter-sidebar')) {

            if($(window).width() <= 768) {
//       $("#sidebar").find('.sidebar').remove();
//       $(".fb-slidebar-inner").load("_include/sidebar.inc", function(){
//          init();
//          initScrollBarFilterMarka();
//          visibleBtnClearFilterSize();
//
//       });

            } else {
//        $(".fb-slidebar-inner").find('.sidebar').remove();
//        $("#sidebar").load("/sidebar.inc", function(){
//          //filterManufacturerUnCheked()
//          init();
//          initScrollBarFilterMarka();
//          visibleBtnClearFilterSize();
//
//        });

            }
            prevW = $(window).width();
            prevH = $(window).height();
            $(window).on("resize", function() {
                var widthChanged = false, heightChanged = false;
                //var wh = parseInt($(window).innerWidth());

                var intFrameWidth = window.innerWidth;
//if($(window).width() != prevW) {
                $('.toggle-xs').find('.main').hide();
                $('.block-complementary-product.toggle-xs').find('.main').show();

                var sidebarMain = $('body').find('#content .sidebar');
                var sidebarAside = $('body').find('.fb-slidebar .sidebar')


                if(intFrameWidth < 768) {
                    if (sidebarMain.length === 0) {
//              $("#sidebar").find('.sidebar').remove();
//              $(".fb-slidebar-inner").load("_include/sidebar.inc", function(){
//                init();
//                initScrollBarFilterMarka();
//                clearFilterManufacturerCheked();
//                visibleBtnClearFilterSize();
//
//              });
                    };
                    if ($('body').hasClass('noscroll')) {
                        var screenWidth = $(window).width();
                        var screenHeight = $(window).height();
                        $('div.noscroll').css({
                            width:screenWidth,
                            height:screenHeight
                        });

                    };
                    //$('.toggle-xs').find('.main').attr('style', '').stop();

                } else {
                    if (sidebarMain.length === 0) {
                        Mall.listing.insertDesktopSidebar();
                        init();
//              $(".fb-slidebar-inner").find('.sidebar').remove();
//              $("#sidebar").load("/sidebar.inc", function(){
//                init();
//                initScrollBarFilterMarka();
//                clearFilterManufacturerCheked();
//                visibleBtnClearFilterSize();
//
//                $( "#slider-range" ).slider({
//                   range: true,
//                   min: 0,
//                   max: 500,
//                   values: [ 75, 300 ],
//                   slide: function(event, ui) {
//                       $("#zakres_min").val(ui.values[0]);
//                       $("#zakres_max").val(ui.values[1]);
//
//
//                   }
//
//                 });
//                  $('#slider-range').on('click', 'a', function(event) {
//                  var checkSlider = $('#checkSlider').find('input');
//                  if (!checkSlider.is(':checked')) {
//                   checkSlider.prop('checked', true);
//                    $('#filter_price').find('.action').removeClass('hidden');
//                  }
//                });
//
//              });
                    };
                    $('#sb-site').removeClass('open');
                    $('.fb-slidebar').removeClass('open');
                    $('body').removeClass('noscroll').find('.noscroll').remove();

                }

//}
                prevW = $(window).width();
                prevH = $(window).height();


            });
        };


// END 



/*        $("#link_basket .dropdown-toggle").on('click', function(event) {
            var intFrameWidth = window.innerWidth;
            if(intFrameWidth > 768) {
                // event.stopPropagation();
                var thisLink = $(this);
                var container = $("#dropdown-basket");
                thisLink.closest('li').toggleClass('open');
                container.toggle();
                return false;
            } else {
                var linkLocation = $(this).attr('href');
                window.location.href = linkLocation;
            }
        });

        $('#dropdown-basket').click(function(event){
            //event.preventDefault();
        });

        $(document).click(function(event){
            var container = $("#dropdown-basket");
            if (container.is(":visible")) {
                //event.preventDefault();
                $('#user_menu').find('.open').removeClass('open');
                $('#dropdown-basket').hide();
            };
        });*/

        $("#toggleSearch .dropdown-toggle").on('click', function(event) {
            event.stopPropagation();
            var thisLink = $(this);
            var container = $("#dropdown-search");
            thisLink.closest('#toggleSearch').toggleClass('open');
            container.toggle();
        });
        $('#dropdown-search').click(function(e){
            e.stopPropagation();
        });

        $(document).click(function(e){
            var container = $("#dropdown-search");
            if (container.is(":visible")) {
                e.stopPropagation();
                $('#toggleSearch').removeClass('open');
                $('#dropdown-search').hide();
            };
        });




        $(document).on('mouseup touchstart', function (e){
            var container = $(".fb-slidebar");
            if(container.is(":visible")){

                if (!container.is(e.target) && container.has(e.target).length === 0) {
                    //hide here
//    container.find('.sidebar').remove()
                    $('#sb-site').removeClass('open');
                    $('.fb-slidebar').removeClass('open');
                    $('body').removeClass('noscroll');
                    $('body').find('.noscroll').remove();
                }
            }
        });

        /*$(document).on('touchstart', function(){
         var container = $(".fb-slidebar");
         if(container.is(":visible")){

         if (!container.is(e.target) && container.has(e.target).length === 0) {
         //hide here
         $('#sb-site').removeClass('open');
         $('.fb-slidebar').removeClass('open');
         $('body').removeClass('noscroll');
         $('body').find('.noscroll').remove();
         }
         }
         }*/






        //$('.actionViewFilter').on('click', function(event){
        //    event.preventDefault();
        //    Mall.listing.insertMobileSidebar();
        //    $('#sb-site').toggleClass('open');
        //    $('.fb-slidebar').toggleClass('open');
        //    var screenWidth = $(window).width();
        //    var screenHeight = $(window).height();
        //    $('body').addClass('noscroll').append('<div class="noscroll" style="width:100%; height:'+screenHeight+'px"></div>');
//
//                });

//            $("#sidebar").slideToggle();



//        $(".fb-slidebar-inner")
//        $(".fb-slidebar-inner").load("/sidebar.inc", function(){
//
//                  init();
//                  initScrollBarFilterMarka();
//                  clearFilterManufacturerCheked();
//                  visibleBtnClearFilterSize();
//                  filterColor();
//                  $( "#slider-range" ).slider({
//                          range: true,
//                          min: 0,
//                          max: 500,
//                          values: [ 75, 300 ],
//                          slide: function(event, ui) {
//                              $("#zakres_min").val(ui.values[0]);
//                              $("#zakres_max").val(ui.values[1]);
//
//                          }
//
//                        });
//                  $('#slider-range').on('click', 'a', function(event) {
//                  var checkSlider = $('#checkSlider').find('input');
//                  if (!checkSlider.is(':checked')) {
//                    checkSlider.prop('checked', true);
//                    $('#filter_price').find('.action').removeClass('hidden');
//                  }
//                });
//
//                  $('#sb-site').toggleClass('open');
//                  $('.fb-slidebar').toggleClass('open');
//                      var screenWidth = $(window).width();
//                      var screenHeight = $(window).height();
//                      $('body').addClass('noscroll').append('<div class="noscroll" style="width:100%; height:'+screenHeight+'px"></div>');
//
//                });

//            $("#sidebar").slideToggle();
//        });




        $('.closeFilterBlock').on('click', function(event) {
            event.preventDefault();
            //$('.toogleFilterBlock').removeClass('closeFilterBlock').addClass('toogleFilterBlock');
            $('#sidebar').hide();


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
            //readOnly: true,
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




// FILTER MARKA



        // END FILTER MARKA


//FUNCTION & INIT
        function initLoad() {
            initScrollBarFilterStyle();     // INIT SCROLL
        }

        function init(){
            styledSelectBox();
            toggleSidebar();                // ZAMYKANIE / OTWIERANIE SIDEBAR MOBILE
            filterManufacturerCheked();     // KOLEJKOWANIE WYBRANYCH PÓL
            clearFilterManufacturerCheked();
            deleteCurrentFilter();          // USUWANIE AKTYWNYCH FILTRÓW
            actionViewFilter();             // OTWARCIE BLOKU Z FILTRAMIE - MOBILE
            filterList();                   // FILTROWANIE LISTY PO WPISANIU WYRAZU/LITERY
            filterColor();                  // OBSŁUGA FILTRA KOLOR
            filterColorChecked();
            clearFilter();                  // CZYSZCZENIE FILTRÓW
            recentlyViewed();               // POKAZ SLAJDÓW
            responsJcarousel();             // POKAZ SLAJDÓW
            masonryMenu();                  // KAFELKI LISTY PRODUKTÓW
            // MENU MOBILE
            closeBlockFilter();             // ZAMKNIĘCIE BLOKU Z FILTRAMI
            activeMenu();                   // ZAZNACZENIE AKTYWNEJ POZYCJI MENU
            cloneMenu();                    // CLONOWANIE MENU
            cloneSubMenu();                 // CLONOWANIE SUBMENU
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
                };
            });
            filterRecommendedProducts.on('click', '.clear', function(event) {
                $(this).closest('.action').addClass('hidden');
            });
        }

        function filterType() {
            var filterType = $('.filter-type');
            filterType.on('click', ':checkbox', function(event) {
                filterType.find('.clear').removeClass('hidden');

                var filterTypeLenght = filterType.find(':checked').length;
                if (filterTypeLenght >= 1) {
                    filterType.find('.action').removeClass('hidden');
                } else {
                    filterType.find('.action').addClass('hidden');
                };
            });
            filterType.on('click', '.clear', function(event) {
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
                };
            });

            filterStyleCheckbox.on('click', ':checkbox', function(event) {
                var filterStyleCheckboxLenght = filterStyleCheckbox.find(':checked').length;
                if (filterStyleCheckboxLenght >= 1) {
                    filterStyleCheckbox.find('.action.clear').removeClass('hidden');
                } else {
                    filterStyleCheckbox.find('.action.clear').addClass('hidden');
                };
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
                };
            });

            filterPrice.on('keyup', '#zakres_min, #zakres_max', function(event) {
                var filterPriceValueMin = filterPrice.find('#zakres_min').val();
                var filterPriceValueMax = filterPrice.find('#zakres_max').val();
                if (filterPriceValueMin.length >=1 || filterPriceValueMax.length >=1 ) {
                    filterPrice.find('.action').removeClass('hidden');
                } else {
                    filterPrice.find('.action').addClass('hidden');
                };
            });

            filterPrice.on('click', '.clear', function(event) {
                $(this).closest('.action').addClass('hidden');
            });

        }

        function visibleBtnClearFilterSize() {



//      var filterSize = $('.filter-size');
//      var btnClear = $('.action.clear');
//      filterSize.on('click', ':checkbox', function(event) {
//        /* Act on the event */
//        var filterSizeLenght = $('.filter-size').find(':checked').length;
//        if (filterSizeLenght >= 1) {
//          filterSize.find('.action').removeClass('hidden');
//        } else {
//          filterSize.find('.action').addClass('hidden');
//          };
//      });
//      filterSize.on('click', '.clear', function(event) {
//        var filterSizeLenght = $('.filter-size').find(':checked').length;
//        event.preventDefault();
//        //if (filterSizeLenght === 0) {
//          $(this).closest('div.action.clear').addClass('hidden');
//        //};
//
//      });
        }

// STYLED SELECTBOX
        function styledSelectBox() {
            //$('.styledSelected').each(function(index, el) {

            //});
            $(".select-styled,.select-styled select").selectbox({

                onOpen: function (inst) {
                    initScrollBarFilterStyle();
                }
            });
            $(".select-styled:disabled,.select-styled select:disabled").selectbox("disable");
        }

//SCROLLBAR 
        function initScrollBarFilterMarka() {
//            var fm = $('body').find(".filter_manufacturerScrollbar");
//
//            if (fm.length >= 1) {
//                fm.mCustomScrollbar({
//                    scrollButtons:{
//                        enable:true
//                    },
//                    advanced:{
//                        updateOnBrowserResize:true
//                    } // removed extra commas
//                });
//            };



        }
        function initScrollBarFilterStyle() {
//            var fs = $(".styledSelected.scrollbar .sbOptionsWrapper");
//            if (fs.length >= 1) {
//                $(".styledSelected.scrollbar .sbOptionsWrapper").mCustomScrollbar({
//
//                    setHeight: '200px',
//                    scrollButtons:{
//                        enable:true
//                    },
//                    theme:"dark-thick"
//                });
//            };
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
                };


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
            if (navSubClone.is(':visible')) {

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
                        };
                    }

                });
            };
        };
        function cloneSubMenu() {
            var elMenu = $('#navigation > ul > li > a');
            $('#navigation').on('click', '[data-toggle="dropdown"]', function(event) {
                event.preventDefault();
                /* Act on the event */
                //$(this).addClass('class_name');
            });
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
            });

        }


// LISTA PRODUKTÓW

        function masonryMenu() {
            $('.header_bottom .dropdown').on('shown.bs.dropdown', function () {
                var $container = $('.jsMasonry');
                $container.masonry({
                    itemSelector: '.box'
                });
            });
        }

// SIDEBAR
        function toggleSidebar() {
//            var elCliced = $('.sidebar').find('h3');
//            elCliced.on('click', function(event) {
//                $(this).children('i').toggleClass('fa-chevron-down');
//                //event.preventDefault();
//                $(this).next('.content').toggle(function() {
//                    $(this).next('.content').stop(true,true).show().closest('.section').find('h3').find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
//                    $(this).children('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
//
//                }, function() {
//                    $(this).next('.content').stop(true,true).hide(function(){
//                        $(this).closest('.section').find('h3').children('i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
//                    });
//                });
//            });
        }

// FILTRY
        function actionViewFilter() {
            $('#view-current-filter').on('click', '.actionViewFilter', function(event) {
                event.preventDefault();


                //$('#sidebar').removeClass('hidden-xs').show(10, function(){

                //$('#sidebar').animate({'left': 0}, 300).css('z-index', '9000');
                //});
            });
        }
// AKTYWNE FILTRY
        function deleteCurrentFilter() {
            $('.current-filter, .view_filter').on('click', '.label>i', function(event) {
                var removeUrl = jQuery(event.target).attr("data-params");
                location.href = removeUrl;
                event.preventDefault();
                var lLabel = $(this).closest('dd').find('.label').length - 1;
                if (lLabel >= 1) {
                    $(this).closest('.label').remove();

                } else {
                    $(this).closest('dl').remove();
                };
                if (lLabel == 0) {
                    $('#view-current-filter').find('.view_filter').css('margin-top', 20);
                }
            });
            $('.current-filter, .view_filter').on('click', '.action a', function(event) {
//    event.preventDefault();
                $(this).closest('dl').remove();
                $('#view-current-filter').find('.view_filter').css('margin-top', 24);
            });
        }
// ZAMKNIĘCIE BLOKU Z FILTRAMI
        function closeBlockFilter(){
            $('.noscroll').on('click',  function(event) {
                event.preventDefault();
                /* Act on the event */
                $('#sb-site').removeClass('open');
                $('.fb-slidebar').removeClass('open');
                $('body').removeClass('noscroll');
                $('body').find('.noscroll').remove();
            });

        }

// FILTROWANIE LISTY PO WPISANIU WYRAZU/LITERY
        function filterList(){
//            $('.filter_manufacturer_search').keyup(function(){
//                console.log($(this));
//                var valThis = $(this).val().toLowerCase();
//                var manufacturerList = $(this).parents('.filter-longlist').find('.manufacturerList');
//                var manufacturerListLi = $(this).parents('.filter-longlist').find('.manufacturerList li');
//                var noresult = 0;
//
//                if(valThis == ""){
//                    manufacturerListLi.show();
//                    noresult = 1;
//
//                    $(manufacturerList).find('li.no-results-found').remove();
//                } else {
//                    manufacturerListLi.each(function(){
//                        var text = $(this).text().toLowerCase();
//                        var match = text.indexOf(valThis);
//                        if (match >= 0) {
//                            $(this).show();
//                            noresult = 1;
//                            $(manufacturerList).find('li.no-results-found').remove();
//                        } else {
//                            $(this).hide();
//                        }
//                    });
//                };
//                if (noresult == 0) {
//                    manufacturerList.append('<li class="no-results-found">Brak wyników.</li>');
//                }
//
//            });
//
//            $('.manufacturerList').on('click', ':checkbox', function(event) {
//                $('#filter_manufacturer_search').val('');
//                $('.manufacturerList').find('li').css('display', 'list-item');
//            });
//
//
//            $('.block-filter').on('click', '.clear', function(event) {
//                event.preventDefault();
//                /* Act on the event */
//                $(this).closest('.content').find('input[type="text"]').val('');
//                $('.manufacturerList > li').each(function(){
//                    $(this).show();
//                });
//            });
        }

// KOLEJKOWANIE WYBRANYCH PÓL
        function filterManufacturerCheked() {
//
//
//            var list = $(".manufacturerList")
////              ,
////              origOrder = list.children()
//                ;
//            var fm = $(".filter_manufacturerScrollbar");
//            list.on("click", ":checkbox", function () {
//                var listD = $(this).parents(".manufacturerList");
//                var wrapper = $(this).parents(".filter-longlist");
//
//                var origOrder = listD.children();
//
//                var i, checked = document.createDocumentFragment(),
//                    unchecked = document.createDocumentFragment();
//                for (i = 0; i < origOrder.length; i++) {
//                    if (origOrder[i].getElementsByTagName("input")[0].checked) {
//                        checked.appendChild(origOrder[i]);
//                    } else {
//                        unchecked.appendChild(origOrder[i]);
//                    }
//                }
//                listD.append(checked).append(unchecked).children('li');
//
//                var listChek = listD.find(':checked');
//                var listChekLastName = listChek.last();
//                var btnClearMark = listD.parents('section').find('.action.clear');
//                listD.find('.lastChecked').removeClass('lastChecked');
//                listChekLastName.closest('li').addClass('lastChecked');
//
//
//                fm.mCustomScrollbar("scrollTo", 0);
//
//                var checkEl = listD.find(':checked').length;
//
//                if (checkEl >= 1) {
//                    wrapper.find('.action.clear').removeClass('hidden');
//                } else {
//                    wrapper.find('.action.clear').addClass('hidden');
//                }
//                ;
//
//            });
//            var btnClearMark = $('#filter_manufacturer')
//            btnClearMark.on('click', '.clear', function (event) {
//
//                $(this).closest('.action.clear').addClass('hidden');
//            });

        }
        jQuery(".filter-enum input[type=image]").click(function(){
            return false;
        })

        function clearFilterManufacturerCheked() {
            var blockList = $("#filter_manufacturer")

            blockList.on('click', 'a.clear', function(event) {

                var list = $(this).parents(".manufacturerList");
                var origOrder = list.children();
                //event.preventDefault();
                list.find('.lastChecked').removeClass('lastChecked')
                var i, checked = document.createDocumentFragment(),
                    unchecked = document.createDocumentFragment();

                for (i = 0; i < origOrder.length; i++) {
                    if (origOrder[i].getElementsByTagName("input")[0].checked) {
                        checked.appendChild(origOrder[i]);
                    } else {
                        unchecked.appendChild(origOrder[i]);
                    }
                    list.append(checked).append(unchecked).children('li');
                }
            });


        }



// OBSŁUGA FILTRA KOLOR

        function filterColor() {

            $('.filter-color label').each(function(index, el) {
                var colorFilter = $(this).data('color');
                var srcImg = $(this).data('img');
                var srcImgHover = $(this).data('imghover');
                //if ($(this).attr("data-color")) {
                $(this).find('span').children('span').css({
                    'background-color': colorFilter
                });
                //};
                if ($(this).attr("data-img")) {
                    $(this).find('span').children('span').css({
                        'background-image': 'url('+srcImg+')'
                    });
                };

                $(this).on('mouseenter', function(){

                    if (colorFilter && !$(this).attr("data-img")) {

                        $(this).find('span').children('span').css({
                            'background-image': 'none'
                        })
                    };

                    if ($(this).attr("data-imgHover")) {
                        console.log(srcImgHover);
                        $(this).find('span').children('span').css({
                            'background-image': 'url('+srcImgHover+')'
                        })
                    };

                });
                $(this).on('mouseleave', function(){

                    if (srcImg) {

                        $(this).find('span').children('span').css({
                            'background-image':  'url('+srcImg+')'
                        })
                    };

                })




                var filterColor = $('#filter_color');
                filterColor.on('click', ':checkbox', function(event) {
                    $('.filter-color .clear').removeClass('hidden');
                    var filterColorLenght = $('.filter-color input:checked').length;
                    if (filterColorLenght >= 1) {
                        $('.filter-color .action').removeClass('hidden');
                    } else {
                        $('.filter-color .action').addClass('hidden');
                    };
                });
                filterColor.on('click', '.clear', function(event) {
                    $(this).closest('.action').addClass('hidden');
                });

            });




        }

        function filterColorChecked() {
            /*var filterColor = $('#filter_color');
             filterColor.on('click', ':checkbox', function(event) {
             filterColor.find('.clear').removeClass('hidden');

             var filterColorLenght = filterColor.find(':checked').length;
             if (filterColorLenght >= 1) {
             filterColor.find('.action').removeClass('hidden');
             } else {
             filterColor.find('.action').addClass('hidden');
             };
             });
             filterColor.on('click', '.clear', function(event) {
             $(this).closest('.action').addClass('hidden');
             });*/
        }
// CZYSZCZENIE FILTRÓW
        function clearFilter(){
            $('.block-filter').on('click', '.clear', function(event) {
                event.preventDefault();
                $(this).closest('.content').find('input[type="checkbox"]:checked').removeAttr('checked');
            });
        }
// WYROWNANIE COLUMN 
        function equalColumnRegisterForm() {
            var cellForm = $('#form-registration > .row > div');
            var heightArray = $('#form-registration > .row > div').map( function(){
                return  $(this).height();
            }).get();
            var maxHeight = Math.max.apply( Math, heightArray);
            cellForm.height(maxHeight);

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
        $("#rwd-banners .next").click(function(){
            rwd_banners.trigger('rwd.next');
        })
        $("#rwd-banners .prev").click(function(){
            rwd_banners.trigger('rwd.prev');
        })
        $("#rwd-banners .play").click(function(){
            rwd_banners.trigger('rwd.play',1000); //rwd.play event accept autoPlay speed as second parameter
        })
        $("#rwd-banners .stop").click(function(){
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
        $("#rwd-inspiration .next").click(function(){
            rwd_inspiration.trigger('rwd.next');
        })
        $("#rwd-inspiration .prev").click(function(){
            rwd_inspiration.trigger('rwd.prev');
        })
        $("#rwd-banners .play").click(function(){
            rwd_inspiration.trigger('rwd.play',1000); //rwd.play event accept autoPlay speed as second parameter
        })
        $("#rwd-inspiration .stop").click(function(){
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
                //alert(imgHeight)
                Mall.rwdCarousel.alignComplementaryProductsPrices(this);
            }
        });

        // Custom Navigation Events
        // $("#rwd-complementary-product .next").click(function(){
        //   rwd_complementary_product.trigger('rwd.next');
        // })
        // $("#rwd-complementary-product .prev").click(function(){
        //   rwd_complementary_product.trigger('rwd.prev');
        // })

//rwd-recently-viewed

        var rwd_recently_viewed = $("#rwd-recently-viewed .rwd-carousel");
        if ( rwd_recently_viewed.length !=0 ) {

            rwd_recently_viewed.rwdCarousel({
                items : 7, //10 items above 1000px browser width
                itemsDesktop : [1000,5], //5 items between 1000px and 901px
                itemsDesktopSmall : [900,4], // betweem 900px and 601px
                itemsTablet: [600,4], //2 items between 600 and 0
                itemsMobile : [480,3], // itemsMobile disabled - inherit from itemsTablet option
                pagination : false,
                navigation: true,
                rewindNav : false,
                itemsScaleUp:false,
                jsonPath : Config.path.recentlyViewed,
                jsonSuccess : customDataSuccess,
                navigationText : false,
                afterUpdate: function(){
                    var imgHeight = rwd_recently_viewed.find('img').height()/2;
                    var imgHeightplus = rwd_recently_viewed.find('img').height()/2-10;
                    rwd_recently_viewed.next('.customNavigation').find('.prev').css({top:imgHeight+'px'});
                    rwd_recently_viewed.next('.customNavigation').find('.next').css({top:imgHeight+'px'});
                    rwd_recently_viewed.find('.rwd-controls').find('.rwd-prev').css({top:imgHeightplus+'px'});
                    rwd_recently_viewed.find('.rwd-controls').find('.rwd-next').css({top:imgHeightplus+'px'});
                },
                afterInit:function(){
                    imagesLoaded( document.querySelector('#rwd-recently-viewed'), function( instance ) {
                        var imgHeight = rwd_recently_viewed.find('img').height()/2;
                        var imgHeightplus = rwd_recently_viewed.find('img').height()/2-10;
                        rwd_recently_viewed.next('.customNavigation').find('.prev').css({top:imgHeight+'px'});
                        rwd_recently_viewed.next('.customNavigation').find('.next').css({top:imgHeight+'px'});
                        rwd_recently_viewed.find('.rwd-controls').find('.rwd-prev').css({top:imgHeightplus+'px'});
                        rwd_recently_viewed.find('.rwd-controls').find('.rwd-next').css({top:imgHeightplus+'px'});
                    });
                }
            });

            // Custom Navigation Events
            $("#rwd-recently-viewed .next").click(function(){
                rwd_recently_viewed.trigger('rwd.next');
            })
            $("#rwd-recently-viewed .prev").click(function(){
                rwd_recently_viewed.trigger('rwd.prev');
            });
        }



        function customDataSuccess(data){
            var content = "";
            if(data.content.length == 0) {
                $("#recently-viewed.recently-viewed-cls").hide();
            }

            for(var i in data["content"]){

                var redirect_url = data["content"][i].redirect_url;
                var image_url = data["content"][i].image_url;
                var title = data["content"][i].title;


                //content += "<img src=\"" +image_url+ "\" alt=\"" +title+ "\">"

                content += "<a href=\""+redirect_url+"\" class=\"simple\">";
                content += "<div class=\"box_listing_product\">";
                content += "<figure class=\"img_product\">";
                content += "<img src=\"" +image_url+ "\" alt=\"" +title+ "\">";
                content += "</figure>";
                content += "<div class=\"name_product hidden-xs\">"+title+"</div>";
                content += "</div>";
                content += "</a>";

            }
            $(rwd_recently_viewed).html(content);
        }


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
        var carouselStage = $('#zoom_gallery .carousel-stage').jcarousel();
        var carouselNavigation = $('#zoom_gallery .carousel-navigation').jcarousel();

        carouselStage.on('jcarousel:reload jcarousel:create', function () {
            var width = carouselStage.innerWidth();

            //if (width >= 600) {
            //    width = width / 3;
            //} else if (width >= 370) {
            //    width = width / 2;
            //}
            carouselStage.jcarousel('items').css('width', width + 'px');
        });
        // Setup the carousels. Adjust the options for both carousels here.





        /* carouselStage.touchwipe({
         wipeLeft: function() {
         carouselStage.jcarousel('next');
         },
         wipeRight: function() {
         carouselStage.jcarousel('prev');
         },
         min_move_x: 20,
         min_move_y: 20,
         preventDefaultEvents: false
         });*/

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
            $('#zoom_gallery .prev-stage')
                .on('jcarouselcontrol:inactive', function() {
                    $(this).addClass('inactive');
                })
                .on('jcarouselcontrol:active', function() {
                    $(this).removeClass('inactive');
                })
                .jcarouselControl({
                    target: '-=1'
                });

            $('#zoom_gallery .next-stage')
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
                min_move_x: 20,
                min_move_y: 20,
                preventDefaultEvents: false

            });





            // Setup controls for the navigation carousel
            $('#zoom_gallery .prev-navigation')
                .on('jcarouselcontrol:inactive', function() {
                    $(this).addClass('inactive');
                })
                .on('jcarouselcontrol:active', function() {
                    $(this).removeClass('inactive');
                })
                .jcarouselControl({
                    target: '-=1'
                });

            $('#zoom_gallery .next-navigation')
                .on('jcarouselcontrol:inactive', function() {
                    $(this).addClass('inactive');
                })
                .on('jcarouselcontrol:active', function() {
                    $(this).removeClass('inactive');
                })
                .jcarouselControl({
                    target: '+=1'
                });
            $('#zoom_gallery .jcarousel-pagination')
                .on('jcarouselpagination:active', 'a', function() {
                    $(this).addClass('active');
                })
                .on('jcarouselpagination:inactive', 'a', function() {
                    $(this).removeClass('active');
                })
                .jcarouselPagination();
        });


        /* =============================== CAROUSEL GALLERY Product MODAL ================================= */

        var connector = function(itemNavigation, carouselStage) {
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



        jQuery('ul.manufacturerList label').on('click', function() {
            var list = jQuery('ul.manufacturerList input:checked');
        })




    });
})(jQuery);

/*function ratySidebar() {

 }*/

(function($,sr){

    // debouncing function from John Hann
    // http://unscriptable.com/index.php/2009/03/20/debouncing-javascript-methods/
    var debounce = function (func, threshold, execAsap) {
        var timeout;

        return function debounced () {
            var obj = this, args = arguments;
            function delayed () {
                if (!execAsap)
                    func.apply(obj, args);
                timeout = null;
            };

            if (timeout)
                clearTimeout(timeout);
            else if (execAsap)
                func.apply(obj, args);

            timeout = setTimeout(delayed, threshold || 100);
        };
    }
    // smartresize
    jQuery.fn[sr] = function(fn){  return fn ? this.bind('resize', debounce(fn)) : this.trigger(sr); };

})(jQuery,'smartresize');


jQuery(window).load(function() {


});
