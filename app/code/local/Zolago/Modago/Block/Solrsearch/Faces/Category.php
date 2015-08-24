<?php
/**
 * Author: Paweł Chyl <pawel.chyl@orba.pl>
 * Date: 21.08.2014
 */

class Zolago_Modago_Block_Solrsearch_Faces_Category extends Zolago_Solrsearch_Block_Faces_Category
{
    /**
     * Returns model of current category.
     *
     * @return Mage_Catalog_Model_Category
     */
    public function getCurrentCategory()
    {
        return Mage::getSingleton('zolagosolrsearch/catalog_product_list')->getCurrentCategory();
    }

    /**
     * Returns URL for parent category.
     *
     * @return null|string
     */
    public function getParentCategoryUrl()
    {
        $rootCategory = Mage::app()->getStore()->getRootCategoryId();
        if($this->getParentBlock()->getMode()==Zolago_Solrsearch_Block_Faces::MODE_CATEGORY){
            if ($rootCategory == $this->getCurrentCategory()->getParentCategory()->getId()) {
                return null;
            }
        }

        if ($rootCategory == $this->getCurrentCategory()->getId()) {
            return null;
        }

        /** @var Zolago_Dropship_Model_Vendor $vendor */
        $vendor = Mage::helper('umicrosite')->getCurrentVendor();
        /** @var Zolago_DropshipMicrosite_Helper_Data $helperZDM */
        $helperZDM = Mage::helper("zolagodropshipmicrosite");
        $vendorRootCategoryId = $helperZDM->getVendorRootCategoryObject()->getId();
        if ($vendor && $vendor->getId()) {
            if($vendorRootCategoryId == $this->getCurrentCategory()->getId()) {
                return null;
            }
        }
        $parentCategory = $this->getCurrentCategory()->getParentCategory();
        $parentCategoryUrl = null;

        if($this->getParentBlock()->getMode() == Zolago_Solrsearch_Block_Faces::MODE_CATEGORY) {
            // Fix for landing pages and campaigns
            /* @var $landingPageHelper Zolago_Campaign_Helper_LandingPage */
            $landingPageHelper = Mage::helper("zolagocampaign/landingPage");
            /** @var Zolago_Campaign_Model_Campaign $campaign */
            $campaign = $landingPageHelper->getCampaign();

            $_query = null;

            if ($campaign && $campaign->getId() && $campaign->getIsLandingPage()) {
                $landing_page_category = $campaign->getData("landing_page_category");

                $subCats = array($landing_page_category => $landing_page_category);

                $children = Mage::getModel('catalog/category')->getCategories($landing_page_category);
                foreach ($children as $category) {
                    $subCats[$category->getId()] = $category->getId();
                }


                if (in_array($parentCategory->getId(), $subCats)) {
                    $_fq = $this->getRequest()->getParam('fq');
                    $_query['fq']['campaign_info_id']    = isset($_fq['campaign_info_id'])    ? $_fq['campaign_info_id']    : null;
                    $_query['fq']['campaign_regular_id'] = isset($_fq['campaign_regular_id']) ? $_fq['campaign_regular_id'] : null;
                }
            }

            $parentCategoryUrl = Mage::getUrl('',
                array(
                    '_query'  => $_query,
                    '_direct' => Mage::getModel('core/url_rewrite')->loadByIdPath('category/' . $parentCategory->getId())->getRequestPath(),
                )
            );

            if ($vendor && $vendor->getId() && $parentCategory->getId() == $vendorRootCategoryId) {
                $parentCategoryUrl = $vendor->getVendorUrl();
            }
        }
        else {
            $id = $parentCategory->getId();
            $_solrDataArray = $this->getSolrData();
            $q = "&q=" . Mage::helper('catalogsearch')->getQueryText();

            if( isset($_solrDataArray['responseHeader']['params']['q']) && !empty($_solrDataArray['responseHeader']['params']['q']) ) {
                $q = "&q=" . $_solrDataArray['responseHeader']['params']['q'];
            }

            $parentCategoryUrl = Mage::getUrl("search") . "?scat={$id}{$q}";
        }

        return $parentCategoryUrl;
    }

    /**
     * Returns name(label) for parent category.
     *
     * @return string
     */
    public function getParentCategoryLabel()
    {
        $helperZSS = Mage::helper('zolagosolrsearch');
        $label = '';
        $rootCategory = Mage::app()->getStore()->getRootCategoryId();
        if($this->getCurrentCategory()->getParentCategory()->getId() == $rootCategory) {
            $label = $helperZSS->__("All categories");
        } else {
            $label = $this->getCurrentCategory()->getParentCategory()->getName();
        }

        /** @var Zolago_Dropship_Model_Vendor $_vendor */
        $_vendor = Mage::helper('umicrosite')->getCurrentVendor();
        if ($_vendor && $_vendor->getId()) {
            if($_vendor->rootCategory()->getId() == $this->getCurrentCategory()->getParentCategory()->getId()) {
                $label = $helperZSS->__("All categories");
            }
        }
        return $label;
    }

    public function getIsSearch(){

        if($this->getParentBlock()->getMode()==Zolago_Solrsearch_Block_Faces::MODE_CATEGORY) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * use only for search page
     * @return bool
     */
    public function getIsParentRootCategory() {
        $result = false;

        $rootCategory = Mage::app()->getStore()->getRootCategoryId();
        if($this->getCurrentCategory()->getParentCategory()->getId() == $rootCategory) {
            $result = true;
        } else {
            $result = false;
        }

        /** @var Zolago_Dropship_Model_Vendor $_vendor */
        $_vendor = Mage::helper('umicrosite')->getCurrentVendor();
        if ($_vendor && $_vendor->getId()) {
            if($_vendor->rootCategory()->getId() == $this->getCurrentCategory()->getParentCategory()->getId()) {
                $result = true;
            }
        }

        return $result;
    }

    public function getCanShowBackToCampaign() {
        /* @var $landingPageHelper Zolago_Campaign_Helper_LandingPage */
        $landingPageHelper = Mage::helper("zolagocampaign/landingPage");

        return  $landingPageHelper->getCanShowBackToCampaign();
    }
} 