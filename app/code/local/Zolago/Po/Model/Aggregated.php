<?php

class Zolago_Po_Model_Aggregated extends Mage_Core_Model_Abstract
{
	protected $_resourceName = "zolagopo/aggregated";
   
	public function generateName() {
		$date = Mage::helper('core')->formatDate(Varien_Date::now(), 'medium');
		$posName = $this->getPos()->getName();
		$externalId = $this->getPos()->getExternalId();
		$this->setAggregatedName($date." / ".$posName." / ".$externalId);
	}
	
	/**
	 * @return Zolago_Pos_Model_Pos
	 */
	public function getPos() {
		if(!$this->hasData("pos")){
			$pos = Mage::getModel("zolagopos/pos");
			$pos->load($this->getPosId());
			$this->setData("pos", $pos);
		}
		return $this->getData("pos");
	}
	
	/**
	 * @return Zolago_Po_Model_Resource_Po_Collection
	 */
	public function getPoCollection() {
		$collection = Mage::getResourceModel("zolagopo/po_collection");
		/* @var $collection Zolago_Po_Model_Resource_Po_Collection */
		$collection->addFieldToFilter("aggregated_id", $this->getId());
		return $collection;
	}
	
	/**
	 * @return \Zolago_Po_Model_Aggregated
	 */
	public function confirm() {
		$this->getResource()->beginTransaction();
		foreach($this->getPoCollection() as $po){
			/* @var $po Zolago_Po_Model_Po */
			$po->getStatusModel()->processConfirmSend($po);
		}
		$this->setStatus(Zolago_Po_Model_Aggregated_Status::STATUS_CONFIRMED);
		$this->save();
		$this->getResource()->commit();
		return $this;
	}
	
	
	public function _beforeDelete() {
		$this->getResource()->beginTransaction();
		foreach($this->getPoCollection() as $po){
			/* @var $po Zolago_Po_Model_Po */
			$po->getStatusModel()->processCancelAggregated($po, true);
		}
		$this->getResource()->commit();
	}
}
