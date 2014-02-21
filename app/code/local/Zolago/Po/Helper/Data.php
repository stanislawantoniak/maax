<?php
class Zolago_Po_Helper_Data extends Unirgy_DropshipPo_Helper_Data
{
	protected $_condJoined = false;
	
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
	

}
