/*!
 * Table Freeze plugin
 * 
 * Author: Bartosz Hejman
 * Date: 15 June 2014
 *
 */
(function($) {
  $.fn.extend({
    tableFreezer: function(options) {
      var cfg = $.extend(true, {
          freezeHeader: true,
          freezeFooter: false,
          freezeColumnsLeft: false,  // provide integer as a number of columns you want to freeze on the left hand side
          freezeColumnsRight: false  // provide integer as a number of columns you want to freeze on the right hand side
      }, options);

      var scrollWidth = cfg.scroll.width;
      var scrollHeight = cfg.scroll.height;
      var fixedLeftWidth = null;
      var fixedHeadHeight = null;

      function init(table) {
      	
      	console.log(table);
      }
    }
  });
})(jQuery);
