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
        $storeId = Mage::app()->getStore()->getId();
        $rootId = Mage::app()->getStore($storeId)->getRootCategoryId();

        $customVendorVars = Mage::helper('core')->jsonDecode($this->getVendor()->getCustomVarsCombined());
        $vendorRootCategory = (isset($customVendorVars['root_category']) && !empty($customVendorVars['root_category'])) ?
            (int)reset($customVendorVars['root_category']) :
            $rootId;

        if($vendorRootCategory > 0){
            $categoryModel = Mage::getModel('catalog/category');
            $categoryObj = $categoryModel->load($vendorRootCategory);
            $categoryName = $categoryObj->getName();

            $categories[$vendorRootCategory] = array('id' => $vendorRootCategory, 'name' => $categoryName, 'edit_url' => '/campaign/placement_category/index/category/' . $vendorRootCategory);
        }

        return $categories;
    }

}