<?php
require_once Mage::getModuleDir('controllers', 'Unirgy_Rma') . "/VendorController.php";
class Zolago_Rma_VendorController extends Unirgy_Rma_VendorController
{
	
	public function editAction() {
		$render = false;
		try{
			$this->_registerRma();
			$render = true;
		}catch(Mage_Core_Exception $e){
			$this->_getSession()->addError($e->getMessage());
		}catch(Exception $e){
			$this->_getSession()->addError(Mage::helper("zolagorma")->__("Other error. Check logs."));
		}
		
		if($render){
			return $this->_renderPage(null, 'urma');
		}
		
		return $this->_redirect("*/*");
	}
 
	/**
	 * @return Zolago_Rma_Model_Rma
	 * @throws Mage_Core_Exception
	 */
	protected function _registerRma() {
		if(!Mage::registry('current_rma')){
			$rma = Mage::getModel("urma/rma");
			if($this->getRequest()->getParam('id')){
				$rma->load($this->getRequest()->getParam('id'));
			}
			if(!$this->_validateRma($rma)){
				throw new Mage_Core_Exception(Mage::helper('zolagorma')->__('Rma not found'));
			}
			Mage::register('current_rma', $rma);
		}
		return Mage::registry('current_rma');
	}
	
	/**
	 * @return boolean
	 */
	protected function _validateRma(Zolago_Rma_Model_Rma $rma) {
		if(!$rma->getId()){
			return false;
		}
		if($rma->getVendor()->getId() != $this->_getSession()->getVendorId()){
			return false;
		}
		return true;
	}
}
