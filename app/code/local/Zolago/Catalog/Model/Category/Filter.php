<?php
class Zolago_Catalog_Model_Category_Filter extends Mage_Core_Model_Abstract{
    

	protected function _construct() {
        $this->_init('zolagocatalog/category_filter');
    }
    protected $_eventPrefix = "zolago_catalog_category_filter";
    
	/**
	 * @return Mage_Eav_Model_Entity_Attribute_Abstract|false
	 */
	public function getAttribute() {
		if(!$this->hasData("attribute")){
			if($this->getAttributeId()){
				$eav = Mage::getSingleton('eav/config');
				/* @var $eav Mage_Eav_Model_Config */
				$this->setData("attribute", $eav->getAttribute(
						Mage_Catalog_Model_Product::ENTITY, $this->getAttributeId()
				));
			}
		}
		return $this->getData("attribute");
	}
  
    
}

