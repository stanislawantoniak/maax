<?php

/**
 * Class GH_MageMonkey_Block_Adminhtml_Ecommerceapi_Grid
 */
class GH_MageMonkey_Block_Adminhtml_Ecommerceapi_Grid extends Ebizmarts_MageMonkey_Block_Adminhtml_Ecommerceapi_Grid {

    protected function _prepareCollection()
    {
        $orders = array();

        foreach (Mage::app()->getStores() as $storeId => $store) {
            $api = Mage::getModel('monkey/api', array('store' => $storeId));
            $result = $api->ecommOrders(0, 500);
            $orders += $result['data'];
        }

        $collection = Mage::getModel('monkey/custom_collection', array($orders));

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }
}