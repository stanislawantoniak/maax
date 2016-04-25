<?php

class ZolagoOs_OmniChannelVendorAskQuestion_Block_Product_Question_List extends Mage_Catalog_Block_Product_View_Description
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($toolbar = $this->getLayout()->getBlock('udqa.product.list.toolbar')) {
            $toolbar->setCollection($this->getQuestionsCollection());
            $this->setChild('toolbar', $toolbar);
        }

        return $this;
    }
    public function getQuestionsCollection()
    {
        return Mage::helper('udqa')->getProductQuestionsCollection();
    }
    public function getProductUrl($question)
    {
        return $this->getUrl('catalog/product/view', array('id'=>$question->getProductId()));
    }
}