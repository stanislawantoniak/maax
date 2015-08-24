<?php

class Zolago_Campaign_Helper_LandingPage extends Mage_Core_Helper_Abstract
{

    public function getCampaignLandingPageBanner()
    {
        $images = new stdClass();

        /** @var Zolago_Dropship_Model_Vendor $vendor */
        $vendor = Mage::helper('umicrosite')->getCurrentVendor();

        $campaignId = $this->getCampaignIdFromParams();
        if (!$campaignId) {
            return $images;
        }
        /** @var Zolago_Campaign_Model_Campaign $campaign */
        $campaign = Mage::getModel("zolagocampaign/campaign")->load($campaignId);

        $campaignWebsites = $campaign->getAllowedWebsites();
        $campaignId = $campaign->getId();
        if ($campaign && $campaignId) {

            if (
                ($campaign->getStatus() == Zolago_Campaign_Model_Campaign_Status::TYPE_ACTIVE)
                && $campaign->getIsLandingPage()
                && $campaign->getLandingPageCategory()
                && in_array(Mage::app()->getWebsite()->getId(), $campaignWebsites)
            ) {
                //context landing_page_context
                $landing_page_context = $campaign->getLandingPageContext();
                $landing_page_category_id = $campaign->getLandingPageCategory();

                $landingPageUrl = $campaign->getData("campaign_url");

                if ($vendor && ($campaign->getContextVendorId() == $vendor->getVendorId()) && $landing_page_context == Zolago_Campaign_Model_Attribute_Source_Campaign_LandingPageContext::LANDING_PAGE_CONTEXT_VENDOR) {
                    //if vendor context
                    $imageData = $this->getLandingPageBanner($campaignId);

                    $images->name_customer = $campaign->getNameCustomer();
                    $images->campaign = $campaign->getLandingPageCategory();

                    $vendorName = $vendor->getUrlKey();
                    $vendorUrlPart = $vendorName . "/";

                    $images->url = Mage::getBaseUrl() . Mage::getModel("catalog/category")->load($landing_page_category_id)->getUrlPath() . "?" . $landingPageUrl;

                    if (array_filter($imageData)) {
                        $images->banners = $imageData;

                    }

                }
                if (!$vendor && $landing_page_context == Zolago_Campaign_Model_Attribute_Source_Campaign_LandingPageContext::LANDING_PAGE_CONTEXT_GALLERY) {
                    //if gallery context
                    //load banner
                    $imageData = $this->getLandingPageBanner($campaignId);

                    $images->name_customer = $campaign->getNameCustomer();
                    $images->campaign = $campaign->getLandingPageCategory();

                    $images->url = Mage::getBaseUrl() . Mage::getModel("catalog/category")->load($landing_page_category_id)->getUrlPath() . "?" . $landingPageUrl;

                    if (array_filter($imageData)) {
                        $images->banners = $imageData;
                    }

                }

            }
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
    public function getCampaignIdFromParams() {

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
        }elseif (isset($fq["campaign_info_id"])) {
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

        $subCats = array();

        /* @var $categoryHelper Zolago_Catalog_Helper_Category */
        $categoryHelper = Mage::helper("zolagocatalog/category");
        $children = $categoryHelper->getChildrenIds($parentCatId);
        $subCats = array_merge($subCats, $children);

        if ($campaign && $campaign->getId() && $campaign->getIsLandingPage() && in_array($parentCatId, $subCats)) {
            return true;
        }
        return false;
    }


    public function getLandingPageUrl($campaignId)
    {
        $key     = 'lp_url_campaign_id_' . $campaignId;
        $urlText = Mage::registry($key);
        if ($urlText === null) {
            $urlText = "";
            /** @var Zolago_Campaign_Model_Campaign $campaign */
            $campaign = Mage::getModel("zolagocampaign/campaign")->load($campaignId);

            if ($campaign->getData("is_landing_page") == Zolago_Campaign_Model_Campaign_Urltype::TYPE_MANUAL_LINK) {
                Mage::unregister($key);
                Mage::register($key, $urlText);
                return $urlText;
            }

            $landing_page_category    = $campaign->getData("landing_page_category");
            $landing_page_category_id = isset($landing_page_category) ? $landing_page_category : 0;
            $landing_page_context     = $campaign->getData("landing_page_context");
            $vendorUrlPart            = "";
            if ($landing_page_context == Zolago_Campaign_Model_Attribute_Source_Campaign_LandingPageContext::LANDING_PAGE_CONTEXT_VENDOR) {
                $vendor               = Mage::getModel("udropship/vendor")->load($campaign->getData("context_vendor_id"));
                $vendorName           = $vendor->getUrlKey();
                $vendorUrlPart        = $vendorName . "/";
            }

            $landingPageUrl = $campaign->getData("campaign_url");
            $urlText        = Mage::getBaseUrl() . $vendorUrlPart . Mage::getModel("catalog/category")->load($landing_page_category_id)->getUrlPath() . "?" . $landingPageUrl;

            Mage::unregister($key);
            Mage::register($key, $urlText);
            return $urlText;
        } else {
            return $urlText;
        }
    }
}