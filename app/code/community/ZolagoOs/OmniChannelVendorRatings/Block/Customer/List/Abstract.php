<?php

abstract class ZolagoOs_OmniChannelVendorRatings_Block_Customer_List_Abstract extends Mage_Sales_Block_Items_Abstract
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($toolbar = $this->getLayout()->getBlock('udratings_list.toolbar')) {
            $toolbar->setCollection($this->getReviewsCollection());
            $this->setChild('toolbar', $toolbar);
        }

        return $this;
    }
    abstract public function getReviewsCollection();
}