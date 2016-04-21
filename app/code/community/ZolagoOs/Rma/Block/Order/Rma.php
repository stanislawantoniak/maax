<?php

class ZolagoOs_Rma_Block_Order_Rma extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('urma/sales/order/rma.phtml');
    }

    protected function _prepareLayout()
    {
        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle($this->__('Order # %s', $this->getOrder()->getRealOrderId()));
        }
        $this->setChild(
            'payment_info',
            $this->helper('payment')->getInfoBlock($this->getOrder()->getPayment())
        );
    }

    public function getPaymentInfoHtml()
    {
        return $this->getChildHtml('payment_info');
    }

    /**
     * Retrieve current order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    public function getBackUrl()
    {
        return Mage::getUrl('*/*/history');
    }

    public function getInvoiceUrl($order)
    {
        return Mage::getUrl('*/*/invoice', array('order_id' => $order->getId()));
    }

    public function getViewUrl($order)
    {
        return Mage::getUrl('*/*/view', array('order_id' => $order->getId()));
    }

    public function getCreditmemoUrl($order)
    {
        return Mage::getUrl('*/*/creditmemo', array('order_id' => $order->getId()));
    }

    public function getShipmentUrl($order)
    {
        return Mage::getUrl('*/*/shipment', array('order_id' => $order->getId()));
    }


    public function getPrintRmaUrl($rma){
        return Mage::getUrl('*/*/printRma', array('rma_id' => $rma->getId()));
    }

    public function getPrintAllRmaUrl($order){
        return Mage::getUrl('*/*/printRma', array('order_id' => $order->getId()));
    }
}
