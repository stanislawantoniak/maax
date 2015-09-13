<?php

/**
 * Class Zolago_Campaign_Model_Campaign
 * @method int getCampaignId()
 * @method int getVendorId()
 * @method int getStatus()
 * @method string getType()
 * @method int getPriceSourceId()
 * @method float getPriceSrp()
 * @method string getName()
 * @method int getPercent()
 * @method string getDateFrom()
 * @method string getDateTo()
 * @method string getCreatedAt()
 * @method string getUpdatedAt()
 * @method string getNameCustomer()
 * @method int getStrikeoutType()
 * @method int getLandingPageCategory()
 * @method int getIsLandingPage()
 * @method int getLandingPageContext()
 * @method int getContextVendorId()
 * @method int getCampaignUrl()
 * @method int getCouponImage()
 * @method int getCouponConditions()
 *
 * @method Zolago_Campaign_Model_Resource_Campaign getResource()
 */
class Zolago_Campaign_Model_Campaign extends Mage_Core_Model_Abstract
{
    const ZOLAGO_CAMPAIGN_ID_CODE = "campaign_regular_id";
    const ZOLAGO_CAMPAIGN_INFO_CODE = "campaign_info_id";
    const ZOLAGO_CAMPAIGN_STRIKEOUT_PRICE_TYPE_CODE = "campaign_strikeout_price_type";

    const ZOLAGO_CAMPAIGN_DISCOUNT_CODE = "percent";
    const ZOLAGO_CAMPAIGN_DISCOUNT_PRICE_SOURCE_CODE = "price_source_id";

    protected $vendor = null;
    protected $contextVendor = null;

    const LP_COUPON_IMAGE_FOLDER = "lp/coupon/image";
    const LP_COUPON_PDF_FOLDER = "lp/coupon/pdf";

    protected function _construct()
    {
        $this->_init("zolagocampaign/campaign");
    }

    /**
     * Vendor who create campaign
     *
     * @return Zolago_Dropship_Model_Vendor|null
     */
    public function getVendor() {
        if ($this->vendor === null) {
            $this->vendor = Mage::getModel("zolagodropship/vendor")->load($this->getVendorId());
        }
        return $this->vendor;
    }

    /**
     * Context vendor when campaign is landing page
     *
     * @return Zolago_Dropship_Model_Vendor|null
     */
    public function getContextVendor() {
        if ($this->contextVendor === null) {
            $this->contextVendor = Mage::getModel("zolagodropship/vendor")->load($this->getContextVendorId());
        }
        return $this->contextVendor;
    }

    /**
     * Return final campaign url
     *
     * @param null|string $customUrl
     * @return string
     * @throws Mage_Core_Exception
     */
    public function getFinalCampaignUrl($customUrl = null) {
        return $customUrl ? $this->getWebsiteUrl() . $customUrl :
            ($this->getLandingPageUrl() ? $this->getLandingPageUrl() : ($this->_getCampaignUrl() ? $this->_getCampaignUrl() : "") );
    }

    /**
     * Get url for website with vendor part (url_key)
     *
     * NOTE: Currently campaign can by assigned to one website
     * (From DB structure can by 1(campaign) to many(websites) but for now is 1 to 1)
     *
     * @param string $customVendorUrlPart
     * @return string
     * @throws Mage_Core_Exception
     */
    public function getWebsiteUrl($customVendorUrlPart = "") {
        $websiteIds = $this->getAllowedWebsites();
        if (empty($websiteIds)) {
//            throw new Mage_Core_Exception("Invalid campaign. No information about allowed websites");
            return null;
        } else {
            /** @var Mage_Core_Model_Website $website */
            $website = Mage::app()->getWebsite($websiteIds[0]); // Currently campaign can by assigned to one website
        }
        $websiteUrl = $website->getConfig("web/unsecure/base_url");

        $localVendorId = Mage::helper('udropship')->getLocalVendorId();
        $vendorUrlPart = $customVendorUrlPart;
        if ($localVendorId != $this->getVendorId() && empty($customVendorUrlPart)) {
            $vendorUrlPart = $this->getVendor()->getUrlKey() . "/";
        }
        return $websiteUrl . $vendorUrlPart;
    }

    /**
     * Get Campaign Website
     * @return mixed
     */
    public function getWebsite()
    {
        $websiteIds = $this->getAllowedWebsites();
        return $websiteIds[0];
    }

    /**
     * Generate campaign url
     *
     * @return string
     */
    private function _getCampaignUrl() {
        $rawCampaignUrl = $this->getData("campaign_url");
        return $this->getWebsiteUrl() . $rawCampaignUrl;
    }

    /**
     * Generate landing page url
     * If Campaign is not landing page return empty string
     * @return string
     */
    private function getLandingPageUrl() {
        $id = $this->getLandingPageCategory();
        $id = !empty($id) ? $id : 0;
        $vendorUrlKey = $this->getContextVendor()->getUrlKey();
        $isLP = (int)$this->getIsLandingPage();

        $cacheKey = "lp_url_".$vendorUrlKey."_category_". $id;
        if (!$this->getData($cacheKey)) {
            $url = "";
            if ($isLP) { // Is landing page

                /* @var $landingPageHelper Zolago_Campaign_Helper_LandingPage */
                $landingPageHelper = Mage::helper("zolagocampaign/landingPage");
                $url = $landingPageHelper->getLandingPageUrlByCampaign($this);
            }
            $this->setData($cacheKey, $url);
        }
        return $this->getData($cacheKey);
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
                /* @var $resource Zolago_Campaign_Model_Resource_Campaign */
                $resource = $this->getResource();
                $campaignProducts = $resource->getCampaignProducts($this);
            }
            $this->setData("campaign_products", implode("," , $campaignProducts));
        }
        return $this->getData("campaign_products");
    }

    /*
     * @return array
     */
    public function getProductCampaign() {
        /* @var $resourceModel Zolago_Campaign_Model_Resource_Campaign */
        $resourceModel = $this->getResource();
        return $resourceModel->getProductCampaign();
    }

    /*
     * @return array
     */
    public function getProductCampaignInfo() {
        return $this->getResource()->getProductCampaignInfo();
    }


    public function unsetCampaignAttributes(){
        $setProductsAsAssigned = array();
        //Select campaigns with expired date
        /* @var $resourceModel Zolago_Campaign_Model_Resource_Campaign */
        $resourceModel = $this->getResource();
        $notValidCampaigns = $resourceModel->getNotValidCampaigns();

        if(empty($notValidCampaigns)){
            return;
        }
        $vendorsInUpdate = array();

        $productsIds = array();
        foreach($notValidCampaigns as $notValidCampaignData){
            $productsIds[$notValidCampaignData['product_id']] = $notValidCampaignData['product_id'];
            $vendorsInUpdate[$notValidCampaignData['vendor_id']] = $notValidCampaignData['vendor_id'];
        }


        $isProductsInSaleOrPromotionByVendor = array();
        foreach ($vendorsInUpdate as $vendorId) {
            $isProductsInSaleOrPromotionByVendor[$vendorId] = $resourceModel->getIsProductsInSaleOrPromotion($productsIds, $vendorId);
        }


        $localeTime = Mage::getModel('core/date')->timestamp(time());
        $localeTimeF = date("Y-m-d H:i", $localeTime);
        $dataToUpdate = array();

        $websiteIdsToUpdate = array();
        $archiveCampaigns = array();
        $anotherCampaignProducts = array();
        foreach ($notValidCampaigns as $notValidCampaign) {
            $campaignExpired = !empty($notValidCampaign['date_to']) && $notValidCampaign['date_to'] <= $localeTimeF;
            if ($campaignExpired) {
                $archiveCampaigns[$notValidCampaign['campaign_id']] = $notValidCampaign['campaign_id'];
            }

            $websiteIdsToUpdate[$notValidCampaign['website_id']] = $notValidCampaign['website_id'];
            $productInValidCampaign = (isset($isProductsInSaleOrPromotionByVendor[$notValidCampaign['vendor_id']]) && in_array($notValidCampaign['product_id'], array_keys($isProductsInSaleOrPromotionByVendor[$notValidCampaign['vendor_id']]))) ? true : false;
            if($notValidCampaign['type'] == Zolago_Campaign_Model_Campaign_Type::TYPE_SALE || $notValidCampaign['type'] == Zolago_Campaign_Model_Campaign_Type::TYPE_PROMOTION){
                if($productInValidCampaign){
                    $anotherCampaignProducts[] = $notValidCampaign['product_id'];
                }

            }
            $dataToUpdate[$notValidCampaign['website_id']][$notValidCampaign['type']][$notValidCampaign['campaign_id']][] = $notValidCampaign['product_id'];

        }

        if (!empty($anotherCampaignProducts)) {
            $resourceModel->setRebuildProductInValidCampaign($anotherCampaignProducts);       
        }

        //Reformat by product_id
        $reformattedDataInfo = array();
        foreach($notValidCampaigns as $notValidCampaignsData){
            if ($notValidCampaignsData["type"] == Zolago_Campaign_Model_Campaign_Type::TYPE_INFO) {
                $reformattedDataInfo[$notValidCampaignsData["website_id"]][$notValidCampaignsData["product_id"]][] = $notValidCampaignsData["campaign_id"];
                $websitesToUpdateInfo[$notValidCampaignsData["website_id"]] = $notValidCampaignsData["website_id"];
            }

        }

        if (!empty($reformattedDataInfo)) {
            /* @var $actionModel Zolago_Catalog_Model_Product_Action */
            $actionModel = Mage::getSingleton('catalog/product_action');

            $productIdsToUpdate = array();

            /* @var $zolagocatalogHelper Zolago_Catalog_Helper_Data */
            $zolagocatalogHelper = Mage::helper('zolagocatalog');
            $stores = $zolagocatalogHelper->getStoresForWebsites($websiteIdsToUpdate);

            if(empty($stores)){
                return;
            }

            $recoverOptionsProducts = array();

            foreach ($reformattedDataInfo as $websiteId => $dataToUpdateInfo) {
                $storesI = isset($stores[$websiteId]) ? $stores[$websiteId] : false;
                if ($storesI) {
                    $productIdsInfoUpdated = $this->unsetInfoCampaignsToProduct($dataToUpdateInfo, $storesI);
                    $productIdsToUpdate = array_merge($productIdsToUpdate, $productIdsInfoUpdated);
                }
            }




            foreach($dataToUpdate as $websiteId => $dataToUpdateCampaigns){
                $storesOfWebsite = (isset($stores[$websiteId]) && !empty($stores[$websiteId])) ? $stores[$websiteId] : false;
                if(!$storesOfWebsite){
                    continue;
                }
                $infoUpdate = array();
                foreach ($dataToUpdateCampaigns as $type => $campaignData) {

                    //unset products campaign attributes
                    foreach ($campaignData as $campaignId => $productIds) {
                        $productIdsToUpdate = array_merge($productIdsToUpdate, $productIds);
                        if ($type == Zolago_Campaign_Model_Campaign_Type::TYPE_PROMOTION || $type == Zolago_Campaign_Model_Campaign_Type::TYPE_SALE) {
                            $attributesData = array(self::ZOLAGO_CAMPAIGN_ID_CODE => null);
                            foreach ($storesOfWebsite as $store) {
                                $actionModel
                                    ->updateAttributesPure($productIds, $attributesData, (int)$store);
                            }
                            unset($store);
                            if(isset($recoverOptionsProducts[$websiteId])){
                                $recoverOptionsProducts[$websiteId] = array_merge($recoverOptionsProducts[$websiteId],$productIds);
                            } else {
                                $recoverOptionsProducts[$websiteId] = $productIds;
                            }
                            $setProductsAsAssigned[$campaignId] = $productIds;

                        }
                    }
                    unset($campaignId);
                    unset($attributesData);

                    //unset special price
                    //unset special price dates
                    //unset SRP price
                    $attributesData = array(
                        'special_price' => '',
                        'special_from_date' => '',
                        'special_to_date' => '',
                        'campaign_strikeout_price_type' => '',
                        'product_flag' => null
                    );
                    foreach ($storesOfWebsite as $store) {
                        $actionModel
                            ->updateAttributesPure($productIdsToUpdate, $attributesData, (int)$store);
                    }
                    unset($store);
                }
            }

            if(!empty($setProductsAsAssigned)){
                /* @var $resourceModel Zolago_Campaign_Model_Resource_Campaign */
                $resourceModel = Mage::getResourceModel('zolagocampaign/campaign');
                foreach($setProductsAsAssigned as $campaignId => $productsAssignedIds){
                    $resourceModel->setProductsAsProcessedByCampaign($campaignId,$productsAssignedIds);
                }

            }

            //3. unset options

            if(!empty($recoverOptionsProducts)){
                //recover options
                /* @var $configurableRModel Zolago_Catalog_Model_Resource_Product_Configurable */
                $configurableRModel = Mage::getResourceModel('zolagocatalog/product_configurable');
                $configurableRModel->setProductOptionsBasedOnSimples($recoverOptionsProducts);
            }

            //4. reindex
            // Better performance
            $indexer = Mage::getResourceModel('catalog/product_indexer_eav_source');
            /* @var $indexer Mage_Catalog_Model_Resource_Product_Indexer_Eav_Source */
            $indexer->reindexEntities($productIdsToUpdate);

            $numberQ = 20;
            if (count($productIdsToUpdate) > $numberQ) {
                $productsToReindexC = array_chunk($productIdsToUpdate, $numberQ);
                foreach ($productsToReindexC as $productsToReindexCItem) {
                    Mage::getResourceModel('catalog/product_indexer_price')->reindexProductIds($productsToReindexCItem);

                }
                unset($productsToReindexCItem);
            } else {
                Mage::getResourceModel('catalog/product_indexer_price')->reindexProductIds($productIdsToUpdate);
            }
//
//
//            //5. push to solr
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
        $productsIdsPullToSolr = array();

        if (empty($salesPromoProductsData)) {
            return $productsIdsPullToSolr;
        }


        /* @var $resourceModel Zolago_Campaign_Model_Resource_Campaign */
        $resourceModel = Mage::getResourceModel('zolagocampaign/campaign');

        /* @var $catalogHelper Zolago_Catalog_Helper_Data */
        $catalogHelper = Mage::helper('zolagocatalog');
        $stores = $catalogHelper->getStoresForWebsites($websiteId);
        $storesToUpdate = isset($stores[$websiteId]) ? $stores[$websiteId] : false;

        if (!$storesToUpdate) {
            return $productsIdsPullToSolr;
        }


        $ids = array_keys($salesPromoProductsData);
        /* @var $aM Zolago_Catalog_Model_Product_Action */
        $aM = Mage::getSingleton('catalog/product_action');
        $priceTypes = $this->getOptionsData(Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_PRICE_TYPE_CODE);

        //Ping converter to get special price
        try {
            /* @var $converter Zolago_Converter_Model_Client */
            $converter = Mage::getModel('zolagoconverter/client');
        } catch (Exception $e) {
            Mage::throwException("Converter is unavailable: check credentials");
            return;
        }

        //1. simple
        $collectionS = Mage::getModel('catalog/product')->getCollection();
        $collectionS->addAttributeToSelect(array('skuv'));
        $collectionS
            ->addAttributeToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));

        $collectionS->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $collectionS->addAttributeToFilter('type_id', Mage_Catalog_Model_Product_Type::TYPE_SIMPLE);
        $collectionS
            ->addFieldToFilter('entity_id', array('in' => $ids));

        foreach ($collectionS as $_productS) {
            $productSId = $_productS->getId();
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

            $priceSSimple = isset($dataSimpleProduct['price_percent']) ? $dataSimpleProduct['price_percent'] : false;
            if ($priceSType && $priceSSimple) {
                $udropship_vendor = $_productS->getData('udropship_vendor');
                $skuv = $_productS->getData('skuv');
                $newSimplePrice = $converter->getPrice($udropship_vendor, $skuv, $priceSType);

                if (!empty($newSimplePrice)) {
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
                    unset($storeId);
                    //set null to attribute for default store id (required for good quote calculation)
                    $aM->updateAttributesPure(array($productSId), array('special_price' => null, 'campaign_regular_id' => null, 'product_flag' => null,'campaign_strikeout_price_type' => null), Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
                    $productsIdsPullToSolr[$productSId] = $productSId;
                    $resourceModel->setCampaignProductAssignedToCampaignFlag(array($dataSimpleProduct['campaign_id']), $productSId);

                }
            }

        }



        //2. get simple products related to configurable
        $attributeSize = Mage::getResourceModel('catalog/product')
            ->getAttribute('size');
        $attributeSizeId = $attributeSize->getAttributeId();

        $configurableProductIds = array();
        //2. get simple products related to configurable
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection
            ->addAttributeToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));

        $collection->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $collection->addAttributeToFilter('type_id', Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE);
        $collection
            ->addFieldToFilter('entity_id', array('in' => $ids));






        $productsData = array(); //configurable options data
        $skuSizeRelation = array();

        $simpleUsed = array();
        /* @var $configModel  Mage_Catalog_Model_Product_Type_Configurable */
        $configModel = Mage::getModel('catalog/product_type_configurable');

        foreach ($collection as $_product) {

            $productId = $_product->getId();

            //recalculate options and set special price

            $configurableProductIds[$productId] = $productId;


            $configurableOptions = $configModel->getConfigurableOptions($_product);

            if (isset($configurableOptions[$attributeSizeId])) {
                $configurableOptionsSize = $configurableOptions[$attributeSizeId];

                foreach ($configurableOptionsSize as $configurableSizeOption) {
                    $skuSizeRelation[$productId][(string)$configurableSizeOption['option_title']] = array('sku' => $configurableSizeOption['sku'], 'size' => $configurableSizeOption['option_title']);
                }
            }

            $childProducts = $configModel->getUsedProducts(null, $_product);

            foreach ($childProducts as $_child) {
                $childId = $_child->getId();
                $productsData[$productId][$childId] = array(
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
        if (empty($productsData)) {
            return;
        }


        $actualSpecialPricesForChildren = array();

        foreach ($productsData as $parentProductId => $simpleProductsData) {
            $priceType = $priceTypes[$salesPromoProductsData[$parentProductId]['price_source']];

            $percent = $salesPromoProductsData[$parentProductId]['price_percent'];

            foreach ($simpleProductsData as $childProductId => $childData) {
                $newPrice = $converter->getPrice($childData['udropship_vendor'], $childData['skuv'], $priceType);

                //if no price in converter do nothing
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

        if(empty($actualSpecialPricesForChildren)){
            return;
        }
        //3. calculate options to configurable
        $sizes = $this->getOptionsData('size', true);


        /* @var $configResourceModel   Zolago_Catalog_Model_Resource_Product_Configurable */
        $configResourceModel = Mage::getResourceModel('zolagocatalog/product_configurable');
        $superAttributes = $configResourceModel->getSuperAttributes($configurableProductIds);


        //4. set options to configurable
        if (empty($skuSizeRelation)) {
            return;
        }


        $pricesData = array();

        foreach ($actualSpecialPricesForChildren as $parentProdId => $actualSpecialPrices) {
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

            //set null to attribute for default store id (required for good quote calculation)
            $aM->updateAttributesPure(array($parentProdId),
                array(
                    'special_price' => null,
                    'campaign_regular_id' => null,
                    'product_flag' => null,
                    'campaign_strikeout_price_type' => null
                ),
                Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID
            );
            $resourceModel->setCampaignProductAssignedToCampaignFlag(array($dataConfigurableProduct['campaign_id']), $parentProdId);
            $productsIdsPullToSolr[$parentProdId] = $parentProdId;
            unset($childProdId);
            unset($priceIncrement);
            unset($parentProdId);
            unset($actualSpecialPrices);
        }
        unset($parentProdId);
        unset($actualSpecialPrices);


        $optionsData = array();

        foreach ($skuSizeRelation as $parentProdId => $skuSizeRelations) {
            $superAttributeId = $superAttributes[$parentProdId]['super_attribute'];
            foreach ($skuSizeRelations as $sizeLabel => $sizeLabelData) {
                $size = $sizes[$sizeLabel];
                $sizeLabelDataSku = $sizeLabelData['sku'];
                $childProdId = $simpleUsed[$sizeLabelDataSku];
                $isPriceExistInConverter = isset($pricesData[$parentProdId][$childProdId]);
                if ($isPriceExistInConverter) {
                    $priceIncrement = $pricesData[$parentProdId][$childProdId]['option_price_increment'];
                    $optionsData[] = "({$superAttributeId},{$size},{$priceIncrement},{$websiteId})";
                }
            }

        }

        //5. set special price to configurable
        if(!empty($optionsData)){
            /* @var $campaignResourceModel   Zolago_Campaign_Model_Resource_Campaign */
            $campaignResourceModel = Mage::getResourceModel('zolagocampaign/campaign');
            $campaignResourceModel->insertOptionsBasedOnCampaign($optionsData);
        }

        return $productsIdsPullToSolr;
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


    public function unsetProductAttributesOnProductRemoveFromCampaign($campaignId, $revertProductOptions)
    {
        if (empty($campaignId)) {
            return;
        }
        if (empty($revertProductOptions)) {
            return;
        }

        $campaign = $this->load($campaignId);
        $productIdsToUpdate = array();
        $websiteIdsToUpdate = array_keys($revertProductOptions);
        /* @var $zolagocatalogHelper Zolago_Catalog_Helper_Data */
        $zolagocatalogHelper = Mage::helper('zolagocatalog');
        $storesByWebsite = $zolagocatalogHelper->getStoresForWebsites($websiteIdsToUpdate);

        foreach ($revertProductOptions as $websiteId => $productIds) {

            $stores = isset($storesByWebsite[$websiteId]) ? $storesByWebsite[$websiteId] : false;
            if ($stores) {
                $this->setCampaignAttributesToProducts($campaignId, $campaign->getType(), $productIds, $stores);
            }
            $productIdsToUpdate = array_merge($productIdsToUpdate, $productIds);
        }

        if ($campaign->getType() == Zolago_Campaign_Model_Campaign_Type::TYPE_PROMOTION || $campaign->getType() == Zolago_Campaign_Model_Campaign_Type::TYPE_SALE) {
            /* @var $configurableRModel Zolago_Catalog_Model_Resource_Product_Configurable */
            $configurableRModel = Mage::getResourceModel('zolagocatalog/product_configurable');
            $configurableRModel->setProductOptionsBasedOnSimples($revertProductOptions);
        }

        //4. reindex
        $indexer = Mage::getResourceModel('catalog/product_indexer_eav_source');
        /* @var $indexer Mage_Catalog_Model_Resource_Product_Indexer_Eav_Source */
        $indexer->reindexEntities($productIdsToUpdate);

        $numberQ = 20;
        if (count($productIdsToUpdate) > $numberQ) {
            $productsToReindexC = array_chunk($productIdsToUpdate, $numberQ);
            foreach ($productsToReindexC as $productsToReindexCItem) {
                Mage::getResourceModel('catalog/product_indexer_price')->reindexProductIds($productsToReindexCItem);

            }
            unset($productsToReindexCItem);
        } else {
            Mage::getResourceModel('catalog/product_indexer_price')->reindexProductIds($productIdsToUpdate);

        }

        //5. push to solr
        Mage::dispatchEvent(
            "catalog_converter_price_update_after",
            array(
                "product_ids" => $productIdsToUpdate
            )
        );
    }

    public function setCampaignAttributesToProducts($campaignId, $type, $productIds, $stores)
    {

        /* @var $actionModel Zolago_Catalog_Model_Product_Action */
        $actionModel = Mage::getSingleton('catalog/product_action');
        if ($type == Zolago_Campaign_Model_Campaign_Type::TYPE_INFO) {

            foreach ($stores as $store) {
                foreach ($productIds as $productId) {

                    $val = Mage::getResourceModel('catalog/product')->getAttributeRawValue($productId, self::ZOLAGO_CAMPAIGN_INFO_CODE, $store);

                    if (!$val) {
                        continue;
                    }
                    $campaignIds = explode(",", $val);
                    $campaignIds = array_diff($campaignIds, array($campaignId));

                    $attributesData = array(self::ZOLAGO_CAMPAIGN_INFO_CODE => (!empty($campaignIds) ? implode(',',$campaignIds) : 0));
                    $actionModel
                        ->updateAttributesPure($productIds, $attributesData, (int)$store);

                }
                unset($productId);
            }
            unset($store);

        } elseif ($type == Zolago_Campaign_Model_Campaign_Type::TYPE_PROMOTION || $type == Zolago_Campaign_Model_Campaign_Type::TYPE_SALE) {
            $attributesData = array(self::ZOLAGO_CAMPAIGN_ID_CODE => 0);
            foreach ($stores as $store) {
                $actionModel
                    ->updateAttributesPure($productIds, $attributesData, (int)$store);
            }
            unset($store);
        }

        $attributesData = array(
            'special_price' => '',
            'special_from_date' => '',
            'special_to_date' => '',
            'campaign_strikeout_price_type' => '',
            'product_flag' => null
        );
        foreach ($stores as $store) {
            $actionModel
                ->updateAttributesPure($productIds, $attributesData, (int)$store);
        }
    }

    /** Remove invalid value from product info_campaign_id
     * @param $dataToUpdate
     * @param $stores
     * @return array
     */
    public function unsetInfoCampaignsToProduct($dataToUpdate, $stores)
    {
        $productIdsUpdated = array();
        if (empty($dataToUpdate)) {
            return $productIdsUpdated;
        }
        //var_dump($dataToUpdate);
        /* @var $aM Zolago_Catalog_Model_Product_Action */
        $aM = Mage::getSingleton('catalog/product_action');
        $toUpdate = array();
        //            $toUpdate HISTOGRAM
//            $toUpdate = array(
//                "store1" => array(
//                    "value" => array("product_id1","product_id2","product_id3")
//                )
//            );
        foreach ($dataToUpdate as $productId => $campaignIds) {
            sort($campaignIds);
            foreach ($stores as $store) {
                $val = Mage::getResourceModel('catalog/product')->getAttributeRawValue($productId, self::ZOLAGO_CAMPAIGN_INFO_CODE, $store);
                $campaignIdsAlready = explode(",", $val);
                sort($campaignIdsAlready);
                $campaignIdsAlready = array_diff($campaignIdsAlready, $campaignIds);

                $toUpdate[$store][implode(',',$campaignIdsAlready)][] = $productId;
            }
            unset($campaignIdsAlready);
        }

        if (empty($toUpdate)) {
            return $productIdsUpdated;
        }
        unset($store);
        unset($productIds);
        $productsAssignedToCampaign = array();

        foreach ($toUpdate as $store => $data) {

            foreach ($data as $value => $productIds) {
                $aM->updateAttributesPure($productIds, array(Zolago_Campaign_Model_Campaign::ZOLAGO_CAMPAIGN_INFO_CODE => (string)$value), $store);
                $productsAssignedToCampaign[$value][] = $productIds;
            }

            //set null to attribute for default store id (required for good quote calculation)
            $aM->updateAttributesPure($productIds, array(Zolago_Campaign_Model_Campaign::ZOLAGO_CAMPAIGN_INFO_CODE => null),
                Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID
            );
            $productIdsUpdated = array_merge($productIdsUpdated, $productIds);
        }
        unset($productIds);
        //setProductsAsProcessedByCampaign
        if(!empty($productsAssignedToCampaign)){
            foreach($productsAssignedToCampaign as $campaignIdsString => $productIds){
                foreach(explode(",", $campaignIdsString) as $campaignId){
                    Mage::log($campaignId, null, "setProductsAsProcessedByCampaign1.log");
                    Mage::log($productIds, null, "setProductsAsProcessedByCampaign1.log");
                    $this->getResource()->setProductsAsProcessed($campaignId, $productIds);
                }
            }
        }
        return $productIdsUpdated;
    }
    /**
     * @param $dataToUpdate
     * @param $stores
     * @return array
     */
    public function setInfoCampaignsToProduct($dataToUpdate, $stores)
    {
        $productIdsUpdated = array();

        //Prepare data array for update
        if (!empty($dataToUpdate)) {
            /* @var $aM Zolago_Catalog_Model_Product_Action */
            $aM = Mage::getSingleton('catalog/product_action');
            $toUpdate = array();
//            $toUpdate HISTOGRAM
//            $toUpdate = array(
//                "store1" => array(
//                    "value" => array("product_id1","product_id2","product_id3")
//                )
//            );
            foreach ($dataToUpdate as $productId => $campaignIds) {
                sort($campaignIds);
                foreach($stores as $store){
                        $toUpdate[$store][implode(",", $campaignIds)][] = $productId;
                }
            }
        }


        if (empty($toUpdate)) {
            return $productIdsUpdated;
        }
        unset($store);
        unset($productIds);

        $productsAssignedToCampaign = array();


        foreach ($toUpdate as $store => $data) {

            foreach ($data as $value => $productIds) {
                $aM->updateAttributesPure($productIds, array(Zolago_Campaign_Model_Campaign::ZOLAGO_CAMPAIGN_INFO_CODE => (string)$value), $store);
                $productsAssignedToCampaign[$value][] = $productIds;
            }

            //set null to attribute for default store id (required for good quote calculation)
            $aM->updateAttributesPure($productIds, array(Zolago_Campaign_Model_Campaign::ZOLAGO_CAMPAIGN_INFO_CODE => null),
                Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID
            );
            $productIdsUpdated = array_merge($productIdsUpdated, $productIds);
        }
        unset($productIds);
        //setProductsAsProcessedByCampaign
        if(!empty($productsAssignedToCampaign)){
            foreach($productsAssignedToCampaign as $campaignIdsString => $productIds){
                foreach(explode(",", $campaignIdsString) as $campaignId){
                    $this->getResource()->setProductsAsProcessedByCampaign($campaignId, $productIds);
                }
            }
        }

        return $productIdsUpdated;
    }


    /**
     * @param $dataToUpdate
     * @param $stores
     * @return array
     */
    public function setSalesPromoCampaignsToProduct($dataToUpdate, $stores)
    {
        //set to product assigned_to_campaign = 1
        /* @var $resourceModel Zolago_Campaign_Model_Resource_Campaign */
        $resourceModel = Mage::getResourceModel('zolagocampaign/campaign');


        $productIdsUpdated = array();
        if (!empty($dataToUpdate)) {
            /* @var $aM Zolago_Catalog_Model_Product_Action */
            $aM = Mage::getSingleton('catalog/product_action');
            foreach ($dataToUpdate as $productId => $data) {

                $attributesData = array(
                    'campaign_strikeout_price_type' => $data['strikeout_type'],
                    'campaign_regular_id' => $data['campaign_id'],
                    'special_from_date' => !empty($data['date_from']) ? date('Y-m-d', strtotime($data['date_from'])) : '',
                    'special_to_date' => !empty($data['date_to']) ? date('Y-m-d', strtotime($data['date_to'])) : ''
                );

                foreach ($stores as $store) {
                    $aM->updateAttributesPure(array($productId), $attributesData, $store);
                }

                //set null to attribute for default store id (required for good quote calculation)
                $aM->updateAttributesPure(array($productId), array('special_price' => null,'campaign_regular_id' => null,'campaign_strikeout_price_type' => null), Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);

                $productIdsUpdated[$productId] = $productId;

                $resourceModel->setCampaignProductAssignedToCampaignFlag(array($data['campaign_id']), $productId);
            }
        }

        return $productIdsUpdated;
    }
    
    /**
     * returns filter key for campaign
     *
     * @return string
     */
     public function getCampaignFilterKey() {
         switch ($this->getData('type')) {             
             case Zolago_Campaign_Model_Campaign_Type::TYPE_INFO:
                 return self::ZOLAGO_CAMPAIGN_INFO_CODE;
             default:
                 return self::ZOLAGO_CAMPAIGN_ID_CODE;
         }
     }

}