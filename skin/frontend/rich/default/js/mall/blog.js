(function () {

    Mall.Blog = {
        bodyClass: '.blog',
        init: function () {
            var _ = this;
            _.setBlogTitle();
            _.setBlogMainSectionHeight();
            jQuery(window).resize(_.setBlogMainSectionHeight);
        },

        setBlogMainSectionHeight: function () {
            var _ = this,
                mainSection = jQuery(Mall.Blog.bodyClass).find('section#main'),
                height = 0;

            if (!Mall.isMobile(Mall.Breakpoint.sm)) {
                var sidebar = jQuery(Mall.Blog.bodyClass).find("aside.sidebar");
                height = (sidebar.height() + 120) + 'px';
            }

            mainSection.css('min-height', height);
        },
        setBlogTitle: function () {
            var blogMobileMenuTitle = jQuery("#blog-mobile-menu-title").attr("data-blog-mobile-menu-title");
            var title = blogMobileMenuTitle ? blogMobileMenuTitle : Mall.translate.__("Blog");

            jQuery("#blog-mobile-menu h1 a").text(title);
        }
    };

})();


jQuery(document).ready(function () {
    Mall.Blog.init();
});