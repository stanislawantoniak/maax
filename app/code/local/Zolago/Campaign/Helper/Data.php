<?php

class Zolago_Campaign_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_campaignIds;

    public function getBannerTypesSlots()
    {
        return Mage::getSingleton('zolagobanner/banner_type')->toOptionHash();
    }

    /**
     * @return Zolago_Dropship_Model_Vendor
     */
    public function getVendor()
    {
        return Mage::getSingleton('udropship/session')->getVendor();
    }

    /**
     * @return array
     */
    public function getAllVendorsList()
    {
        $vendorModel = Mage::getModel('udropship/vendor');
        $vendorCollection = $vendorModel->getCollection();
        $vendorsList = array();
        foreach ($vendorCollection as $vendorObj) {
            $vendorsList[$vendorObj->getId()] = $vendorObj->getVendorName();
        }
        return $vendorsList;
    }

    /**
     * @return array
     * @throws Mage_Core_Exception
     */
    public function getVendorCategoriesList()
    {
        $websitesAllowed = $this->getVendor()->getWebsitesAllowed();

        $categories = array();
        if (empty($websitesAllowed)) {
            return $categories;
        }
        foreach ($websitesAllowed as $websiteId) {
            $website = Mage::getModel("core/website")->load($websiteId);
            $categories[$websiteId]["website"] = $website->getName();
            $defaultStoreId = $website->getDefaultStore()->getId();

            //1. Get vendor root category
            // /zolagoosadmin/adminhtml_vendor/edit/ -> Preferences -> Root categories -> Category ID
            $rootCatID = Mage::app()->getStore($defaultStoreId)->getRootCategoryId();

            $customVendorVars = Mage::helper('core')->jsonDecode($this->getVendor()->getCustomVarsCombined());

            $vendorRootCategory = (isset($customVendorVars['root_category']) && !empty($customVendorVars['root_category'][$websiteId]) > 0) ?
                (int)$customVendorVars['root_category'][$websiteId] :
                $rootCatID;

            if ($vendorRootCategory > 0) {
                //get all display_mode = page
                $cats = $this->getAllChildren($vendorRootCategory);

                $catList = $this->getCategoriesDisplayModePage($cats);
                $cats = $vendorRootCategory . "," . trim($catList, ",");

                $collection = Mage::getModel("catalog/category")->getCollection()
                    ->addFieldToFilter('entity_id', array('in' => explode(",", $cats)))
                    ->addAttributeToSelect('name')
                    ->addAttributeToSort('level', 'ASC');

                foreach ($collection as $collectionItem) {
                    $path = $collectionItem->getPath();

                    if (in_array($vendorRootCategory, explode("/", $path))) {
                        $categories[$websiteId]["categories"][] = array(
                            'id' => $collectionItem->getId(),
                            'name' => $collectionItem->getName(),
                            'edit_url' => Mage::getUrl("campaign/placement_category/",
                                array(
                                    "category" => $collectionItem->getId(),
                                    "website" => $websiteId,
                                    "_secure" => true
                                )

                            )
                        );
                    }

                }
            }
        }

        return $categories;
    }

    public function getCategoriesDisplayModePage($cats)
    {
        $allCats = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('is_active', '1')
            ->addAttributeToFilter('display_mode', Mage_Catalog_Model_Category::DM_PAGE)
            ->addAttributeToFilter('include_in_menu', '1')
            ->addAttributeToFilter('parent_id', array('in' => $cats))
            ->addAttributeToSort('position', 'asc');


        $ids = '';

        foreach ($allCats as $category) {
            $ids .= ',' . $category->getId();

            $subcats = $category->getChildren();
            if ($subcats != '') {
                $ids .= $this->getCategoriesDisplayModePage($category->getId());
            }
        }

        return $ids;
    }

    /**
     * @param $catId
     * @return array
     */
    public function getAllChildren($catId)
    {
        $cats = array($catId);
        $categoryV = Mage::getModel('catalog/category')->load($catId);
        $children = $categoryV->getChildren();

        if (!empty($children)) {
            foreach (explode(",", $children) as $childrenId) {
                array_push($cats, $childrenId);
                $categoryCh = Mage::getModel('catalog/category')->load($childrenId);
                $children2 = $categoryCh->getChildren();

                if (!empty($children2)) {
                    foreach (explode(",", $children2) as $children2Id) {
                        array_push($cats, $children2Id);
                        $categoryCh2 = Mage::getModel('catalog/category')->load($children2Id);
                        $children3 = $categoryCh2->getChildren();

                        if (!empty($children3)) {
                            foreach (explode(",", $children3) as $children3Id) {
                                array_push($cats, $children3Id);
                                $categoryCh3 = Mage::getModel('catalog/category')->load($children3Id);
                                $children4 = $categoryCh3->getChildren();

                                if (!empty($children4)) {
                                    foreach (explode(",", $children4) as $children4Id) {
                                        array_push($cats, $children4Id);
                                    }
                                }
                            }
                        }
                    }
                }

            }
        }

        return $cats;
    }


    /**
     * @param null $vendorId
     * @return string
     */
    public function getCategoriesTree($vendorId = null, $website = null)
    {
        $rootCatId = Mage::app()->getStore()->getRootCategoryId();
        if (!empty($vendorId)) {
            $vendor = Mage::getModel("udropship/vendor")->load($vendorId);
            $rootCategory = $vendor->getData("root_category");

            $vendorRootCategoryId = (isset($rootCategory[$website]) && !empty($rootCategory[$website])) ? $rootCategory[$website] : $rootCatId;

            if ($vendorRootCategoryId > 0) {
                $rootCatId = $vendorRootCategoryId;
                $vendorRootCategory = Mage::getModel("catalog/category")->load($vendorRootCategoryId);
                $tree = "<ul>";
                $tree .= '<li id="' . $vendorRootCategory->getId() . '" data-name="' . $vendorRootCategory->getName() . '" data-url="' . $vendorRootCategory->getUrl() . '">' . $vendorRootCategory->getName() . "";
                $tree .= $this->getTreeCategories($rootCatId);
                $tree .= "</ul>";
                return $tree;
            }
        }
        return $this->getTreeCategories($rootCatId, true);
    }

    /*
     *
     */
    public function getTreeCategories($parentId, $includeRootCategory = false)
    {
        $html = "";
        $allCats = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('is_active', '1')
            ->addAttributeToFilter('include_in_menu', '1')
            ->addAttributeToFilter('parent_id', array('eq' => $parentId));

        if ($includeRootCategory) {
            $rootCategory = Mage::getModel("catalog/category")->load($parentId);
            $html = "<ul>";
            $html .= '<li id="' . $rootCategory->getId() . '" data-name="' . $rootCategory->getName() . '" data-url="' . $rootCategory->getUrl() . '">' . $rootCategory->getName() . "";
        }


        $html .= '<ul>';

        foreach ($allCats as $category) {
            $html .= '<li id="' . $category->getId() . '" data-name="' . $category->getName() . '" data-url="' . $category->getUrl() . '">' . $category->getName() . "";
            $subcats = $category->getChildren();
            if ($subcats != '') {
                $html .= $this->getTreeCategories($category->getId());
            }
            $html .= '</li>';
        }

        if ($includeRootCategory) {

            $html .= "</ul>";
        }

        $html .= '</ul>';


        return $html;
    }


    /**
     * return campaign ids from request
     *
     * @return array
     */
    public function getCampaignIdsFromUrl()
    {
        if (is_null($this->_campaignIds)) {
            $params = Mage::app()->getRequest()->getParams();
            $this->_campaignIds = $this->parseCampaignIds($params);
        }
        return $this->_campaignIds;
    }

    /**
     * parse campaign ids from url params
     *
     * @param array $params
     * @return array
     */

    public function parseCampaignIds($params)
    {
        $id = array();
        if (!empty($params['fq'])) {
            if (!empty($params['fq']['campaign_regular_id'])) {
                if (is_array($params['fq']['campaign_regular_id'])) {
                    $id += $params['fq']['campaign_regular_id'];
                } else {
                    $id[] = $params['fq']['campaign_regular_id'];
                }
            }
            if (!empty($params['fq']['campaign_info_id'])) {
                if (is_array($params['fq']['campaign_info_id'])) {
                    $id += $params['fq']['campaign_info_id'];
                } else {
                    $id[] = $params['fq']['campaign_info_id'];
                }
            }
        }
        return array_unique($id);
    }
}