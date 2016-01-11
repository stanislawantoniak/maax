<?php

/**
 * Class Zolago_Dropship_Model_Source_Brandshop_Indexbygoogle
 */
class Zolago_Dropship_Model_Source_Brandshop_Indexbygoogle
    extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    const BRANDSHOP_INDEX_BY_GOOGLE_USE_VENDOR_CONFIG = 0;
    const BRANDSHOP_INDEX_BY_GOOGLE_YES = 1;
    const BRANDSHOP_INDEX_BY_GOOGLE_NO = 2;


    /**
     * Retrieve all attribute options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $helper = Mage::helper('zolagodropship');

            $this->_options = array(
                array(
                    'label' => $helper->__('Use vendor config'),
                    'value' => self::BRANDSHOP_INDEX_BY_GOOGLE_USE_VENDOR_CONFIG
                ),
                array(
                    'label' => $helper->__('Yes'),
                    'value' => self::BRANDSHOP_INDEX_BY_GOOGLE_YES
                ),
                array(
                    'label' => $helper->__('No'),
                    'value' => self::BRANDSHOP_INDEX_BY_GOOGLE_NO
                )
            );
        }
        return $this->_options;
    }

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }


    public function toOptionHash()
    {
        $helper = Mage::helper('zolagodropship');
        $indexByGoogleOptions = array(
            self::BRANDSHOP_INDEX_BY_GOOGLE_USE_VENDOR_CONFIG => $helper->__('Use vendor config'),
            self::BRANDSHOP_INDEX_BY_GOOGLE_YES => $helper->__('Yes'),
            self::BRANDSHOP_INDEX_BY_GOOGLE_NO => $helper->__('No'),
        );
        return $indexByGoogleOptions;
    }
}