<?php
class Zolago_Sizetable_Block_Dropship_Sizetable_Assign extends Mage_Core_Block_Template {
	protected $asid; //attribute set id
	protected $brands;
	protected $attributeSets;
	protected $session;

	public function __construct() {
		$this->asid = Mage::getModel('catalog/product')->getResource()->getEntityType()->getId();
		$this->session = Mage::getSingleton('udropship/session');
	}

	protected function _beforeToHtml() {
		$this->getGrid();
		return parent::_beforeToHtml();
	}

	public function getGridJsObjectName() {
		return $this->getGrid()->getJsObjectName();
	}

	/**
	 * @return Zolago_Sizetable_Block_Dropship_Sizetable_Assign_Grid
	 */
	public function getGrid() {
		if(!$this->getData("grid")){
			$design = Mage::getDesign();
			$design->setArea("adminhtml");
			$block = $this->getLayout()->
			createBlock("zolagosizetable/dropship_sizetable_assign_grid");
			$block->setParentBlock($this);
			$this->setGridHtml($block->toHtml());
			$this->setData("grid", $block);
			$design->setArea("frontend");
		}
		return $this->getData("grid");
	}

	public function getBrands() {
		return Mage::helper("zolagosizetable")->getBrands($this->getVendorId());
	}

	public function getAttributeSets() {
		if(!$this->attributeSets) {
			$collection = Mage::getModel('eav/entity_attribute_set')->getCollection()
				->setEntityTypeFilter($this->asid)
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
		/** @var Zolago_Sizetable_Model_Resource_Sizetable_Collection $collection */
		$collection = Mage::getModel("zolagosizetable/sizetable")->getResourceCollection()->addOrder("name","ASC");
		$collection->addVendorFilter($this->getVendorId());
		$out = array(''=>'');
		foreach($collection as $sizetable) {
			$out[$sizetable['sizetable_id']] = $sizetable->getName();
		}
		return $out;
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

	public function getFormkey() {
		return '<input name="form_key" type="hidden" value="'.Mage::getSingleton('core/session')->getFormKey().'">';
	}
}