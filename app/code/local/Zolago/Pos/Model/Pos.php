<?php
class Zolago_Pos_Model_Pos extends Mage_Core_Model_Abstract{
    
    protected function _construct() {
        $this->_init('zolagopos/pos');
    }
	
	/**
	 * @param Unirgy_Dropship_Model_Vendor|int $vendor
	 * @return boolean
	 */
	public function isAssignedToVendor($vendor) {
		if($vendor instanceof Unirgy_Dropship_Model_Vendor){
			$vendor = $vendor->getId();
		}
		return $this->getResource()->isAssignedToVendor($this, $vendor);
	}


	/**
     * @return Unirgy_Dropship_Model_Mysql4_Vendor_Collection
     */
    public function getVendorCollection() {
        $collection = Mage::getResourceModel('udropship/vendor_collection');
        $this->getResource()->addPosToVendorCollection($collection);
        if($this->getId()){
            $collection->addFieldToFilter("pos_id", $this->getId());
        }else{
            $collection->addFieldToFilter("pos_id", -1);
        }
        return $collection;
    }
    
    /**
     * @param array $data
     * @return array
     */
    public function validate($data=null) {
        
        if($data===null){
            $data = $this->getData();
        }
        elseif($data instanceof Varien_Object){
            $data = $data->getData();
        }
        
        if(!is_array($data)){
            return false;
        }
        
        $errors = $this->getValidator()->validate($data);

        if (empty($errors)) {
            return true;
        }
        return $errors;
    
    }
    
    public function getRegionText() {
        if($this->getRegionId()){
            return Mage::getModel("directory/region")->load($this->getRegionId())->getName();
        }
        return $this->getRegion();
    }
    
    /**
     * @return Zolago_Pos_Model_Pos_Validator
     */
    public function getValidator() {
        return Mage::getSingleton("zolagopos/pos_validator");
    }
    
}

