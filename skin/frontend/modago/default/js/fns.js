jQuery.noConflict();
(function( $ ) {
  $(function() {
      $(document).ajaxComplete(function(event, xhr, settings) {
        dropDownSelectListAjax();
         var scroll = $('body').find('.mCustomScrollbar');
          if (scroll.length >= 1) {
          scroll.mCustomScrollbar({
                setHeight:100,
                theme:"dark-thick",
                scrollButtons:{
                   enable:true
                }
              });
          };
          var sliderRange = $( "#slider-range" );
          if (sliderRange.length >= 1) {
          $( "#slider-range" ).slider({
                  range: true,
                  min: 0,
                  max: 500,
                  values: [ 75, 300 ],
                  slide: function(event, ui) {
                      $("#zakres_min").val(ui.values[0]);
                      $("#zakres_max").val(ui.values[1]);
                  }

                }); 

               $("#zakres_min").val($("#slider-range").slider("values", 0));
               $("#zakres_max").val($("#slider-range").slider("values", 1));
          };
      });
//equalizeHeights();
var flags_container = $('.carousel-stage');
if (flags_container.length > 0 ) {
  
};

if ($('body').hasClass('node-type-list')) {
  equalizeHeights();
  var container = document.querySelector('.jsMasonryContent');
  notifElem = document.querySelector('#notification'); 
    imagesLoaded( document.querySelector('#items-product'), function( instance ) {
        var msnry = new Masonry( container, {
         itemSelector: '.item',
         isResizable: true
      });
        msnry.on( 'layoutComplete', function( msnryInstance, laidOutItems ) {
          equalizeHeights()
        });
        equalizeHeights()

    });






};




function equalizeHeights() {
  var heights = new Array();
  $('#items-product .item').each(function() {
    heights.push($(this).height());
  });
  var min = Math.min.apply( Math, heights );
  //console.log(min)
  var con = $('#items-product').innerHeight();
  $('#items-product').css('height', 'auto');
  $('#items-product').css('height', con-min);
}




/*

if ($('body').hasClass('node-type-list')) {
    imagesLoaded( document.querySelector('#items-product'), function( instance ) {
      //console.log('all images are loaded');
          var $containerContent = $('.jsMasonryContent');
                     // initialize
                     $containerContent.masonry({
                      itemSelector: '.item',
                      isResizable: true
                     });
                    
         var jsMasonryContent = $('.jsMasonryContent').height(),
              jsMasonryContent = jsMasonryContent-250;
              //console.log(jsMasonryContent)
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
         // console.log('test');
          $(this).closest('form').find('div.orders_someone_else').toggleClass('hidden');
          //hiddenEl.show();
        });


      }
      
       
       var footerHeight = 0,
           footerTop = 0,
           $footer = $("#footer");
           positionFooter();
       function positionFooter() {
            var  sbsite = $('#sb-site').height();
            //console.log(sbsite);
       
                footerHeight = $footer.height();
    
       
               if ( (sbsite) < $(window).height()) {
                   $footer.css({
                        position: "fixed",
                        bottom: "0px",
                        left:"0px",
                        width:"100%"
                   })
               } else {
                   $footer.css({
                        position: "static"
                   })
               }
               
       }

       $(window)
               .scroll(positionFooter)
               .resize(positionFooter)
               


      var intFrameWidth = window.innerWidth;
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
        event.preventDefault();
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
      var headList = $('.button-select.ajax');
      var listSelect = $('.dropdown-select ul');
      headList.on('click', function(event) {
        event.preventDefault();
        $(this).next('.dropdown-select').stop(true).slideToggle(200);
      });
      listSelect.on('click', 'a', function(event) {
        event.preventDefault();
        var thisVal = $(this).html();
        $(this).closest('.select-group').find('.button-select').html(thisVal+'<span class="down"></span>');
        $(this).closest('.dropdown-select').slideUp(200);
      });
       $(document).click(function(e) {
            if (!$(e.target).parents().andSelf().is('.select-group')) {
                $(".dropdown-select").slideUp(200);
            }
        });

    }
    dropDownSelectListNoAjax();
    function dropDownSelectListNoAjax() {
      var headList = $('.button-select.no-ajax');
      var listSelect = $('.dropdown-select ul');
      headList.on('click', function(event) {
        event.preventDefault();
        $(this).next('.dropdown-select').stop(true).slideToggle(200);
      });
      listSelect.on('click', 'a', function(event) {
        event.preventDefault();
        var thisVal = $(this).html();
        $(this).closest('.select-group').find('.button-select').html(thisVal+'<span class="down"></span>');
        $(this).closest('.dropdown-select').slideUp(200);
      });
       $(document).click(function(e) {
            if (!$(e.target).parents().andSelf().is('.select-group')) {
                $(".dropdown-select").slideUp(200);
            }
        });

    }

    var scroll = $('body').find('.mCustomScrollbar');
    if (scroll.length >= 1) {
    scroll.mCustomScrollbar({
          setHeight:100,
          theme:"dark-thick",
          scrollButtons:{
             enable:true
          }
        });
    };


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
          if (widthBlock > widthThis) {
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

          

          //console.log('widthThis' + widthThis + ' :: PriceBlock' + childrenPrice + ' :: LikeBlock' +  childrenLike + ' :: Szerokość bloku ' + widthBlock)

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

        

          //console.log(oldPrice)
    }
    $('input[type=text],input[type=email],input[type=password],textarea ').tooltip({
        placement: "auto",
        trigger: "focus"
        //container: 'body'
    });

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

        //console.log(htmlHintWidth);
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
      //console.log(itemProductId);
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
  
      $('#accordion').on('click', '.panel-title a', function () {
         $(this).find('i').toggleClass('bullet-strzalka-down bullet-strzalka-up');
      })

      var toggleXS = $('body').find('.toggle-xs');
      //
          $('.toggle-xs').on('click', '.title_section', function(event) {
            var intFrameWidth = window.innerWidth;
            if(intFrameWidth < 768) {
              event.preventDefault();
              $(this).closest('.section').find('.main, .rwdCarousel').slideToggle();
              $(this).find('i').toggleClass('bullet-strzalka-down bullet-strzalka-up');
            };
            //console.log('test')
          });



    jQuery.fn.resize_delayed = function ( func, duration ){
        this.resize(function() {
            clearTimeout( window.____resize_delayed );
            window.____resize_delayed = setTimeout( func, duration);
        });
    };

    // USAGE:
   /* jQuery( window ).resize_delayed( function (){

        console.log( 'resize_delayed' );
        jsMasonryContentHeight();
        function jsMasonryContentHeight() {
          var jsMasonryContent = 0;
          var jsMasonryContent = $('.jsMasonryContent').height(),
              jsMasonryContentH = jsMasonryContent;
           $('.jsMasonryContent').css('height', jsMasonryContentH-300);
           console.log(jsMasonryContentH)
        }

    }, 500 );*/

     $(window).on("resize", function() {
       var intFrameWidth = window.innerWidth;
       //console.log('Width: '+intFrameWidth)
       listinghelper();
       if (intFrameWidth > 768) {
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
        //$('body.node-type-view-product').find('.section').find('.title_section').closest('header').children('h2').children('i').removeClass('bullet-strzalka-up').addClass('bullet-strzalka-down')
        //$('body.node-type-view-product').find('.section').find('.title_section').closest('header').next('div').css({
        //  display: 'none'
        //});
      };
     });


       





    
      $('#header').on('click', '.toggleMenu', function(event) {
        event.preventDefault();
        var screenWidth = $(window).width();
        var screenHeight = $(window).height();
        $('body').toggleClass('sb-open noscroll');
        $('#sb-site').toggleClass('open');
        $('.sb-slidebar').toggleClass('sb-active');
        $('body').addClass('noscroll').append('<div class="noscroll" style="width:100%; height:'+screenHeight+'px"></div>');
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

    if($(window).width() < 768) {
       $("#sidebar").find('.sidebar').remove();
       $(".fb-slidebar-inner").load("_include/sidebar.inc", function(){
          init();
          initScrollBarFilterMarka();
          visibleBtnClearFilterSize();
       });
        
     } else {
        $(".fb-slidebar-inner").find('.sidebar').remove();
        $("#sidebar").load("_include/sidebar.inc", function(){
          //filterManufacturerUnCheked()
          init();
          initScrollBarFilterMarka();
          visibleBtnClearFilterSize();
          /*$( "#slider-range" ).slider({
                  range: true,
                  min: 0,
                  max: 500,
                  values: [ 75, 300 ],
                  slide: function(event, ui) {
                      $("#zakres_min").val(ui.values[0]);
                      $("#zakres_max").val(ui.values[1]);
                  }

                }); */
        });
        
     }
 
  $(window).on("resize", function() {
    
      //var wh = parseInt($(window).innerWidth());

      var intFrameWidth = window.innerWidth;
      
      $('.toggle-xs').find('.main').show();
      //console.log(intFrameWidth)
        if(intFrameWidth < 768) {
            $("#sidebar").find('.sidebar').remove();
            $(".fb-slidebar-inner").one().load("_include/sidebar.inc", function(){
              init();
              initScrollBarFilterMarka();
              clearFilterManufacturerCheked();
              visibleBtnClearFilterSize();
            });
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
            $(".fb-slidebar-inner").find('.sidebar').remove();
            $("#sidebar").load("_include/sidebar.inc", function(){
              init();
              initScrollBarFilterMarka();
              clearFilterManufacturerCheked();
              visibleBtnClearFilterSize();
              $( "#slider-range" ).slider({
                 range: true,
                 min: 0,
                 max: 500,
                 values: [ 75, 300 ],
                 slide: function(event, ui) {
                     $("#zakres_min").val(ui.values[0]);
                     $("#zakres_max").val(ui.values[1]);
                 }

               }); 

            });
            $('#sb-site').removeClass('open');
            $('.fb-slidebar').removeClass('open');
            $('body').removeClass('noscroll').find('.noscroll').remove();
            
         }
    });
};
// END 


$("#link_basket .dropdown-toggle").on('click', function(event) {
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
    event.preventDefault();
});

$(document).click(function(event){
  var container = $("#dropdown-basket");
  if (container.is(":visible")) {
      event.preventDefault();
      $('#user_menu').find('.open').removeClass('open');
      $('#dropdown-basket').hide();
    };
});

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




$(document).mouseup(function (e){
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
});






$('.actionViewFilter').on('click', function(event){
        event.preventDefault();
        $('#sb-site').toggleClass('open');
        $('.fb-slidebar').toggleClass('open');
            var screenWidth = $(window).width();
            var screenHeight = $(window).height();
            $('body').addClass('noscroll').append('<div class="noscroll" style="width:100%; height:'+screenHeight+'px"></div>');
            //$("#sidebar").slideToggle();    
   });




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
  path: 'skin/frontend/modago/default/images/raty',
  starOff : 'star-off-big-custom.png',
  starOn  : 'star-on-big-custom.png',
  starHalf  : 'star-half-big-custom.png',
  size   : 22,
  readOnly: true,
  half     : true,
  number: function() {
    return $(this).attr('data-number');
  },
  score: function() {
    return $(this).attr('data-score');
  }
});

$('.raty_note dd div').raty({
  path: 'skin/frontend/modago/default/images/raty',
  starOff : 'star-off-custom.png',
  starOn  : 'star-on-custom.png',
  //readOnly: true,
  size   : 17,

  number: function() {
    return $(this).attr('data-number');
  },
  score: function() {
    return $(this).attr('data-score');
  }
});

$('.ratings tr td div').raty({
  path: 'skin/frontend/modago/default/images/raty',
  starOff : 'star-off-custom.png',
  starOn  : 'star-on-custom.png',
  //readOnly: true,
  size   : 17,

  number: function() {
    return $(this).attr('data-number');
  },
  score: function() {
    return $(this).attr('data-score');
  }
});

$('.comment_rating').raty({
  path: 'skin/frontend/modago/default/images/raty',
  starOff : 'star-off-custom.png',
  starOn  : 'star-on-custom.png',
  size   : 17,
  readOnly: true,
  number: function() {
    return $(this).attr('data-number');
  },
  score: function() {
    return $(this).attr('data-score');
  }
});
$('#average_note_client .note').raty({
    path: 'skin/frontend/modago/default/images/raty',
    starOff : 'star-small-off.png',
    starOn  : 'star-small.png',
    size   : 13,
    readOnly: true,
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
        //console.log(filterSizeLenght);
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
  var filterType = $('#filter_type');
  filterType.on('click', ':checkbox', function(event) {
    filterType.find('.clear').removeClass('hidden');

      var filterTypeLenght = filterType.find(':checked').length;
        //console.log(filterSizeLenght);
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
        //console.log(filterSizeLenght);
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
      //console.log(filterStyleSelectVal)
  });

      filterStyleCheckbox.on('click', ':checkbox', function(event) {
          var filterStyleCheckboxLenght = filterStyleCheckbox.find(':checked').length;
            //console.log(filterStyleCheckboxLenght);
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
        //console.log(filterSizeLenght);
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

    

      var filterSize = $('#filter_size');
      var btnClear = $('.action.clear');
      filterSize.on('click', ':checkbox', function(event) {
        /* Act on the event */
        var filterSizeLenght = $('#filter_size').find(':checked').length;
        //console.log(filterSizeLenght);
        if (filterSizeLenght >= 1) {
          filterSize.find('.action').removeClass('hidden');
        } else {
          filterSize.find('.action').addClass('hidden');
          };
      });
      filterSize.on('click', '.clear', function(event) {
        var filterSizeLenght = $('#filter_size').find(':checked').length;
        event.preventDefault();
        //if (filterSizeLenght === 0) {
          $(this).closest('div.action.clear').addClass('hidden');
        //};
        
      });
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
  var fm = $('body').find(".filter_manufacturerScrollbar");

  if (fm.length >= 1) {
  fm.mCustomScrollbar({
         scrollButtons:{
             enable:true
         },
         advanced:{  
                 updateOnBrowserResize:true  
               } // removed extra commas  
     });
  };



}
function initScrollBarFilterStyle() {
 var fs = $(".styledSelected.scrollbar .sbOptionsWrapper");
  if (fs.length >= 1) {
     $(".styledSelected.scrollbar .sbOptionsWrapper").mCustomScrollbar({
      
      setHeight: '200px',
        scrollButtons:{
            enable:true
        },
        theme:"dark-thick"
    });
  }; 
}

// MENU MOBILE

function showSubMenuMobile(){
  var mobileMenu = $('#nav_mobile');

  mobileMenu.on('click', 'a', function(event) {
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
        event.preventDefault();
        if ($(this).hasClass('children')) {
          containerCloneMenu.html('');
          $(this).next('ul').clone().appendTo(containerCloneMenu).css('width', '100%').slideDown(300,function(){
            var $container = $('.jsMasonry');
            $container.masonry({
              itemSelector: '.box'
            });
          });
        } else {
          containerCloneMenu.html('');
        };
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
    event.preventDefault();
    $(this).closest('#nav_desc').find('.active').removeClass('active');
    $(this).closest('#nav_desc').find('.fa-caret-up').removeClass('fa-caret-up');
    $(this).find('i').addClass('fa-caret-up');
    $(this).addClass('active');
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
  var elCliced = $('.sidebar').find('h3');
  elCliced.on('click', function(event) {
     $(this).children('i').toggleClass('fa-chevron-down');
    //event.preventDefault();
      $(this).next('.content').toggle(function() {
      $(this).next('.content').stop(true,true).show().closest('.section').find('h3').find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
      $(this).children('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
      
    }, function() {
      $(this).next('.content').stop(true,true).hide(function(){
        $(this).closest('.section').find('h3').children('i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
      });  
    });   
  });
}

// FILTRY
function actionViewFilter() {
  $('#view-current-filter').on('click', '.actionViewFilter', function(event) {
    event.preventDefault();
//<<<<<<< HEAD

      //$('#sidebar').removeClass('hidden-xs').show(10, function(){

      //$('#sidebar').animate({'left': 0}, 300).css('z-index', '9000');
    //});
  });
}
// AKTYWNE FILTRY
function deleteCurrentFilter() {
  $('.current-filter, .view_filter').on('click', '.fa-times', function(event) {
    event.preventDefault();
    var lLabel = $(this).closest('dd').find('.label').length - 1;
    //console.log(lLabel);
    if (lLabel >= 1) {
      $(this).closest('.label').remove();
    } else {
      $(this).closest('dl').remove();
    };
  });
  $('.current-filter, .view_filter').on('click', '.clearAll', function(event) {
    event.preventDefault();
    $(this).closest('dl').remove();
  });
}
// ZAMKNIĘCIE BLOKU Z FILTRAMI
function closeBlockFilter(){
  $('.noscroll').on('click',  function(event) {
      event.preventDefault();
      /* Act on the event */
      //console.log('click');
      $('#sb-site').removeClass('open');
      $('.fb-slidebar').removeClass('open');
      $('body').removeClass('noscroll');
      $('body').find('.noscroll').remove();
  });
//=======
   
//>>>>>>> 3ae349a31563aaffc7b5c939377ba35c676c0e36

}

// FILTROWANIE LISTY PO WPISANIU WYRAZU/LITERY
function filterList(){
    $('#filter_manufacturer_search').keyup(function(){
        var valThis = $(this).val().toLowerCase();
        var noresult = 0;
        if(valThis == ""){
            $('.manufacturerList > li').show();
            noresult = 1;
          $('.no-results-found').remove();
        } else {
            $('.manufacturerList > li').each(function(){
                var text = $(this).text().toLowerCase();
                var match = text.indexOf(valThis);
                if (match >= 0) {
                    $(this).show();
                    noresult = 1;
                $('.no-results-found').remove();
                } else {
                    $(this).hide();
                }
            });
       };
        if (noresult == 0) {
            $(".manufacturerList").append('<li class="no-results-found">Brak wyników.</li>');
        }

    });

    $('.manufacturerList').on('click', ':checkbox', function(event) {
          $('#filter_manufacturer_search').val('');
          $('.manufacturerList').find('li').css('display', 'list-item');
        });


    $('.block-filter').on('click', '.clear', function(event) {
      event.preventDefault();
      /* Act on the event */
      $(this).closest('.content').find('input[type="text"]').val('');
      $('.manufacturerList > li').each(function(){
        $(this).show();
      });
    });
}

// KOLEJKOWANIE WYBRANYCH PÓL
function filterManufacturerCheked() {
  
    
    var list = $(".manufacturerList"),
        origOrder = list.children();
    var fm = $(".filter_manufacturerScrollbar");
    list.on("click", ":checkbox", function() {

        var i, checked = document.createDocumentFragment(),
            unchecked = document.createDocumentFragment();
        for (i = 0; i < origOrder.length; i++) {
            if (origOrder[i].getElementsByTagName("input")[0].checked) {
                checked.appendChild(origOrder[i]);
            } else {
                unchecked.appendChild(origOrder[i]);
            }
           //console.log(origOrder[i])
        }
        list.append(checked).append(unchecked).children('li');
        
        var listChek = list.find(':checked');
        var listChekLastName = listChek.last();
        var btnClearMark = $('#filter_manufacturer').find('.action.clear');
        list.find('.lastChecked').removeClass('lastChecked');
        listChekLastName.closest('li').addClass('lastChecked');
        fm.mCustomScrollbar("scrollTo",0);
        var checkEl = list.find(':checked').length;
        if (checkEl >= 1) {
          $(this).closest('#filter_manufacturer').find('.action.clear').removeClass('hidden');
        } else {
          $(this).closest('#filter_manufacturer').find('.action.clear').addClass('hidden');
          };

        //console.log(checkEl);
    });
    var btnClearMark = $('#filter_manufacturer')
    btnClearMark.on('click', '.clear', function(event) {
      $(this).closest('.action.clear').addClass('hidden');
    });
    
}

function clearFilterManufacturerCheked() {
      var blockList = $("#filter_manufacturer")
      var list = $(".manufacturerList"),
      origOrder = list.children();
      blockList.on('click', 'a.clear', function(event) {
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
                   //console.log(origOrder[i])
                }    
    });


}



// OBSŁUGA FILTRA KOLOR

function filterColor() {
  $('#filter_color').find('label').each(function(index, el) {
    var colorFilter = $(this).data('color');
    $(this).find('span').children('span').css({
      'background-color': colorFilter
    });

  });

  var filterColor = $('#filter_color');
  filterColor.on('click', ':checkbox', function(event) {
    filterColor.find('.clear').removeClass('hidden');

      var filterColorLenght = filterColor.find(':checked').length;
        //console.log(filterSizeLenght);
        if (filterColorLenght >= 1) {
          filterColor.find('.action').removeClass('hidden');
        } else {
          filterColor.find('.action').addClass('hidden');
          };
  });
   filterColor.on('click', '.clear', function(event) {
    $(this).closest('.action').addClass('hidden');
  });


}
// CZYSZCZENIE FILTRÓW
function clearFilter(){
  $('.block-filter').on('click', '.clear', function(event) {
    event.preventDefault();
    $(this).closest('.content').find('input[type="checkbox"]:checked').removeAttr('checked');
  });
}
// WYROWNANIE COLUMN 
function equalColumn() {
  
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
          rewindNav : false
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
           rewindNav : false,
           itemsScaleUp:true,
           afterUpdate: function(){
              var imgHeight = rwd_complementary_product.find('img').height()/2;
              rwd_complementary_product.next('.customNavigation').find('.prev').css({top:imgHeight+'px'});
              rwd_complementary_product.next('.customNavigation').find('.next').css({top:imgHeight+'px'});
           },
           afterInit:function(){
              var imgHeight = rwd_complementary_product.find('img').height()/2;
              rwd_complementary_product.next('.customNavigation').find('.prev').css({top:imgHeight+'px'});
              rwd_complementary_product.next('.customNavigation').find('.next').css({top:imgHeight+'px'});
              //alert(imgHeight)
           }
      });

      // Custom Navigation Events
      $("#rwd-complementary-product .next").click(function(){
        rwd_complementary_product.trigger('rwd.next');
      })
      $("#rwd-complementary-product .prev").click(function(){
        rwd_complementary_product.trigger('rwd.prev');
      })
      $("#rwd-complementary-product .play").click(function(){
        rwd_complementary_product.trigger('rwd.play',1000); //rwd.play event accept autoPlay speed as second parameter
      })
      $("#rwd-complementary-product .stop").click(function(){
        rwd_complementary_product.trigger('rwd.stop');
      });


      //rwd-recently-viewed

      var rwd_recently_viewed = $("#rwd-recently-viewed .rwd-carousel");

      rwd_recently_viewed.rwdCarousel({
          items : 7, //10 items above 1000px browser width
          itemsDesktop : [1000,5], //5 items between 1000px and 901px
          itemsDesktopSmall : [900,4], // betweem 900px and 601px
          itemsTablet: [600,4], //2 items between 600 and 0
          itemsMobile : [480,3], // itemsMobile disabled - inherit from itemsTablet option
          pagination : false,
           rewindNav : false,
           itemsScaleUp:true,
           afterUpdate: function(){
              var imgHeight = rwd_recently_viewed.find('img').height()/2;
              rwd_recently_viewed.next('.customNavigation').find('.prev').css({top:imgHeight+'px'});
              rwd_recently_viewed.next('.customNavigation').find('.next').css({top:imgHeight+'px'});
           },
           afterInit:function(){
              var imgHeight = rwd_recently_viewed.find('img').height()/2;
              rwd_recently_viewed.next('.customNavigation').find('.prev').css({top:imgHeight+'px'});
              rwd_recently_viewed.next('.customNavigation').find('.next').css({top:imgHeight+'px'});
           }
      });

      // Custom Navigation Events
      $("#rwd-recently-viewed .next").click(function(){
        rwd_recently_viewed.trigger('rwd.next');
      })
      $("#rwd-recently-viewed .prev").click(function(){
        rwd_recently_viewed.trigger('rwd.prev');
      })
      $("#rwd-recently-viewed .play").click(function(){
        rwd_recently_viewed.trigger('rwd.play',1000); //rwd.play event accept autoPlay speed as second parameter
      })
      $("#rwd-recently-viewed .stop").click(function(){
        rwd_recently_viewed.trigger('rwd.stop');
      });



     
    // END RWD CAROUSEL
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
                //console.log(width)
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
            },
            wipeRight: function() {
              $(".carousel-stage").jcarousel('scroll', '-=1');
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
    //console.log(list);
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