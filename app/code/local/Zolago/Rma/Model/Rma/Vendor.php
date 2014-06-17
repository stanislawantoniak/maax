<?php
class Zolago_Rma_Model_Rma_Vendor extends Unirgy_Dropship_Model_Vendor{

    public function getRmaReasonVendorCollection(){
    	
		$vendor_id = $this->getVendorId();
		$collection = NULL;
		if($vendor_id){
			
			$collection = Mage::getModel('zolagorma/rma_reason_vendor')->getCollection()
							   									       ->addFieldToFilter('vendor_id', $vendor_id);
			
		}
		
		return $collection;
    }
}