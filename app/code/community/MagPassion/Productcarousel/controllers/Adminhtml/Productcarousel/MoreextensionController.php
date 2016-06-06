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
 * More Extension admin controller
 *
 * @category	MagPassion
 * @package		MagPassion_Productcarousel
 * @author MagPassion.com
 */
class MagPassion_Productcarousel_Adminhtml_Productcarousel_MoreextensionController extends MagPassion_Productcarousel_Controller_Adminhtml_Productcarousel{
	/**
	 * init the moreextension
	 * @access protected
	 * @return MagPassion_Productcarousel_Model_Moreextension
	 */
	protected function _initMoreextension(){
		
	}
 	/**
	 * default action
	 * @access public
	 * @return void
	 * @author MagPassion.com
	 */
	public function indexAction() {
		$this->loadLayout();
		$this->_title(Mage::helper('productcarousel')->__('Productcarousel'))
			 ->_title(Mage::helper('productcarousel')->__('More Extensions'));
		$this->renderLayout();
     
	}
	
}