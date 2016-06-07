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
 * Product Carousel helper
 *
 * @category	MagPassion
 * @package		MagPassion_Productcarousel
 * @author MagPassion.com
 */
class MagPassion_Productcarousel_Helper_Productcarousel extends Mage_Core_Helper_Abstract{
	/**
	 * check if load jquery
	 * @access public
	 * @return bool
	 * @author MagPassion.com
	 */
	public function getUseLoadJquery(){
		return Mage::getStoreConfigFlag('productcarousel/productcarousel/loadjquery');
	}
}