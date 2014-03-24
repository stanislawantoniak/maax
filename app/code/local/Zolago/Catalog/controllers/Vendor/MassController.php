<?php

class Zolago_Catalog_Vendor_MassController 
	extends Zolago_Dropship_Controller_Vendor_Abstract {
	/**
	 * Index
	 */
	public function indexAction() {
		$this->_renderPage(array("default", "adminhtml_head"), 'zolagocatalog');
	}
	
}


