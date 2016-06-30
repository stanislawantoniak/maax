<?php

/**
 * Class Zolago_Campaign_Model_Campaign_ProductAttribute
 */
class Zolago_Campaign_Model_Campaign_ProductAttribute extends Zolago_Campaign_Model_Campaign
{

    /**
     * Set prices from converter to simple products
     *
     * @param $salesPromoProductsData
     * @param $websiteId
     * @return array
     * @throws Mage_Core_Exception
     */
    public function setPromoCampaignAttributesToSimpleVisibleProducts($salesPromoProductsData, $websiteId)
    {

        /* @var $catalogHelper Zolago_Catalog_Helper_Data */
        $catalogHelper = Mage::helper('zolagocatalog');
        $stores = $catalogHelper->getStoresForWebsites($websiteId);
        $storesToUpdate = isset($stores[$websiteId]) ? $stores[$websiteId] : false;


        $productsIdsPullToSolr = array();

        $origStore = Mage::app()->getStore();

        $store = Mage::app()
            ->getWebsite($websiteId)
            ->getDefaultGroup()
            ->getDefaultStore();
        Mage::app()->setCurrentStore($store);

        $ids = array_keys($salesPromoProductsData);
        //1. Get collection of simple products
        /* @var $collectionS Mage_Catalog_Model_Resource_Product_Collection */
        $collectionS = Mage::getResourceModel('zolagocatalog/product_collection');
        $collectionS->addAttributeToSelect('skuv');
        $collectionS->addAttributeToSelect("udropship_vendor");
        $collectionS->addAttributeToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));
        $collectionS->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $collectionS->addAttributeToFilter('type_id', Mage_Catalog_Model_Product_Type::TYPE_SIMPLE);

        $collectionS->addFieldToFilter('entity_id', array('in' => $ids));

        if($collectionS->getSize() <= 0){
            Mage::app()->setCurrentStore($origStore);
            return $productsIdsPullToSolr; //Nothing to update
        }

        //2. Collect data before ask converter prices
        $converterBatchData = array();

        $priceTypes = $this->getOptionsData(Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_PRICE_TYPE_CODE);

        $vendorSkuvIdRelation = array();
        foreach ($collectionS as $_productS) {
            $productSId = $_productS->getId();
            $dataSimpleProduct = isset($salesPromoProductsData[$productSId]) ? $salesPromoProductsData[$productSId] : false;
            $priceSType = isset($priceTypes[$dataSimpleProduct['price_source']]) ? $priceTypes[$dataSimpleProduct['price_source']] : false;

            $converterBatchData[$_productS->getData('udropship_vendor')][$_productS->getData('skuv')] = $priceSType;
            $vendorSkuvIdRelation[$_productS->getData('udropship_vendor')][$_productS->getData('skuv')] = $productSId;
            unset($productSId);
            unset($priceSType);
        }
        unset($_productS);
        Mage::app()->setCurrentStore($origStore);

        //3. Request prices from converter
        $actualPricesForSimple = array();

        if (!empty($converterBatchData)) {
            foreach ($converterBatchData as $vendorExternalId => $vendorProductsData) {
                foreach ($vendorProductsData as $skuv => $converterPriceType) {
                    $actualPricesForSimple[$vendorExternalId][$skuv] = Mage::getResourceModel('catalog/product')->getAttributeRawValue($vendorSkuvIdRelation[$vendorExternalId][$skuv], 'external_price_' . $converterPriceType, 0);
                }
            }
        }

        if (empty($actualPricesForSimple)) {
            return $productsIdsPullToSolr;
        }

        //4. Collect product ids with actual prices
        $productIdsWithActualPrices = array();
        foreach ($actualPricesForSimple as $vendorId => $actualPricesForSimpleItem) {
            foreach ($actualPricesForSimpleItem as $skuOfVendor => $actualPriceForProduct) {
                if (isset($vendorSkuvIdRelation[$vendorId][$skuOfVendor])) {
                    $productIdWithActualPrice = $vendorSkuvIdRelation[$vendorId][$skuOfVendor];
                    $productIdsWithActualPrices[] = $productIdWithActualPrice;
                }
                unset($productIdWithActualPrice);
            }
        }


        /* @var $aM Zolago_Catalog_Model_Product_Action */
        $aM = Mage::getSingleton('catalog/product_action');
        $resourceModel = $this->getResource();


        foreach ($collectionS as $_productS) {
            $productSId = $_productS->getId();

            //No price => No update
            if (!in_array($productSId, $productIdsWithActualPrices)) {
                continue;
            }
            $udropshipVendor = $_productS->getData('udropship_vendor');
            $skuv = $_productS->getData('skuv');

            $dataSimpleProduct = isset($salesPromoProductsData[$productSId]) ? $salesPromoProductsData[$productSId] : false;

            if (!$dataSimpleProduct) {
                continue;
            }

            $productFlag = '';
            if ($dataSimpleProduct['campaign_type'] == Zolago_Campaign_Model_Campaign_Type::TYPE_PROMOTION) {
                $productFlag = Zolago_Catalog_Model_Product_Source_Flag::FLAG_PROMOTION;
            } elseif ($dataSimpleProduct['campaign_type'] == Zolago_Campaign_Model_Campaign_Type::TYPE_SALE) {
                $productFlag = Zolago_Catalog_Model_Product_Source_Flag::FLAG_SALE;
            }

            $priceSType = isset($priceTypes[$dataSimpleProduct['price_source']]) ? $priceTypes[$dataSimpleProduct['price_source']] : false;
            $priceSSimple = isset($dataSimpleProduct['price_percent']) ? $dataSimpleProduct['price_percent'] : 0;

            if (!$priceSType) {
                continue;
            }

            $newSimplePrice = isset($actualPricesForSimple[$udropshipVendor][$skuv]) ? $actualPricesForSimple[$udropshipVendor][$skuv] : 0;

            if (empty($newSimplePrice)) {
                continue;
            }
            $newSimplePricePriceWithPercent = $newSimplePrice - $newSimplePrice * ((int)$priceSSimple / 100);


            foreach ($storesToUpdate as $storeId) {
                $aM->updateAttributesPure(
                    array($productSId),
                    array(
                        'special_price' => $newSimplePricePriceWithPercent,

                        'campaign_strikeout_price_type' => $dataSimpleProduct['campaign_strikeout_price_type'],
                        'campaign_regular_id' => $dataSimpleProduct['campaign_id'],
                        'special_from_date' => !empty($dataSimpleProduct['date_from']) ? date('Y-m-d', strtotime($dataSimpleProduct['date_from'])) : '',
                        'special_to_date' => !empty($dataSimpleProduct['date_to']) ? date('Y-m-d', strtotime($dataSimpleProduct['date_to'])) : '',

                        'product_flag' => $productFlag
                    ),
                    $storeId
                );
            }

            //unset($storeId);
            $productsIdsPullToSolr[] = $productSId;
            $resourceModel->setCampaignProductAssignedToCampaignFlag(array($dataSimpleProduct['campaign_id']), $productSId);
        }
        //set null to attribute for default store id (required for good quote calculation)
        $aM->updateAttributesPure(
            array_keys($productsIdsPullToSolr),
            array(
                'special_price' => null,
                'campaign_regular_id' => null,
                'product_flag' => null,
                'campaign_strikeout_price_type' => null
            ),
            Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID
        );

        return array_unique($productsIdsPullToSolr);

    }


    /**
     * Set prices from converter to simple products
     *
     * @param $salesPromoProductsData
     * @param $websiteId
     * @return array
     */
    public function setPromoCampaignAttributesToConfigurableVisibleProducts($salesPromoProductsData, $websiteId)
    {

        $productsIdsPullToSolr = array();

        $origStore = Mage::app()->getStore();

        $ids = array_keys($salesPromoProductsData);

        $store = Mage::app()
            ->getWebsite($websiteId)
            ->getDefaultGroup()
            ->getDefaultStore();
        Mage::app()->setCurrentStore($store);

        //1. Get collection of configurable products
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getResourceModel('zolagocatalog/product_collection');
        $collection->addAttributeToSelect('skuv');
        $collection->addAttributeToSelect("udropship_vendor");
        $collection->addAttributeToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));
        $collection->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $collection->addAttributeToFilter('type_id', Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE);
        $collection->addFieldToFilter('entity_id', array('in' => $ids));

        if($collection->getSize() <= 0){
            Mage::app()->setCurrentStore($origStore);
            return $productsIdsPullToSolr; //Nothing to update
        }

        //2. Discover simple products attached to configurable
        //2.1. Collect data to $converterBatchData before ask converter prices
        $converterBatchData = array();
        $priceTypes = $this->getOptionsData(Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_PRICE_TYPE_CODE);
        /* @var $configModel  Zolago_Catalog_Model_Product_Type_Configurable */
        $configModel = Mage::getModel('zolagocatalog/product_type_configurable');

        $productsData = array(); //configurable options data
        $simpleUsed = array();
        $simpleUsed2 = array();
        $skuSizeRelation = array();

        $configurableProductIds = array();

        $childProductsByAttribute = $configModel->getUsedProductsByAttribute($ids);


        foreach ($collection as $_product) {
            $productId = $_product->getId();
            $campaignDataForConfigurableProduct = isset($salesPromoProductsData[$productId]) ? $salesPromoProductsData[$productId] : false;
            $priceType = isset($priceTypes[$campaignDataForConfigurableProduct['price_source']]) ? $priceTypes[$campaignDataForConfigurableProduct['price_source']] : false;

            //recalculate options and set special price

            $configurableProductIds[$productId] = $productId;

            //Product have no children
            if(!isset($childProductsByAttribute[$productId])){
                continue;
            }
            $childProducts = $childProductsByAttribute[$productId];

            foreach ($childProducts as $_child) {

                $productsData[$productId][$_child["id"]] = array(
                    'sku' => $_child["sku"],
                    'skuv' => $_child["skuv"],
                    'udropship_vendor' => $_product->getUdropshipVendor()
                );
                $skuSizeRelation[$productId][$_child["id"]] = $_child["size"];

               $converterBatchData[$_product->getData('udropship_vendor')][$_child["skuv"]] = $priceType;

                $simpleUsed[$_child["sku"]] = $_child["id"];
                $simpleUsed2[$_child["skuv"]] = $_child["id"];
            }
            unset($child);
        }

        Mage::app()->setCurrentStore($origStore);
        if (empty($converterBatchData)) {
            return $productsIdsPullToSolr;
        }


        //3. Get prices for related simple from converters
        $actualSpecialPricesForChildren = array();

        if (!empty($converterBatchData)) {
            foreach ($converterBatchData as $vendorExternalId => $vendorProductsData) {
                foreach($vendorProductsData as $skuv => $converterPriceType){
                    $actualSpecialPricesForChildren[$vendorExternalId][$skuv] = Mage::getResourceModel('catalog/product')->getAttributeRawValue($simpleUsed2[$skuv], 'external_price_'.$converterPriceType, 0);
                }
            }
        }

        //4. Collect product ids with actual prices
        $productIdsWithActualPrices = array();
        foreach ($actualSpecialPricesForChildren as $vendorId => $actualSpecialPricesForChildrenItem) {
            foreach ($actualSpecialPricesForChildrenItem as $skuOfVendor => $actualPriceForProduct) {
                if (isset($vendorSkuvIdRelation[$vendorId][$skuOfVendor])) {
                    $productIdWithActualPrice = $vendorSkuvIdRelation[$vendorId][$skuOfVendor];
                    $productIdsWithActualPrices[] = $productIdWithActualPrice;
                }
                unset($productIdWithActualPrice);
            }
        }

        //5. Implement percent to price from converters
        $finalSpecialPricesForChildren = array();
        foreach ($productsData as $parentProductId => $simpleProductsData) {
            $percent = $salesPromoProductsData[$parentProductId]['price_percent'];
            foreach ($simpleProductsData as $childProductId => $childData) {
                if (!isset($actualSpecialPricesForChildren[$childData['udropship_vendor']][$childData['skuv']])) {
                    continue;
                }
                $newPrice = $actualSpecialPricesForChildren[$childData['udropship_vendor']][$childData['skuv']];
                //if no price in converter do nothing
                if (empty($newPrice)) {
                    continue;
                }
                $newPriceWithPercent = $newPrice - $newPrice * ((int)$percent / 100);
                $finalSpecialPricesForChildren[$parentProductId][$childProductId] = $newPriceWithPercent;
            }
        }

        //5. Set Campaign attributes to configurable
        $productsIdsPullToSolr = $this->setSpecialPriceToConfigurableByChildren($salesPromoProductsData,$finalSpecialPricesForChildren, $skuSizeRelation, $websiteId);

        return $productsIdsPullToSolr;
    }


    /**
     * @param $salesPromoProductsData
     * @param $finalSpecialPricesForChildren
     * @param $storesToUpdate
     */
    public function setSpecialPriceToConfigurableByChildren($salesPromoProductsData,$finalSpecialPricesForChildren, $skuSizeRelation, $websiteId){
        $productsIdsPullToSolr = array();
        $configurableIds = array_keys($finalSpecialPricesForChildren);
        /* @var $catalogHelper Zolago_Catalog_Helper_Data */
        $catalogHelper = Mage::helper('zolagocatalog');
        $stores = $catalogHelper->getStoresForWebsites($websiteId);
        $storesToUpdate = isset($stores[$websiteId]) ? $stores[$websiteId] : false;

        $pricesData = array();

        foreach ($finalSpecialPricesForChildren as $parentProdId => $actualSpecialPrices) {
            $minPriceForProduct = min(array_values($actualSpecialPrices));

            $dataConfigurableProduct = isset($salesPromoProductsData[$parentProdId]) ? $salesPromoProductsData[$parentProdId] : false;
            if (!$dataConfigurableProduct) {
                continue;
            }

            $pricesData[$parentProdId]['special_price'] = $minPriceForProduct;
            foreach ($actualSpecialPrices as $childProdId => $childPrice) {
                $priceIncrement = (float)$childPrice - $minPriceForProduct;
                $pricesData[$parentProdId][$childProdId]['option_price_increment'] = $priceIncrement;
            }
            unset($childProdId);
            unset($childPrice);

            $productFlag = '';
            if ($dataConfigurableProduct['campaign_type'] == Zolago_Campaign_Model_Campaign_Type::TYPE_PROMOTION) {
                $productFlag = Zolago_Catalog_Model_Product_Source_Flag::FLAG_PROMOTION;
            } elseif ($dataConfigurableProduct['campaign_type'] == Zolago_Campaign_Model_Campaign_Type::TYPE_SALE) {
                $productFlag = Zolago_Catalog_Model_Product_Source_Flag::FLAG_SALE;
            }


            $campaignStrikeoutPriceType = $dataConfigurableProduct['campaign_strikeout_price_type'];
            $campaignRegularId = $dataConfigurableProduct['campaign_id'];
            $specialFromDate = !empty($dataConfigurableProduct['date_from']) ? date('Y-m-d', strtotime($dataConfigurableProduct['date_from'])) : '';
            $specialToDate = !empty($dataConfigurableProduct['date_to']) ? date('Y-m-d', strtotime($dataConfigurableProduct['date_to'])) : '';

            $dataToUpdate["special_price"][(string)$minPriceForProduct][] = $parentProdId;
            $dataToUpdate["campaign_strikeout_price_type"][$campaignStrikeoutPriceType][] = $parentProdId;
            $dataToUpdate["campaign_regular_id"][$campaignRegularId][] = $parentProdId;
            $dataToUpdate["special_from_date"][(string)$specialFromDate][] = $parentProdId;
            $dataToUpdate["special_to_date"][(string)$specialToDate][] = $parentProdId;
            $dataToUpdate["product_flag"][$productFlag][] = $parentProdId;

            /* @var $resourceModel Zolago_Campaign_Model_Resource_Campaign */
            $resourceModel = Mage::getResourceModel('zolagocampaign/campaign');

            $resourceModel->setCampaignProductAssignedToCampaignFlag(array($dataConfigurableProduct['campaign_id']), $parentProdId);
            $productsIdsPullToSolr[$parentProdId] = $parentProdId;

        }
        /* @var $aM Zolago_Catalog_Model_Product_Action */
        $aM = Mage::getSingleton('catalog/product_action');

        if(!empty($dataToUpdate)){
            foreach($dataToUpdate as $attributeName => $data){
                foreach($data as $value => $idsToUpdate){
                    foreach ($storesToUpdate as $storeId) {
                        $aM->updateAttributesPure($idsToUpdate,array($attributeName => $value),$storeId);
                    }
                }
            }
        }
        //set null to attribute for default store id (required for good quote calculation)
        $aM->updateAttributesPure(array_values($productsIdsPullToSolr),
            array(
                'special_price' => null,
                'campaign_regular_id' => null,
                'product_flag' => null,
                'campaign_strikeout_price_type' => null
            ),
            Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID
        );

        //6. Set configurable options based on simple prices

        $this->setOptionsBasedOnCampaign($pricesData,$skuSizeRelation, $websiteId);

        return $productsIdsPullToSolr;
    }

    /**
     *
     * @param $pricesData
     * @param $skuSizeRelation   $skuSizeRelation[parent_id][child_id] = child_size;
     * @param $websiteId
     * @throws Exception
     */
    public function setOptionsBasedOnCampaign($pricesData,$skuSizeRelation,$websiteId){

        $parentIds = array_keys($skuSizeRelation);
        /* @var $configResourceModel   Zolago_Catalog_Model_Resource_Product_Configurable */
        $configResourceModel = Mage::getResourceModel('zolagocatalog/product_configurable');
        $superAttributes = $configResourceModel->getSuperAttributes($parentIds);
        $optionsData = array();
        foreach ($skuSizeRelation as $parentProdId => $skuSizeRelations) {
            //Mage::log("Super attribute for product_id={$parentProdId}");
            if(!isset($superAttributes[$parentProdId]['super_attribute'])){
                Mage::log("No super attribute for product_id={$parentProdId}", null, "super_attribute_bad.log");
                continue;
            }

            $superAttributeId = $superAttributes[$parentProdId]['super_attribute'];
            foreach ($skuSizeRelations as $childProdId => $size) {
                $isPriceExistInConverter = isset($pricesData[$parentProdId][$childProdId]);
                if ($isPriceExistInConverter) {
                    $priceIncrement = $pricesData[$parentProdId][$childProdId]['option_price_increment'];
                    $optionsData[] = "({$superAttributeId},{$size},{$priceIncrement},{$websiteId})";
                }
            }

        }

        if(!empty($optionsData)){
            /* @var $campaignResourceModel   Zolago_Campaign_Model_Resource_Campaign */
            $campaignResourceModel = Mage::getResourceModel('zolagocampaign/campaign');
            $campaignResourceModel->insertOptionsBasedOnCampaign($optionsData);
        }
    }


    /**
     * Recover (set to null) attributes
     * campaign_regular_id,
     * special_price,special_from_date,special_to_date,
     * campaign_strikeout_price_type,
     * product_flag
     *
     * @param $dataToUpdate
     * @param $productsToDeleteFromTable
     * @return array
     */
    public function unsetPromoCampaignAttributesToVisibleProducts($dataToUpdate, $productsToDeleteFromTable = array()){

        $productIdsToUpdate = array();

        $updateCollector = array();

        foreach ($dataToUpdate as $websiteId => $dataToUpdateCampaigns) {
            foreach ($dataToUpdateCampaigns as $type => $campaignData) {
                //unset products campaign attributes
                foreach ($campaignData as $campaignId => $productIds) {
                    $productIdsToUpdate = array_merge($productIdsToUpdate, $productIds);
                    if ($type == Zolago_Campaign_Model_Campaign_Type::TYPE_PROMOTION || $type == Zolago_Campaign_Model_Campaign_Type::TYPE_SALE) {

                        if(!isset($updateCollector[$websiteId])){
                            $updateCollector[$websiteId] = array();
                        }
                        $updateCollector[$websiteId] = array_merge($updateCollector[$websiteId], $productIds);

                        if (isset($recoverOptionsProducts[$websiteId])) {
                            $recoverOptionsProducts[$websiteId] = array_merge($recoverOptionsProducts[$websiteId], $productIds);
                        } else {
                            $recoverOptionsProducts[$websiteId] = $productIds;
                        }
                        $setProductsAsAssigned[$campaignId] = $productIds;

                    }
                }
                unset($campaignId);
            }
        }

        unset($websiteId);
        if(!empty($updateCollector)){
            $websiteIdsToUpdate = array_keys($dataToUpdate);
            /* @var $zolagocatalogHelper Zolago_Catalog_Helper_Data */
            $zolagocatalogHelper = Mage::helper('zolagocatalog');
            $stores = $zolagocatalogHelper->getStoresForWebsites($websiteIdsToUpdate);

            /* @var $actionModel Zolago_Catalog_Model_Product_Action */
            $actionModel = Mage::getSingleton('catalog/product_action');

            //unset special price
            //unset special price dates
            //unset SRP price
            $attributesData = array(
                self::ZOLAGO_CAMPAIGN_ID_CODE => null,
                'special_price' => '',
                'special_from_date' => '',
                'special_to_date' => '',
                'campaign_strikeout_price_type' => '',
                'product_flag' => null
            );


            foreach($updateCollector as $websiteId => $productsIds){
                if(!isset($stores))
                    continue;

                foreach ($stores[$websiteId] as $storeId) {

                    $actionModel->updateAttributesPure($productsIds, $attributesData, $storeId);

                    $store = Mage::getModel("core/store")->load($storeId);
                    $col = Zolago_Turpentine_Model_Observer_Ban::collectProductsBeforeBan($productsIds, $store);
                    Mage::dispatchEvent("zolagocatalog_converter_stock_complete", array("products" => $col));
                }
            }
        }

        //3.2. Recover options for configurable products
        if (!empty($recoverOptionsProducts)) {
            //recover options
            /* @var $configurableRModel Zolago_Catalog_Model_Resource_Product_Configurable */
            $configurableRModel = Mage::getResourceModel('zolagocatalog/product_configurable');
            $configurableRModel->recoverProductPriceAndOptionsBasedOnSimples($recoverOptionsProducts);
        }

        //4.1 Delete products with status 2
        if (!empty($productsToDeleteFromTable)) {
            foreach ($productsToDeleteFromTable as $campaignId => $productIds) {
                $this->getResource()->deleteProductsFromTableMass($campaignId, $productIds);
            }
        }

        //4.2 Set products as processed
        if (!empty($setProductsAsAssigned)) {
            /* @var $resourceModel Zolago_Campaign_Model_Resource_Campaign */
            $resourceModel = $this->getResource();
            foreach ($setProductsAsAssigned as $campaignId => $productsAssignedIds) {
                $resourceModel->setProductsAsProcessedByCampaign($campaignId, $productsAssignedIds);
            }
        }
        return $productIdsToUpdate;
    }
}