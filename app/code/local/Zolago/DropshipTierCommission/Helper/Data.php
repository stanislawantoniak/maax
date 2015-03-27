<?php

class Zolago_DropshipTierCommission_Helper_Data extends Unirgy_DropshipTierCommission_Helper_Data
{

    public function processPo($po)
    {
        $this->_processPoCommission($po);
//        $this->_processPoTransactionFee($po); // removed fixed rates
    }

    /**
     * @param $po Zolago_Po_Model_Po
     */
    protected function _processPoCommission($po)
    {
        $tierRates = $this->getGlobalTierComConfig();

        // Tier rates for vendor
        $vendorTierRates = $po->getUdropshipVendor() ? $this->getTiercomRates($po->getUdropshipVendor()) : array();

        /** @var Zolago_Dropship_Helper_Data $hlpUd */
        $hlpUd = Mage::helper('udropship');

        /** @var Zolago_Dropship_Model_Vendor $vendor */
        $vendor = $hlpUd->getVendor($po->getUdropshipVendor());

        //GLOBAL default values
        $defaultCommissionPercent = Mage::getStoreConfig('udropship/tiercom/commission_percent');
        $defaultSaleCommissionPercent = Mage::getStoreConfig('udropship/tiercom/sale_commission_percent');

        //vendor default values
        $defaultVendorCommissionPercent = $vendor->getCommissionPercent();
        $defaultSaleVendorCommissionPercent = $vendor->getSaleCommissionPercent();

        foreach ($vendorTierRates as $cat => $val) {
            if (!empty($val['value'])) {
                $tierRates[$cat]['value'] = $val['value'];
            } else if (!empty($defaultVendorCommissionPercent)) {
                $tierRates[$cat]['value'] = $defaultVendorCommissionPercent;
            }
            if (!empty($val['sale_value'])) {
                $tierRates[$cat]['sale_value'] = $val['sale_value'];
            } else if (!empty($defaultSaleVendorCommissionPercent)) {
                $tierRates[$cat]['sale_value'] = $vendor->getSaleCommissionPercent();
            }
        }

        $products = $po->getAllItems();
        $locale = Mage::app()->getLocale();
        $allIds = array();
        foreach ($products as $item) {
            if ($this->canSetCommission($item)) {
                $id = $item->getProductId();
                $allIds[] = $id;
            }
        }

        /// sale flag
        $saleProducts = Mage::getResourceModel('zolagocatalog/product_collection');
        $saleProducts->addIdFilter($allIds);
        $saleProducts->addProductFlagAttributeToSelect($po->getStore()->getId());
        $saleItems = array();
        foreach ($saleProducts as $product) {
            $id = $product->getData('entity_id');
            $saleItems[$id] = $id;
        }
        foreach ($products as $item) {
            if ($this->canSetCommission($item)) {
                $id = $item->getProductId();
                $product = Mage::getModel('catalog/product')->load($id);
                $categories = $product->getCategoryIds();
                $commission = $defaultCommissionPercent;

                foreach ($categories as $catId) {
                    if (isset($tierRates[$catId])) {
                        if (!empty($tierRates[$catId]['value'])) {
                            $commission = $tierRates[$catId]['value'];
                        }
                    }
                }

                // override if product is in sale
                if (!empty($saleItems[$id])) {
                    $commission = $defaultSaleCommissionPercent;
                    if (!empty($defaultSaleCommissionPercent)) {
                        $commission = $defaultSaleCommissionPercent;
                    }

                    foreach ($categories as $catId) {
                        if (isset($tierRates[$catId])) {
                            if (!empty($tierRates[$catId]['sale_value'])) {
                                $commission = $tierRates[$catId]['sale_value'];
                            }
                        }
                    }
                }
                $item->setCommissionPercent($locale->getNumber($commission));
            }
        }
    }

    /**
     * @param $item Zolago_Po_Model_Po_Item
     * @return bool
     */
    public function canSetCommission($item) {
        $cp = $item->getData('commission_percent');
        return is_null($cp);
    }


}
