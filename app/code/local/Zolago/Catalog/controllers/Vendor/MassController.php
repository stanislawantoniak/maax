<?php

class Zolago_Catalog_Vendor_MassController 
	extends Zolago_Dropship_Controller_Vendor_Abstract {
	/**
	 * Index
	 */
	public function indexAction() {
		Mage::register('as_frontend', true);// Tell block class to use regular URL's
		
		$this->_renderPage(array('default', 'formkey', 'adminhtml_head'), 'zolagocatalog');
	}
	
	public function saveAjaxAction() {
		$response = array();
		if($this->getRequest()->isPost()){
			// Do save
			$response = array("status"=>1);
		}else{
			$response = array(
				"status"=>0, 
				"content"=>Mage::helper("zolagocatalog")->__("Wrogn HTTP method")
			);
		}
		
		
		$this->getResponse()->
				setBody(Zend_Json::encode($response))->
				setHeader('content-type', 'application/json');
	}


	public function gridAction(){
		$design = Mage::getDesign();
		$design->setArea("adminhtml");
		$this->loadLayout();
		$block = $this->getLayout()->createBlock("zolagocatalog/vendor_mass_grid");

		$this->getResponse()->setBody($block->toHtml());
	}
	
	public function massDeleteAction() {
		var_export($this->getRequest()->getParams());
	}
	
	
}


