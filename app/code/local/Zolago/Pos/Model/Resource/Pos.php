<?php
class Zolago_Pos_Model_Resource_Pos
    extends Mage_Core_Model_Resource_Db_Abstract{
    

    protected function _construct() {
        $this->_init('zolagopos/pos', "pos_id");
    }
    
    public function addPosToVendorCollection(Mage_Core_Model_Resource_Db_Collection_Abstract $collection) {
        $collection->getSelect()->joinLeft(
                array("pos_vendor"=>$this->getTable('zolagopos/pos_vendor')), 
                "main_table.vendor_id=pos_vendor.vendor_id",
                array("pos_id", "is_owner")
        );
    }


    /**
     * Set times
     * @param Mage_Core_Model_Abstract $object
     * @return type
     */
    protected function _prepareDataForSave(Mage_Core_Model_Abstract $object)
    {
        // Times
        $currentTime = Varien_Date::now();
        if ((!$object->getId() 
            || $object->isObjectNew()) 
            && !$object->getCreatedAt()) {
            
            $object->setCreatedAt($currentTime);
        }
        $object->setUpdatedAt($currentTime);
        return parent::_prepareDataForSave($object);
    }
    
}

?>
