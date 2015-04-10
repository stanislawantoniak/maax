<?php

class Zolago_Reports_Model_Product_Index_Viewed extends Mage_Reports_Model_Product_Index_Viewed
{
    /**
     * Gets sharing code
     *
     * @return string
     */
    public function getSharingCode() {
        if ($this->hasData('sharing_code')) {
            return $this->getData('sharing_code');
        }
        /** @var Mage_Wishlist_Model_Wishlist $wishlist */
        $wishlist = Mage::helper('zolagowishlist')->getWishlist();
        return $wishlist->getData('sharing_code');
    }

    /**
     * Prepare customer/sharing_code (old visitor_id), store data before save
     *
     * @return Zolago_Reports_Model_Product_Index_Viewed
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        if (!$this->hasSharingCode()) {
            $this->setSharingCode($this->getSharingCode());
        }
        if (!$this->hasCustomerId()) {
            $this->setCustomerId($this->getCustomerId());
        }
        if (!$this->hasStoreId()) {
            $this->setStoreId($this->getStoreId());
        }
        if (!$this->hasAddedAt()) {
            $time = Mage::getSingleton('core/data')->timestamp();
            $this->setAddedAt(date('Y-m-d H:i:s', $time));
        }

        return $this;
    }

    /**
     * On customer login merge sharing_code (old visitor)/customer index
     *
     * @return Zolago_Reports_Model_Product_Index_Viewed
     */
    public function updateCustomerFromVisitor()
    {
        // todo
        $this->_getResource()->updateCustomerFromVisitor($this);
        return $this;
    }

    /**
     * Purge sharing_code (old visitor) data by customer (logout)
     *
     * @return Zolago_Reports_Model_Product_Index_Viewed
     */
    public function purgeVisitorByCustomer()
    {
        // todo
        $this->_getResource()->purgeVisitorByCustomer($this);
        return $this;
    }


}
