<?php
/**
 * Solr helper
 *
 *
 * @category    Zolago
 * @package     Zolago_Solrsearch
 */
class Zolago_Solrsearch_Helper_Data extends Mage_Core_Helper_Abstract
{
    const ZOLAGO_USE_IN_SEARCH_CONTEXT = 'use_in_search_context';

    public static function extract_domain($domain)
    {
        if (preg_match("/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i", $domain, $matches)) {
            return $matches['domain'];
        } else {
            return $domain;
        }
    }

    public static function extract_subdomains($domain)
    {
        $subdomains = $domain;
        $domain = self::extract_domain($subdomains);

        $subdomains = rtrim(strstr($subdomains, $domain, true), '.');

        return $subdomains;
    }

    public static function getTreeCategoriesSelect($parentId, $level, $cat)
    {
        if ($level > 5) {
            return '';
        } // Make sure not to have an endless recursion
        $allCats = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('is_active', '1')
            ->addAttributeToFilter( self::ZOLAGO_USE_IN_SEARCH_CONTEXT , array('eq' => 1))
            ->addAttributeToFilter('include_in_menu', '1')
            ->addAttributeToFilter('parent_id', array('eq' => $parentId));

        $html = '';
        foreach ($allCats as $category) {
            $selected = '';
            if($category->getId() == $cat){
                $selected = ' selected="selected" ';
            }
            $html .= '<option value="' . $category->getId() . '" '. $selected.'>' . str_repeat("&nbsp;", 4 * $level) . Mage::helper(
                    'catalog'
                )->__($category->getName()) . "</option>";
            $subcats = $category->getChildren();
            if ($subcats != '') {
                $html .= self::getTreeCategoriesSelect($category->getId(), $level + 1,$cat);
            }
        }
        return $html;
    }

    public static function getTreeCategories($parentId, $isChild)
    {

        $cats = array();
        $allCats = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('is_active', '1')
            ->addAttributeToFilter(self::ZOLAGO_USE_IN_SEARCH_CONTEXT, array('eq' => 1))
            ->addAttributeToFilter('include_in_menu', '1')
            ->addAttributeToFilter('parent_id', array('eq' => $parentId));

        foreach ($allCats as $category) {
            $cats[$category->getId()]['id'] = $category->getId();
            $cats[$category->getId()]['name'] = Mage::helper('catalog')->__($category->getName());
            $subCats = $category->getChildren();
            if (strlen($subCats)>0) {
                $cats[$category->getId()]['sub'] = self::getTreeCategories($category->getId(), true);
            }
        }

        return $cats;

    }

    public function getContextUrl()
    {
        $uri = $this->_getUrl(
            'zolagosolrsearch/context',
            array(
                 '_secure' => Mage::app()->getFrontController()->getRequest()->isSecure(),
                 '_nosid'  => true,
            )
        );

        return trim($uri, '/');
    }

    /**
     * Construct context search selector
     * @return string
     */
    public function getContextSelectorHtml()
    {
        $filterQuery = (array)Mage::getSingleton('core/session')->getSolrFilterQuery();

        $selectedContext = 0;
        if (isset($filterQuery['category_id']) && isset($filterQuery['category_id'][0])) {
            $selectedContext = $filterQuery['category_id'][0];
        }

        $rootCatId = Mage::app()->getStore()->getRootCategoryId();

        $catListHtmlSelect = '<select name="scat">'
            . '<option value="0">' . Mage::helper('catalog')->__('Everywhere') . '</option>';

        $catListHtmlSelect .= self::getTreeCategoriesSelect($rootCatId, 0, $selectedContext);

        if (Mage::registry('current_category')) {
            $catListHtmlSelect
                .= '<option value="' . Mage::registry('current_category')->getId() . '">'
                . Mage::helper('catalog')->__('This category')
                . '</option>';
        }
        $catListHtmlSelect .= "</select>";

        return $catListHtmlSelect;
    }


    /**
     * Set Vendor Search Context
     */
    public function setVendorSearchContext(){
        $h = Mage::helper('umicrosite');
        if($h->getCurrentVendor()){
            $vendorUrlKey = $h->getCurrentVendor()->getUrlKey();
            $filterQuery = array('udropship_vendor_text' => $vendorUrlKey);
            Mage::getSingleton('core/session')->setSolrFilterQuery(array_unique($filterQuery));
        }
    }
}