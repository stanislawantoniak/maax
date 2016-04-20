<?php

class ZolagoOs_OmniChannelVendorRatings_Block_Vendor_List extends Mage_Core_Block_Template
{
    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        if ($toolbar = $this->getLayout()->getBlock('udratings_list.toolbar')) {
            $toolbar->setCollection($this->getReviewsCollection());
            $this->setChild('toolbar', $toolbar);
        }

        return $this;
    }
    protected $_reviewCollection;
    public function getReviewsCollection()
    {
        if (null === $this->_reviewCollection) {
            $this->_reviewCollection = Mage::helper('udratings')->getVendorReviewsCollection($this->getVendor()->getId());
        }
        return $this->_reviewCollection;
    }
    public function getVendor()
    {
        $vId = $this->getRequest()->getParam('id');
        $vId = $vId ? $vId : Mage::helper('umicrosite')->getCurrentVendor()->getId();
        return Mage::helper('udropship')->getVendor($vId);
    }
    public function getSize()
    {
        return $this->getReviewsCollection()->getSize();
    }
    public function getAddressFormatted($review)
    {
        $addrStr = '';
        if (($__sa = $review->getShippingAddress())) {
            $addrStr = $__sa->getCity();
            if ($__sa->getRegionCode()) {
                $addrStr .= ', '.$__sa->getRegionCode();
            }
            $addrStr .= ', '.$__sa->getCountry();
        }
        return $addrStr;
    }
}