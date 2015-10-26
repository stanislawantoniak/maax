<?php
/**
 * Front menu block class
 */
class Zolago_Modago_Block_Page_Html_Header_Menu extends Mage_Core_Block_Template {
    
    /**
     * Prepare html
     */
    protected function _toHtml() {
        /** @var $this Mage_Core_Block_Template */
        /** @var Zolago_Dropship_Model_Vendor $vendor */
        $vendor = Mage::helper('umicrosite')->getCurrentVendor();
        /** @var Zolago_Solrsearch_Helper_Data $helper */
        $helperSolrSearch = Mage::helper("zolagosolrsearch");
        $category = $helperSolrSearch->getCurrentCategory();

        if ($vendor && $vendor->isBrandshop()) {
            //when vendor(brandshop) show navigation only on the cms categories
            if(!($category && $category->getDisplayMode() == Mage_Catalog_Model_Category::DM_PAGE)) {
                return; // no menu
            }
        }
        return parent::_toHtml();
    }
    
    /**
     * prepare javascript
     */

    protected function getJavascript() {
        $request = Mage::app()->getRequest();
        $module  = $request->getModuleName();
        $name    = $request->getControllerName();
        $action  = $request->getActionName();
        $script = "<script>Mall.Navigation.currentCategoryId.push('".(Mage::registry('current_category') ? Mage::registry('current_category')->getId() : $module."/".$name."/".$action)."');</script>";

        return $script;
    }
    
    protected function getCategoryMobile() {
        $lambda = function() {
            return $this->getChildHtml('category.main.menu.mobile');
        };
        $currentCategory = Mage::registry('current_category');
        if ($currentCategory) {
            return Mage::helper('zolagocommon')->getCache('category_main_menu_mobile_'.$currentCategory->getId(),self::CACHE_GROUP,$lambda,array());
        }
        return '';
    }
}