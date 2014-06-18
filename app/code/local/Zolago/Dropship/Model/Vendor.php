<?php
class Zolago_Dropship_Model_Vendor extends Unirgy_Dropship_Model_Vendor
{
		
	/**
	 * @return array
	 */
	public function getChildVendorIds() {
		if(!$this->hasData('child_vendor_ids')){
			$this->setData('child_vendor_ids', $this->getResource()->getChildVendorIds($this));
		}
		return $this->getData('child_vendor_ids');
	}
	
	/**
	 * @return array
	 */
	public function getAllowedPos() {
		if(!$this->hasData("allowed_pos")){
			$allowedPos = array();
			if($this->getId()){
				$allowedPos = $this->getResource()->getAllowedPos($this);
			}
			$this->setData("allowed_pos", $allowedPos);
		}
		return $this->getData("allowed_pos");
	}
	
	/**
	 * @return Zolago_Rma_Model_Resource_Rma_Reason_Collection
	 */
	public function getRmaReasonVendorCollection(){
    	//$vendor_id = $this->getVendorId();
		$collection = Mage::getResourceModel('zolagorma/rma_reason_vendor_collection');
		/* @var $collection Zolago_Rma_Model_Resource_Rma_Reason_Collection */
		if($this->getId()){
			$collection->addFieldToFilter('vendor_id', $this->getId());
		}else{
			$collection->addFieldToFilter('vendor_id', -1);
		}
		return $collection;
    }
}
 