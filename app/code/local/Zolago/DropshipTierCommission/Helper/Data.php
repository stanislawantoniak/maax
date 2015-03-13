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
        /** @var Zolago_Dropship_Helper_Data $hlpUd */
        $hlpUd = Mage::helper('udropship');
        /** @var Zolago_Dropship_Model_Vendor $vendor */
        $vendor = $hlpUd->getVendor($po->getUdropshipVendor());

        // Tier rates for vendor
        $tierRates = $po->getUdropshipVendor() ? $this->getTiercomRates($po->getUdropshipVendor()) : array();

        // Global tier rates
        $globalTierRates = $this->getGlobalTierComConfig();

        // Top categories for commissions
        // All children of udropship/tiercom/tiered_category_parent
        $topCats = $this->getTopCategories();

        // Collecting product ids witch don't have parent_item_id and
        // where commission can be set
        // parent_item_id means this is simple product
        // NOTE: by now adding product to PO by vendor portal add only simple product
        // ( not pair configurable and simple (with link to configurable) like in standard behaviour )
        $pIds = array(); // Products ids
        foreach ($po->getAllItems() as $item) {
            /** @var Zolago_Po_Model_Po_Item $item */
            if ($item->getParentItemId() || $item->getOrderItem()->getParentItem() ){
                Mage::log('ma parenta item: ' . $item->getId(), null, 'mylog.log');
                continue;
            }
            if ($this->canSetCommission($item)) {
                $pIds[] = $item->getProductId();
            }
        }

//        Mage::log($pIds, null, 'mylog.log');

        if (empty($pIds)) {
            Mage::log('no products to process!', null, 'mylog.log');
            return;
        }
        /** @var Mage_Catalog_Model_Resource_Product_Collection $products */
        $products = Mage::getResourceModel('catalog/product_collection')->addIdFilter($pIds);

        /** @var Zolago_Catalog_Model_Resource_Product_Configurable $modelZCPC */
        $modelZCPC = Mage::getResourceModel('zolagocatalog/product_configurable');
        $productsRelations = $modelZCPC->getConfigurableSimpleRelationArray($pIds);
//        Mage::log($productsRelations, null, 'mylog.log');

        $allIds = $pIds;
        foreach ($productsRelations as $k => $v) {
            $allIds[] = $k;
        }

//        Mage::log($allIds, null, 'mylog.log');


        /** @var Zolago_Campaign_Model_Resource_Campaign $resCampaign */
        $resCampaign = Mage::getModel('zolagocampaign/campaign')->getResource();
        $productsWithFlagSale = $resCampaign->getIsProductsInSaleOrPromotion($allIds, $vendor->getId(), Zolago_Campaign_Model_Campaign_Type::TYPE_SALE);
//        Mage::log($productsWithFlagSale, null, 'mylog.log');


        // If product have attribute for custom commission
        // get info about it
        $tcProdAttrCode = null;
        if (($tcProdAttr = $this->getCommProductAttribute())) {
            $tcProdAttrCode = $tcProdAttr->getAttributeCode();
            $products->addAttributeToSelect($tcProdAttrCode);
        }

        // Collecting categories ids of products
        $catIdsToLoad = array();
        $catIds = array();
        foreach ($po->getAllItems() as $item) {
            /** @var Zolago_Po_Model_Po_Item $item */
            $itemId = spl_object_hash($item);
            if ($item->getParentItemId() || $item->getOrderItem()->getParentItem() ||
                !($product = $products->getItemById($item->getProductId())) || !$this->canSetCommission($item)) {
                continue;
            }
            $_catIds = $product->getCategoryIds();
            if (empty($_catIds)) continue;
            reset($_catIds);
            $catIdsToLoad = array_merge($catIdsToLoad, $_catIds);
            $catIds[$itemId] = $_catIds;
        }
        $catIdsToLoad = array_unique($catIdsToLoad);

        /** @var Mage_Catalog_Model_Resource_Category_Collection $iCats */
        $iCats = Mage::getResourceModel('catalog/category_collection')->addIdFilter($catIdsToLoad);
        $subcatMatchFlag = Mage::getStoreConfigFlag('udropship/tiercom/match_subcategories');

        // Calculating commissions
        $locale = Mage::app()->getLocale();
        $ratesToUse = array();
        foreach ($po->getAllItems() as $item) {
            /** @var Zolago_Po_Model_Po_Item $item */

            if (!$this->canSetCommission($item) || $item->getParentItemId() || $item->getOrderItem()->getParentItem()) {
                Mage::log('no comm for product: '.$item->getId(), null, 'mylog.log');
                continue;
            }

            $itemId = spl_object_hash($item);
            $product = $products->getItemById($item->getProductId());
            $prodAttr = $product->getData($tcProdAttrCode);

            if ($product && $tcProdAttr && !empty($prodAttr)) {
                $ratesToUse[$itemId]['value'] = $locale->getNumber($product->getData($tcProdAttrCode));
            } elseif (!empty($catIds[$itemId])) {
                $exactMatched = $subcatMatched = false;
                $isGlobalTier = true;
                foreach ($catIds[$itemId] as $iCatId) {
                    if (!($iCat = $iCats->getItemById($iCatId))) continue;
                    $_subcatMatched = false;
                    $_isGlobalTier = true;
                    $_exactMatched = $topCats->getItemById($iCatId);
                    $catId = null;

                    if ($_exactMatched) {
                        // There is exact match
                        $catId = $iCatId;
                    } elseif ($subcatMatchFlag) {
                        // NO exact match
                        // but submach option is ON
                        $_catPath = explode(',', Mage::helper('udropship/catalog')->getPathInStore($iCat));
                        foreach ($_catPath as $_catPathId) {
                            if ($topCats->getItemById($_catPathId)) {
                                // pierwsza znaleziona subkategoria
                                $catId = $_catPathId;
                                $_subcatMatched = true;
                                break;
                            }
                        }
                    }
                    if ($catId && $topCats->getItemById($catId)) {
                        $_rateToUse = array();
                        if ($this->isProductHaveFlagSale($product->getId(), $productsRelations, $productsWithFlagSale)) {
                            // Have flag SALE
                            Mage::log($product->getId() . " | " . $product->getSku() . ' have flage SALE!', null, 'mylog.log');

                            if (isset($tierRates[$catId]) && !empty($tierRates[$catId]['sale_value'])) {
                                $_rateToUse['value'] = $tierRates[$catId]['sale_value'];
                                $_isGlobalTier = false;
                            } else {
                                if (isset($globalTierRates[$catId]) && !empty($globalTierRates[$catId]['sale_value'])) {
                                    $_rateToUse['value'] = $globalTierRates[$catId]['sale_value'];
                                }
                            }

                        } else {
                            // Product do not have flag SALE
                            if (isset($tierRates[$catId]) && !empty($tierRates[$catId]['value'])) {
                                $_rateToUse['value'] = $tierRates[$catId]['value'];
                                $_isGlobalTier = false;
                            } else {
                                if (isset($globalTierRates[$catId]) && !empty($globalTierRates[$catId]['value'])) {
                                    $_rateToUse['value'] = $globalTierRates[$catId]['value'];
                                }
                            }
                        }

                        if (!empty($_rateToUse['value'])
                            && (
                                !$_isGlobalTier && $isGlobalTier
                                || !$_isGlobalTier && ($_exactMatched || !$exactMatched)
                                || $_isGlobalTier && $isGlobalTier && ($_exactMatched || !$exactMatched)
                            )
                        ) {
                            $_rateToUse['value'] = $locale->getNumber($_rateToUse['value']);
                            $ratesToUse[$itemId] = $_rateToUse;
                        }
                    }
                    $exactMatched = $exactMatched || $_exactMatched;
                    $subcatMatched = $subcatMatched || $_subcatMatched;
                    $isGlobalTier = $isGlobalTier && $_isGlobalTier;
                }
            }

            // If no commission found for this item
            // then default will be used
            if (!isset($ratesToUse[$itemId])) {
                Mage::log('No commission found for this item: '.$product->getId() . " | " . $product->getSku(), null, 'mylog.log');
                // If 'Default Commission Percent' is set for vendor
                // use it; if not use default
                // that same logic for Default SALE commission Percent
                $vcp = $vendor->getCommissionPercent();
                if ($this->isProductHaveFlagSale($product->getId(), $productsRelations, $productsWithFlagSale)) {
                    if (!empty($vcp)) {
                        $ratesToUse[$itemId]['value'] = $locale->getNumber($vendor->getSaleCommissionPercent());
                    } else {
                        $ratesToUse[$itemId]['value'] = $locale->getNumber(Mage::getStoreConfig('udropship/tiercom/sale_commission_percent'));
                    }
                } else {
                    if (!empty($vcp)) {
                        $ratesToUse[$itemId]['value'] = $locale->getNumber($vendor->getCommissionPercent());
                    } else {
                        $ratesToUse[$itemId]['value'] = $locale->getNumber(Mage::getStoreConfig('udropship/tiercom/commission_percent'));
                    }
                }
            }

            if (isset($ratesToUse[$itemId])) {
                if (isset($ratesToUse[$itemId]['value'])) {
                    Mage::log("[{$item->getId()}] setCommissionPercent {$ratesToUse[$itemId]['value']} for: ". $product->getId() . " | " . $product->getSku(), null, 'mylog.log');
                    $item->setCommissionPercent($ratesToUse[$itemId]['value']);
//                    $item->save(); //only for tests
                }
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
     * For $productsRelations
     * @see Zolago_Catalog_Model_Resource_Product_Configurable->getConfigurableSimpleRelationArray
     *
     * For $productsWithFlagSale
     * @see Zolago_Campaign_Model_Resource_Campaign->getIsProductsInSaleOrPromotion
     *
     * @param $productId
     * @param $productsRelations
     * @param $productsWithFlagSale
     * @return bool
     */
    protected function isProductHaveFlagSale($productId, $productsRelations, $productsWithFlagSale) {
        // Validations
        if (empty($productId) || empty($productsRelations) || empty($productsWithFlagSale)) {
            return false;
        }

        // First easy check
        if (isset($productsWithFlagSale[$productId])) {
            return true;
        }

        // Getting parentId of simple product
        $parentId = null;
        foreach ($productsRelations as $k => $v) {
            foreach ($v as $simpleId) {
                if ($simpleId == $productId) {
                    $parentId = $k;
                }
            }
        }

        // Next easy check
        if (is_null($parentId)) {
            return false;
        }

        // Checking a parent od simple product
        if (isset($productsRelations[$parentId])) {
            return true;
        }

        return false;
    }
}
