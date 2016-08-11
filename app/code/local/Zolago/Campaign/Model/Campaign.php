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
 * @method string getActiveFilterLabel()
 * @method string getBannerTextInfo()
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
    public function getWebsiteUrl($customVendorUrlPart = "")
    {
        $websiteIds = $this->getAllowedWebsites();
        if (empty($websiteIds)) {
            return null;
        } else {
            /** @var Mage_Core_Model_Website $website */
            // Currently campaign can by assigned to one website
            $website = Mage::app()->getWebsite($websiteIds[0]);
        }
        $websiteUrl = $website->getConfig("web/unsecure/base_url");

        $localVendorId = Mage::helper('udropship')->getLocalVendorId();
        $vendorUrlPart = $customVendorUrlPart;

        if (
            $localVendorId != $this->getVendorId()  //NOT local vendor
            && empty($customVendorUrlPart)
            && !$website->getHaveSpecificDomain()   //NOT Sklep Wlasny
        ) {
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
        return (trim($rawCampaignUrl) == '#') ? '' : $this->getWebsiteUrl() . $rawCampaignUrl;
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

    protected function clearInfoProductAttributes($saleCampaignIds, $storeId){

        $origStore = Mage::app()->getStore();

        $store = Mage::getModel("core/store")->load($storeId);
        Mage::app()->setCurrentStore($store);

        $productsCollections = Mage::getResourceModel("catalog/product_collection");
        $productsCollections->joinAttribute("campaign_info_id", 'catalog_product/campaign_info_id', 'entity_id', null, 'left', $storeId);

        // Try use regexp to match vales with boundary (like comma, ^, $)  - (123,456,678)
        foreach($saleCampaignIds as $saleCampaignId){
            $productsCollections->getSelect()->orHaving(
                "campaign_info_id "." REGEXP ?", "[[:<:]]".$saleCampaignId."[[:>:]]"
            );
        }
        //$productsCollections->getSelect()->__toString()
        $productIds = array();
        foreach ($productsCollections as $_product) {
            $productIds[$storeId] = $_product->getId();
        }
        Mage::app()->setCurrentStore($origStore);
        return $productIds;

    }

    protected function clearSaleProductAttributes($infoCampaignIds, $storeId){

        $origStore = Mage::app()->getStore();

        $store = Mage::getModel('core/store')->load($storeId);
        Mage::app()->setCurrentStore($store);

        $productsCollections = Mage::getResourceModel("catalog/product_collection");
        $productsCollections->joinAttribute("campaign_regular_id", 'catalog_product/campaign_regular_id', 'entity_id', null, 'left', $storeId);
        $productsCollections->addFieldToFilter("campaign_regular_id", array("in" => $infoCampaignIds));

        $productsIds = array();
        foreach ($productsCollections as $_product) {
            $productsIds[] = $_product->getId();
        }

        Mage::app()->setCurrentStore($origStore);

        if(!empty($productsIds)){
            /* @var $actionModel Zolago_Catalog_Model_Product_Action */
            $actionModel = Mage::getSingleton('catalog/product_action');

            $attributesData = array(
                self::ZOLAGO_CAMPAIGN_ID_CODE => null,
                'special_price' => '',
                'special_from_date' => '',
                'special_to_date' => '',
                'campaign_strikeout_price_type' => '',
                'product_flag' => null
            );
            $actionModel->updateAttributesPure($productsIds, $attributesData, $storeId);
        }

        return $productsIds;

    }

    /**
     * Handle change website on campaign
     */
    protected function handleChangeWebsite()
    {
        $productIdsToUpdate = array();
        $ids1 = $this->handleChangeWebsiteOnInfoCampaign();
        $productIdsToUpdate = array_merge($productIdsToUpdate, $ids1);

        $ids2 = $this->handleChangeWebsiteOnSaleCampaign();
        $productIdsToUpdate = array_merge($productIdsToUpdate, $ids2);

        return $productIdsToUpdate;
    }

    /**
     * Handle change  campaign type
     */
    protected function handleChangeType()
    {
        $productIdsToUpdate = array();
        $ids1 = $this->handleChangeCampaignFromSaleToInfo();
        $productIdsToUpdate = array_merge($productIdsToUpdate, $ids1);

        $ids2 = $this->handleChangeCampaignFromInfoToSale();
        $productIdsToUpdate = array_merge($productIdsToUpdate, $ids2);

        return $productIdsToUpdate;
    }

    protected function handleChangeWebsiteOnSaleCampaign()
    {

        $productIdsUpdated = array();

        $origStore = Mage::app()->getStore();

        $allWebsites = Mage::app()->getWebsites();

        $toRestore = array();
        foreach ($allWebsites as $_website) {
            $websiteId = $_website->getId();

            $saleCampaigns = Mage::getModel("zolagocampaign/campaign")->getCollection();
            $saleCampaigns->addFieldToFilter(
                "type", array("in" =>
                    array(
                        Zolago_Campaign_Model_Campaign_Type::TYPE_SALE,
                        Zolago_Campaign_Model_Campaign_Type::TYPE_PROMOTION
                    )
                )
            );
            $saleCampaigns->addFieldToFilter("website_id", array("nin" => $websiteId));
            $saleCampaigns->getSelect()->join(
                'zolago_campaign_website',
                'zolago_campaign_website.campaign_id = main_table.campaign_id',
                array()
            );
            $saleCampaignIds = $saleCampaigns->getAllIds();

            if (empty($saleCampaignIds))
                continue;

            $storeIds = $_website->getStoreIds();

            foreach ($storeIds as $storeId) {

                $store = Mage::app()
                    ->getWebsite($_website)
                    ->getDefaultGroup()
                    ->getDefaultStore();
                Mage::app()->setCurrentStore($store);

                $productsCollections = Mage::getResourceModel("catalog/product_collection");
                $productsCollections->joinAttribute(
                    "campaign_regular_id",
                    'catalog_product/campaign_regular_id',
                    'entity_id',
                    null,
                    'left',
                    $storeId);
                $productsCollections->addAttributeToFilter("campaign_regular_id", array("neq" => 'NULL'));
                $productsCollections->addAttributeToFilter("campaign_regular_id", array("in" => $saleCampaignIds));


                if ($productsCollections->count() > 0){
                    $toRestore[$storeId] = $productsCollections->getAllIds();
                }


                Mage::app()->setCurrentStore($origStore);
            }

        }

        if (empty($toRestore))
            return $productIdsUpdated;

        /* @var $actionModel Zolago_Catalog_Model_Product_Action */
        $actionModel = Mage::getSingleton('catalog/product_action');

        foreach ($toRestore as $storeId => $productsIds) {
            $attributesData = array(
                self::ZOLAGO_CAMPAIGN_ID_CODE => null,
                'special_price' => '',
                'special_from_date' => '',
                'special_to_date' => '',
                'campaign_strikeout_price_type' => '',
                'product_flag' => null
            );
            $actionModel->updateAttributesPure($productsIds, $attributesData, $storeId);
            $productIdsUpdated = array_merge($productIdsUpdated, $productsIds);

            $store = Mage::getModel("core/store")->load($storeId);
            $col = Zolago_Turpentine_Model_Observer_Ban::collectProductsBeforeBan($productsIds, $store);
            Mage::dispatchEvent("zolagocatalog_converter_stock_complete", array("products" => $col));
        }


        return $productIdsUpdated;
    }

    protected function handleChangeWebsiteOnInfoCampaign()
    {
        $origStore = Mage::app()->getStore();

        $productIdsUpdated = array();

        $allWebsites = Mage::app()->getWebsites();

        $toRestore = array();
        foreach ($allWebsites as $_website) {
            $websiteId = $_website->getId();


            $infoCampaigns = Mage::getModel("zolagocampaign/campaign")->getCollection();
            $infoCampaigns->addFieldToFilter("type", Zolago_Campaign_Model_Campaign_Type::TYPE_INFO);
            $infoCampaigns->addFieldToFilter("website_id", array("nin" => $websiteId));
            $infoCampaigns->getSelect()->join(
                'zolago_campaign_website',
                'zolago_campaign_website.campaign_id = main_table.campaign_id',
                array()
            );
            $infoCampaignIds = $infoCampaigns->getAllIds();
            if (empty($infoCampaignIds))
                continue;



            $storeIds = $_website->getStoreIds();

            foreach ($storeIds as $storeId) {

                $store = Mage::app()
                    ->getWebsite($_website)
                    ->getDefaultGroup()
                    ->getDefaultStore();
                Mage::app()->setCurrentStore($store);

                $productsCollections = Mage::getResourceModel("catalog/product_collection");
                $productsCollections->joinAttribute(
                    "campaign_info_id",
                    'catalog_product/campaign_info_id',
                    'entity_id',
                    null,
                    'left',
                    $storeId);
                $productsCollections->addAttributeToFilter("campaign_info_id", array("neq" => 'NULL'));

                foreach ($productsCollections as $productsCollectionItem) {
                    $campaign_info_id = $productsCollectionItem->getData("campaign_info_id");
                    $intersect = array_intersect(explode(",", $campaign_info_id), $infoCampaignIds);

                    $toRestore[$storeId][$productsCollectionItem->getId()] = $intersect;
                }

                Mage::app()->setCurrentStore($origStore);
            }


        }

        unset($storeId);

        if (empty($toRestore))
            return $productIdsUpdated;

        foreach ($toRestore as $storeId => $data) {
            foreach ($data as $productId => $campaignIds) {

                $val = Mage::getResourceModel('catalog/product')->getAttributeRawValue($productId, self::ZOLAGO_CAMPAIGN_INFO_CODE, $storeId);
                $campaignIdsAlready = explode(",", $val);

                sort($campaignIdsAlready);
                $campaignIdsAlready = array_diff($campaignIdsAlready, $campaignIds);

                $toUpdate[$storeId][implode(',', $campaignIdsAlready)][] = $productId;

            }
        }


        if (empty($toUpdate))
            return $productIdsUpdated;


        $updatedIds = array();
        /* @var $aM Zolago_Catalog_Model_Product_Action */
        $aM = Mage::getSingleton('catalog/product_action');

        unset($store);
        unset($productIds);
        foreach ($toUpdate as $store => $data) {

            foreach ($data as $value => $productIds) {
                $aM->updateAttributesPure($productIds, array(Zolago_Campaign_Model_Campaign::ZOLAGO_CAMPAIGN_INFO_CODE => (string)$value), $store);
                $updatedIds = array_merge($updatedIds, $productIds);
            }
        }
        unset($productIds);

        //set null to attribute for default store id (required for good quote calculation)
        $aM->updateAttributesPure($updatedIds, array(Zolago_Campaign_Model_Campaign::ZOLAGO_CAMPAIGN_INFO_CODE => null),
            Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID
        );
        $productIdsUpdated = array_merge($productIdsUpdated, $updatedIds);

        return $productIdsUpdated;

    }
    /**
     * Clear SALE and PROMO product attributes
     * if campaign type was changed to INFO
     *
     * @return array
     */
    protected function handleChangeCampaignFromSaleToInfo()
    {
        $productIdsToUpdate = array();

        $infoCampaigns = Mage::getModel("zolagocampaign/campaign")->getCollection();
        $infoCampaigns->addFieldToFilter("type", Zolago_Campaign_Model_Campaign_Type::TYPE_INFO);
        $infoCampaignIds = $infoCampaigns->getAllIds();

        if(empty($infoCampaignIds))
            return $productIdsToUpdate;


        $stores = Mage::app()->getStores();


        foreach ($stores as $store) {
            $productIdsSalePromotionUpdated = $this->clearSaleProductAttributes($infoCampaignIds, $store->getId());
            $productIdsToUpdate = array_merge($productIdsToUpdate, $productIdsSalePromotionUpdated);


            $col = Zolago_Turpentine_Model_Observer_Ban::collectProductsBeforeBan($productIdsSalePromotionUpdated, $store);
            Mage::dispatchEvent("zolagocatalog_converter_stock_complete", array("products" => $col));
        }
        return $productIdsToUpdate;

    }


    /**
     * Clear INFO product attributes
     * if campaign type was changed to SALE or PROMO
     *
     * @return array
     */
    protected function handleChangeCampaignFromInfoToSale()
    {
        $result = array();
        $products = array();

        $saleCampaigns = Mage::getModel("zolagocampaign/campaign")->getCollection();
        $saleCampaigns->addFieldToFilter("type", array("in" => array(Zolago_Campaign_Model_Campaign_Type::TYPE_PROMOTION, Zolago_Campaign_Model_Campaign_Type::TYPE_SALE)));
        $saleCampaignIds = $saleCampaigns->getAllIds();

        if (empty($saleCampaignIds))
            return $result;


        $stores = Mage::app()->getStores();

        foreach ($stores as $store) {
            $productIdsInfoUpdated = $this->clearInfoProductAttributes($saleCampaignIds, $store->getId());

            $col = Zolago_Turpentine_Model_Observer_Ban::collectProductsBeforeBan($productIdsInfoUpdated, $store);
            Mage::dispatchEvent("zolagocatalog_converter_stock_complete", array("products" => $col));

            $products = array_merge($products, $productIdsInfoUpdated);
        }
        if (empty($products))
            return $result;

        $recoverInfoProducts = array("products" => $products, "campaigns" => $saleCampaignIds);

        if (!empty($recoverInfoProducts))
            return $this->removeSaleCampaignFromInfo($recoverInfoProducts);
    }

    /**
     * Recover product attributes if product in not valid campaign
     * @throws Exception
     */
    public function unsetCampaignAttributes(){

        $productIdsToUpdate = array();

        $productChangeWebsiteCleared = $this->handleChangeWebsite();
        $productIdsToUpdate = array_merge($productIdsToUpdate, $productChangeWebsiteCleared);

        $productChangeCampaignTypeCleared = $this->handleChangeType();
        $productIdsToUpdate = array_merge($productIdsToUpdate, $productChangeCampaignTypeCleared);


        //Select campaigns with expired date
        /* @var $resourceModel Zolago_Campaign_Model_Resource_Campaign */
        $resourceModel = $this->getResource();
        $notValidCampaignsData = $resourceModel->getNotValidCampaignProducts(); //products need to be updated

        if(empty($notValidCampaignsData))
            return;


        $vendorsInUpdate = array();
        $productsIds = array();
        $productsToDeleteFromTable = array(); //Zolago_Campaign_Model_Resource_Campaign::CAMPAIGN_PRODUCTS_TO_DELETE

        /**
         * Collect data:
         * a) $productsIds - product ids that should be recalculated because of they included to not valid campaign(s)
         *
         *
         */
        foreach($notValidCampaignsData as $notValidCampaign){
            $productsIds[$notValidCampaign['product_id']] = $notValidCampaign['product_id'];
            unset($notValidCampaign);
        }

        $notValidCampaigns = $resourceModel->getNotValidCampaignInfoPerProduct($productsIds);

        /**
         * Collect data:
         * b) $vendorsInUpdate - vendor ids  to detect if product in SALE or PROMOTION
         * c) $productsToDeleteFromTable products with status Zolago_Campaign_Model_Resource_Campaign::CAMPAIGN_PRODUCTS_TO_DELETE to delete them physically
         * from table after attributes will be reverted
         *
         */
        foreach($notValidCampaigns as $notValidCampaignsItem){
            $productsIds[$notValidCampaignsItem['product_id']] = $notValidCampaignsItem['product_id'];
            $vendorsInUpdate[$notValidCampaignsItem['vendor_id']] = $notValidCampaignsItem['vendor_id'];
            if($notValidCampaignsItem["assigned_to_campaign"] == Zolago_Campaign_Model_Resource_Campaign::CAMPAIGN_PRODUCTS_TO_DELETE){
                $productsToDeleteFromTable[$notValidCampaignsItem["campaign_id"]][] = $notValidCampaignsItem['product_id'];
            }
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

        //Reformat by product_id for INFO campaigns
        $reformattedDataInfo = array();
        foreach($notValidCampaigns as $notValidCampaignsData){
            if ($notValidCampaignsData["type"] == Zolago_Campaign_Model_Campaign_Type::TYPE_INFO) {
                $reformattedDataInfo[$notValidCampaignsData["website_id"]][$notValidCampaignsData["product_id"]][] = $notValidCampaignsData["campaign_id"];
                $websitesToUpdateInfo[$notValidCampaignsData["website_id"]] = $notValidCampaignsData["website_id"];
            }
        }



        //2. Recover products from INFO campaigns
        if (!empty($reformattedDataInfo)) {

            /* @var $zolagocatalogHelper Zolago_Catalog_Helper_Data */
            $zolagocatalogHelper = Mage::helper('zolagocatalog');
            $stores = $zolagocatalogHelper->getStoresForWebsites($websiteIdsToUpdate);

            if(empty($stores))
                return;

            //Recover campaign_info_id attribute
            foreach ($reformattedDataInfo as $websiteId => $dataToUpdateInfo) {
                $storesI = isset($stores[$websiteId]) ? $stores[$websiteId] : false;
                if ($storesI) {
                    $productIdsInfoUpdated = $this->recoverInfoCampaignsToProduct($dataToUpdateInfo, $storesI, $productsToDeleteFromTable);
                    $productIdsToUpdate = array_merge($productIdsToUpdate, $productIdsInfoUpdated);
                }
            }
        }


        //3. Recover products from SALE and PROMOTION campaigns

        //3.1. Recover (set to null) attributes campaign_regular_id, special_price,special_from_date,special_to_date,campaign_strikeout_price_type,product_flag
        // and recover options for configurable
        /* @var $productAttributeCampaignModel Zolago_Campaign_Model_Campaign_ProductAttribute */
        $productAttributeCampaignModel = Mage::getModel("zolagocampaign/campaign_productAttribute");
        $productIdsSalePromotionUpdated = $productAttributeCampaignModel->unsetPromoCampaignAttributesToVisibleProducts($dataToUpdate, $productsToDeleteFromTable);
        $productIdsToUpdate = array_merge($productIdsToUpdate, $productIdsSalePromotionUpdated);

        //3.2. set SALE/PROMO FLAG
        /* @var $zolagoCatalogProductConfigurableModel Zolago_Catalog_Model_Resource_Product_Configurable */
        $zolagoCatalogProductConfigurableModel = Mage::getResourceModel('zolagocatalog/product_configurable');
        $zolagoCatalogProductConfigurableModel->updateSalePromoFlag($productIdsToUpdate);

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


        //5. push to solr
        Mage::dispatchEvent(
            "catalog_converter_price_update_after",
            array(
                "product_ids" => $productIdsToUpdate
            )
        );


    }

    /**
     * $salesPromoProductsData - array('configurable_product_id' => array('price_source' => price_source, 'price_percent' => price_percent))
     *
     * @param $salesPromoProductsData
     * @param $websiteId
     * @return array
     * @throws Exception
     * @throws Mage_Core_Exception
     */
    public function setProductOptionsByCampaign($salesPromoProductsData, $websiteId)
    {
        $productsIdsPullToSolr = array();

        if (empty($salesPromoProductsData)) {
            return $productsIdsPullToSolr;
        }

        /* @var $catalogHelper Zolago_Catalog_Helper_Data */
        $catalogHelper = Mage::helper('zolagocatalog');
        $stores = $catalogHelper->getStoresForWebsites($websiteId);
        $storesToUpdate = isset($stores[$websiteId]) ? $stores[$websiteId] : false;

        if (!$storesToUpdate) {
            return $productsIdsPullToSolr;
        }

        $productAttributeCampaignModel = Mage::getModel("zolagocampaign/campaign_productAttribute");
        //1. Update attributes for simple visible products
        $simpleUpdated = $productAttributeCampaignModel->setPromoCampaignAttributesToSimpleVisibleProducts($salesPromoProductsData, $websiteId);
        $productsIdsPullToSolr = array_merge($productsIdsPullToSolr,$simpleUpdated);

        //1. Update attributes for configurable visible products
        $configurableUpdated = $productAttributeCampaignModel->setPromoCampaignAttributesToConfigurableVisibleProducts($salesPromoProductsData,$websiteId);
        $productsIdsPullToSolr = array_merge($productsIdsPullToSolr,$configurableUpdated);

        return $productsIdsPullToSolr;
    }


    /**
     * @param $attributeCode
     * @param bool|false $byLabel
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

    public function removeSaleCampaignFromInfo($recoverInfoProducts){


        $productIdsUpdated = array();

        $products = isset($recoverInfoProducts["products"]) ? $recoverInfoProducts["products"]: array();
        $campaignIds = isset($recoverInfoProducts["campaigns"]) ? $recoverInfoProducts["campaigns"] : array();
        if(empty($campaignIds) || empty($products)){
            $productIdsUpdated;
        }
        $toUpdate = array();
        foreach($products as $storeId => $productId){
            $val = Mage::getResourceModel('catalog/product')->getAttributeRawValue($productId, self::ZOLAGO_CAMPAIGN_INFO_CODE, $storeId);
            $campaignIdsAlready = explode(",", $val);


            sort($campaignIdsAlready);
            $campaignIdsAlready = array_diff($campaignIdsAlready, $campaignIds);

            $toUpdate[$storeId][implode(',', $campaignIdsAlready)][] = $productId;
        }



        if (empty($toUpdate)) {
            return $productIdsUpdated;
        }

        /* @var $aM Zolago_Catalog_Model_Product_Action */
        $aM = Mage::getSingleton('catalog/product_action');

        unset($store);
        unset($productIds);

        $updatedIds = array();

        foreach ($toUpdate as $store => $data) {

            foreach ($data as $value => $productIds) {
                $aM->updateAttributesPure($productIds, array(Zolago_Campaign_Model_Campaign::ZOLAGO_CAMPAIGN_INFO_CODE => (string)$value), $store);
                $updatedIds = array_merge($updatedIds, $productIds);
            }

            $col = Zolago_Turpentine_Model_Observer_Ban::collectProductsBeforeBan($updatedIds, $store);
            Mage::dispatchEvent("zolagocatalog_converter_stock_complete", array("products" => $col));
        }
        unset($productIds);

        //set null to attribute for default store id (required for good quote calculation)
        $aM->updateAttributesPure($updatedIds, array(Zolago_Campaign_Model_Campaign::ZOLAGO_CAMPAIGN_INFO_CODE => null),
            Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID
        );
        $productIdsUpdated = array_merge($productIdsUpdated, $updatedIds);

        return $productIdsUpdated;
    }

    /**
     * Remove invalid value from product info_campaign_id:
     *  Details: as far as product can be assigned to several info campaigns,
     * then we need
     * a) to get product info_campaign_id value (string)(campaign_id1, campaign_id2, ....) using Mage::getResourceModel('catalog/product')->getAttributeRawValue($productId, self::ZOLAGO_CAMPAIGN_INFO_CODE, $store)
     * b) remove invalid campaign ids from value
     * c) set to product a list of valid campaigns or NULL if there is no valid campaigns for this product
     * @param $dataToUpdate
     * @param $stores
     * @param $productsToDeleteFromTable
     * @return array
     */
    public function recoverInfoCampaignsToProduct($dataToUpdate, $stores, $productsToDeleteFromTable = array())
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
        $productsAssignedToCampaign = array();
        foreach ($dataToUpdate as $productId => $campaignIds) {
            sort($campaignIds);
            foreach ($stores as $store) {

                $val = Mage::getResourceModel('catalog/product')->getAttributeRawValue($productId, self::ZOLAGO_CAMPAIGN_INFO_CODE, $store);
                $campaignIdsAlready = explode(",", $val);

                sort($campaignIdsAlready);
                $campaignIdsAlready = array_diff($campaignIdsAlready, $campaignIds);


                $toUpdate[$store][implode(',', $campaignIdsAlready)][] = $productId;


                $productsAssignedToCampaign[implode(",", $campaignIds)][$productId] = $productId;

            }
            unset($campaignIdsAlready);
        }

        if (empty($toUpdate)) {
            return $productIdsUpdated;
        }
        unset($store);
        unset($productIds);

        $updatedIds = array();

        foreach ($toUpdate as $store => $data) {

            foreach ($data as $value => $productIds) {
                $aM->updateAttributesPure($productIds, array(Zolago_Campaign_Model_Campaign::ZOLAGO_CAMPAIGN_INFO_CODE => (string)$value), $store);
                $updatedIds = array_merge($updatedIds, $productIds);
            }
        }
        unset($productIds);


        //set null to attribute for default store id (required for good quote calculation)
        $aM->updateAttributesPure($updatedIds, array(Zolago_Campaign_Model_Campaign::ZOLAGO_CAMPAIGN_INFO_CODE => null),
            Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID
        );
        $productIdsUpdated = array_merge($productIdsUpdated, $updatedIds);


        //Delete products with status 2
        if (!empty($productsToDeleteFromTable)) {
            foreach ($productsToDeleteFromTable as $campaignId => $productIds) {
                $this->getResource()->deleteProductsFromTableMass($campaignId, $productIds);
            }
        }

        //Set products as processed
        if (!empty($productsAssignedToCampaign)) {
            foreach ($productsAssignedToCampaign as $campaignIdsString => $productIds) {
                foreach (explode(",", $campaignIdsString) as $campaignId) {
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


        $updatedIds = array();

        foreach ($toUpdate as $store => $data) {
            foreach ($data as $value => $productIds) {
                $aM->updateAttributesPure($productIds, array(Zolago_Campaign_Model_Campaign::ZOLAGO_CAMPAIGN_INFO_CODE => (string)$value), $store);
                $productsAssignedToCampaign[$value][] = $productIds;
                $updatedIds = array_merge($updatedIds, $productIds);
            }
        }

        //set null to attribute for default store id (required for good quote calculation)
        $aM->updateAttributesPure($updatedIds, array(Zolago_Campaign_Model_Campaign::ZOLAGO_CAMPAIGN_INFO_CODE => null),
            Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID
        );
        $productIdsUpdated = array_merge($productIdsUpdated, $updatedIds);

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