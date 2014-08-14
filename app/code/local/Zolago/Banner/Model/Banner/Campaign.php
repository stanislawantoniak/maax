<?php

class Zolago_Banner_Model_Banner_Campaign
{
    /**
     * @return array
     */
    public function toOptionHash()
    {
        $vendor = Mage::getSingleton('udropship/session')->getVendor();
        $collection = Mage::getResourceModel('zolagocampaign/campaign_collection');
        $collection->addVendorFilter($vendor);
        $campaigns = $collection->load();
        $options = array();
        foreach ($campaigns as $campaign) {
            $options[$campaign->getId()] = $campaign->getName();
        }
        return $options;
    }
}