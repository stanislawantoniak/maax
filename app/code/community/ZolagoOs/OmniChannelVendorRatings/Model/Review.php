<?php

class ZolagoOs_OmniChannelVendorRatings_Model_Review extends Mage_Review_Model_Review
{
    public function aggregate()
    {
        Mage::helper('udratings')->useEt($this->getEntityId());
        parent::aggregate();
        Mage::helper('udratings')->resetEt();
        return $this;
    }

    public function getEntitySummary($product, $storeId=0)
    {
        Mage::helper('udratings')->useEt($this->getEntityId());
        parent::getEntitySummary($product);
        Mage::helper('udratings')->resetEt();
    }
}