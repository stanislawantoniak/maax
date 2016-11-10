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
    protected $campaing = null;

    protected function _construct()
    {
        $this->_init("zolagocampaign/placement");
    }

    /**
     * @return Zolago_Campaign_Model_Campaign|null
     */
    public function getCampaign() {
        if ($this->campaing === null) {
            $this->campaing = Mage::getModel("zolagocampaign/campaign")->load($this->getCampaignId());
        }
        return $this->campaing;
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
                $bannerImage["url"] = $this->getUrl($bannerImage["url"]);
            } else {
                // Array of arrays
                foreach ($bannerImage as $i => &$item) {
                    $item["url"] = $this->getUrl($item["url"]);
                }
            }
            $this->setData("banner_image_data", $bannerImage);
        }
        return $this->getData("banner_image_data");
    }

    /**
     * Get final url for placement
     *
     * @param null|string $customUrl
     * @return string
     */
    private function getUrl($customUrl = null) {
        return $this->getCampaign()->getFinalCampaignUrl($customUrl);
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
                $bannerCaption["url"] = $this->getUrl($bannerCaption["url"]);
            } else {
                // Array of arrays
                foreach ($bannerCaption as $i => &$item) {
                    $item["url"] = $this->getUrl($item["url"]);
                }
            }
            $this->setData("banner_caption_data", $bannerCaption);
        }
        return $this->getData("banner_caption_data");
    }
}