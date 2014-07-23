<?php
class Zolago_Campaign_Model_Campaign_Pricesource{
	
	/**
	 * @return array
	 */
	public function toOptionHash() {
		$out = array();
		foreach($this->getAllOptions() as $opt){
			$out[$opt['value']] = $opt['label'];
		}
		return $out;
	}
	
	/**
	 * @return array
	 */
	public function getAllOptions() {
		return $this->getPriceTypeAttribute()->getSource()->getAllOptions(false);
	}
	
	/**
	 * @return Mage_Catalog_Model_Resource_Eav_Attribute
	 */
	public function getPriceTypeAttribute() {
		return Mage::getSingleton('eav/config')->getAttribute(
				Mage_Catalog_Model_Product::ENTITY, 
				Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_PRICE_TYPE_CODE
		);
	}
    
}