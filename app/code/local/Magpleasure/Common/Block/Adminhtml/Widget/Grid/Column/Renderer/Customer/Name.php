<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Block_Adminhtml_Widget_Grid_Column_Renderer_Customer_Name
    extends Magpleasure_Common_Block_Adminhtml_Widget_Grid_Column_Renderer_Abstract
{

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        $customerId = $this->_getValue($row);
        if ($customerId) {
            $html = "";
            /** @var Mage_Customer_Model_Customer $customer  */
            $customer = Mage::getModel('customer/customer')->load($customerId);
            $name = $customer->getName();
            $url = $this->getUrl('adminhtml/customer/edit', array('id'=>$customerId));
            $html .= "<a href=\"{$url}\" target=\"_blank\">{$name}</a>";
            return $html;
        } else {
            return $this->_commonHelper()->__("Guest");
        }
        return parent::render($row);
    }



}