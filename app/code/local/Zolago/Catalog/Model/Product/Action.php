<?php
class Zolago_Catalog_Model_Product_Action extends Mage_Catalog_Model_Product_Action {
	public function updateAttributes($productIds, $attrData, $storeId){
		$ret =  parent::updateAttributes($productIds, $attrData, $storeId);
		// Add after event
        Mage::dispatchEvent('catalog_product_attribute_update_after', array(
            'attributes_data' => $attrData,
            'product_ids'   => $productIds,
            'store_id'      => $storeId
        ));
		return $ret;
	}
}
