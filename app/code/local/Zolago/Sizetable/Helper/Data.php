<?php
class Zolago_Sizetable_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getSizetableCMS($vendor_id, $store_id, $attribute_set_id, $brand_id,$product = null)
    {
        /** @var Zolago_Sizetable_Model_Resource_Sizetable $model */
        if (!$out = $this->getSizetableAttributeCms($product,$store_id)) {
            $model = Mage::getModel('zolagosizetable/resource_sizetable');
            $out = $model->getSizetableCMS($vendor_id, $store_id, $attribute_set_id, $brand_id);
        }
        return $out;
    }

    /**
     * brand id
     * @return int
     */
    public function getBrandId() {
        Mage::helper('zolagocatalog')->getBrandId();
    }

    public function getBrands($vendor,$storeId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID,$firstEmpty = false) {
        if($vendor instanceof ZolagoOs_OmniChannel_Model_Vendor) {
            $vendor = $vendor->getId();
        }
        $mid = Mage::getSingleton("eav/config")->getAttribute(Mage_Catalog_Model_Product::ENTITY,'manufacturer')->getAttributeId();
        $collection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                      ->setAttributeFilter($mid)
                      ->setStoreFilter($storeId)
                      ->join(array('table_alias'=>'zolagosizetable/vendor_brand'), 'main_table.option_id = table_alias.brand_id','')
                      ->addFieldToFilter("table_alias.vendor_id",$vendor);
        $brands = $firstEmpty ? array('' => '') : array();
        foreach($collection as $k=>$brand) {
            $brands[$k] = $brand->getValue();
        }
        return $brands;
    }
    public function getSizetableAttributeCMS($product,$store_id) {
        if (empty($product)) {
            return null;
        }
        if (!$sizetable_id = $product->getData('custom_sizetable')) {
            return null;
        }
        $sizetable = Mage::getModel('zolagosizetable/sizetable')->load($sizetable_id);
        $sizetable->getScopes();
        $scopes = $sizetable->getSizetable();
        if (empty($scopes[$store_id])) {
            $out = $sizetable->getDefaultValue();
        } else {
            $out = $scopes[$store_id];
        }
        return $out;

    }
}