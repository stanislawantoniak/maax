<?php

/**
 * Class Zolago_Campaign_Model_Campaign_LandingPage
 */
class Zolago_Campaign_Model_Campaign_LandingPage extends Zolago_Campaign_Model_Campaign
{
    /**
     * Check if category root (VENDOR ROOT or GALLERY ROOT) for current campaign
     *
     * @param Mage_Catalog_Model_Category $category
     * @return bool
     * @throws Mage_Core_Exception
     */
    public function isCategoryRootForCurrentCampaign(Mage_Catalog_Model_Category $category)
    {
        $isRoot = false;

        if (!$category) {
            return false;
        }

        /* @var $campaign Zolago_Campaign_Model_Campaign */
        $campaign = $category->getCurrentCampaign();

        if (!$campaign) {
            return false;
        }

        $campaignContext = $campaign->getLandingPageContext();

        if ($campaignContext == Zolago_Campaign_Model_Attribute_Source_Campaign_LandingPageContext::LANDING_PAGE_CONTEXT_VENDOR
        ) {
            // If vendor context
            $vendor = Mage::helper('umicrosite')->getCurrentVendor();
            $vendorRootCategories = $vendor->getRootCategory();

            $website = Mage::app()->getWebsite()->getId();
            $vendorRootCategoryForWebsite = isset($vendorRootCategories[$website]) ? $vendorRootCategories[$website] : 0;

            if ($vendorRootCategoryForWebsite == $category->getId()) {
                $isRoot = true;
            }

        } elseif ($campaignContext == Zolago_Campaign_Model_Attribute_Source_Campaign_LandingPageContext::LANDING_PAGE_CONTEXT_GALLERY) {
            // If gallery context
            $rootCategoryId = Mage::app()->getStore()->getRootCategoryId();
            if ($rootCategoryId == $category->getId()) {
                $isRoot = true;
            }
        }
        return $isRoot;
    }
}