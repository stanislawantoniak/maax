<?php
/**
  
 */

class ZolagoOs_OmniChannel_Model_Mysql4_Vendor_Backend extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
    protected static $_isEnabled;
    protected function _isEnabled()
    {
        if (is_null(self::$_isEnabled)) {
            $module = Mage::getConfig()->getNode('modules/ZolagoOs_OmniChannel');
            self::$_isEnabled = $module && $module->is('active');
        }
        return self::$_isEnabled;
    }

    public function getDefaultValue()
    {
        return parent::getDefaultValue();
        if (is_null($this->_defaultValue)) {
            $this->_defaultValue = Mage::helper('udropship')->getLocalVendorId($this->getAttribute()->getStoreId());
        }
        return $this->_defaultValue;
    }

    public function afterLoad($object)
    {
        parent::afterLoad($object);
        if (!$this->_isEnabled()) {
            return;
        }
        $attrCode = $this->getAttribute()->getAttributeCode();
        $defValue = $this->getDefaultValue();
        if (!$object->getData($attrCode) && $defValue) {
            $object->setData($attrCode, $defValue);
        }
    }
}