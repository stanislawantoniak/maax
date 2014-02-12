<?php
class Zolago_Pos_Model_Resource_Pos
    extends Mage_Core_Model_Resource_Db_Abstract{
    

    protected function _construct() {
        $this->_init('zolagopos/pos', "pos_id");
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
