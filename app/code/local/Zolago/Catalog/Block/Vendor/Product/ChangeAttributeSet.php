<?php

/**
 * Class Zolago_Catalog_Block_Vendor_Product_ChangeAttributeSet
 */
class Zolago_Catalog_Block_Vendor_Product_ChangeAttributeSet extends Mage_Core_Block_Template
{
    /**
     * @return array
     */
    public function getVendorAttributeSets()
    {
        $vendorAttributeSets = array();

        $entityTypeId = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
        $collection = Mage::getResourceModel('eav/entity_attribute_set_collection');

        $collection->addFieldToFilter("entity_type_id", $entityTypeId);
        //TODO uncomment after test
        $collection->addFieldToFilter("use_to_create_product", 1);
        //$collection->addFieldToFilter("main_table.attribute_set_id", array("neq" => $this->getAttributeSetId()));

        $collection->setOrder('attribute_set_name', 'ASC');
        $collection->getSelect()
            ->join(
                array('vendor_attribute_set' => Mage::getSingleton('core/resource')->getTableName(
                    "zolagosizetable/vendor_attribute_set"
                )),
                'vendor_attribute_set.attribute_set_id=main_table.attribute_set_id'
            )
            ->where("vendor_attribute_set.vendor_id=?", $this->getVendor()->getId());

        foreach ($collection as $collectionItem) {
            $vendorAttributeSets[$collectionItem->getAttributeSetId()] = $collectionItem->getAttributeSetName();
        }
        asort($vendorAttributeSets);
        return $vendorAttributeSets;
    }

    public function getAttributeSetId()
    {
        return Mage::app()->getRequest()->getParam("attribute_set_id", 0);
    }

    public function getChangeUrl()
    {
        return $this->getUrl("*/*/*");
    }

    public function getVendor()
    {
        return Mage::getModel("udropship/session")->getVendor();
    }

    /**
     * @return string
     */
    public function getActionUrl()
    {
        return $this->getUrl("*/*/massAttributeSet", array("_secure" => true));
    }
}