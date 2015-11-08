<?php

/**
 * Class Zolago_Catalog_Block_Vendor_Product_ChangeAttributeSet
 */
class Zolago_Catalog_Block_Vendor_Product_ChangeAttributeSet extends Mage_Core_Block_Template
{
    public function getAttributeSets()
    {
        $array = array();
        return $array;
    }

    public function getAttributeSetId()
    {
        return $this->getParentBlock()->getAttributeSetId();
    }

    public function getChangeUrl()
    {
        return $this->getUrl("*/*/*");
    }

    public function getVendor()
    {
        return Mage::getModel("udropship/session")->getVendor();
    }
}