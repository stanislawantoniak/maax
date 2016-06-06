/**
 * MagPassion_ProductCarousel extension
 * 
 * @category   	MagPassion
 * @package		MagPassion_AdvancedMenu
 * @copyright  	Copyright (c) 2013 by MagPassion (http://magpassion.com)
 * @license	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Edit menu item js
 *
 * @category	MagPassion
 * @package		MagPassion_ProductCarousel
 * @author MagPassion
 */
 
function changeCategory() {
	var cateTitle = jQuery("#productcarousel_category_id :selected").text();
	while (cateTitle[0] === '-') cateTitle = cateTitle.substring(1);
	
	jQuery('#productcarousel_category').val(cateTitle);
}

jQuery( document ).ready(function() {
    jQuery('#productcarousel_category').val('All Category');
	jQuery('#productcarousel_category').parent().parent().addClass('mp_tr_hide');
	
});