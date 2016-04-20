<?php

class ZolagoOs_OmniChannelVendorRatings_Block_ReviewForm extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('unirgy/ratings/customer/review_form.phtml');
    }

    protected function _beforeToHtml()
    {
        $data = Mage::helper('udratings')->fetchFormData($this->getRelEntityPkValue());
        $data = new Varien_Object($data);

        // add logged in customer name as nickname
        if (!$data->getNickname()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            if ($customer && $customer->getId()) {
                $data->setNickname($customer->getFirstname());
            }
        }
        $this->assign('data', $data);
        return parent::_beforeToHtml();
    }

    public function getAction()
    {
        $id = $this->getEntityPkValue();
        $relId = $this->getRelEntityPkValue();
        return Mage::getUrl('udratings/customer/post', array('id'=>$id, 'rel_id'=>$relId));
    }

    protected function _getRatingsCollection()
    {
        $ratingCollection = Mage::getModel('rating/rating')
            ->getResourceCollection()
            ->addEntityFilter('udropship_vendor')
            ->setPositionOrder()
            ->addRatingPerStoreName(Mage::app()->getStore()->getId())
            ->setStoreFilter(Mage::app()->getStore()->getId());
        return $ratingCollection;
    }
    protected $_aggregateRatings;
    public function getAggregateRatings()
    {
        if (null === $this->_aggregateRatings) {
            $this->_aggregateRatings = $this->_getRatingsCollection()
                ->addFieldToFilter('is_aggregate', 1)
                ->addOptionToItems();
        }
        return $this->_aggregateRatings;
    }
    protected $_nonAggregateRatings;
    public function getNonAggregateRatings()
    {
        if (null === $this->_nonAggregateRatings) {
            $this->_nonAggregateRatings = $this->_getRatingsCollection()
                ->addFieldToFilter('is_aggregate', 0)
                ->addOptionToItems();
        }
        return $this->_nonAggregateRatings;
    }
}
