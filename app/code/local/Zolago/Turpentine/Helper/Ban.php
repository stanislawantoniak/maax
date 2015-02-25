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
            $urlPatternsFin[] = "##_NEVER_MATCH_##";
        }

        return $urlPatternsFin;
    }
}
