<?php

class Zolago_Campaign_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function getBannerTypesSlots()
    {
        return Mage::getSingleton('zolagobanner/banner_type')->toOptionHash();
    }
    public function getVendor(){
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


    public function getVendorCategoriesList()
    {
        $categories = array();
        //1. Get vendor root category
        // /udropshipadmin/adminhtml_vendor/edit/ -> Preferences -> Root categories -> Category ID
        $rootCatID = Mage::app()->getStore()->getRootCategoryId();

        $customVendorVars = Mage::helper('core')->jsonDecode($this->getVendor()->getCustomVarsCombined());

        $vendorRootCategory = (isset($customVendorVars['root_category']) && !empty($customVendorVars['root_category']) && (int)reset($customVendorVars['root_category']) > 0) ?
            (int)reset($customVendorVars['root_category']) :
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
                    $categories[] = array(
                        'id' => $collectionItem->getId(),
                        'name' => $collectionItem->getName(),
                        'edit_url' => '/campaign/placement_category/index/category/' . $collectionItem->getId());
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
    public function getCategoriesTree($vendorId = null){
        $rootCatId = Mage::app()->getStore()->getRootCategoryId();
        if(!empty($vendorId)){
            $vendor = Mage::getModel("udropship/vendor")->load($vendorId);

            $customVendorVars = Mage::helper('core')->jsonDecode($vendor->getCustomVarsCombined());

            $vendorRootCategory = (isset($customVendorVars['root_category']) && !empty($customVendorVars['root_category']) && (int)reset($customVendorVars['root_category']) > 0) ?
                (int)reset($customVendorVars['root_category']) :
                $rootCatId;

            if ($vendorRootCategory > 0) {
                $rootCatId = $vendorRootCategory;
            }
        }
        return  $this->getTreeCategories($rootCatId, false);
    }

    public function getTreeCategories($parentId)
    {
        $html = "";
        $allCats = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('is_active', '1')
            ->addAttributeToFilter('include_in_menu', '1')
            ->addAttributeToFilter('parent_id', array('eq' => $parentId));


        $html .= '<ul>';

        foreach ($allCats as $category) {
            $html .= '<li id="'.$category->getId().'" data-name="' . $category->getName() . '">' . $category->getName() . "";
            $subcats = $category->getChildren();
            if ($subcats != '') {
                $html .= $this->getTreeCategories($category->getId(), true);
            }
            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }

}