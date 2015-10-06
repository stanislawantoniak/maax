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
        $key = 'html_header_navigation';
        if (!($html = $this->_getApp()->loadCache($key)) || 
            !$this->_getApp()->useCache(self::CACHE_GROUP)) {
            $html = $this->getLayout()->createBlock('cms/block')->setBlockId('navigation-main-wrapper')->toHtml();
            if ($this->_getApp()->useCache(self::CACHE_GROUP)) {
                $this->_getApp()->saveCache($html,$key,array(self::CACHE_GROUP),Zolago_Common_Block_Page_Html_Head::BLOCK_CACHE_TTL);
            }
        }
        return $html;
    }
}