<?php
require_once Mage::getModuleDir('controllers', 'Unirgy_Rma') . "/VendorController.php";
class Zolago_Rma_VendorController extends Unirgy_Rma_VendorController
{
	/**
	 * Display edit form
	 * @return null
	 */
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
	 * Save address obejct
	 * @retur null
	 */
	public function saveAddressAction(){
		$req	=	$this->getRequest();
		$data	=	$req->getPost();
		$type	=	$req->getParam("type");
		
		$session = $this->_getSession();
		/* @var $session Zolago_Dropship_Model_Session */
		
		
		try{
			$rma = $this->_registerRma();
			
			if(isset($data['restore']) && $data['restore']==1){
				if($type==Mage_Sales_Model_Order_Address::TYPE_SHIPPING){
					$rma->clearOwnShippingAddress();
				}else{
					$rma->clearOwnBillingAddress();
				}
				
				Mage::dispatchEvent("zolagorma_rma_address_restore", array(
					"rma"		=> $rma, 
					"type"		=> $type
				));
				
				$rma->save();
				$session->addSuccess(Mage::helper("zolagorma")->__("Address restored"));
				$response['content']['reload']=1;
			}elseif(isset($data['add_own']) && $data['add_own']==1){
				if($type==Mage_Sales_Model_Order_Address::TYPE_SHIPPING){
					$orignAddress = $rma->getOrder()->getShippingAddress();
					$oldAddress = $rma->getShippingAddress();
				}else{
					$orignAddress = $rma->getOrder()->getBillingAddress();
					$oldAddress = $rma->getBillingAddress();
				}
				$newAddress = clone $orignAddress;
				$newAddress->addData($data);
				if($type==Mage_Sales_Model_Order_Address::TYPE_SHIPPING){
					$rma->setOwnShippingAddress($newAddress);
				}else{
					$rma->setOwnBillingAddress($newAddress);
				}
				
				
				Mage::dispatchEvent("zolagorma_rma_address_change", array(
					"rma"			=> $rma, 
					"new_address"	=> $newAddress, 
					"old_address"	=> $oldAddress, 
					"type"			=> $type
				));
				
				$rma->save();
				
				$session->addSuccess(Mage::helper("zolagorma")->__("Address changed"));
			}
		}catch(Mage_Core_Exception $e){
			$session->addError($e->getMessage());
		}catch(Exception $e){
			Mage::logException($e);
			$session->addError(Mage::helper("zolagorma")->__("Some errors occure. Check logs."));
		}
		
		return $this->_redirectReferer();
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
