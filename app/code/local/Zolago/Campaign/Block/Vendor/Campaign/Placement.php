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
            $catList = $this->getCategoriesDisplayModePage($vendorRootCategory);
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


    public function getCategoriesDisplayModePage($parentId)
    {
        $allCats = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('is_active', '1')
            ->addAttributeToFilter('display_mode', Mage_Catalog_Model_Category::DM_PAGE)
            ->addAttributeToFilter('include_in_menu', '1')
            ->addAttributeToFilter('parent_id', array('eq' => $parentId))
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