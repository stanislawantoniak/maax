<?php

class Zolago_Po_Model_Aggregated extends Mage_Core_Model_Abstract
{
	protected $_resourceName = "zolagopo/aggregated";
	protected $_resourceCollectionName = "zolagopo/aggregated_collection";
   
	/**
	 * @param Unirgy_Dropship_Model_Vendor $venndor
	 * @param Zolago_Operator_Model_Operator $operator
	 * @return boolean
	 */
	public function isAllowed(Unirgy_Dropship_Model_Vendor $vendor = null, Zolago_Operator_Model_Operator $operator = null) {
		if($operator instanceof Zolago_Operator_Model_Operator){
			return in_array($this->getPosId(), $operator->getAllowedPos());
		}elseif($vendor instanceof Zolago_Dropship_Model_Vendor){
			return in_array($this->getPosId(), $vendor->getAllowedPos());
		}
		return false;
	}
	/**
	 * @return Zolago_Po_Model_Aggregated
	 */
	public function generateName() {
		$date = new Zend_Date(null,null,Mage::app()->getLocale()->getLocaleCode());
		$externalId = $this->getPos()->getExternalId();
		$candidate = $date->toString("dd-MM-yyyy")."-".$externalId."-";
		$incrementNo = 1;
		
		$coll = $this->getCollection();
		/* @var $coll Zolago_Po_Model_Resource_Aggregated_Collection */
		$coll->addFieldToFilter("pos_id", $this->getPosId());
		$coll->setOrder("sequence", "DESC");
		if($item=$coll->getFirstItem()){
			$incrementNo = $item->getSequence()+1;
		}
		$this->setSequence($incrementNo);
		$this->setAggregatedName($candidate.$incrementNo);
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function isConfirmed() {
		return $this->getStatus() == Zolago_Po_Model_Aggregated_Status::STATUS_CONFIRMED;
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
		$pdf = Mage::getModel('zolagopo/aggregated_pdf');
		$pdf->deleteFile($this->getId());
	}
	public function getPdfFile() {
		$pdf = Mage::getModel('zolagopo/aggregated_pdf');
		return $pdf->getPdfFile($this->getId());
	}
}
