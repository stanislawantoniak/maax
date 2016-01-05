<?php

class Zolago_Dropship_Model_Source_Indexbygoogle
    extends Zolago_Catalog_Model_Product_Source_Abstract
{
    const PRODUCT_INDEX_BY_GOOGLE_USE_CONFIG = 0;
    const PRODUCT_INDEX_BY_GOOGLE_NO = 1;
    const PRODUCT_INDEX_BY_GOOGLE_YES = 2;


    /**
     * Retrieve all attribute options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = array(
                array(
                    'label' => Mage::helper('catalog')->__('Use vendor and brandshop config'),
                    'value' => self::PRODUCT_INDEX_BY_GOOGLE_USE_CONFIG
                ),
                array(
                    'label' => Mage::helper('catalog')->__('Yes'),
                    'value' => self::PRODUCT_INDEX_BY_GOOGLE_YES
                ),
                array(
                    'label' => Mage::helper('catalog')->__('No'),
                    'value' => self::PRODUCT_INDEX_BY_GOOGLE_NO
                )
            );
        }
        return $this->_options;
    }

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }


}