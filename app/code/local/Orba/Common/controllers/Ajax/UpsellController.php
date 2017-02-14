<?php

class Orba_Common_Ajax_UpsellController extends Orba_Common_Controller_Ajax {
	
    public function getInfoAction() {
        $pid = $this->getRequest()->getParam('pid');
        $block = Mage::app()->getLayout()->createBlock('zolagocatalog/product_list_upsell_sizes');
        $block->setProductId($pid);
        echo $block->toHtml();
    }	
}