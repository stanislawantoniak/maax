<?php

class Zolago_Adminhtml_Catalog_Product_Action_SolrController extends Mage_Adminhtml_Controller_Action {

    public function pushAction() {

        $ids = Mage::app()->getRequest()->getParam('product');

        // push to solr
        Mage::dispatchEvent(
            "admin_manually_push_to_solr_after",
            array(
                "product_ids"   => $ids,
                "check_parents" => true
            )
        );

        $this->_redirectReferer();
        var_dump($ids);
        die("happy die!");
    }

    protected function _isAllowed() {
        return true; //todo
    }
}