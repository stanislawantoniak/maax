<?php

class Zolago_Campaign_Block_Vendor_Campaign_Placement extends Mage_Core_Block_Template
{

    protected function _beforeToHtml()
    {
        return parent::_beforeToHtml();
    }

    /**
     * @return Unirgy_Dropship_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('udropship/session');
    }

    public function getVendor(){
        return Mage::getSingleton('udropship/session')->getVendor();
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

                if(in_array($vendorRootCategory, explode("/", $path))){
                    $categories[] = array(
                        'id' => $collectionItem->getId(),
                        'name' => $collectionItem->getName(),
                        'edit_url' => '/campaign/placement_category/index/category/' . $collectionItem->getId());
                }

            }
        }

        return $categories;
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

}