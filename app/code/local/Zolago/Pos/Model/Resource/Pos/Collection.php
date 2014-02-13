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
            array("pos_vendor"=>$this->getTable('zolagopos/pos_vendor')), 
            "pos_vendor.pos_id=main_table.pos_id AND is_owner=1",
            array()
        );
        $this->getSelect()->joinLeft(
            array("vendor"=>$this->getTable('udropship/vendor')), 
            "vendor.vendor_id=pos_vendor.vendor_id",
            array("vendor_owner_name"=>"vendor.vendor_name")
        );
        return $this;
    }
    
}
