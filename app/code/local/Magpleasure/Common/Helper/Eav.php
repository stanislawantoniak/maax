<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */
class Magpleasure_Common_Helper_Eav extends Mage_Core_Helper_Abstract
{
    protected $_helper;

    /**
     * EAV Helper
     *
     * @return Magpleasure_Common_Model_Eav_Helper
     */
    protected function _getEavHelper()
    {
        if (!$this->_helper){
            $this->_helper = new Magpleasure_Common_Model_Eav_Helper('core_setup');
        }
        return $this->_helper;
    }


    public function getEntityTypeIdByCode($entityCode)
    {
        $setup = $this->_getEavHelper();
        return $setup->getEntityType($entityCode, 'entity_type_id');
    }

    public function getAttributesByEntityType($entityCode)
    {
        $entityTypeId = $this->getEntityTypeIdByCode($entityCode);

        if ($entityTypeId){
            return $this->_getEavHelper()->getAttributes($entityTypeId);
        }
        return array();
    }

    public function getEntityNameByModelName($modelName)
    {
        return $this->_getEavHelper()->getEntityTypeNameByModelName($modelName, 'entity_model');
    }

    public function getAttributeById($attributeId)
    {
        return $this->_getEavHelper()->getAttributeById($attributeId);
    }

    public function getAttributeByCode($entityTypeId, $attributeId)
    {
        return $this->_getEavHelper()->getAttributeById($this->_getEavHelper()->getAttributeId($entityTypeId, $attributeId));
    }
}