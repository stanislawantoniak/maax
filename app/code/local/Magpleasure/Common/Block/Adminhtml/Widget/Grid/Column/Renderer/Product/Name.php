<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Block_Adminhtml_Widget_Grid_Column_Renderer_Product_Name
    extends Magpleasure_Common_Block_Adminhtml_Widget_Grid_Column_Renderer_Abstract
{

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        $productId = $this->_getValue($row);
        if ($productId) {
            $html = "";
            /** @var Mage_Catalog_Model_Product $product  */
            $product = Mage::getModel('catalog/product')->load($productId);
            $name = $product->getName();
            $url = $this->getUrl('adminhtml/catalog_product/edit', array('id'=>$productId));
            $html .= "<a href=\"{$url}\" target=\"_blank\">{$name}</a>";
            return $html;
        }
        return parent::render($row);
    }



}