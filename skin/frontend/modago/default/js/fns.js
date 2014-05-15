jQuery.noConflict();
(function( $ ) {
  $(function() {

    var rwd_banners = $("#rwd-banners .rwd-carousel");
 
      rwd_banners.rwdCarousel({
          items : 3, //10 items above 1000px browser width
          itemsDesktop : [1000,3], //5 items between 1000px and 901px
          itemsDesktopSmall : [900,2], // betweem 900px and 601px
          itemsTablet: [600,2], //2 items between 600 and 0
          itemsMobile : [480,1], // itemsMobile disabled - inherit from itemsTablet option
          pagination : false,
           itemsScaleUp:true
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
           itemsScaleUp:true
      });

      console.log(rwd_inspiration);
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



     
    // END RWD CAROUSEL
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
          toggleSidebar();
          filterColor();
          deleteCurrentFilter();
          clearFilter();
          closeBlockFilter();
       });
        
     } else {
        $(".fb-slidebar-inner").find('.sidebar').remove();
        $("#sidebar").load("_include/sidebar.inc", function(){
          toggleSidebar();
          filterColor();
          deleteCurrentFilter();
          clearFilter();
        });
        
     }
    $(window).resize(function() {
        if($(window).width() < 768) {
            $("#sidebar").find('.sidebar').remove();
            $(".fb-slidebar-inner").load("_include/sidebar.inc", function(){
              toggleSidebar();
              filterColor();
              deleteCurrentFilter();
              clearFilter();
              closeBlockFilter();
            });
            if ($('body').hasClass('noscroll')) {
              var screenWidth = $(window).width();
              var screenHeight = $(window).height();
              $('div.noscroll').css({
                width:screenWidth,
                height:screenHeight
              });

            };

            
         } else {
            $(".fb-slidebar-inner").find('.sidebar').remove();
            $("#sidebar").load("_include/sidebar.inc", function(){
              toggleSidebar();
              filterColor();
              deleteCurrentFilter();
              clearFilter();
            });
            $('#sb-site').removeClass('open');
            $('.fb-slidebar').removeClass('open');
            $('body').removeClass('noscroll').find('.noscroll').remove();
         }
    });
};
// END 

$("#link_basket").on('click', '.dropdown-toggle', function(event) {
    event.preventDefault();
    var thisLink = $(this);
    thisLink.closest('li').addClass('open');
    var blockSearch = $('#dropdown-basket');
    blockSearch.toggle();

 });

$(document).mouseup(function (e){
  var container = $("#dropdown-basket");
  if(container.is(":visible")){
    
    if (!container.is(e.target) && container.has(e.target).length === 0) {
    //hide here
    $('#user_menu').find('.open').removeClass('open');
    $('#dropdown-basket').hide();
    }
  }
});



$("#toggleSearch").on('click', '.dropdown-toggle', function(event) {
    var thisLink = $(this);
    thisLink.closest('#toggleSearch').addClass('open');
    var blockSearch = $('#dropdown-search');
    blockSearch.toggle();

 });



$(document).mouseup(function (e){
  var container = $("#dropdown-search");
  if(container.is(":visible")){
    
    if (!container.is(e.target) && container.has(e.target).length === 0) {
    //hide here
    $('#toggleSearch').removeClass('open');
    $('#dropdown-search').hide();
    }
  }
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



      $(".select-styled,.select-styled select").selectbox();
      
      //$('.dropdown-toggle').dropdown();
      
     
    //var $container = $('.jsMasonry');
    // initialize
    //$container.masonry({
    //  itemSelector: '.box',
    //  isResizable: true
    //}).data('masonry');
if ($('body').hasClass('node-type-list')) {
    imagesLoaded( document.querySelector('#items-product'), function( instance ) {
      console.log('all images are loaded');
    });


    var $container = $('.jsMasonryContent').masonry();
    // layout Masonry again after all images have loaded
    $container.imagesLoaded( function() {
      $container.masonry({
        itemSelector: '.box'
      });
    });

};

    // checkbox & radiobox 

masonryMenu();
toggleSidebar();
filterColor();
cloneMenu()
closeMenu();
responsJcarousel();
recentlyViewed();
showSubMenuMobile();
//$.slidebars();
//$.filterbars();
activeMenu();
deleteCurrentFilter();
clearFilter();

linkCloseMenu();

//actionViewFilter();
//toogleFilterBlock();

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

          




var $containerContent = $('.jsMasonryContent');
    $containerContent.masonry({
      //columnWidth: 100,
      itemSelector: '.item'
    });

function closeBlockFilter(){
  $('.noscroll').on('click',  function(event) {
      event.preventDefault();
      /* Act on the event */
      console.log('click');
      $('#sb-site').removeClass('open');
      $('.fb-slidebar').removeClass('open');
      $('body').removeClass('noscroll');
      $('body').find('.noscroll').remove();
  });

}

function actionViewFilter() {
  $('#view-current-filter').on('click', '.actionViewFilter', function(event) {
    event.preventDefault();

    $('#sidebar').removeClass('hidden-xs').show(10, function(){

      $('#sidebar').animate({'left': 0}, 300).css('z-index', '9000');
    });
  });
}

function deleteCurrentFilter() {
  $('.current-filter, .view_filter').on('click', '.fa-times', function(event) {
    event.preventDefault();
    var lLabel = $(this).closest('dd').find('.label').length - 1;
    console.log(lLabel);
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


function clearFilter(){
  $('.block-filter').on('click', '.clear', function(event) {
    event.preventDefault();
    /* Act on the event */
    $(this).closest('.content').find('input[type="checkbox"]:checked').removeAttr('checked');
  });
}


function filterColor() {
  $('#filter_color').find('label').each(function(index, el) {
    var colorFilter = $(this).data('color');
    $(this).find('span').children('span').css({
      'background-color': colorFilter
    });

  });
}


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
      })
      
    });
    
    
  });
}

    
function cloneSubMenu() {
	var elMenu = $('#navigation > ul > li > a');
	$('#navigation').on('click', '[data-toggle="dropdown"]', function(event) {
		event.preventDefault();
		/* Act on the event */
		//$(this).addClass('class_name');
	});
}

function activeMenu() {
  $('#nav_desc').on('click', 'a', function(event) {
    event.preventDefault();
    $(this).closest('#nav_desc').find('.active').removeClass('active');
    $(this).closest('#nav_desc').find('.fa-caret-up').removeClass('fa-caret-up');
    $(this).find('i').addClass('fa-caret-up');
    $(this).addClass('active');
  });
}


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

function showSubMenuMobile(){
  var mobileMenu = $('#nav_mobile');

  mobileMenu.on('click', 'a', function(event) {
    event.preventDefault();
    //$(this).closest(mobileMenu).find('.open').removeClass('open');
    $(this).next('ul').toggleClass('open');

    
  });
}



function masonryMenu() {
	$('.header_bottom .dropdown').on('shown.bs.dropdown', function () {
			var $container = $('.jsMasonry');
			  $container.masonry({
			    itemSelector: '.box'
			  });
	});
}



// HELPER MODAL LOGO SALLEr

  

  $('#seller_description').on('shown.bs.modal', function (e) {
    var sallerlogo = $('.logo_salles').find('img');
    var sallerlogoHeight = sallerlogo.height();

    sallerlogo.css({
      'margin-top' : sallerlogoHeight+'px'
    });

})


// RATY{ path: 'assets/images' }

$('#average_rating').raty({
  path: 'skin/frontend/orba/modago/images/raty',
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
  path: 'skin/frontend/orba/modago/images/raty',
  starOff : 'star-off-custom.png',
  starOn  : 'star-on-custom.png',
  size   : 17,

  number: function() {
    return $(this).attr('data-number');
  },
  score: function() {
    return $(this).attr('data-score');
  }
});

$('.comment_rating').raty({
  path: 'skin/frontend/orba/modago/images/raty',
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

// RATING 
$('body').find('.rating').each(function(index, el) {
  var rating = $(this).data('percent');
  $(this).children('span').animate({width:rating+'%'}, 1000);

});

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
});
/* =============================== END::// CAROUSEL GALLERY Product ================================= */



/* =============================== CAROUSEL GALLERY Product MODAL ================================= */
$('#product_gallery').on('show.bs.modal', function (e) {


   var connectorModal = function(itemNavigation, carouselStage) {
      return carouselStage.jcarousel('items').eq(itemNavigation.index());
  };


      // Setup the carousels. Adjust the options for both carousels here.
      var carouselStage = $('.carousel-stage-modd').jcarousel();
      var carouselNavigation = $('.carousel-navigation-modd').jcarousel();

      // We loop through the items of the navigation carousel and set it up
      // as a control for an item from the stage carousel.
      carouselNavigation.jcarousel('items').each(function() {
          var item = $(this);
          //item.append('<div class="shadow"></div>')
          // This is where we actually connect to items.
          var target = connectorModal(item, carouselStage);

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
  // ZOOM
    $(".my-foto").imagezoomsl({
                      
      zoomrange: [1, 12],
       zoomstart: 4,
       innerzoom: true,
       magnifierborder: "none"    
    });
});
/* =============================== END::// CAROUSEL GALLERY Product MODAL ================================= */




/* FUNCTON RESPONSIVE JCAROUSEL */

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





  });
})(jQuery);

