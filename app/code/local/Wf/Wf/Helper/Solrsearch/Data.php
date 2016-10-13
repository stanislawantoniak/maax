<?php

/**
 * Class Wf_Wf_Helper_Solrsearch_Data
 */
class Wf_Wf_Helper_Solrsearch_Data extends Zolago_Solrsearch_Helper_Data {

    /**
     * @param Zolago_Solrsearch_Model_Catalog_Product_List $listModel
     * @return array
     */
    public function prepareAjaxProducts(Zolago_Solrsearch_Model_Catalog_Product_List $listModel) {
        // Create product list
        $products = array();

        /** @var Zolago_Common_Helper_Data $hlp */
        $hlp = Mage::helper('zolagocommon');

        foreach ($listModel->getCollection() as $product) {
            /* @var $product Zolago_Solrsearch_Model_Catalog_Product */
            $_product[0] = $product->getId();
            $_product[1] = $product->getName();
//			$_product[2] = $this->_prepareCurrentUrl($product->getCurrentUrl());
            $_product[2] = $product->getCurrentUrl();
            $_product[3] = floatval($product->getStrikeoutPrice());
            $_product[4] = floatval($product->getFinalPrice());
            $_product[5] = $product->getWishlistCount();
            $_product[6] = $product->getInMyWishlist();
            $_product[7] = $this->_prepareListingResizedImageUrl($product->getListingResizedImageUrl());
            $imageSizes = $product->getListingResizedImageInfo();
            $_product[8] = !is_null($imageSizes) ? 100 * round(($imageSizes["height"] / $imageSizes["width"]),2) : 1;
            $_product[9] = $this->_prepareManufacturerLogoUrl($product->getManufacturerLogoUrl());
            $_product[10]= $product->getSku();
            $_product[11]= $hlp->getSkuvFromSku($product->getSku(),$product->getUdropshipVendor());
            $_product[12]= (int)$product->getProductFlag();
            $_product[13]= (int)$product->getIsNew();
            $_product[14]= (int)$product->getIsBestseller();
            $_product[15] = Mage::helper('zolagocatalog/product')->getProductBestFlag($product);
            $products[] = $_product;
        }
        return $products;
    }


    /**
     * @return array
     */
    public function getSolrDocFileds() {
        return array_keys($this->_solrToMageMap);
    }

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
                resize(262, 335);
            } catch (Exception $ex) {
                Mage::logException($ex);
            }

            $model->setData("listing_resized_image_url", $return . ""); // Cast to string
        }

        return $model->getData("listing_resized_image_url");
    }
}