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
	
}
