<?php

class Zolago_Campaign_Model_Campaign extends Mage_Core_Model_Abstract
{
    const ZOLAGO_CAMPAIGN_ID_CODE = "campaign_regular_id";
    const ZOLAGO_CAMPAIGN_INFO_CODE = "campaign_info_id";
    const ZOLAGO_CAMPAIGN_STRIKEOUT_PRICE_TYPE_CODE = "campaign_strikeout_price_type";

    protected function _construct()
    {
        $this->_init("zolagocampaign/campaign");
    }

    /**
     * @param array $data
     * @return boolean|array
     */
    public function validate($data = null)
    {
        if ($data === null) {
            $data = $this->getData();
        } elseif ($data instanceof Varien_Object) {
            $data = $data->getData();
        }

        if (!is_array($data)) {
            return false;
        }

        $errors = Mage::getSingleton("zolagocampaign/campaign_validator")->validate($data);

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }
    /**
     * @return array
     */
    public function getAllowedWebsites() {
        if(!$this->hasData("website_ids")){
            $allowedWebsites = array();
            if($this->getId()){
                $allowedWebsites = $this->getResource()->getAllowedWebsites($this);
            }
            $this->setData("website_ids", $allowedWebsites);
        }
        return $this->getData("website_ids");
    }

    /**
     * @return array
     */
    public function getCampaignProducts() {
        if(!$this->hasData("campaign_products")){
            $campaignProducts = array();
            if($this->getId()){
                $campaignProducts = $this->getResource()->getCampaignProducts($this);
            }
            $this->setData("campaign_products", implode("," , $campaignProducts));
        }
        return $this->getData("campaign_products");
    }

    /*
     * @return array
     */
    public function getProductCampaign() {
        return $this->getResource()->getProductCampaign();
    }

    /*
     * @return array
     */
    public function getProductCampaignInfo() {
        return $this->getResource()->getProductCampaignInfo();
    }


    public function processCampaignAttributes(){
        //Select campaigns with expired date
        /* @var $resourceModel Zolago_Campaign_Model_Resource_Campaign */
        $resourceModel = $this->getResource();
        $notValidCampaigns = $resourceModel->getNotValidCampaigns();

        if(empty($notValidCampaigns)){
            return;
        }

        $localeTime = Mage::getModel('core/date')->timestamp(time());
        $localeTimeF = date("Y-m-d H:i", $localeTime);
        $dataToUpdate = array();

        foreach ($notValidCampaigns as $notValidCampaign) {
            if (!empty($notValidCampaign['date_to'])
                && $notValidCampaign['date_to'] <= $localeTimeF
            ) {
                $archiveCampaigns[$notValidCampaign['campaign_id']] = $notValidCampaign['campaign_id'];
            }
            $dataToUpdate[$notValidCampaign['type']][$notValidCampaign['campaign_id']][] = $notValidCampaign['product_id'];
        }

        //When ending date comes Campaign status goes to archive
        $collection = Mage::getModel("zolagocampaign/campaign")
            ->getCollection();
        $collection->addFieldToFilter('campaign_id', array('in', $archiveCampaigns));

        foreach ($collection as $collectionItem) {
            $collectionItem->setData('status', Zolago_Campaign_Model_Campaign_Status::TYPE_ARCHIVE);
            $collectionItem->save();
        }

        if (!empty($dataToUpdate)) {
            $actionModel = Mage::getSingleton('catalog/product_action');

//            $storeId = array(Mage_Core_Model_App::ADMIN_STORE_ID);
            $storeId = array();
            $allStores = Mage::app()->getStores();
            foreach ($allStores as $_eachStoreId => $val) {
                $_storeId = Mage::app()->getStore($_eachStoreId)->getId();
                $storeId[] = $_storeId;
            }
            $productIdsToUpdate = array();
            foreach ($dataToUpdate as $type => $campaignData) {
                //unset products campaign attributes
                foreach ($campaignData as $campaignId => $productIds) {
                    $productIdsToUpdate = array_merge($productIdsToUpdate, $productIds);
                    if ($type == Zolago_Campaign_Model_Campaign_Type::TYPE_INFO) {
                        foreach ($storeId as $store) {
                            foreach ($productIds as $productId) {
                                $val = Mage::getResourceModel('catalog/product')->getAttributeRawValue($productId, self::ZOLAGO_CAMPAIGN_INFO_CODE, $store);
                                $campaignIds = explode(",", $val);
                                $campaignIds = array_diff($campaignIds, array($campaignId));
                                if (!empty($campaignIds)) {
                                    $attributesData = array(self::ZOLAGO_CAMPAIGN_INFO_CODE => $campaignIds);
                                    $actionModel
                                        ->updateAttributesNoIndex($productIds, $attributesData, (int)$store);
                                }
                            }
                        }

                    } elseif ($type == Zolago_Campaign_Model_Campaign_Type::TYPE_PROMOTION || $type == Zolago_Campaign_Model_Campaign_Type::TYPE_SALE) {
                        $attributesData = array(self::ZOLAGO_CAMPAIGN_ID_CODE => 0);
                        foreach ($storeId as $store) {
                            $actionModel
                                ->updateAttributesNoIndex($productIds, $attributesData, (int)$store);
                        }
                    }
                }
                unset($attributesData);

                //unset special price
                //unset special price dates
                //unset SRP price
                $attributesData = array('special_price' => '', 'special_from_date' => '', 'special_to_date' => '', 'campaign_strikeout_price_type' =>'');
                foreach ($storeId as $store) {
                    $actionModel
                        ->updateAttributesNoIndex($productIdsToUpdate, $attributesData, (int)$store);
                }
            }

            //3. reindex
            $actionModel->reindexAfterMassAttributeChange();

            //4. push to solr
            Mage::dispatchEvent(
                "catalog_converter_price_update_after",
                array(
                    "product_ids" => $productIdsToUpdate
                )
            );
        }

    }


    /**
     * $salesPromoProductsData - array('configurable_product_id' => array('price_source' => price_source, 'price_percent' => price_percent))
     * @param $salesPromoProductsData
     */
    public function setProductOptionsByCampaign($salesPromoProductsData, $websiteId)
    {
        if (empty($salesPromoProductsData)) {
            return;
        }
        $stores = array();
        $allStores = Mage::app()->getStores();
        foreach ($allStores as $_eachStoreId => $val) {
            $_storeId = Mage::app()->getStore($_eachStoreId)->getId();
            $stores[] = $_storeId;
        }
        $productsData = array();

        $attributeSize = Mage::getResourceModel('catalog/product')
            ->getAttribute('size');
        $attributeSizeId= $attributeSize->getAttributeId();

        $ids = array_keys($salesPromoProductsData);
        $configurableProductIds = array();
        //1. get simple products related to configurable
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->addAttributeToFilter('visibility', array('neq' => 1));
        $collection->addAttributeToFilter('type_id', Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE);
        $collection->addFieldToFilter('entity_id', array('in' => $ids));

        $skuSizeRelation = array();

        $simpleUsed = array();
        foreach ($collection as $_product) {
            $parentId = $_product->getId();
            $configurableProductIds[$parentId] = $parentId;

            /* @var $configModel  Mage_Catalog_Model_Product_Type_Configurable */
            $configModel = Mage::getModel('catalog/product_type_configurable');
            $configurableOptions = $configModel->getConfigurableOptions($_product);

            if(isset($configurableOptions[$attributeSizeId])){
                $configurableOptionsSize = $configurableOptions[$attributeSizeId];
                foreach($configurableOptionsSize as $configurableSizeOption){
                    $skuSizeRelation[$parentId][(string)$configurableSizeOption['option_title']]= array('sku' => $configurableSizeOption['sku'],'size' => $configurableSizeOption['option_title']);
                }
            }

            $childProducts = $configModel->getUsedProducts(null, $_product);

            foreach ($childProducts as $_child) {
                $childId = $_child->getId();
                $productsData[$parentId][$childId] = array(
                    'sku' => $_child->getSku(),
                    'skuv' => $_child->getSkuv(),
                    'udropship_vendor' => $_child->getUdropshipVendor()
                );
                $simpleUsed[(string)$_child->getSku()] = $childId;
            }
            unset($child);
            unset($childId);
        }

        unset($product);
        unset($parentId);
        unset($childProducts);


        //2. get prices for simple from converters
        if(empty($productsData)){
            return;
        }
        //Ping converter to get special price
        try {
            /* @var $converter Zolago_Converter_Model_Client */
            $converter = Mage::getModel('zolagoconverter/client');
        } catch (Exception $e) {
            Mage::throwException("Converter is unavailable: check credentials");
            return;
        }
        $priceTypes = $this->getOptionsData(Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_PRICE_TYPE_CODE);

        $actualSpecialPricesForChildren = array();
        foreach ($productsData as $parentProductId => $simpleProductsData) {
            $priceType = $priceTypes[$salesPromoProductsData[$parentProductId]['price_source']];
            $percent = $salesPromoProductsData[$parentProductId]['price_percent'];

            foreach ($simpleProductsData as $childProductId => $childData) {
                $newPrice = $converter->getPrice($childData['udropship_vendor'], $childData['skuv'], $priceType);
                if (!empty($newPrice)) {
                    $newPriceWithPercent = $newPrice - $newPrice * ((int)$percent / 100);
                    $actualSpecialPricesForChildren[$parentProductId][$childProductId] = $newPriceWithPercent;
                }
            }

            unset($childProductId);
            unset($childData);
            unset($newPrice);
            unset($newPriceWithPercent);
        }
        unset($simpleProductsData);
        unset($parentProductId);
        unset($priceType);
        unset($percent);

        //3. calculate options to configurable
        $sizes = $this->getOptionsData('size', true);


        /* @var $configResourceModel   Zolago_Catalog_Model_Resource_Product_Configurable    */
        $configResourceModel = Mage::getResourceModel('zolagocatalog/product_configurable');
        $superAttributes = $configResourceModel->getSuperAttributes($configurableProductIds);


        //4. set options to configurable
        if(empty($skuSizeRelation)){
            return;
        }


        $pricesData = array();
        foreach ($actualSpecialPricesForChildren as $parentProdId => $actualSpecialPrices) {
            $minPriceForProduct = min(array_values($actualSpecialPrices));

            $pricesData[$parentProdId]['special_price'] = $minPriceForProduct;
            foreach ($actualSpecialPrices as $childProdId => $childPrice) {
                $priceIncrement = (float)$childPrice - $minPriceForProduct;
                $pricesData[$parentProdId][$childProdId]['option_price_increment'] = $priceIncrement;


            }

            foreach ($stores as $storeId) {
                Mage::getSingleton('catalog/product_action')->updateAttributesNoIndex(
                    array($parentProdId), array('special_price' => $minPriceForProduct), $storeId
                );
            }

            unset($childProdId);
            unset($priceIncrement);
            unset($parentProdId);
            unset($actualSpecialPrices);
        }
        unset($parentProdId);
        unset($actualSpecialPrices);


        $optionsData = array();
        $optionsArray = array();
        foreach ($skuSizeRelation as $parentProdId => $skuSizeRelations) {
            $superAttributeId = $superAttributes[$parentProdId]['super_attribute'];
            foreach ($skuSizeRelations as $sizeLabel => $sizeLabelData) {
                $size = $sizes[$sizeLabel];
                $sizeLabelDataSku = $sizeLabelData['sku'];
                $childProdId = $simpleUsed[$sizeLabelDataSku];

                $priceIncrement = $pricesData[$parentProdId][$childProdId]['option_price_increment'];
                $optionsData[] = "({$superAttributeId},{$size},{$priceIncrement},{$websiteId})";
                //product_super_attribute_id,value_index,pricing_value,website_id
//                $optionsArray[] = array(
//                    'product_super_attribute_id' => $superAttributeId,
//                    'value_index' => $size,
//                    'pricing_value' => $priceIncrement,
//                    'website_id' => $websiteId
//                );
            }

        }


        //5. set special price to configurable
        /* @var $campaignResourceModel   Zolago_Campaign_Model_Resource_Campaign */
        $campaignResourceModel = Mage::getResourceModel('zolagocampaign/campaign');
        $campaignResourceModel->insertOptionsBasedOnCampaign($optionsData);
    }


    /**
     * @return array
     */
    public function getOptionsData($attributeCode, $byLabel = false)
    {
        $attribute = Mage::getResourceModel('catalog/product')
            ->getAttribute($attributeCode);

        $options = Mage::getResourceModel('eav/entity_attribute_option_collection');
        $values = $options->setAttributeFilter($attribute->getId())->setStoreFilter(0)->toOptionArray();

        $result = array();
        if ($byLabel) {
            foreach ($values as $value) {
                $result[$value['label']] = $value['value'];
            }
        } else {
            foreach ($values as $value) {
                $result[$value['value']] = $value['label'];
            }
        }

        return $result;
    }

    public function getExpiredCampaigns()
    {
        return $this->getResource()->getExpiredCampaigns();
    }
}