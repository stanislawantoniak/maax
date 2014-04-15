<?php
class Zolago_Po_Helper_Data extends Unirgy_DropshipPo_Helper_Data
{
	protected $_condJoined = false;
	
	public function setCondJoined($flag) {
		$this->_condJoined = $flag;
	}
	/**
	 * Add operator filter if session is in operator mode
	 * @return Unirgy_DropshipPo_Model_Mysql4_Po_Collection
	 */
	public function getVendorPoCollection() {
		$collection = parent::getVendorPoCollection();
		if($this->_condJoined){
			return $collection;
		}
		/* @var $collection Unirgy_DropshipPo_Model_Mysql4_Po_Collection */
		$session = Mage::getSingleton('udropship/session');
		/* @var $session Zolago_Dropship_Model_Session */
		if($session->isOperatorMode()){
			Mage::getResourceModel("zolagooperator/operator")->
				addOperatorFilterToPoCollection($collection, $session->getOperator());
		}
		$this->_condJoined = true;
		return $collection;
	}
	
	public function getDhlSettings($vendor, $posId) {
		$dhlSettings = false;
		$posModel = Mage::getModel('zolagopos/pos')->load($posId);
		if ($posModel && $posModel->getId() && $posModel->getUseDhl() && $posModel->getDhlLogin() && $posModel->getDhlPassword() && $posModel->getDhlAccount()) {
			$dhlSettings['login']		= $posModel->getDhlLogin();
			$dhlSettings['account']		= $posModel->getDhlAccount();
			$dhlSettings['password']	= $posModel->getDhlPassword();
		} elseif ($vendor && $vendor->getId() && $vendor->getUseDhl() && $vendor->getDhlLogin() && $vendor->getDhlPassword() && $vendor->getDhlAccount()) {
			$dhlSettings['login']		= $vendor->getDhlLogin();
			$dhlSettings['account']		= $vendor->getDhlAccount();
			$dhlSettings['password']	= $vendor->getDhlPassword();
		}
		
		return $dhlSettings;
	}
}
