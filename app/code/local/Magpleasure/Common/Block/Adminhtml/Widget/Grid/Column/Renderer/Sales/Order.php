<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Block_Adminhtml_Widget_Grid_Column_Renderer_Sales_Order
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
        $orderId = $this->_getValue($row);
        if ($orderId) {
            $html = "";
            /** @var Mage_Sales_Model_Order $order  */
            $order = Mage::getModel('sales/order')->load($orderId);
            $incrementId = $order->getIncrementId();
            $url = $this->getUrl('adminhtml/sales_order/view', array('order_id'=>$orderId));
            $html .= "<a href=\"{$url}\" target=\"_blank\">#{$incrementId}</a>";
            return $html;
        } else {
            return $this->_commonHelper()->__("No order");
        }
    }
}