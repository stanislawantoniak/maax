<?php

class ZolagoOs_OmniChannelVendorRatings_Model_Mysql4_RatingOptionVoteCollection extends Mage_Rating_Model_Mysql4_Rating_Option_Vote_Collection
{
    public function addRatingInfo($storeId=null)
    {
        if (Mage::helper('udropship')->compareMageVer('1.6.0.0','1.11.0.0', '<')) {
            return parent::addRatingInfo($storeId);
        } else {
            $result = parent::addRatingInfo($storeId);
            $this->getSelect()->columns(array('is_aggregate'=>'rating.is_aggregate'));
            return $result;
        }
    }
}