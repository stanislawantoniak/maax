<?php

/**
 * Class Zolago_Campaign_Model_Campaign_ProductAttribute
 */
class Zolago_Campaign_Model_Campaign_ProductAttribute extends Zolago_Campaign_Model_Campaign
{


    /**
     * Initialize converter cliend
     *
     * @return void|Zolago_Converter_Model_Client
     * @throws Mage_Core_Exception
     */
    protected function initConverter()
    {
        //Ping converter to get special price
        try {
            /* @var $converter Zolago_Converter_Model_Client */
            $converter = Mage::getModel('zolagoconverter/client');
            return $converter;
        } catch (Exception $e) {
            Mage::throwException("Converter is unavailable: check credentials");
            return;
        }
    }


    /**
     * Set prices from converter to simple products
     *
     * @param $salesPromoProductsData
     * @param $ids
     * @param $storesToUpdate
     * @return array
     */
    public function setPromoCampaignAttributesToSimpleVisibleProducts($salesPromoProductsData, $storesToUpdate)
    {
        $productsIdsPullToSolr = array();

        $converter = $this->initConverter();
        if (!$converter) {
            return $productsIdsPullToSolr; //Nothing updated
        }
        $ids = array_keys($salesPromoProductsData);
        //1. Get collection of simple products
        /* @var $collectionS Mage_Catalog_Model_Resource_Product_Collection */
        $collectionS = Mage::getResourceModel('zolagocatalog/product_collection');
        $collectionS->addAttributeToSelect('skuv');
        $collectionS->addAttributeToSelect("udropship_vendor");
        $collectionS->addAttributeToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));
        $collectionS->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $collectionS->addAttributeToFilter('type_id', Mage_Catalog_Model_Product_Type::TYPE_SIMPLE);
        //$fakeProducts = array(1846, 1847,1848,1849,     2288,2290,2291);
        //$ids = array_merge($ids,$fakeProducts);
        $collectionS->addFieldToFilter('entity_id', array('in' => $ids));

        if($collectionS->getSize() <= 0){
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


        //$converterBatchData[4] = array("32251-33X-L" => "A","32251-33X-M" => "A","32251-33X-S" => "A", "32251-33X-XL" => "A");
        //$converterBatchData[5] = array("1045-CZARNY-70C" => "A","1045-CZARNY-70E" => "A","1045-CZARNY-75B" => "A");


        //3. Request prices from converter
        $actualPricesForSimple = array();
        if (!empty($converterBatchData)) {
            foreach ($converterBatchData as $vendorExternalId => $vendorProductsData) {
                $actualPricesForSimple = $actualPricesForSimple + $converter->getPriceBatch($vendorExternalId, $vendorProductsData);
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
     * @param $ids
     * @param $storesToUpdate
     * @return array
     */
    public function setPromoCampaignAttributesToConfigurableVisibleProducts($salesPromoProductsData, $websiteId)
    {

        $productsIdsPullToSolr = array();

        $converter = $this->initConverter();

        if (!$converter) {
            return $productsIdsPullToSolr; //Nothing to update
        }
        $ids = array_keys($salesPromoProductsData);
        //krumo($salesPromoProductsData);

        //1. Get collection of configurable products
        /* @var $collectionS Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getResourceModel('zolagocatalog/product_collection');
        $collection->addAttributeToSelect('skuv');
        $collection->addAttributeToSelect("udropship_vendor");
        $collection->addAttributeToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));
        $collection->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $collection->addAttributeToFilter('type_id', Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE);
        $collection->addFieldToFilter('entity_id', array('in' => $ids));

        if($collection->getSize() <= 0){
            return $productsIdsPullToSolr; //Nothing to update
        }

        $attributeSize = Mage::getResourceModel('catalog/product')
            ->getAttribute('size');
        $attributeSizeId = $attributeSize->getAttributeId();

        //2. Discover simple products attached to configurable
        //2.1. Collect data to $converterBatchData before ask converter prices
        $converterBatchData = array();
        $priceTypes = $this->getOptionsData(Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_PRICE_TYPE_CODE);
        /* @var $configModel  Zolago_Catalog_Model_Product_Type_Configurable */
        $configModel = Mage::getModel('zolagocatalog/product_type_configurable');

        $productsData = array(); //configurable options data
        $simpleUsed = array();
        $skuSizeRelation = array();

        $configurableProductIds = array();

        $childProductsByAttribute = $configModel->getUsedProductsByAttribute($attributeSizeId, $ids);

        //die("test9");
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

            //die("test9");
            foreach ($childProducts as $_child) {
                //krumo($_child->getData());
                $productsData[$productId][$_child["id"]] = array(
                    'sku' => $_child["sku"],
                    'skuv' => $_child["skuv"],
                    'udropship_vendor' => $_product->getUdropshipVendor()
                );
                $skuSizeRelation[$productId][$_child["id"]] = $_child["size"];

                $converterBatchData[$_product->getData('udropship_vendor')][$_child["skuv"]] = $priceType;

                $simpleUsed[$_child["sku"]] = $_child["id"];
            }
            unset($child);
        }

        //die("XXX");
        if (empty($converterBatchData)) {
            return $productsIdsPullToSolr;
        }
        //3. Get prices for related simple from converters
        $actualSpecialPricesForChildren = array();
        if (!empty($converterBatchData)) {
            foreach ($converterBatchData as $vendorExternalId => $vendorProductsData) {
                $actualSpecialPricesForChildren +=  $converter->getPriceBatch($vendorExternalId, $vendorProductsData);
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

        die("test6.1");

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
        //krumo($finalSpecialPricesForChildren);
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
            /* @var $aM Zolago_Catalog_Model_Product_Action */
            $aM = Mage::getSingleton('catalog/product_action');

            foreach ($storesToUpdate as $storeId) {
                $aM->updateAttributesPure(
                    array($parentProdId),
                    array(
                        'special_price' => $minPriceForProduct,

                        'campaign_strikeout_price_type' => $dataConfigurableProduct['campaign_strikeout_price_type'],
                        'campaign_regular_id' => $dataConfigurableProduct['campaign_id'],
                        'special_from_date' => !empty($dataConfigurableProduct['date_from']) ? date('Y-m-d', strtotime($dataConfigurableProduct['date_from'])) : '',
                        'special_to_date' => !empty($dataConfigurableProduct['date_to']) ? date('Y-m-d', strtotime($dataConfigurableProduct['date_to'])) : '',

                        'product_flag' => $productFlag
                    ),
                    $storeId
                );
            }


            /* @var $resourceModel Zolago_Campaign_Model_Resource_Campaign */
            $resourceModel = Mage::getResourceModel('zolagocampaign/campaign');
            //TODO uncomment after test
            //$resourceModel->setCampaignProductAssignedToCampaignFlag(array($dataConfigurableProduct['campaign_id']), $parentProdId);
            $productsIdsPullToSolr[$parentProdId] = $parentProdId;

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
        }
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

        foreach ($skuSizeRelation as $parentProdId => $skuSizeRelations) {
            Mage::log("Super attribute for product_id={$parentProdId}");
            if(!isset($superAttributes[$parentProdId]['super_attribute'])){
                Mage::log("No super attribute for product_id={$parentProdId}");
                continue;
            }
            Mage::log("Super attribute for product_id={$parentProdId}");

            $superAttributeId = $superAttributes[$parentProdId]['super_attribute'];
            foreach ($skuSizeRelations as $childProdId => $size) {
                $isPriceExistInConverter = isset($pricesData[$parentProdId][$childProdId]);
                if ($isPriceExistInConverter) {
                    $priceIncrement = $pricesData[$parentProdId][$childProdId]['option_price_increment'];
                    $optionsData[] = "({$superAttributeId},{$size},{$priceIncrement},{$websiteId})";
                }
            }
            Mage::log($optionsData);
            Mage::log("---------------------");

        }

        if(!empty($optionsData)){
            /* @var $campaignResourceModel   Zolago_Campaign_Model_Resource_Campaign */
            $campaignResourceModel = Mage::getResourceModel('zolagocampaign/campaign');
            $campaignResourceModel->insertOptionsBasedOnCampaign($optionsData);
        }
    }
}