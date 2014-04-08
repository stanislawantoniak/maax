<?php
class Zolago_Catalog_Block_Vendor_Mass_Editor extends Mage_Core_Block_Template {
	
	protected $_availableTypes = array("text", "select", "multiselect", "textarea", "date");
	
	public function getSaveUrl() {
		return $this->getUrl("*/*/saveAjax");
	}
	
    public function getGrid() {
		if($this->getParentBlock() && $this->getParentBlock()->getGrid()){
			return $this->getParentBlock()->getGrid();
		}
		throw new Mage_Exception("No grid specified");
	}
	
	public function getSubmitButtonHtml() {
		$btn = $this->getLayout()->
				createBlock("adminhtml/widget_button")->
				setType("button")->
				setId($this->buildFieldId("submit"))->
				setLabel("Confirm changes");
		return $btn->toHtml();
	}
	
	public function getChangeField(Mage_Adminhtml_Block_Widget_Grid_Column $column) {
		$attribute = $this->_getAttributeFromColumn($column);
		if(!$this->_validateAttribute($attribute)){
			return null;
		}
		$field = $this->getForm()->addField(
				$this->buildFieldId($attribute->getAttributeCode(), "change"), 
				"checkbox", 
				array(
					"label"	=> Mage::helper("catalog")->__("Change"),
					"class"	=> "changer",
					"rel"	=> $this->buildFieldId($attribute->getAttributeCode())
				)
		);
		return $field;
		
	}
	
	public function getField(Mage_Adminhtml_Block_Widget_Grid_Column $column) {
		$attribute = $this->_getAttributeFromColumn($column);
		if(!$this->_validateAttribute($attribute)){
			return null;
		}
		$type = $this->parseInputType($attribute);
		$config = array(
			"name"=>"attributes[".$attribute->getAttributeCode()."]",
			"disabled" => "disabled"
		);
		$field = $this->getForm()->addField(
				$this->buildFieldId($attribute->getAttributeCode()), 
				$type, 
				$this->_processConfig($config, $attribute, $type)
		);
		return $this->_processField($field, $attribute);
	}
	
	protected function _validateAttribute(Mage_Catalog_Model_Resource_Eav_Attribute $attribute = null) {
		return $attribute && 
			   Mage::helper("zolagoeav")->isAttributeEditableNormal($attribute) && 
			   !$attribute->getIsUnique() && 
			   !in_array($attribute->getFrontendInput(), array("media_image", "fixed_tax"));
	}
	
	public function buildFieldId($id, $postfix=null) {
		return $this->getGrid()->getId() . "_" . $id . ($postfix ? "_".$postfix : "");
	}
	
	protected function _processField($field, Mage_Catalog_Model_Resource_Eav_Attribute $attribute) {
		if($field->getExtType()=="multiple") {
			$field->setSize(3);
		}
		return $field;
	}
	
	
	
	protected function _processConfig(array $config, Mage_Catalog_Model_Resource_Eav_Attribute $attribute, $type){
		$extend = array();
		// By Widget Type
		switch ($type) {
			case "textarea":
				$extend['style'] = "height: 60px;";
			break;
			case "select":
				$extend['values'] = $attribute->getSource()->getAllOptions();
			break;
			case "multiselect":
				$extend['values'] = $attribute->getSource()->getAllOptions(false);
			break;
			case "date":
				$extend['format'] = $this->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
				$extend['image'] = $this->getSkinUrl("images/grid-cal.gif");
			break;
			default:
				break;
		}
		// By EAV Atrribute Inout
		switch ($attribute->getFrontendInput()){
			case "price":
				$extend['class'] = "input-text validate-number";
			break;
		}
		
		// Required?
		if($attribute->getIsRequired()){
			$extend['required'] = true;
		}
		return array_merge($config, $extend);
	}
	
	/**
	 * @return Mage_Core_Model_Locale
	 */
	public function getLocale() {
		if(!$this->getData("locale")){
			$locale = Mage::getModel("core/locale");
			/* @var $locale Mage_Core_Model_Locale */
			$locale->setLocale(Mage::getStoreConfig('general/locale/code', $this->getStore()->getId()));
			$this->setData("locale", $locale);
		}
		return $this->getData("locale");
	}
	
	/**
	 * @return Mage_Core_Model_Store
	 */
	public function getStore() {
		return $this->getGrid()->getStore();
	}
	
	public function parseInputType($attribute) {
		$type = $attribute->getFrontendInput();
		if($type == "price"){
			$type = "input";
		}elseif($type == "boolean"){
			$type = "select";
		}
		return $this->_validateType($type) ? $type : "text";
	}
	
	protected function _validateType($type){
		return in_array($type, $this->_availableTypes);
	}
	
	/**
	 * Unused - add smoe extra fields
	 * @todo implement
	 * @param type $column
	 * @return array
	 */
	public function getAdditionalFields($column) {
		$attribute = $this->_getAttributeFromColumn($column);
		if(!$this->_validateAttribute($attribute)){
			return null;
		}
		$code = $attribute->getAttributeCode();
		switch ($attribute->getFrontendInput()) {
			case "multiselect":
				$field = $this->getForm()->addField($code."_mode", "radios", array(
					"name"		=> "attributes_mode"."[".$code."]",
					"values"	=> $this->_getMultiselectModeValues(),
					"value"     => "add",
					"disabled"  => "dsiabled",
					"class"		=> "additional_field",
					"separator" => "<br/>"
				));
				return array($field);
			break;
		}
		return array();
	}
	
	protected function _getMultiselectModeValues(){
		return array(
			array("value"=>"add", "label"=>Mage::helper('zolagocatalog')->__("Add")),
			array("value"=>"set", "label"=> Mage::helper('zolagocatalog')->__("Set"))
		);
	}
	
	/**
	 * @return Varien_Data_Form
	 */
	protected function getForm() {
		if(!$this->getData("form")){
			$this->setData("form", new Varien_Data_Form());
		}
		return $this->getData("form");
	}


	/**
	 * @param type $column
	 * @return Mage_Catalog_Model_Resource_Eav_Attribute
	 */
	protected function _getAttributeFromColumn($column){
		return $column->getAttribute();
	}
	
	/**
	 * Fill some fileds with empty values (like multiple select)
	 * @param Varien_Data_Form_Element_Abstract $field
	 * @return string
	 */
	public function getEmptyValueField($field) {
		if($field->getExtType()=="multiple"){
			$empty = $this->getForm()->addField(
				$this->buildFieldId($field->getId(), "empty"), 
				"hidden", 
				array(
					"name"		=> preg_replace("/\[\]$/", "", $field->getName()),
					"value"		=> "",
					"disabled"  => "dsiabled",
					"class"		=> "empty_field"
				)
			);
			return $empty->toHTml();
		}
		return '';
	}
	
	public function getScope($attribute=null){
		if($attribute instanceof Mage_Catalog_Model_Resource_Eav_Attribute){
			switch ($attribute->getIsGlobal()){
				case "0":
					return Mage::helper("catalog")->__("Store");
				break;
				case "1":
					return Mage::helper("catalog")->__("Global");
				break;
				case "2":
					return Mage::helper("catalog")->__("Website");
				break;
			}
		}
		return '';
	}
}