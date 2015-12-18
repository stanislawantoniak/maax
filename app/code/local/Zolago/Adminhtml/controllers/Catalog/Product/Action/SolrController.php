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
        $this->_getSession()->addSuccess($this->getHelper()->__('%s successfully push to solr.', count($ids)));
        $this->_redirectReferer();
    }

    protected function _isAllowed() {
        return true; //todo
    }

    /**
     * @return Zolago_Adminhtml_Helper_Data
     */
    public function getHelper() {
        return Mage::helper("zolagoadminhtml");
    }
}