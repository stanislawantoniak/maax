<?php
class Zolago_Mapper_Model_Mapper_Condition_Product extends Mage_CatalogRule_Model_Rule_Condition_Product {
    
	protected $_excludedAttributes = array("attribute_set_id", "category_ids");
	protected $_rule;
	
	public function __construct($ruleModel) {
		if(! $ruleModel instanceof Mage_Rule_Model_Rule){
			throw new Exception("Specify rule model");
		}
		$this->_rule = $ruleModel;
		parent::__construct();
	}
	
	public function loadAttributeOptions() {
		// Get attributes by attribute set and condiitons;
		
        $productAttributes = $this->getAttribuesCollection();
		
        $attributes = array();
        foreach ($productAttributes as $attribute) {
            $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }
		// $attributes['category_ids'] = 'category_ids'; 
        asort($attributes);
        $this->setAttributeOption($attributes);
        return $this;
    }
   
	public function getAttribuesCollection($attributeSetId=null){
		
		if(!$this->getData("attributes_collection")){
			
			if(!$attributeSetId){
				if($this->_rule && $this->_rule->getAttributeSetId()){
					$attributeSetId = $this->_rule->getAttributeSetId();
				}
				if(!$attributeSetId){
					throw new Exception("No attribute set id specified");
				}
			}
			
			$productAttributes = Mage::getResourceModel('catalog/product_attribute_collection');
			/* @var $productAttributes Mage_Catalog_Model_Resource_Product_Attribute_Collection */

			// Join attribute set info
			$select = $productAttributes->getSelect();

			$select->join(
					array("entity_attribute"=>$productAttributes->getTable("eav/entity_attribute")),
					"entity_attribute.attribute_id=main_table.attribute_id".
					" AND ".
					"entity_attribute.entity_type_id=main_table.entity_type_id",
					array("attribute_set_id")
			);
			// Attr set id
			$productAttributes->addFieldToFilter("attribute_set_id", 
					$attributeSetId ? $attributeSetId : -1);		
			// Visable attribute
			$productAttributes->addFieldToFilter("is_visible", 1);
			// Mapped attribute
			//$productAttributes->addFieldToFilter("is_mappable", 1);
			// Exclude attributes
			$productAttributes->addFieldToFilter("attribute_code", 
					array("nin"=>$this->_getExcludedAttributes()));
			
			$this->setData("attributes_collection", $productAttributes);
		}
		
		return $this->getData("attributes_collection");
		
	}
	
	
	public function _getExcludedAttributes() {
		return $this->_excludedAttributes;
	}
    
}
