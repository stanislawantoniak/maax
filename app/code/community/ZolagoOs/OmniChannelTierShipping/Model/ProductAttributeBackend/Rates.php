<?php
/**
  
 */

class ZolagoOs_OmniChannelTierShipping_Model_ProductAttributeBackend_Rates extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
    public function afterLoad($object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        try {
            $decoded = $object->getData($attrCode);
            if (!is_array($decoded)) {
                $decoded = Mage::helper('udropship')->unserialize($decoded);
            }
            if (is_array($decoded)) {
                usort($decoded, array($this, 'sortBySortOrder'));
            } else {
                $decoded = array();
            }
            $object->setData($attrCode, $decoded);
        } catch (Exception $e) {}

    }

    public function sortBySortOrder($a, $b)
    {
        if (@$a['sort_order']<@$b['sort_order']) {
            return -1;
        } elseif (@$a['sort_order']>@$b['sort_order']) {
            return 1;
        }
        return 0;
    }

    public function beforeSave($object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        if (($attrValue = $object->getData($attrCode)) && is_array($attrValue)) {
            unset($attrValue['$ROW']);
            unset($attrValue['$$ROW']);
            usort($attrValue, array($this, 'sortBySortOrder'));
            $object->setData($attrCode, Mage::helper('udropship')->serialize($attrValue));
        }
    }
}