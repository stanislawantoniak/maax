<?php

class ZolagoOs_OmniChannelVendorRatings_Block_Customer_List extends ZolagoOs_OmniChannelVendorRatings_Block_Customer_List_Abstract
{
    protected $_reviewCollection;
    public function getReviewsCollection()
    {
        if (null === $this->_reviewCollection) {
            $this->_reviewCollection = Mage::helper('udratings')->getCustomerReviewsCollection();
        }
        return $this->_reviewCollection;
    }
}