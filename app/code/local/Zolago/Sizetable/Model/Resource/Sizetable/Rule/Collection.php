<?php
class Zolago_Sizetable_Model_Resource_Sizetable_Rule_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract {
  
    protected function _construct() {
        parent::_construct();
        $this->_init('zolagosizetable/sizetable_rule');
    }
	
	/**
	 * @param ZolagoOs_OmniChannel_Model_Vendor|int $vendor
	 * @return Zolago_Sizetable_Model_Resource_Sizetable_Rule_Collection
	 */
	public function addVendorFilter($vendor){
		if($vendor instanceof ZolagoOs_OmniChannel_Model_Vendor){
			$vendor = $vendor->getId();
		}
		$this->addFieldToFilter(
				"main_table.vendor_id",
				$vendor
		);
		return $this;
	}

	public function joinSizetables() {
		$this->join(
			array("sizetable"=>"sizetable"),
			"main_table.sizetable_id = sizetable.sizetable_id",
			"sizetable.name");
		return $this;
	}

	public function joinSizetableBrands() {
		$storeId = Mage::app()->getStore()->getStoreId();
		$this->getSelect()
			->joinLeft(array("brand"=>$this->getTableName('eav/attribute_option_value')),
				"main_table.brand_id = brand.option_id",
				"brand.value"
			)
			->where("main_table.brand_id IS NULL OR (brand.store_id = $storeId OR brand.store_id = 0)");
		return $this;
	}

	public function joinSizetableAttributes() {
		$this->getSelect()
		->joinLeft(array("attribute"=>$this->getTableName('eav/attribute_set')),
			"main_table.attribute_set_id = attribute.attribute_set_id",
			"attribute.attribute_set_name"
		);
		return $this;
	}

	public function getTableName($model) {
		return Mage::getSingleton('core/resource')->getTableName($model);
	}

}
