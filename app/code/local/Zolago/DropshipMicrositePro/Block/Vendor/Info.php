<?php
/**
 * User: Andrzej Spólnicki
 */

class Zolago_DropshipMicrositePro_Block_Vendor_Info extends Zolago_Catalog_Block_Product_Vendor_Info  {

    /**
     * @return Zolago_Dropship_Model_Vendor
     */
    public function getVendor() {

        return $_vendor = Mage::helper('umicrosite')->getCurrentVendor();

    }
} 