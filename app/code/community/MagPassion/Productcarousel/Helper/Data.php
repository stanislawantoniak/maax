<?php 
/**
 * MagPassion_Productcarousel extension
 * 
 * @category   	MagPassion
 * @package		MagPassion_Productcarousel
 * @copyright  	Copyright (c) 2014 by MagPassion (http://magpassion.com)
 * @license	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Productcarousel default helper
 *
 * @category	MagPassion
 * @package		MagPassion_Productcarousel
 * @author MagPassion.com
 */
class MagPassion_Productcarousel_Helper_Data extends Mage_Core_Helper_Abstract{
	/**
	 * get the url to the product carousels list page
	 * @access public
	 * @return string
	 * @author MagPassion.com
	 */
	public function getProductcarouselsUrl(){
		return Mage::getUrl('productcarousel/productcarousel/index');
	}
}