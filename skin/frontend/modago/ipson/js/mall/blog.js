(function () {

    Mall.Blog = {
        bodyClass: '.blog',
        init: function () {
            var _ = this;
            _.setBlogMainSectionHeight();
            jQuery(window).resize(_.setBlogMainSectionHeight);
        },

        setBlogMainSectionHeight: function () {
            var _ = this,
                mainSection = jQuery(Mall.Blog.bodyClass).find('section#main'),
                height = 0;

            if(!Mall.isMobile(Mall.Breakpoint.sm)) {
                var sidebar = jQuery(Mall.Blog.bodyClass).find("aside.sidebar");
                height = (sidebar.height() + 120) + 'px';
            }

            mainSection.css('min-height', height);
        },
    };

})();


jQuery(document).ready(function() {
    Mall.Blog.init();
});