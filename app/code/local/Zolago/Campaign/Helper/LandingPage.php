<?php

class Zolago_Campaign_Helper_LandingPage extends Mage_Core_Helper_Abstract
{

    public function getCampaignLandingPageBanner(){
        $images = new stdClass();

        /** @var Zolago_Dropship_Model_Vendor $vendor */
        $vendor = Mage::helper('umicrosite')->getCurrentVendor();

        $fq = Mage::app()->getRequest()->getParam("fq");

        if(isset($fq["campaign_regular_id"]) || isset($fq["campaign_info_id"])){
            $landingPageCampaign = "";
            if(isset($fq["campaign_regular_id"])){
                $landingPageCampaign = $fq["campaign_regular_id"][0];
            }
            if(isset($fq["campaign_info_id"])){
                $landingPageCampaign = $fq["campaign_info_id"][0];
            }
            $landingPageCampaign = trim($landingPageCampaign);

            $campaign = Mage::getModel("zolagocampaign/campaign")
                ->load($landingPageCampaign,"name_customer");

            $campaignId = $campaign->getId();
            if ($campaignId !== NULL) {

                if (
                    ($campaign->getStatus() == Zolago_Campaign_Model_Campaign_Status::TYPE_ACTIVE)
                    && $campaign->getIsLandingPage()
                    && $campaign->getLandingPageCategory()
                ) {
                    //context landing_page_context
                    $landing_page_context = $campaign->getData("landing_page_context");
                    $landing_page_category_id = $campaign->getData("landing_page_category");

                    $landingPageUrl = $campaign->getData("landing_page_url");


                    if($vendor && ($campaign->getContextVendorId() == $vendor->getVendorId()) && $landing_page_context == Zolago_Campaign_Model_Attribute_Source_Campaign_LandingPageContext::LANDING_PAGE_CONTEXT_VENDOR){
                        //if vendor context
                        $imageData = $this->getLandingPageBanner($campaignId);

                        $images->name_customer = $campaign->getData("name_customer");
                        $images->campaign = $campaign->getLandingPageCategory();

                        $vendorName = $vendor->getUrlKey();
                        $vendorUrlPart = $vendorName."/";


                        $images->url =  Mage::getBaseUrl(). $vendorUrlPart . Mage::getModel("catalog/category")->load($landing_page_category_id)->getUrlPath(). "?" . $landingPageUrl;

                        if(array_filter($imageData)){
                            $images->banners = $imageData;

                        }

                    }
                    if(!$vendor && $landing_page_context == Zolago_Campaign_Model_Attribute_Source_Campaign_LandingPageContext::LANDING_PAGE_CONTEXT_GALLERY){
                        //if gallery context
                        //load banner
                        $imageData = $this->getLandingPageBanner($campaignId);

                        $images->name_customer = $campaign->getData("name_customer");
                        $images->campaign = $campaign->getLandingPageCategory();

                        $images->url =  Mage::getBaseUrl(). Mage::getModel("catalog/category")->load($landing_page_category_id)->getUrlPath(). "?" . $landingPageUrl;

                        if(array_filter($imageData)){
                            $images->banners = $imageData;
                        }

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
     * Return current campaign for listing if correct
     * @return false|Mage_Core_Model_Abstract|Zolago_Campaign_Model_Campaign
     */
    public function getCampaign() {
        if (!Mage::registry("current_zolagocampaign")) {

            /** @var Zolago_Dropship_Model_Vendor $vendor */
            $vendor = Mage::helper('umicrosite')->getCurrentVendor();
            $fq = Mage::app()->getRequest()->getParam("fq");

            if (isset($fq["campaign_regular_id"]) || isset($fq["campaign_info_id"])) {
                $landingPageCampaign = "";
                if (isset($fq["campaign_regular_id"])) {
                    $landingPageCampaign = $fq["campaign_regular_id"][0];
                }
                if (isset($fq["campaign_info_id"])) {
                    $landingPageCampaign = $fq["campaign_info_id"][0];
                }
                $landingPageCampaign = trim($landingPageCampaign);

                /** @var Zolago_Campaign_Model_Campaign $campaign */
                $campaign = Mage::getModel("zolagocampaign/campaign")
                    ->load($landingPageCampaign, "name_customer");

                if ($campaign->getId()) {
                    if (
                        ($campaign->getStatus() == Zolago_Campaign_Model_Campaign_Status::TYPE_ACTIVE)
                        && $campaign->getIsLandingPage()
                        && $campaign->getLandingPageCategory()
                    ) {
                        //context landing_page_context
                        $landing_page_context = $campaign->getData("landing_page_context");

                        if ($vendor && ($campaign->getContextVendorId() == $vendor->getVendorId()) && $landing_page_context == Zolago_Campaign_Model_Attribute_Source_Campaign_LandingPageContext::LANDING_PAGE_CONTEXT_VENDOR) {
                            //if vendor context
                            Mage::register("current_zolagocampaign", $campaign);
                        }
                        if (!$vendor && $landing_page_context == Zolago_Campaign_Model_Attribute_Source_Campaign_LandingPageContext::LANDING_PAGE_CONTEXT_GALLERY) {
                            //if gallery context
                            Mage::register("current_zolagocampaign", $campaign);
                        }
                    }
                }
            }
            if (!Mage::registry("current_zolagocampaign")) {
                Mage::register("current_zolagocampaign", Mage::getModel("zolagocampaign/campaign"));
            }
        }
        return Mage::registry("current_zolagocampaign");
    }
}