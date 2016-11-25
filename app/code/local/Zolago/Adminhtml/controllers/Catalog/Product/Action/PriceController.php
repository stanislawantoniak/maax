<?php

class Zolago_Adminhtml_Catalog_Product_Action_PriceController extends Mage_Adminhtml_Controller_Action {

    /**
     * When admin select mass action on admin product grid
     */
    public function pushAction() {

        $ids = Mage::app()->getRequest()->getParam('product');

        Zolago_Catalog_Helper_Configurable::queue($ids);
        $this->_getSession()->addSuccess($this->getHelper()->__('%s products have been added to the price queue.', count($ids)));
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