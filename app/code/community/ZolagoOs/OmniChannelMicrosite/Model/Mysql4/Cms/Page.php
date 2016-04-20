<?php
/**
  
 */

class ZolagoOs_OmniChannelMicrosite_Model_Mysql4_Cms_Page extends Mage_Cms_Model_Mysql4_Page
{
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        $vendor = Mage::helper('umicrosite')->getCurrentVendor();
        if ($vendor) {
            $select->where("udropship_vendor=? || identifier='no-route'", $vendor->getId());
        } else {
            $select->where('udropship_vendor is null');
        }
        return $select;
    }
}