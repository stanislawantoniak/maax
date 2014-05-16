<?php

class Zolago_Po_Vendor_AggregatedController 
	extends Zolago_Dropship_Controller_Vendor_Abstract {
	
	
	/**
	 * @return Unirgy_Dropship_Model_Vendor
	 */
	protected function _getVendor() {
		return $this->_getSession()->getVendor();
	}
	
	public function indexAction() {
		// Override origin index
		Mage::register('as_frontend', true);// Tell block class to use regular URL's
		$this->_renderPage(array('default', 'formkey', 'adminhtml_head'), 'zolagopo_aggregated');
	}
	
}


