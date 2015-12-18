<?php

class Zolago_Adminhtml_Catalog_Product_Action_SolrController extends Mage_Adminhtml_Controller_Action {

    /**
     * When admin select mass action on admin product grid
     */
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
        $this->_getSession()->addSuccess($this->getHelper()->__('%s products have been added to the solr queue.', count($ids)));
        $this->_redirectReferer();
    }

    /**
     * ACL
     * @return bool
     */
    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/products');
    }

    /**
     * @return Zolago_Adminhtml_Helper_Data
     */
    public function getHelper() {
        return Mage::helper("zolagoadminhtml");
    }
}