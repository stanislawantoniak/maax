<?php

/**
 * Class Zolago_Campaign_Model_Placement
 *
 * @method int getPlacementId()
 * @method int getVendorId()
 * @method int getCategoryId()
 * @method int getCampaignId()
 * @method int getBannerId()
 * @method int getType()
 * @method int getPosition()
 * @method int getPriority()
 */
class Zolago_Campaign_Model_Placement extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init("zolagocampaign/placement");
    }

    /**
     * Unserialize data from banner_content
     * For now works with correct collection
     * @see Zolago_Campaign_Model_Resource_Placement_Collection::addBanner()
     * Todo: load data from table if not from collection
     * @return array|mixed
     */
    public function getBannerImageData() {
        if (!$this->getData("banner_image_data")) {
            $bannerImage = $this->getData("banner_image");
            $type = $this->getData("type");
            if ($type == Zolago_Banner_Model_Banner_Type::TYPE_SLIDER) {
                $bannerImage = !empty($bannerImage) ? unserialize($bannerImage) : array();
            } elseif ($type == Zolago_Banner_Model_Banner_Type::TYPE_BOX) {
                $bannerImage = !empty($bannerImage) ? unserialize($bannerImage)[1] : array();
            } elseif ($type == Zolago_Banner_Model_Banner_Type::TYPE_INSPIRATION) {
                $bannerImage = !empty($bannerImage) ? unserialize($bannerImage) : array();
            } elseif ($type == Zolago_Banner_Model_Banner_Type::TYPE_LANDING_PAGE_CREATIVE) {
                $bannerImage = !empty($bannerImage) ? unserialize($bannerImage) : array(); // NOT tested
            } else {
                $bannerImage = array();
            }

            if (isset($bannerImage["url"])) {
                $bannerImage["url"] = $this->getUrl($bannerImage);
            } else {
                // Array of arrays
                foreach ($bannerImage as $i => &$item) {
                    $item["url"] = $this->getUrl($item);
                }
            }
            $this->setData("banner_image_data", $bannerImage);
        }
        return $this->getData("banner_image_data");
    }

    /**
     * Get final url for placement
     * Param $item must by array
     * Todo: load data from table if not from collection
     * @param $item
     * @return string
     */
    private function getUrl($item) {
        $localVendorId = Mage::helper('udropship')->getLocalVendorId();
        $vendorUrlPart = "";
        if ($localVendorId != $this->getData("campaign_vendor_id")) {
            $vendorUrlPart = $this->getData("vendor_url_key") . "/";
        }
        return $item['url'] ? Mage::getUrl("/", array("_no_vendor" => true)) . $vendorUrlPart . $item['url'] :
            ($this->getLandingPageUrl() ? $this->getLandingPageUrl() : ($this->getCampaignUrl() ? $this->getCampaignUrl() : "") );
    }

    /**
     * Generate campaign url for placement
     * For works
     * @see Zolago_Campaign_Model_Resource_Placement_Collection::addVendor("campaign")
     * @see Zolago_Campaign_Model_Resource_Placement_Collection::addCampaign()
     * Todo: load data from table if not from collection
     * @return string
     */
    private function getCampaignUrl() {
        $localVendorId = Mage::helper('udropship')->getLocalVendorId();
        $vendorUrlPart = "";
        if ($localVendorId != $this->getData("campaign_vendor_id")) {
            $vendorUrlPart = $this->getData("vendor_url_key") . "/";
        }
        return Mage::getUrl("/", array("_no_vendor" => true)) . $vendorUrlPart . $this->getData("campaign_url");
    }

    /**
     * Generate landing page url
     * For works
     * @see Zolago_Campaign_Model_Resource_Placement_Collection::addCampaign()
     * Todo: load data from table if not from collection
     * @return string
     */
    private function getLandingPageUrl() {
        $id = $this->getData("campaign_landing_page_category_id");
        $id = !empty($id) ? $id : 0;
        $vendorUrlKey = $this->getData("lp_vendor_url_key");
        $isLP = (int)$this->getData("campaign_is_landing_page");

        $cacheKey = "lp_url_".$vendorUrlKey."_category_". $id;
        if (!$this->getData($cacheKey)) {
            $url = "";
            if ($isLP) { // is Landing Page
                $lpUrl = $this->getData("campaign_url");
                $vendorUrlPart = "";
                if ($this->getData("campaign_landing_page_context")
                    == Zolago_Campaign_Model_Attribute_Source_Campaign_LandingPageContext::LANDING_PAGE_CONTEXT_VENDOR
                ) {
                    $vendorUrlPart = $vendorUrlKey . "/";
                }
                $url = Mage::getUrl("/", array("_no_vendor" => true)) . $vendorUrlPart . Mage::getModel("catalog/category")->load($id)->getUrlPath() . "?" . $lpUrl;
            }
            $this->setData($cacheKey, $url);
        }
        return $this->getData($cacheKey);
    }

    /**
     * Unserialize data from banner_content
     * For works
     * @see Zolago_Campaign_Model_Resource_Placement_Collection::addBanner()
     * Todo: load data from table if not from collection
     * @return array|mixed
     */
    public function getBannerCaptionData() {
        if (!$this->getData("banner_caption_data")) {
            $bannerCaption = $this->getData("banner_caption");
            $type = $this->getData("type");

            /**
             * Captions currently in use for Sliders and Inspirations
             */
            if ($type == Zolago_Banner_Model_Banner_Type::TYPE_SLIDER) {
                $bannerCaption = !empty($bannerCaption) ? unserialize($bannerCaption) : array();
            } elseif ($type == Zolago_Banner_Model_Banner_Type::TYPE_INSPIRATION) {
                $bannerCaption = !empty($bannerCaption) ? unserialize($bannerCaption) : array();
            } else {
                $bannerCaption = array();
            }

            // Setup final url
            if (isset($bannerCaption["url"])) {
                $bannerCaption["url"] = $this->getUrl($bannerCaption);
            } else {
                // Array of arrays
                foreach ($bannerCaption as $i => &$item) {
                    $item["url"] = $this->getUrl($item);
                }
            }
            $this->setData("banner_caption_data", $bannerCaption);
        }
        return $this->getData("banner_caption_data");
    }
}