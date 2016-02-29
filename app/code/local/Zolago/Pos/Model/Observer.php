<?php
class Zolago_Pos_Model_Observer {
	
	public function addPosInfoToOrderInfo() {
		if (($soi = Mage::app()->getLayout()->getBlock('order_info'))
            && ($po = Mage::registry('current_udpo'))
			&& $po->getDefaultPosId())
        {
			$soi->setDefaultPosId($po->getDefaultPosId());
			$soi->setDefaultPosName($po->getDefaultPosName());
		}
	}
	
	public function udpoOrderSaveBefore($observer) { // After
		$udpos = $observer->getUdpos();
		foreach($udpos as $udpo){
			/* @var $udpo Unirgy_DropshipPo_Model_Po */
			$this->_assignPosToPo($udpo);
		}
		
	}
	
	protected function _assignPosToPo($udpo) {
		/* @var $udpo Unirgy_DropshipPo_Model_Po */
		if(!$udpo->getId() && !$udpo->getDefaultPosId()){
			$vendor = $udpo->getVendor();
			$bestPos = $this->_getBestPosByVendor($vendor);
			if($bestPos){
				$udpo->setDefaultPosId($bestPos->getId());
				$udpo->setDefaultPosName($bestPos->getName());
			}
			
		}
	}
	
	protected function _getBestPosByVendor($vendor) {
		/* @var $vendor Unirgy_Dropship_Model_Vendor */
		$collection = Mage::getResourceModel("zolagopos/pos_collection");
		/* @var $collection Zolago_Pos_Model_Resource_Pos_Collection */
		$collection->addVendorFilter($vendor);
		$collection->addActiveFilter();
		$collection->setOrder("priority", "DESC");
		$collection->setPageSize(1);
		return $collection->getFirstItem();
	}

    public function setAppropriatePoPos(){

    }
}
