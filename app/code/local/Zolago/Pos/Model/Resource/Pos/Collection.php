<?php

class Zolago_Pos_Model_Resource_Pos_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract {
  
    protected function _construct() {
        parent::_construct();
        $this->_init('zolagopos/pos');
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
    
}
