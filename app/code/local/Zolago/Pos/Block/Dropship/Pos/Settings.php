<?php
/**
 * pos global settings for vendor
 */

class Zolago_Pos_Block_Dropship_Pos_Settings extends Mage_Core_Block_Template {
    
    /**
     * pos list
     *
     * @return Zolago_Pos_Model_Resource_Pos_Collection
     */
     public function getPosList() {
         $vendor = $this->getVendor();
        /* @var $vendor Unirgy_Dropship_Model_Vendor */
        $collection = Mage::getResourceModel("zolagopos/pos_collection");
        /* @var $collection Zolago_Pos_Model_Resource_Pos_Collection */
        $collection->addVendorFilter($vendor);
        $collection->addFieldToFilter('is_active',1);
        $collection->setOrder("name","ASC");
        return $collection;

     }

    public function getSaveUrl()
    {
        /**
         * @see Zolago_Pos_Dropship_PosController::settingsSaveAction
         */
        return Mage::getUrl("udropship/pos/settingsSave");
    }
     
     
    /**
     * logged vendor
     *
     * @return Zolago_Dropship_Model_Vendor
     */
     public function getVendor() {
         return Mage::getSingleton('udropship/session')->getVendor();
     }

    public function getPosConfigurableWebsites()
    {
        $websites = Mage::app()->getWebsites();
        $websitesAllowed = $this->getVendor()->getWebsitesAllowed();

        foreach ($websites as $websiteId => $website) {
            if (!in_array($websiteId, $websitesAllowed)) {
                unset($websites[$websiteId]);
            }
        }

        return $websites;
    }

    /*
     *
     */
    public function getPosWebsiteRelation()
    {
        $result = array();
        $posWebsiteRelation = Mage::getResourceModel("zolagopos/pos")
            ->getPosWebsiteRelation($this->getVendor()->getId());

        if (empty($posWebsiteRelation))
            return $result;

        foreach ($posWebsiteRelation as $posWebsiteRelationItem) {
            $result[$posWebsiteRelationItem["website_id"]][$posWebsiteRelationItem["pos_id"]] = $posWebsiteRelationItem["pos_id"];
        }
        return $result;
    }
	
}
