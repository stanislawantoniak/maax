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
            if($category && $category->getDisplayMode() == Mage_Catalog_Model_Category::DM_PAGE) {
                echo $this->_prepareBlock(); 
                $this->getLayout()->createBlock('cms/block')->setBlockId('navigation-main-wrapper')->toHtml();
            }
        } else {
            echo $this->_prepareBlock();
        }
    }
    
    /**
     * create block from cache
     */

    protected function _prepareBlock() {
        $lambda = function ($foo) {
            return Mage::app()->getLayout()->createBlock('cms/block')->setBlockId('navigation-main-wrapper')->toHtml();
        };
        $html = Mage::helper('zolagocommon')->getCache('html_header_navigation',self::CACHE_GROUP,$lambda,array());
        return $html;
    }
}