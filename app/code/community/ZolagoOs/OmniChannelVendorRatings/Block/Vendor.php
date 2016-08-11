<?php

class ZolagoOs_OmniChannelVendorRatings_Block_Vendor extends Mage_Core_Block_Template
{
    protected $_availableTemplates = array(
        'default' => 'unirgy/ratings/vendor/summary.phtml',
        'short'   => 'unirgy/ratings/vendor/summary_short.phtml'
    );

    public function getSummaryHtml($vendor, $templateType, $displayIfNoReviews)
    {
        // pick template among available
        if (empty($this->_availableTemplates[$templateType])) {
            $templateType = 'default';
        }
        $this->setTemplate($this->_availableTemplates[$templateType]);

        $this->setDisplayIfEmpty($displayIfNoReviews);

        $this->setVendor($vendor);
        $vendor = $this->getVendor();
        if (!$vendor->getRatingSummary()) {
            Mage::helper('udratings')->useMyEt();
            Mage::getModel('review/review')->getEntitySummary($vendor, Mage::app()->getStore()->getId());
            Mage::helper('udratings')->resetEt();
        }

        return $this->toHtml();
    }

    public function getRatingSummary()
    {
        return $this->getVendor()->getRatingSummary()->getRatingSummary();
    }

    public function getReviewsCount()
    {
        return $this->getVendor()->getRatingSummary()->getReviewsCount();
    }

    public function setVendor($vendor)
    {
        $this->setData('vendor', Mage::helper('udropship')->getVendor($vendor));
        return $this;
    }

    public function getReviewsUrl()
    {
        return Mage::getUrl('udratings/vendor/index', array(
           'id'        => $this->getVendor()->getId(),
        ));
    }
    public function addTemplate($type, $template)
    {
        $this->_availableTemplates[$type] = $template;
    }
}
