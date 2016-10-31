<?php

/**
 * Class StandAlone_Ipson_Helper_Solrsearch_Data
 */
class StandAlone_Ipson_Helper_Solrsearch_Data extends Zolago_Solrsearch_Helper_Data {

    /**
     * @param Mage_Catalog_Model_Product $model
     * @return string | empty_string
     */
    public function getListingResizedImageUrl(Mage_Catalog_Model_Product $model) {

        if (!$model->hasData("listing_resized_image_url")) {

            $return = null;
            try {
                $return = Mage::helper('catalog/image')->
                init($model, 'image')->
                keepAspectRatio(true)->
                constrainOnly(true)->
                keepFrame(true)->
                resize(462, 535);
            } catch (Exception $ex) {
                Mage::logException($ex);
            }

            $model->setData("listing_resized_image_url", $return . ""); // Cast to string
        }

        return $model->getData("listing_resized_image_url");
    }



    /**
     * @param Zolago_Solrsearch_Model_Catalog_Product_List $listModel
     * @return array
     */
    public function prepareAjaxProducts(Zolago_Solrsearch_Model_Catalog_Product_List $listModel) {
        // Create product list
        $products = array();

        /** @var Zolago_Common_Helper_Data $hlp */
        $hlp = Mage::helper('zolagocommon');

        /** @var Zolago_Catalog_Helper_Product $hlp */
        $productHelper = Mage::helper('zolagocatalog/product');

        /* @var $stockItemModel Mage_CatalogInventory_Model_Stock_Item */
        $stockItemModel = Mage::getModel('cataloginventory/stock_item');

        foreach ($listModel->getCollection() as $product) {
            /* @var $product Zolago_Solrsearch_Model_Catalog_Product */

            $_product[0] = $product->getId();
            $_product[1] = $product->getName();
            $_product[2] = $product->getCurrentUrl();
            $_product[3] = floatval($product->getStrikeoutPrice());
            $_product[4] = floatval($product->getFinalPrice());
            $_product[5] = $product->getWishlistCount();
            $_product[6] = $product->getInMyWishlist();
            $_product[7] = $this->_prepareListingResizedImageUrl($product->getListingResizedImageUrl());
            $imageSizes = $product->getListingResizedImageInfo();
            $_product[8] = !is_null($imageSizes) ? 100 * round(($imageSizes["height"] / $imageSizes["width"]),2) : 1;
            //$_product[9] = $this->_prepareManufacturerLogoUrl($product->getManufacturerLogoUrl());
            $_product[10]= $product->getSku();
            $_product[11]= $hlp->getSkuvFromSku($product->getSku(),$product->getUdropshipVendor());

            $_product[12]= (int)$product->getProductFlag();
            $_product[13]= (int)$product->getIsNew();
            $_product[14]= (int)$product->getIsBestseller();

            $_product[15] = $productHelper->getProductBestFlag($product);
            $_product[16]= $product->getTypeId();

            /* Stock info */
            $stockItem = $stockItemModel->loadByProduct($product);
            if(!!$stockItem->getBackorders()){
                $_product[17]= (int) $stockItem->getMaxSaleQty();
            } else {
                $_product[17]= (int) $stockItem->getQty();
            }
            $_product[18]= (int) $stockItem->getMinSaleQty();
            /* Stock info */

            $products[] = $_product;
        }

        return $products;
    }

}