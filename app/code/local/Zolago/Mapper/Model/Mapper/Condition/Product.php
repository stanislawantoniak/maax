<?php
class Zolago_Mapper_Model_Mapper_Condition_Product extends Mage_CatalogRule_Model_Rule_Condition_Product {
    
	protected $_excludedAttributes = array("attribute_set_id", "category_ids");


	public function loadAttributeOptions() {
		$attributeSetId = $this->getAttributeSetId();
        $productAttributes = Mage::getResourceModel('catalog/product_attribute_collection');
		/* @var $attrCollection Mage_Catalog_Model_Resource_Product_Attribute_Collection */

		$productAttributes->addFieldToFilter("is_visible", 1);
		//$productAttributes->addFieldToFilter("is_mappable", 1);
		$productAttributes->addFieldToFilter("attribute_code", 
				array("nin"=>$this->_getExcludedAttributes()));
		
        $attributes = array();
        foreach ($productAttributes as $attribute) {
            $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }
		// $attributes['category_ids'] = 'category_ids'; 
        asort($attributes);
        $this->setAttributeOption($attributes);
        return $this;
    }
    
    public function getAttributeElement() {
		$element = parent::getAttributeElement();
        //$element->setShowAsText(true);
		return $element;
    }
	
	public function _getExcludedAttributes() {
		return $this->_excludedAttributes;
	}
    
}
