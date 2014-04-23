jQuery.noConflict();
(function( $ ) {
  $(function() {
    
      
      $('[role="search"]').on('click', '.dropdown-menu a', function(event) {
      	event.preventDefault();
      	var thisValue = $(this).text();
      	$(this).closest('ul').find('.current').removeClass('current');
      	$(this).addClass('current');
      	$('#value_search_category').text(thisValue);
      });
      
      
      
      
      
      
      
      
      
      
      
  });
})(jQuery);
