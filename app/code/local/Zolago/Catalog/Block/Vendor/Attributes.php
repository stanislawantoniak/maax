<?php

class Zolago_Catalog_Block_Vendor_Attributes extends Mage_Core_Block_Template
{

    
    /**
     * attribute set list for select
     */
     protected function _getAttributeSetList() {
         $attributeSetCollection = Mage::getResourceModel('eav/entity_attribute_set_collection') ->load();
         $list = array();
         foreach ($attributeSetCollection as $id=>$item) {
             $list[$item->getId()] = $item->getAttributeSetName();
         }
         return $list;
     }
}