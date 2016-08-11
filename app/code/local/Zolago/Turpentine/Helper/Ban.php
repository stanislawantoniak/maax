<?php

class Zolago_Turpentine_Helper_Ban extends Nexcessnet_Turpentine_Helper_Ban {

    /**
     * @param $ids
     * @return array
     */
    public function getMultiProductBanRegex( $ids ) {
        /** @var Mage_Core_Model_Resource_Url_Rewrite_Collection $coll */
        $coll = Mage::getModel('core/url_rewrite')->getCollection();
        $coll->distinct(true);
        $coll->addFieldToSelect('request_path');
        $coll->addFieldToSelect('product_id');
        $coll->addFieldToFilter('product_id', array( 'in' => $ids));        

        $urlPatterns = array();
        foreach ($coll as $row) {
            $rp = $row->getData('request_path');
            $productId = $row->getData('product_id');
            $urlPatterns["$rp"] = true;
            $urlPatterns["catalog/product/view/id/$productId"] = true;
        }
        $urlPatternsFin = array();
        foreach ($urlPatterns as $key => $v) {
            $urlPatternsFin['regex'][] = "(?:$key)";
            $urlPatternsFin['heating'][] = $key;
        }
        unset($urlPatterns);

        if ( empty($urlPatternsFin) ) {
            $urlPatternsFin['regex'] = array();
            $urlPatternsFin['heating'] = array(); 
        }

        return $urlPatternsFin;
    }

    /**
     * @param $ids
     * @param bool $visibility
     * @return Zolago_Catalog_Model_Resource_Product_Collection
     */
    public function prepareCollectionForMultiProductBan( $ids, $visibility = true ) {

        if (is_array($ids)) {
            $_ids = $ids;
        } else {
            $_ids = array($ids);
        }
        $_ids = array_unique($_ids);

        /** @var Zolago_Catalog_Model_Resource_Product_Collection $coll */
        $coll = Mage::getResourceModel('zolagocatalog/product_collection');
        $coll->addFieldToFilter('entity_id', array( 'in' => $_ids));
        if ($visibility) {
            $coll->addAttributeToFilter("visibility", array('in' =>
                array( Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG,
                    Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH,
                    Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)));
        }
        return $coll;
    }
}
