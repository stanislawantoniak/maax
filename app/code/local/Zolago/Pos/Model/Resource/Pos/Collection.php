<?php

class Zolago_Pos_Model_Resource_Pos_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract {
  
    protected function _construct() {
        parent::_construct();
        $this->_init('zolagopos/pos');
    }
	
	/**
	 * @return array
	 */
	public function toOptionArray()
    {
        return $this->_toOptionArray("pos_id", "name");
    }

	/**
	 * @return Zolago_Pos_Model_Resource_Pos_Collection
	 */
	public function addActiveFilter() {
		$this->addFieldToFilter("is_active", 1);
		return $this;
	}

	/**
     * @return Zolago_Pos_Model_Resource_Pos_Collection
     */
    public function addVendorOwnerName(){
        $this->getSelect()->joinLeft(
            array("vendor"=>$this->getTable('udropship/vendor')), 
            "vendor.vendor_id=main_table.vendor_owner_id",
            array("vendor_owner_name"=>"vendor.vendor_name")
        );
        return $this;
    }
	
	/**
	 * @param Unirgy_Dropship_Model_Vendor|int $vendor
	 * @return Zolago_Pos_Model_Resource_Pos_Collection
	 */
	public function addVendorFilter($vendor){
		if($vendor instanceof Unirgy_Dropship_Model_Vendor){
			$vendor = $vendor->getId();
		}
		$condition = $this->getConnection()->quoteInto(
				"pos_vendor.pos_id=main_table.pos_id AND pos_vendor.vendor_id=?", 
				$vendor
		);
		$this->getSelect()->join(
            array("pos_vendor"=>$this->getTable('zolagopos/pos_vendor')), 
            $condition,
            array()
        );
		$this->getSelect()->group('main_table.pos_id');
		return $this;
	}

    /**
     * @return mixed
     */
    public function getMinPOSStock()
    {
        $readConnection = Mage::getSingleton('core/resource')
            ->getConnection('core_read');
        $value = $this
            ->addFieldToFilter('external_id', array('neq' => NULL))
            ->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array('external_id', 'minimal_stock'));
        $minPOSValues = $readConnection->fetchPairs($value);
        return $minPOSValues;
    }
}
