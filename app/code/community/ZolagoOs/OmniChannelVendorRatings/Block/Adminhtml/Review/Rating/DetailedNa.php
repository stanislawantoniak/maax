<?php

class ZolagoOs_OmniChannelVendorRatings_Block_Adminhtml_Review_Rating_DetailedNa extends Mage_Adminhtml_Block_Template
{
    protected $_voteCollection = false;
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('udratings/rating/detailed_na.phtml');
        if( Mage::registry('review_data') ) {
            $this->setReviewId(Mage::registry('review_data')->getReviewId());
        }
    }

    public function getRating()
    {
        if( !$this->getRatingCollection() ) {
            if( Mage::registry('review_data') ) {
                $stores = Mage::registry('review_data')->getStores();

                $stores = array_diff($stores, array(0));

                $ratingCollection = Mage::getModel('rating/rating')
                    ->getResourceCollection()
                    ->addEntityFilter('udropship_vendor')
                    ->addFieldToFilter('is_aggregate', 0)
                    ->setStoreFilter($stores)
                    ->setPositionOrder()
                    ->load()
                    ->addOptionToItems();

                $this->_voteCollection = Mage::getModel('rating/rating_option_vote')
                    ->getResourceCollection()
                    ->setReviewFilter($this->getReviewId())
                    ->addOptionInfo()
                    ->load()
                    ->addRatingOptions();

            } elseif (!$this->getIsIndependentMode()) {
                $ratingCollection = Mage::getModel('rating/rating')
                    ->getResourceCollection()
                    ->addEntityFilter('udropship_vendor')
                    ->addFieldToFilter('is_aggregate', 0)
                    ->setStoreFilter(null)
                    ->setPositionOrder()
                    ->load()
                    ->addOptionToItems();
            } else {
                 $ratingCollection = Mage::getModel('rating/rating')
                    ->getResourceCollection()
                    ->addEntityFilter('udropship_vendor')
                    ->addFieldToFilter('is_aggregate', 0)
                    ->setStoreFilter($this->getRequest()->getParam('select_stores') ? $this->getRequest()->getParam('select_stores') : $this->getRequest()->getParam('stores'))
                    ->setPositionOrder()
                    ->load()
                    ->addOptionToItems();


            }
            $this->setRatingCollection( ( $ratingCollection->getSize() ) ? $ratingCollection : false );
        }
        return $this->getRatingCollection();
    }

    public function setIndependentMode()
    {
        $this->setIsIndependentMode(true);
        return $this;
    }

    public function isSelected($option, $rating)
    {
        if($this->getIsIndependentMode()) {
            $ratings = $this->getRequest()->getParam('ratings');

            if(isset($ratings[$option->getRatingId()])) {
                return $option->getId() == $ratings[$option->getRatingId()];
            }

            return false;
        }

        if($this->_voteCollection) {
            foreach($this->_voteCollection as $vote) {
                if($option->getId() == $vote->getOptionId()) {
                    return true;
                }
            }
        }

        return false;
    }
}
