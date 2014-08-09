<?php

class Zolago_Campaign_Model_Resource_Campaign_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract {
  
    protected function _construct() {
        parent::_construct();
        $this->_init('zolagocampaign/campaign');
    }
	
	/**
	 * @return Zolago_Campaign_Model_Resource_Campaign_Collection
	 */
	public function addActiveFilter() {
		$this->addFieldToFilter("is_active", 1);
		return $this;
	}

	/**
	 * @param Unirgy_Dropship_Model_Vendor | ini $vendor
	 * @return Zolago_Campaign_Model_Resource_Campaign_Collection
	 */
    public function addVendorFilter($vendor) {
		if($vendor instanceof Unirgy_Dropship_Model_Vendor){
			$vendor = $vendor->getId();
		}
		$this->addFieldToFilter('vendor_id',(int)$vendor);
		return $this;
    }
	/**
	 * @param string $login
	 * @return Zolago_Campaign_Model_Resource_Campaign_Collection
	 */
	public function	addLoginFilter($login){
		$this->addFieldToFilter("email", $login);
		$this->addActiveFilter();
		return $this;
	}
	
    protected function _toOptionArray($valueField='campaign_id', $labelField='name', $additional=array())
    {
		return parent::_toOptionArray($valueField, $labelField, $additional);
    }

    
}
