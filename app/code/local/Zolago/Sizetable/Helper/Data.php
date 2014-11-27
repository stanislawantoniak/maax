<?php
class Zolago_Sizetable_Helper_Data extends Mage_Core_Helper_Abstract{

    public function getSizetableCMS($vendor_id, $store_id, $attribute_set_id, $brand_id)
    {
        /** @var Zolago_Sizetable_Model_Resource_Sizetable $model */
        $model = Mage::getModel('zolagosizetable/resource_sizetable');

        return $model->getSizetableCMS($vendor_id, $store_id, $attribute_set_id, $brand_id);
    }

    /**
     * brand id
     * @return int
     */
    public function getBrandId() {
        $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product','manufacturer');
        return $attribute->getId();
    }

	public function getBrands($vendor,$storeId = 0) {
		if($vendor instanceof Unirgy_Dropship_Model_Vendor){
			$vendor = $vendor->getId();
		}
		$mid = Mage::getSingleton("eav/config")->getAttribute('catalog_product','manufacturer')->getAttributeId();
		$collection = Mage::getResourceModel('eav/entity_attribute_option_collection')
			->setAttributeFilter($mid)
			->setStoreFilter($storeId, false)
			->join(array('table_alias'=>'zolagosizetable/vendor_brand'), 'main_table.option_id = table_alias.brand_id','')
			->addFieldToFilter("table_alias.vendor_id",$vendor);
		$brands = array('' => '');
		foreach($collection as $k=>$brand) {
			$brands[$k] = $brand->getValue();
		}
		return $brands;
	}
} 