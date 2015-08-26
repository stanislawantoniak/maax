<?php

class Zolago_Campaign_Helper_LandingPage extends Mage_Core_Helper_Abstract
{

    /**
     * Get vendor data by vendor_id
     * @param $vendorId
     * @return bool|Unirgy_Dropship_Model_Vendor|Zolago_Dropship_Model_Vendor
     */
    protected function _getVendorData($vendorId)
    {
        if (empty($vendorId)) {
            return false;
        }
        $vendor = Mage::getModel("udropship/vendor")->load($vendorId);
        return $vendor;
    }

    public function getCampaignLandingPageBanner()
    {
        $images = new stdClass();

        $campaign = $this->getCampaign();
        $campaignId = $campaign->getId();

        if (!$campaignId) {
            return $images;
        }

        return $this->getCampaignLandingPageBannerByCampaign($campaign);
    }

    /**
     *
     * @param Zolago_Campaign_Model_Campaign $campaign
     * @return
     */

    public function getCampaignLandingPageBannerByCampaign($campaign)
    {
        $images = array();

        $campaignId = $campaign->getId();

        //load banner
        $imageData = $this->getLandingPageBanner($campaignId);

        $images["name_customer"] = $campaign->getNameCustomer();
        $images["campaign"] = $campaign->getLandingPageCategory();
        $images['url'] = $campaign->getLandingPageLink();

        if (array_filter($imageData)) {
            $images['banners'] = $imageData;
        }

        return $images;
    }
    /**
     * Get creative of company with landing page
     * @param $campaignId
     * @return mixed
     */
    public function getLandingPageBanner($campaignId)
    {
        $bannerCollection = Mage::getModel("zolagobanner/banner")
            ->getCollection();
        $bannerCollection->addFieldToFilter("campaign_id", $campaignId);
        $bannerCollection->addFieldToFilter("type", Zolago_Banner_Model_Banner_Type::TYPE_LANDING_PAGE_CREATIVE);
        $bannerCollection->getSelect()
            ->join(
                array('banner_content' => Mage::getSingleton('core/resource')->getTableName(
                    "zolagobanner/banner_content"
                )),
                'banner_content.banner_id = main_table.banner_id'
            )
            ->where("campaign_id=?", $campaignId);

        $landingPageBanner = $bannerCollection->getFirstItem();

        $imageData = (array)unserialize($landingPageBanner->getData("image"));

        return $imageData;
    }

    /**
     * Return campaign_id from params if exist otherwise null
     * @return int|null
     */
    public function getCampaignIdFromParams()
    {

        $id = null;
        if (Mage::registry("listing_reload_params")) {
            $params = Mage::registry("listing_reload_params");
        } else {
            $params = Mage::app()->getRequest()->getParams();
        }
        $fq = isset($params["fq"]) ? $params["fq"] : array();

        if (empty($fq)) {
            return $id;
        } elseif (isset($fq["campaign_regular_id"])) {
            return (int)$fq["campaign_regular_id"][0];
        } elseif (isset($fq["campaign_info_id"])) {
            return (int)$fq["campaign_info_id"][0];
        } else {
            return $id;
        }
    }

    /**
     * Return current campaign for listing
     * If sth not correct return empty campaign model so always check model before
     *
     * Note:
     * Recommended check
     * if ($campaign && $campaign->getId()) { ... }
     *
     * @return false|Mage_Core_Model_Abstract|Zolago_Campaign_Model_Campaign
     */
    public function getCampaign()
    {
        if (!Mage::registry("current_zolagocampaign")) {

            /** @var Zolago_Dropship_Model_Vendor $vendor */
            $vendor = Mage::helper('umicrosite')->getCurrentVendor();
            $campaignId = $this->getCampaignIdFromParams();
            if ($campaignId) {
                /** @var Zolago_Campaign_Model_Campaign $campaign */
                $campaign = Mage::getModel("zolagocampaign/campaign")->load($campaignId);
                $campaignWebsites = $campaign->getAllowedWebsites();

                if ($campaign->getId()) {
                    if (
                        ($campaign->getStatus() == Zolago_Campaign_Model_Campaign_Status::TYPE_ACTIVE)
                        && $campaign->getIsLandingPage()
                        && $campaign->getLandingPageCategory()
                        && in_array(Mage::app()->getWebsite()->getId(), $campaignWebsites)
                    ) {
                        //context landing_page_context
                        $landing_page_context = $campaign->getData("landing_page_context");

                        if ($vendor && ($campaign->getContextVendorId() == $vendor->getVendorId())
                            && $landing_page_context == Zolago_Campaign_Model_Attribute_Source_Campaign_LandingPageContext::LANDING_PAGE_CONTEXT_VENDOR
                        ) {
                            // If vendor context
                            Mage::register("current_zolagocampaign", $campaign);
                        } elseif (!$vendor && $landing_page_context == Zolago_Campaign_Model_Attribute_Source_Campaign_LandingPageContext::LANDING_PAGE_CONTEXT_GALLERY) {
                            // If gallery context
                            Mage::register("current_zolagocampaign", $campaign);
                        }
                        //TODO ???????
                        $campaign->setLandingPageLink($this->getLandingPageUrl());
                    }
                }
            }
            if (!Mage::registry("current_zolagocampaign")) {
                // If not save empty model so always check before:
                // if ($campaign && $campaign->getId()) { ... }
                Mage::register("current_zolagocampaign", Mage::getModel("zolagocampaign/campaign"));
            }
        }
        return Mage::registry("current_zolagocampaign");
    }


    public function getCanShowBackToCampaign()
    {
        $campaign = $this->getCampaign();
        $parentCat = Mage::registry('current_category')->getParentCategory();

        if ($campaign && $campaign->getId() && $campaign->getLandingPageCategory() == $parentCat->getId()) {
            return true;
        }
        return false;
    }

    /**
     * Return if campaign_regular_id or campaign_info_id should be kept in url
     * on mobile "Go up" link
     * Zolago_Solrsearch_Block_Catalog_Product_List_Header_Category template
     * @return bool
     */
    public function getKeepParametersCampaign()
    {
        $campaign = $this->getCampaign();
        $parentCat = Mage::registry('current_category')->getParentCategory();
        $parentCatId = $parentCat->getId();

        $subCats = array($parentCatId => $parentCatId);

        /* @var $categoryHelper Zolago_Catalog_Helper_Category */
        $categoryHelper = Mage::helper("zolagocatalog/category");
        $children = $categoryHelper->getChildrenIds($parentCatId);
        $subCats = array_merge($subCats, $children);

        if ($campaign && $campaign->getId() && $campaign->getIsLandingPage() && in_array($parentCatId, $subCats)) {
            return true;
        }
        return false;
    }


    /**
     * Construct landing page url
     * @param null $campaignId
     * @param bool|TRUE $includeParams
     * @return string
     */
    public function getLandingPageUrl($campaignId = NULL, $includeParams = TRUE, $categoryToCompare = NULL)
    {
        if(is_null($campaignId)){
            //Try to get campaign_id from params
            $campaignId = $this->getCampaignIdFromParams();
        }
        if(is_null($campaignId)){
            return "";
        }
        $key = 'lp_url_campaign_id_' . $campaignId;
        $urlText = Mage::registry($key);
        if ($urlText !== null) {
            return $urlText;
        }
        $urlText = "";
        /** @var Zolago_Campaign_Model_Campaign $campaign */
        $campaign = Mage::getModel("zolagocampaign/campaign")->load($campaignId);

        if ($campaign->getIsLandingPage() == Zolago_Campaign_Model_Campaign_Urltype::TYPE_MANUAL_LINK) {
            Mage::unregister($key);
            Mage::register($key, $urlText);
            return $urlText;
        }

        $landingPageCategory = $campaign->getLandingPageCategory();
        $landingPageCategoryId = isset($landingPageCategory) ? $landingPageCategory : 0;
        $landingPageContext = $campaign->getLandingPageContext();


        //Get campaign website
        $websiteId = $campaign->getWebsite();
        $website = Mage::getModel('core/website')->load($websiteId);
        $storeIds = $website->getStoreIds();
        //--Get campaign website

        $rootId = Mage::app()->getStore($storeIds[1])->getRootCategoryId();

        $url = Mage::getBaseUrl();
        $vendorRootCategoryId = 0;
        //If vendor context, then modify url according to vendor context
        if ($landingPageContext == Zolago_Campaign_Model_Attribute_Source_Campaign_LandingPageContext::LANDING_PAGE_CONTEXT_VENDOR) {
            $contextVendorId = $campaign->getContextVendorId();
            $contextVendor = $this->_getVendorData($contextVendorId);
            $vendorRootCategories = $contextVendor->getRootCategory();

            if (!Mage::helper("zolagodropship")->isLocalVendor($contextVendorId)) {
                $url = Mage::helper("zolagodropshipmicrosite")->getVendorUrl($contextVendor, true);
                $vendorRootCategoryId = isset($vendorRootCategories[$websiteId]) ? $vendorRootCategories[$websiteId] : 0;
            }
        }

        $landingPageUrl = $campaign->getCampaignUrl();

        $landingPageCategoryUrl = "";

        $currentCategory = Mage::registry("current_category");
        if (
            //Avoid links /modagomall
            $landingPageCategoryId !== $rootId
            &&
            //Avoid links /moda-menska (if moda-menska is vendor root category)
            $landingPageCategory !== $vendorRootCategoryId
        ) {
            $landingPageCategoryModel = Mage::getModel("catalog/category")->load($landingPageCategoryId);
            $landingPageCategoryUrl = $landingPageCategoryModel->getUrlPath();
        }
        if ($currentCategory) {
            $currentCategoryId = $currentCategory->getId();
            if($categoryToCompare){
                $currentCategoryId = $categoryToCompare;
            }
            if (
                //Avoid links /modagomall
                $currentCategoryId !== $rootId
                &&
                //Avoid links /moda-menska (if moda-menska is vendor root category)
                $currentCategoryId !== $vendorRootCategoryId
            ) {
                $landingPageCategoryModel = Mage::getModel("catalog/category")->load($currentCategoryId);
                $landingPageCategoryUrl = $landingPageCategoryModel->getUrlPath();
            }
        }

        $urlText = $url . $landingPageCategoryUrl . "?" . $landingPageUrl;
        if(!$includeParams){
            $urlText = $url . $landingPageCategoryUrl;
        }

        Mage::unregister($key);
        Mage::register($key, $urlText);
        return $urlText;

    }
}