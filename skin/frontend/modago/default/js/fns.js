jQuery.noConflict();
(function( $ ) {
  $(function() {

  						var wi = $(window).width();
  						     
  		  		 		
  		  		        if (wi <= 480){
  		  		            $('body').find('.jsMasonry').removeClass('jsMasonry');
  		  		            }
                    else if  (wi >= 768) {
                       
                          cloneMenu();
                          masonryMenu();
                       
                    }
  		  		        else if (wi <= 767){
  		  		              $('.header_bottom').removeClass('cdmm');
  		  		              $('.header_bottom').find('.cdmm-fw').removeClass('cdmm-fw');
  		  		            }
                    else if (wi >= 767){
  
                          
                        }
  		  		        else if (wi <= 980){
  		  		            
  		  		            }
  		  		        else if (wi <= 1200){
  		  		             
  		  		            } 
  		  		            else if (wi > 768) {
  		  		            	
  		  		            }
  		  		        else {
  		  		             
  		  		            }
  		 
  		      


      $(".select-styled,.select-styled select").selectbox();
      $(".fhmm").fitVids();
      $('.dropdown-toggle').dropdown();
      $(document).on('click', '.fhmm .dropdown-menu', function(e) {
        e.stopPropagation()
      })
      $('nav#sliding-menu').mmenu({
		slidingSubmenus: false
		});
    var $container = $('.jsMasonry');
    // initialize
    $container.masonry({
      //columnWidth: 200,
      itemSelector: '.box'
    });
    // checkbox & radiobox 
    
cloneSubMenu();
toggleSidebar();
filterColor();

var $containerContent = $('.jsMasonryContent');
    $containerContent.masonry({
      //columnWidth: 100,
      itemSelector: '.item'
    });




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
     $(this).children('span').toggleClass('glyphicon-chevron-down');
    //event.preventDefault();
      $(this).next('.content').toggle(function() {
      $(this).next('.content').stop(true,true).slideDown('fast', function() {
        
      }).closest('.section').find('h3').find('span').removeClass('glyphicon-chevron-up').addClass('glyphicon-chevron-down');
      $(this).children('span').removeClass('glyphicon-chevron-up').addClass('glyphicon-chevron-down');
      
    }, function() {
      $(this).next('.content').stop(true,true).slideUp('fast', function(){
        $(this).closest('.section').find('h3').children('span').removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-up');
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


function cloneMenu() {
  

	    
      $('.header_bottom #nav_desc .dropdown').on('show.bs.dropdown', function () {
		    	var h = $(this).find('.dropdown-menu');
		    	h.css('display', 'none');
		    	h.clone().appendTo('#clone_submenu .container').css('width', '100%').slideDown(300,function(){
		    			  	var $container = $('.jsMasonry');
		    			    $container.masonry({
		    			      itemSelector: '.box'
		    			    });
		    	});	   	
		});

		$('.header_bottom .dropdown').on('hidden.bs.dropdown', function () {			
				$('#clone_submenu .container').children('ul').slideUp(300,function(){
				    $('#clone_submenu .container').children('ul').remove();
				})			
		});

   

};



function masonryMenu() {
	$('.header_bottom .dropdown').on('shown.bs.dropdown', function () {
			var $container = $('.jsMasonry');
			  $container.masonry({
			    itemSelector: '.box'
			  });
	});
}


$(window).resize(function() {
              var wi = $(window).width();
       
              if (wi <= 480){
                  $('body').find('.jsMasonry').removeClass('jsMasonry');
                  }
              else if (wi <= 767){
                    $('.header_bottom').removeClass('cdmm');
                    $('.header_bottom').find('.cdmm-fw').removeClass('cdmm-fw');
                  }
               else if (wi >= 767){
                  $('.header_bottom').removeClass('cdmm');
                      masonryMenu();
                       cloneMenu();
                  }
              else if (wi <= 980){
                  
                  }
              else if (wi <= 1200){
                   
                  } 
                   else if (wi >= 768) {
                         
                        }
              else {
                   
                  }
          }); 

responsJcarousel();

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
              .on('jcarousel:reload jcarousel:create', function () {
                 // var width = jcarousel.innerWidth();

                 // if (width >= 600) {
                 //     width = width / 5;
                 // } else if (width >= 736) {
                 //     width = width / 3;
                 // }

                //jcarousel.jcarousel('items').css('width', width + 'px');
              })
              .jcarousel({
                  wrap: 'circular'
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

