<?php

class ZolagoOs_OmniChannelVendorAskQuestion_Block_Product_Tab extends Mage_Catalog_Block_Product_View_Description
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->getLayout()->createBlock('page/html_pager', 'udqa.product.list.toolbar');

        $this->setChild('udqa.list',
            $this->getLayout()->createBlock('udqa/product_question_list', 'udqa.product.list')->setTemplate('udqa/product/list.phtml')
        );
        $this->setChild('udqa.qa',
            $this->getLayout()->createBlock('udqa/product_question', 'udqa.product.question')->setTemplate('udqa/product/question.phtml')
        );
        return $this;
    }
}
