<?php

/**
 * @method Zolago_Reports_Model_Resource_Product_Index_Viewed _getResource()
 * Class Zolago_Reports_Model_Product_Index_Viewed
 */
class Zolago_Reports_Model_Product_Index_Viewed extends Mage_Reports_Model_Product_Index_Viewed
{
    public function _construct() {
        parent::_construct();
    }

    /**
     * Gets sharing code
     *
     * @return string
     */
    public function getSharingCode() {
        if ($this->hasData('sharing_code')) {
            $sc = $this->getData('sharing_code');
        } elseif (Mage::registry('sharing_code')) {
            $sc = Mage::registry('sharing_code');
            $this->setData('sharing_code', $sc);
        } else {
            $sc = Mage::helper('zolagowishlist')->getWishlist()->getData('sharing_code');
        }
        return $sc;
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
        if (!$this->hasAddedAt()) {
            $time = Mage::getSingleton('core/data')->timestamp();
            $this->setAddedAt(date('Y-m-d H:i:s', $time));
        }

        return $this;
    }
}
