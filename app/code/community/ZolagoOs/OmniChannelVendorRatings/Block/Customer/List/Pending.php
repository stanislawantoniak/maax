<?php

class ZolagoOs_OmniChannelVendorRatings_Block_Customer_List_Pending extends ZolagoOs_OmniChannelVendorRatings_Block_Customer_List_Abstract
{
    protected $_reviewCollection;
    public function getReviewsCollection()
    {
        if (null === $this->_reviewCollection) {
            $this->_reviewCollection = Mage::helper('udratings')->getPendingCustomerReviewsCollection();
        }
        return $this->_reviewCollection;
    }
    
}