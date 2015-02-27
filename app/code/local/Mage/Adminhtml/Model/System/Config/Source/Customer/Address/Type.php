<?php

class Mage_Adminhtml_Model_System_Config_Source_Customer_Address_Type
{
    /**
     * Retrieve possible customer address types
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Mage_Customer_Model_Address_Abstract::TYPE_BILLING,
                'label' => Mage::helper('adminhtml')->__('Billing Address')
            ),
            array(
                'value' => Mage_Customer_Model_Address_Abstract::TYPE_SHIPPING,
                'label' => Mage::helper('adminhtml')->__('Shipping Address')
            )
        );
    }
}
