<?php
/**
 * pos global settings for vendor
 */

class Zolago_Pos_Block_Dropship_Pos_Settings extends Mage_Core_Block_Template {
    
    /**
     * pos list
     *
     * @return array
     */
     public function getPosList() {
         $vendor = $this->getVendor();
        /* @var $vendor ZolagoOs_OmniChannel_Model_Vendor */
        $collection = Mage::getResourceModel("zolagopos/pos_collection");
        /* @var $collection Zolago_Pos_Model_Resource_Pos_Collection */
        $collection->addVendorFilter($vendor);
        $collection->addFieldToFilter('is_active',1);
        $collection->setOrder("name","ASC");
        return $collection;

     }
     
     
    /**
     * logged vendor
     *
     * @return ZolagoOs_OmniChannel_Model_Vendor     
     */
     public function getVendor() {
         return Mage::getSingleton('udropship/session')->getVendor();
     }
	
	
}
