<?php
/**
 * Front menu block class
 */
class Zolago_Modago_Block_Page_Html_Header_Menu extends Mage_Core_Block_Template {


    /**
     * Check if current url is url for home page
     *
     * @return true
     */
    public function getIsHomePage()
    {
        return $this->getUrl('') == $this->getUrl('*/*/*',
            array(
                //'_current'=>true,       //_current	bool	Uses the current module, controller, action and parameters
                '_use_rewrite' => true,
                "_no_vendor" => TRUE      // home page but not vendor home
            )
        );
    }

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
     * Usages: for highlighted navigation desktop element in cms block (navigation-main-desktop)
     * put in anchor element property data-catids
     * for example data-catids="mypromotions"
     * if url look like: http://www.example.com/mypromotions
     */
    protected function getJavascript() {
        $request = Mage::app()->getRequest();
        $module  = $request->getModuleName();
        $name    = $request->getControllerName();
        $action  = $request->getActionName();
        if (Mage::registry('current_category')) {
            $script = "<script>Mall.Navigation.currentCategoryId.push('".Mage::registry('current_category')->getId()."');</script>";
        } else {
            $script  = "<script>";
            $script .= "Mall.Navigation.currentCategoryId.push('".$module."/".$name."/".$action."');\n";// mypromotions/index/index
            $script .= "Mall.Navigation.currentCategoryId.push('".$module."/".$name."');\n";// mypromotions/index
            $script .= "Mall.Navigation.currentCategoryId.push('".$module."');\n";// mypromotions <- easiest for write in cms block
            $script .= "</script>";
        }

        return $script;
    }
    
    protected function getCategoryMobile() {
        $lambda = function() {
            return $this->getChildHtml('category.main.menu.mobile');
        };
        $currentCategory = Mage::registry('current_category');
        if ($currentCategory) {
            /** @var Zolago_Common_Helper_Data $hlp */
            $hlp = Mage::helper('zolagocommon');
            /** @var Zolago_Dropship_Model_Vendor|false $vendor */
            $vendor = Mage::helper("umicrosite")->getCurrentVendor();
            return $hlp->getCache(
                'category_main_menu_mobile_'.$currentCategory->getId().'_'.($vendor ? (int)$vendor->getId() : 0) .'_'. Mage::app()->getStore()->getId()
                ,self::CACHE_GROUP
                ,$lambda
                ,array()
            );
        }
        return '';
    }
}