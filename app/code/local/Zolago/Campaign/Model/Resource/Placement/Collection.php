<?php

/**
 * Class Zolago_Campaign_Model_Resource_Placement_Collection
 */
class Zolago_Campaign_Model_Resource_Placement_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    /**
     * NOTE: main_table -> zolago_campaign_placement ("zolagocampaign/campaign_placement")
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('zolagocampaign/placement');
    }

    /**
     * Add data from campaign ("zolagocampaign/campaign") table
     * Add data from campaign ("zolagocampaign/campaign_website") table
     *
     * Note: if $websiteId === true use current website
     *
     * @param bool|int|array|Mage_Core_Model_Website $website
     * @return $this
     * @throws Mage_Core_Exception
     */
    public function addCampaign($website = true) {
        $this->getSelect()->joinLeft(
            array("campaign" => $this->getTable("zolagocampaign/campaign")),
            "campaign.campaign_id = main_table.campaign_id",
            array(
                'campaign_name'         => 'campaign.name',
                'campaign_name_customer'=> 'campaign.name_customer',
                'campaign_date_from'    => 'campaign.date_from',
                'campaign_date_to'      => 'campaign.date_to',
                'campaign_status'       => 'campaign.status',
                'campaign_vendor_id'    => 'campaign.vendor_id',
                'campaign_url'          => 'campaign.campaign_url',
                'campaign_type'         => 'campaign.type',
                'campaign_is_landing_page' => 'campaign.is_landing_page',
                'campaign_context_vendor_id'  => 'campaign.context_vendor_id',
                'campaign_landing_page_context'  => 'campaign.landing_page_context',
                'campaign_landing_page_category_id' => 'campaign.landing_page_category',
            )
        );

        if($website) {
            $this->addWebsite();
            $this->addWebsiteFilter($website);
        }
        return $this;
    }

    /**
     * Add data from ("zolagocampaign/campaign_website") table
     *
     * @return $this
     */
    public function addWebsite() {
        $this->getSelect()->joinLeft(
            array('campaign_website' => $this->getTable("zolagocampaign/campaign_website")),
            'campaign_website.campaign_id = campaign.campaign_id',
            array(
                "campaign_website_id"  => "campaign_website.website_id"
            )
        );
        return $this;
    }

    /**
     * Filtering websites
     * Param can by bool(true only), id, array of ids or website model
     *
     * @param bool|int|array|Mage_Core_Model_Website $website
     * @return $this
     * @throws Mage_Core_Exception
     */
    public function addWebsiteFilter($website) {
        if ($website === true) {
            $website = Mage::app()->getWebsite()->getId();
        }
        if ($website instanceof Mage_Core_Model_Website) {
            $website = $website->getId();
        }
        if (!is_array($website)) {
            $website = array($website);
        }
        $this->addFieldToFilter("campaign_website.website_id", array("in" => $website));
        return $this;
    }

    /**
     * Add data from banner ("zolagobanner/banner") table
     * Add data from banner content ("zolagobanner/banner_content") table
     * If $bannerTypesFilter not specified all banner types returned
     *
     * @param array|string $bannerTypesFilter
     * @return $this
     */
    public function addBanner($bannerTypesFilter = array()) {

        $this->getSelect()->joinLeft(
            array("banner" => $this->getTable("zolagobanner/banner")),
            "banner.banner_id = main_table.banner_id",
            array(
                'banner_name'       => 'banner.name'
            )
        );
        $this->getSelect()->joinLeft(
            array("banner_content"  => $this->getTable("zolagobanner/banner_content")),
            "banner.banner_id = banner_content.banner_id",
            array(
                'banner_show'       => 'banner_content.show',
                'banner_html'       => 'banner_content.html',
                'banner_image'      => 'banner_content.image',
                'banner_caption'    => 'banner_content.caption'
            )
        );

        if(!empty($bannerTypesFilter)){
            $this->addBannerTypeFilter($bannerTypesFilter);
        }

        return $this;
    }

    /**
     * Filtering categories
     * Param can be model, id or array of ids
     *
     * @param Mage_Catalog_Model_Category|int|array $category
     * @return $this
     */
    public function	addCategoryFilter($category){
        if ($category instanceof Mage_Catalog_Model_Category) {
            $category = $category->getId();
        }
        if (!is_array($category)) {
            $category = array($category);
        }
        $this->addFieldToFilter("main_table.category_id", array("in" => $category));
        return $this;
    }

    /**
     * Add data from vendor ("udropship/vendor") table
     * $joinFrom may by "main_table" or "campaign" or ...
     *
     * @return $this
     */
    public function addVendor() {
        $this->getSelect()->joinLeft(
            array("vendor" => $this->getTable("udropship/vendor")),
            "vendor.vendor_id = campaign.vendor_id",
            array(
                'vendor_url_key' => 'vendor.url_key'
            )
        );
        $this->getSelect()->joinLeft(
            array("lp_vendor" => $this->getTable("udropship/vendor")),
            "lp_vendor.vendor_id = campaign.context_vendor_id",
            array(
                'lp_vendor_url_key' => 'lp_vendor.url_key'
            )
        );
        return $this;
    }

    /**
     * Filtering vendors
     * Param can by model, id or array of ids
     *
     * @param int|array|ZolagoOs_OmniChannel_Model_Vendor $vendor
     * @return $this
     */
    public function addVendorFilter($vendor) {
        if($vendor instanceof ZolagoOs_OmniChannel_Model_Vendor) {
            $vendor = $vendor->getId();
        }
        if (!is_array($vendor)) {
            $vendor = array($vendor);
        }
        $this->addFieldToFilter("main_table.vendor_id", array("in" => $vendor));
        return $this;
    }

    /**
     * Banner types filtering
     * Param can by string or array of strings
     * For types @see Zolago_Banner_Model_Banner_Type
     *
     * @param string|array $bannerType
     * @return $this
     */
    private function addBannerTypeFilter($bannerType) {

        if (!is_array($bannerType)) {
            $bannerType = array($bannerType);
        }
        $this->addFieldToFilter("banner.type", array("in" => $bannerType));
        return $this;
    }

    /**
     * Return placement for specific categories, vendors and websites
     *
     * $category param can be model, id or array of ids
     * $vendor param can be model, id or array of ids
     * $bannerTypeFilter param can by string or array of strings
     *      For types @see Zolago_Banner_Model_Banner_Type
     * $website param can by bool(true only), id, array of ids or website model
     * NOTE: if $websiteId === true use current website
     *
     * @param Mage_Catalog_Model_Category|int|array $category
     * @param int|array|ZolagoOs_OmniChannel_Model_Vendor $vendor
     * @param string|array $bannerTypeFilter
     * @param bool|int|array|Mage_Core_Model_Website $website
     * @return $this
     */
    public function addPlacementForCategory($category, $vendor, $bannerTypeFilter = array(), $website = true) {

        // NOTE: main_table -> campaign_placement

        // Add data from campaign
        $this->addCampaign($website);
        // Add data from banner & banner content
        $this->addBanner($bannerTypeFilter);
        // For category
        $this->addCategoryFilter($category);
        // For vendor
        $this->addVendor();
        $this->addVendorFilter($vendor);

        $this->setOrder("banner.type", self::SORT_ORDER_DESC);
        $this->setOrder("main_table.priority", self::SORT_ORDER_ASC);

        return $this;
    }

}