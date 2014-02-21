<?php

class Zolago_Po_VendorController extends Zolago_Dropship_Controller_Vendor_Abstract {
	public function updatePosAction(){
		
		$poId = $this->getRequest()->getParam("id");
		$posId = $this->getRequest()->getParam("pos_id");
		
		$po = Mage::getModel("udpo/po")->load($poId);
		/* @var $po Unirgy_DropshipPo_Model_Po */
		$pos = Mage::getModel("zolagopos/pos")->load($posId);
		/* @var $pos Zolago_Pos_Model_Pos */
		$session = $this->_getSession();
		/* @var $session Zolago_Dropship_Model_Session */
		
		$reload = false;
		
		$this->getResponse()->setHeader("content-type", "application/json");
		
		if($po->getId() && $pos->getId() && $pos->isAssignedToVendor($session->getVendor())){
			$po->setDefaultPosId($pos->getId());
			$po->setDefaultPosName($pos->getName());
			if($session->isOperatorMode()){
				if(!in_array($pos->getId(), $session->getOperator()->getAllowedPos())){
					$reload = true;
				}
			}
			$po->save();
			$this->getResponse()->setBody(Zend_Json::encode(array(
				"status"=>1, 
				"reload"=>$reload, 
				"pos"=>$pos->getData()
			)));
			return;
		}
		
		$this->getResponse()->setBody(Zend_Json::encode(array("status"=>0, "message"=>"Some error occure")));
	}

}


