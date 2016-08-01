<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Block_Adminhtml_Widget_Grid_Column_Renderer_Product_Thumbnail
    extends Magpleasure_Common_Block_Adminhtml_Widget_Grid_Column_Renderer_Abstract
{
    public function getImageUrl(Mage_Catalog_Model_Product $product)
    {
        $imageUrl = $this->_getImageHelper()->init($product, 'image')->__toString();
        return $imageUrl;
    }

    public function getThumbnailUrl(Mage_Catalog_Model_Product $product)
    {
        $imageUrl = $this->_getImageHelper()->init($product, 'image')->resize($this->getWidth(), $this->getHeight())->__toString();
        return $imageUrl;
    }

    /**
     * Return Catalog Product Image helper instance
     *
     * @return Mage_Catalog_Helper_Image
     */
    protected function _getImageHelper()
    {
        return Mage::helper('catalog/image');
    }

    public function getWidth()
    {
        return Mage::getStoreConfig('magpleasure/thumbnail/width');
    }

    public function getHeight()
    {
        return Mage::getStoreConfig('magpleasure/thumbnail/height');
    }

    /**
     * Product
     *
     * @param $productId
     * @return bool|Mage_Catalog_Model_Product
     */
    protected function _getProduct($productId)
    {
        $product = Mage::getModel('catalog/product')->load($productId);
        if ($product->getId()){
            return $product;
        }
        return false;
    }

    public function render(Varien_Object $row)
    {
        $productId = $this->_getValue($row);
        if ($productId && ($product = $this->_getProduct($productId))){
            $height = $this->getHeight();
            $width = $this->getWidth();
            try {
                $url = $this->getImageUrl($product);
                $thumbnailUrl = $this->getThumbnailUrl($product);
                $productName = $this->htmlEscape($product->getName());
                return "
                <div class=\"mp-common-image\" style=\"width: {$width}px; height: {$height}px;\">
                    <a class=\"mp-common-image-link\" href=\"{$url}\" rel=\"lightbox[product_{$productId}]\" >
                        <img width=\"{$width}px\" height=\"{$height}px\" src=\"{$thumbnailUrl}\" alt=\"{$productName}\" />
                    </a>
                </div>
            ";
            } catch (Exception $e) {
                return "";
            }
        }
        return "";
    }



}