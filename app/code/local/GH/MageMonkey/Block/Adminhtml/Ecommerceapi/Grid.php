<?php

/**
 * Class GH_MageMonkey_Block_Adminhtml_Ecommerceapi_Grid
 */
class GH_MageMonkey_Block_Adminhtml_Ecommerceapi_Grid extends Ebizmarts_MageMonkey_Block_Adminhtml_Ecommerceapi_Grid
{

    /**
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $orders = array();


        foreach (Mage::app()->getStores() as $storeId => $store) {
            $api = Mage::getModel('monkey/api', array('store' => $storeId));
            $result = $api->ecommOrders(0, 500);

            try {
                if (isset($result['data'])) {
                    $orders += $result['data'];
                    $collection = Mage::getModel('monkey/custom_collection', array($orders));
                } else {
                    echo $result . " STORE_ID : {$storeId} <br />";
                }

            } catch (Mage_Adminhtml_Exception $e) {
                Mage::logException($e);
            }
        }


        try {
            $this->setCollection($collection);

        } catch (Mage_Adminhtml_Exception $e) {
            Mage::logException($e);
        }

        if (!empty($orders)) {
            return parent::_prepareCollection();
        }

    }
}