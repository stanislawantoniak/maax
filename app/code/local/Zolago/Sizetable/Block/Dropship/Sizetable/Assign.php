<?php
class Zolago_Sizetable_Block_Dropship_Sizetable_Assign extends Mage_Core_Block_Template {
	protected $mid; //manufacturer attribute id
	protected $brands;
	protected $attributeSets;
	protected $session;

	public function __construct() {
		$this->mid = Mage::getSingleton("eav/config")->getAttribute('catalog_product','manufacturer')->getAttributeId();
		$this->session = Mage::getSingleton('udropship/session');
	}

	public function getAssigns() {
		return array(
			0 => array(

			)
		);
	}

	public function getBrands() {
		if(!$this->brands) {
			$collection = Mage::getResourceModel('eav/entity_attribute_option_collection')
				->setAttributeFilter($this->mid)
				->setStoreFilter(0, false)
				->join(array('table_alias'=>'zolagosizetable/vendor_brand'), 'main_table.option_id = table_alias.brand_id','')
				->addFieldToFilter("table_alias.vendor_id",$this->getVendorId());
			$brands = array('' => '');
			foreach($collection as $k=>$brand) {
				$brands[$k] = $brand->getValue();
			}
			$this->brands = $brands;
		}
		return $this->brands;
	}

	public function getAttributeSets() {
		if(!$this->attributeSets) {
			$collection = Mage::getModel('eav/entity_attribute_set')->getCollection()
				->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getEntityType()->getId())
				->join(array('table_alias'=>'zolagosizetable/vendor_attribute_set'),'main_table.attribute_set_id = table_alias.attribute_set_id','')
				->addFieldToFilter("table_alias.vendor_id",$this->getVendorId());
			$attrs = array('' => '');
			foreach($collection as $attr) {
				$attrs[$attr->getAttributeSetId()] = $attr->getAttributeSetName();
			}
			$this->attributeSets = $attrs;
		}
		return $this->attributeSets;
	}

	public function getSizeTables() {
		//$model = Mage::getModel("zolagosizetable/sizetable");
	}

	public function getBrandName($bid) {
		foreach($this->getBrands() as $brand) {
			if($brand['value'] == $bid)
				return $brand['label'];
		}
		return false;
	}

	public function getSession() {
		return $this->session;
	}

	public function getVendorId() {
		return $this->session->getVendor()->getVendorId();
	}

	public function getAction() {
		return $this->getUrl("udropship/sizetable/assign");
	}
}