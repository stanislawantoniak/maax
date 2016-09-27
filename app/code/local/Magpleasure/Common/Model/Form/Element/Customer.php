<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Model_Form_Element_Customer extends Varien_Data_Form_Element_Text
{
    /**
     * Common Helper
     *
     * @return Magpleasure_Common_Helper_Data
     */
    protected function _getCommonHelper()
    {
        return Mage::helper('magpleasure');
    }

    protected function _getCustomerName($customerId)
    {
        /** @var Mage_Customer_Model_Customer $cusotmer  */
        $customer = Mage::getModel('customer/customer')->load($customerId);
        return $customer->getName();
    }

    /**
     * Retrives element html
     * @return string
     */
    public function getElementHtml()
    {
        $customerId = $this->getValue();
        $html = "";
        if ($customerId){
            $url = Mage::helper('adminhtml')->getUrl('adminhtml/customer/edit', array('id' => $customerId));
            $html .= "<a href=\"{$url}\" target=\"_blank\">".$this->_getCustomerName($customerId)."</a>";
        } else {
            $html .= $this->_getCommonHelper()->__('Guest');
        }
        return $html;
    }
}