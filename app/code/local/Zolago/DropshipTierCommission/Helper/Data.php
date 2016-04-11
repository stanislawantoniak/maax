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
		/** @var Zolago_Catalog_Model_Resource_Product_Collection $saleProducts */
        $saleProducts = Mage::getResourceModel('zolagocatalog/product_collection');
        $saleProducts->addIdFilter($allIds);
        $saleProducts->addProductFlagAttributeToSelect(Zolago_Catalog_Model_Product_Source_Flag::FLAG_SALE, $po->getStore()->getId());
        $saleItems = array();
        foreach ($saleProducts as $product) {
            $id = $product->getData('entity_id');
            $saleItems[$id] = $id;
        }
        foreach ($products as $item) {
            if ($this->canSetCommission($item)) {
                $id = $item->getProductId();
                $product = Mage::getModel('catalog/product')->load($id);
                $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')
                    ->getParentIdsByChild($id);
                $parentId = isset($parentIds[0]) ? $parentIds[0] : 0;

                if (!empty($parentId)) {
                    //get from parent
                    $productP = Mage::getModel('catalog/product')->load($parentId);
                    $categoriesP = $productP->getCategoryIds();
                    $commission = !empty($defaultVendorCommissionPercent) ? $defaultVendorCommissionPercent : $defaultCommissionPercent;

                    foreach ($categoriesP as $catPId) {
                        if (isset($tierRates[$catPId])) {
                            if (!empty($tierRates[$catPId]['value'])) {
                                $commission = $tierRates[$catPId]['value'];
                            }
                        }
                    }
                    unset($catPId);
                    // override if product is in sale
                    if (!empty($saleItems[$parentId])) {
                        $commission = !empty($defaultSaleVendorCommissionPercent) ? $defaultSaleVendorCommissionPercent : $defaultSaleCommissionPercent;

                        foreach ($categoriesP as $catPId) {
                            if (isset($tierRates[$catPId])) {
                                if (!empty($tierRates[$catPId]['sale_value'])) {
                                    $commission = $tierRates[$catPId]['sale_value'];
                                }
                            }
                        }
                    }
                } else {
                    $categoriesS = $product->getCategoryIds();

                    $commission = !empty($defaultVendorCommissionPercent) ? $defaultVendorCommissionPercent : $defaultCommissionPercent;

                    foreach ($categoriesS as $catSId) {
                        if (isset($tierRates[$catSId])) {
                            if (!empty($tierRates[$catSId]['value'])) {
                                $commission = $tierRates[$catSId]['value'];
                            }
                        }
                    }

                    unset($catSId);
                    // override if product is in sale
                    if (!empty($saleItems[$id])) {
                        $commission = !empty($defaultSaleVendorCommissionPercent) ? $defaultSaleVendorCommissionPercent : $defaultSaleCommissionPercent;

                        foreach ($categoriesS as $catSId) {
                            if (isset($tierRates[$catSId])) {
                                if (!empty($tierRates[$catSId]['sale_value'])) {
                                    $commission = $tierRates[$catSId]['sale_value'];
                                }
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

	/**
	 * Retrieve terminal percent
	 *
	 * Note: Prowizja dla wyprzedaży wyliczana dla produktów,
	 * które mają cenę sprzedaży mniejszą o n-procent od ceny przekreślonej
	 *
	 * @param Zolago_Dropship_Model_Vendor $vendor
	 * @param $store
	 * @return float
	 */
	public function getTerminalPercentForChargeLowerCommission(Zolago_Dropship_Model_Vendor $vendor, $store = null) {
		$percent = $vPercent = $vendor->getTerminalPercentForChargeLowerCommission();
		if (empty($vPercent)) {
			$percent = Mage::getStoreConfig('udropship/tiercom/terminal_percent_for_charge_lower_commission', $store);
		}
		return (float)$percent;
	}
}
