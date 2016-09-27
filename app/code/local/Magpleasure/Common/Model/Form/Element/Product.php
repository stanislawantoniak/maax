<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Model_Form_Element_Product extends Varien_Data_Form_Element_Text
{
    /**
     * Common Helper
     *
     * @return Magpleasure_Common_Helper_Data
     */
    protected function _getCommonHelper()
    {
        return Mage::helper('magpleasure');
    }

    protected function _getProductName($productId)
    {
        /** @var Mage_Catalog_Model_Product $cusotmer  */
        $product = Mage::getModel('catalog/product')->load($productId);
        return $product->getName();
    }

    /**
     * Retrives element html
     * @return string
     */
    public function getElementHtml()
    {
        $productId = $this->getValue();
        $html = "";
        if ($productId){
            $url = Mage::helper('adminhtml')->getUrl('adminhtml/catalog_product/edit', array('id'=>$productId));
            $html .= "<a href=\"{$url}\" target=\"_blank\">".$this->_getProductName($productId)."</a>";
        } else {
            $html .= $this->_getCommonHelper()->__('Product not found');
        }
        return $html;
    }
}