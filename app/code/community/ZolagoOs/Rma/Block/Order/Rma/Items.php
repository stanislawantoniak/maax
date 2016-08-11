<?php

/**
 * Sales order view items block
 */
class ZolagoOs_Rma_Block_Order_Rma_Items extends Mage_Sales_Block_Items_Abstract
{
    /**
     * Retrieve current order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    public function getPrintRmaUrl($rma){
        return Mage::getUrl('*/*/printRma', array('rma_id' => $rma->getId()));
    }

    public function getPrintAllRmasUrl($order){
        return Mage::getUrl('*/*/printRma', array('order_id' => $order->getId()));
    }

    public function getCommentsHtml($rma)
    {
        $html = '';
        $comments = $this->getChild('rma_comments');
        if ($comments) {
            $comments->setEntity($rma)
                ->setTitle(Mage::helper('sales')->__('About Your Return'));
            $html = $comments->toHtml();
        }
        return $html;
    }
}
