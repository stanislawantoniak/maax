<?php

class GH_Dhl_Model_Resource_Dhl extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('ghdhl/dhl', "id");
    }

    /**
     *
     * @param Mage_Core_Model_Abstract $object
     * @return type
     */
    protected function _prepareDataForSave(Mage_Core_Model_Abstract $object)
    {
        // Times
        $currentTime = Mage::getModel('core/date')->date('Y-m-d H:i:s');
        if ((!$object->getId() || $object->isObjectNew()) && !$object->getCreationTime()) {

            $object->setCreationTime($currentTime);
        }
        $object->setUpdateTime($currentTime);


        return parent::_prepareDataForSave($object);
    }
}