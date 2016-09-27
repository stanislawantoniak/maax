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
                resize(262, 335);
            } catch (Exception $ex) {
                Mage::logException($ex);
            }

            $model->setData("listing_resized_image_url", $return . ""); // Cast to string
        }

        return $model->getData("listing_resized_image_url");
    }

}