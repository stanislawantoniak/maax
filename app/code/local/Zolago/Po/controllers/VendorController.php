<?php

class Zolago_Po_VendorController extends Zolago_Dropship_Controller_Vendor_Abstract {
	
	public function saveShippingAddressAction(){
		$req = $this->getRequest();
		$poId = $req->getParam("po_id");
		$data = $req->getPost();
		
		$po = Mage::getModel("udpo/po")->load($poId);
		/* @var $po Zolago_Po_Model_Po */
		$session = $this->_getSession();
		/* @var $session Zolago_Dropship_Model_Session */
		
		$this->getResponse()->setHeader("content-type", "application/json");
		
		if(!$po->getId()){
			$this->getResponse()->setBody(Zend_Json::encode(array(
				"status"=>0, 
				"content"=>Mage::helper("zolagopo")->__("Wrong PO Id")
			)));
			return;
		}
		
		if($po->getVendor()->getId()!=$session->getVendor()->getId()){
			$this->getResponse()->setBody(Zend_Json::encode(array(
				"status"=>0, 
				"content"=>Mage::helper("zolagopo")->__("You have no access to this PO")
			)));
			return;
		}
		
		$response = array(
			"status"=>1,
			"content"=>array()
		);
		
		try{
			if(isset($data['restore'])){
				$po->clearOwnShippingAddress();
				$po->save();
				$session->addSuccess(Mage::helper("zolagopo")->__("Address restored"));
				$response['content']['reload']=1;
			}elseif(isset($data['add_own'])){
				$orignAddress = $po->getOrder()->getShippingAddress();
				$newAddress = clone $orignAddress;
				$newAddress->addData($data);
				$po->setOwnShippingAddress($newAddress);
				$po->save();
				$session->addSuccess(Mage::helper("zolagopo")->__("Address changed"));
				$response['content']['reload']=1;
			}
		}catch(Exception $e){
			Mage::logException($e);
			$response = array(
				"status"=>0, 
				"content"=>Mage::helper("zolagopo")->__("Some errors occure. Check logs.")
			);
		}
		
		$this->getResponse()->setBody(Zend_Json::encode($response));
	}

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
		
		if($po->getId() && $pos->getId() && 
				$po->getVendor()->getId()==$session->getVendor()->getId() &&
				$pos->isAssignedToVendor($session->getVendor())){
			
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


