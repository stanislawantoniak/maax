<?php

class Zolago_Turpentine_Helper_Ban extends Nexcessnet_Turpentine_Helper_Ban {

    public function getMultiProductBanRegex( $ids ) {

        $urlPatterns = array();
        foreach($this->getmultiParentProducts($ids) as $product) {
            if ( $product->getUrlKey() ) {
                $urlPatterns[] = $product->getUrlKey();
            }
        }
        if ( empty($urlPatterns) ) {
            $urlPatterns[] = "##_NEVER_MATCH_##";
        }
        $pattern = sprintf( '(?:%s)', implode( '|', $urlPatterns ) );
        return $pattern;

    }


    public function getMultiParentProducts($ids) {

        /** @var Mage_Catalog_Model_Resource_Product_Type_Configurable $resCPTC */
        $resCPTC = Mage::getResourceModel('catalog/product_type_configurable');
        $forBanIds = array_flip($resCPTC->getParentIdsByChild($ids));

        foreach ($ids as $id) {
            if (!isset($forBanIds[$id])) {
                $forBanIds[$id] = true;// any value, no needed
            }
        }

        $resultBanIds = array();
        foreach ($forBanIds as $key => $id) {
            $resultBanIds[] = $key;
        }
        unset($forBanIds);

        $forVarnishColl = Mage::getModel('catalog/product')->getCollection();
        $forVarnishColl->addFieldToFilter('entity_id', array("in" => $resultBanIds));

        return $forVarnishColl;
    }
}
