/**
 * Created by pawel on 11.04.14.
 */
jQuery.noConflict();
(function( $ ) {
    $(function() {
        var ww = $(window).width();
//        if (ww >= 767) {
//            $('li.dropdown').on('show.bs.dropdown', function () {
//                var h = $(this).children('ul');
//                h.hide();
//                h.clone().appendTo('#submenu').slideDown();
//            });
//            $('li.dropdown').on('hidden.bs.dropdown', function () {
//                $('#submenu').children('ul').slideUp(300,function(){
//                    $(this).remove()
//                })
//                $(this).show();
//            });
//        };

        $('.dropdown').on('hidden.bs.dropdown', function () {
            $(this).show();
        });

        // CAROUSEL  1

        $('.white-wrapper .jcarousel').jcarousel({
            wrap: 'circular',
            visible:5
        });

        $('.white-wrapper .last-seen1').jcarousel({
            wrap: 'circular',
            visible:7
        });


        $('.jcarousel-control-prev')
            .on('jcarouselcontrol:active', function() {
                $(this).removeClass('inactive');
            })
            .on('jcarouselcontrol:inactive', function() {
                $(this).addClass('inactive');
            })
            .jcarouselControl({

                target: '-=1'
            });

        $('.jcarousel-control-next')
            .on('jcarouselcontrol:active', function() {
                $(this).removeClass('inactive');
            })
            .on('jcarouselcontrol:inactive', function() {
                $(this).addClass('inactive');
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
})(jQuery);