<?php

class Zolago_Po_Vendor_AggregatedController 
	extends Zolago_Dropship_Controller_Vendor_Abstract {
	
	
	/**
	 * @return ZolagoOs_OmniChannel_Model_Vendor
	 */
	protected function _getVendor() {
		return $this->_getSession()->getVendor();
	}
	
	/**
	 * @return Zolago_Operator_Model_Operator
	 */
	protected function _getOperator() {
		return $this->_getSession()->getOperator();
	}
	
	/**
	 * @return Zolago_Po_Model_Aggregated
	 * @throws Mage_Core_Exception
	 */
	protected function _registerAggregated() {
		if(!Mage::registry('current_aggregated')){
			$aggregated = Mage::getModel("zolagopo/aggregated");
			$aggregated->load($this->getRequest()->getParam('id'));
			/* @var $aggregated Zolago_Po_Model_Aggregated */
			$session = $this->_getSession();
			/* @var $session Zolago_Dropship_Model_Session */
			if(!$aggregated->isAllowed($this->_getVendor(), $session->isOperatorMode() ? $this->_getOperator() : null)){
				throw new Mage_Core_Exception(Mage::helper("zolagopo")->__("You are not allowed to operate this order"));
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
				Mage::helper("zolagopo")->__("There was a technical error. Please contact shop Administrator.")
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
			
			if($aggregated->isConfirmed()){
				throw new Mage_Core_Exception(Mage::helper("zolagopo")->__("Cannot remove confirmed dispatch list"));
			}
			
			$aggregated->delete();
			$this->_getSession()->addSuccess(
				Mage::helper("zolagopo")->__("Dispatch list removed.")
			);
		}catch(Mage_Core_Exception $e){
			$this->_getSession()->addError($e->getMessage());
		}catch(Exception $e){
			$this->_getSession()->addError(
				Mage::helper("zolagopo")->__("There was a technical error. Please contact shop Administrator.")
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
				Mage::helper("zolagopo")->__("There was a technical error. Please contact shop Administrator.")
			);
			Mage::logException($e);
		}
		return $this->_redirectReferer();
	}
	
}


