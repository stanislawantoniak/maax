<?php
class Zolago_Customer_Model_Resource_Attachtoken
    extends Mage_Core_Model_Resource_Db_Abstract{
    

    protected function _construct() {
        $this->_init('zolagocustomer/attachtoken', "token_id");
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
    
    /**
     * @param Zend_Date $date
     * @return int
     */
    public function cleanOldTokens(Zend_Date $date) {
        $write = $this->_getWriteAdapter();
        $whereCond = $write->quoteInto("created_at<?", 
            $date->toString(Varien_Date::DATE_INTERNAL_FORMAT));
        return $write->delete($this->getMainTable(), $whereCond);
    }
    
}
