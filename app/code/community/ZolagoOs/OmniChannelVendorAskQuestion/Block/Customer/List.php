<?php

class ZolagoOs_OmniChannelVendorAskQuestion_Block_Customer_List extends Mage_Core_Block_Template
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($toolbar = $this->getLayout()->getBlock('udqa_list.toolbar')) {
            $toolbar->setCollection($this->getQuestionsCollection());
            $this->setChild('toolbar', $toolbar);
        }

        return $this;
    }
    public function getQuestionsCollection()
    {
        return Mage::helper('udqa')->getCustomerQuestionsCollection();
    }
    public function getNewUrl()
    {
        return $this->getUrl('udqa/customer/new');
    }
    public function getViewUrl($question)
    {
        return $this->getUrl('udqa/customer/view', array('question_id'=>$question->getId()));
    }
    public function getProductUrl($question)
    {
        return $this->getUrl('catalog/product/view', array('id'=>$question->getProductId()));
    }
    public function getShipmentUrl($question)
    {
        return $this->getUrl('sales/order/shipment', array('order_id'=>$question->getOrderId()));
    }

}