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
            /** @var Zolago_Po_Model_Po_Item $item */
            if ($this->canSetCommission($item)) {
                $id = $item->getProductId();
                $allIds[] = $id;
            }
        }
		
		$terminalPercent = $this->getTerminalPercentForChargeLowerCommission($vendor);
		$lowerCommissionItems = array();
        foreach ($products as $item) {
            if ($this->canSetCommission($item)) {
                $id = $item->getProductId();
                $product = $item->getProduct();
                $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')
                    ->getParentIdsByChild($id);
                $parentId = isset($parentIds[0]) ? $parentIds[0] : 0;

				// Retrieve items for lower commission (previously sales item)
				// now attribute 'product_flag' (FLAG_SALE|FLAG_PROMOTION)
				// @see Zolago_Catalog_Model_Product_Source_Flag is only for user on front
				// attribute 'charge_lower_commission' is now for logic with lower commission 
				if ($product->getChargeLowerCommission() >= $terminalPercent) {
					$lowerCommissionItems[$id] = $id;
					if (!empty($parentId)) {
						$lowerCommissionItems[$parentId] = $parentId;
					}
				}

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
                    if (!empty($lowerCommissionItems[$parentId])) {
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
                    if (!empty($lowerCommissionItems[$id])) {
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
