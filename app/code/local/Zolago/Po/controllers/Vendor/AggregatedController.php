<?php

class Zolago_Po_Vendor_AggregatedController 
	extends Zolago_Dropship_Controller_Vendor_Abstract {
	
	
	/**
	 * @return Unirgy_Dropship_Model_Vendor
	 */
	protected function _getVendor() {
		return $this->_getSession()->getVendor();
	}
	
	/**
	 * @return Zolago_Po_Model_Aggregated
	 */
	protected function _registerAggregated() {
		if(!Mage::registry('current_aggregated')){
			$aggregated = Mage::getModel("zolagopo/aggregated");
			$aggregated->load($this->getRequest()->getParam('id'));
			if($aggregated->getVendorId()!=$this->_getVendor()->getId()){
				throw new Mage_Core_Exception(Mage::helper("zolagopo")->__("It's not your object"));
			}
			Mage::register('current_aggregated', $aggregated);
		}
		return Mage::registry('current_aggregated');
	}
	
	public function indexAction() {
		Mage::register('as_frontend', true);// Tell block class to use regular URL's
		$this->_renderPage(array('default', 'formkey', 'adminhtml_head'), 'zolagopo_aggregated');
	}
	
	/**
 	 * Confirm dispatch reference
	 * @return void
	 */
	public function confirmAction() {
		try{
			$aggregated  = $this->_registerAggregated();
			$aggregated->confirm(true);
			$this->_getSession()->addSuccess(
				Mage::helper("zolagopo")->__("Dispatch ref. confirmed")
			);
		}catch(Mage_Core_Exception $e){
			$this->_getSession()->addError($e->getMessage());
		}catch(Exception $e){
			$this->_getSession()->addError(
				Mage::helper("zolagopo")->__("Some error occure")
			);
			Mage::logException($e);
		}
		return $this->_redirectReferer();
	}
	/**
 	 * Remove dispatch reference
	 * @return void
	 */
	public function removeAction() {
		try{
			$aggregated  = $this->_registerAggregated();
			$aggregated->delete();
			$this->_getSession()->addSuccess(
				Mage::helper("zolagopo")->__("Dispatch reference removed.")
			);
		}catch(Mage_Core_Exception $e){
			$this->_getSession()->addError($e->getMessage());
		}catch(Exception $e){
			$this->_getSession()->addError(
				Mage::helper("zolagopo")->__("Some error occure")
			);
			Mage::logException($e);
		}
		return $this->_redirectReferer();
	}
	
	/**
 	 * Remove dispatch reference
	 * @return void
	 */
	public function downloadAction() {
		
		try{
			$aggregated  = $this->_registerAggregated();
			$dhlFileName = $aggregated->getPdfFile();

			$ioAdapter = new Varien_Io_File();
			if ($dhlFileName) {
				return $this->_prepareDownloadResponse(basename($dhlFileName), @$ioAdapter->read($dhlFileName), 'application/pdf');
			}
			
		}catch(Mage_Core_Exception $e){
			$this->_getSession()->addError($e->getMessage());
		}catch(Exception $e){
			$this->_getSession()->addError(
				Mage::helper("zolagopo")->__("Some error occure")
			);
			Mage::logException($e);
		}
		return $this->_redirectReferer();
	}
	
}


