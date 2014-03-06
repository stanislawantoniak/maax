<?php
class Zolago_Adminhtml_Block_Catalog_Category_Filters extends Mage_Adminhtml_Block_Widget_Container
{
	protected function _prepareLayout() {
		$this->_addButton("back", array(
			"label" => Mage::helper('zolagoadminhtml')->__("Back"),
			"class" => "back",
			"onclick" => "setLocation('".$this->getUrl("*/catalog_category/index", array("id"=>  $this->getCategory()->getId()))."')"
		));
		$this->_addButton("save", array(
			"label" => Mage::helper('zolagoadminhtml')->__("Save"),
			"class" => "save",
			"onclick" => "editForm.submit();"
		));
		return parent::_prepareLayout();
	}
	
	public function getSaveUrl() {
		return $this->getUrl("*/catalog_category_filters/save", array("_current"=>true));
	}
	
	public function getHeaderText() {
		return Mage::helper('zolagoadminhtml')->__("Edit filters for %s", 
			$this->htmlEscape($this->getCategory()->getName()));
	}
	
	/**
	 * @return Varien_Data_Form
	 */
	public function getForm() {
		if(!$this->getData("form")){
			$form = new Varien_Data_Form();
			$form->setAction($this->getSaveUrl());
			$this->setData("form", $form);
			
		}
		return $this->getData("form");
	}


	public function getPossibleAttributes() {
		$resMapper = Mage::getResourceModel('zolagomapper/mapper');
		/* @var $resMapper Zolago_Mapper_Model_Resource_Mapper */
		return $resMapper->getAttributesByCategory($this->getCategory()->getId());
	}
	
	public function getAddButtonHtml($id) {
		$btn = $this->getLayout()->createBlock('adminhtml/widget_button');
		$btn->addData(array(
			"label"	=>	Mage::helper("zolagoadminhtml")->__("Add"),
			"class" => "add",
			"type"	=> "button"
		));
		$btn->setId($id);
		return $btn->toHtml();
	}


	public function getAttributesSelectHtml($id) {
		$conf = array(
			"values" => $this->getPossibleAttributes(),
		);
		
		$select = new Varien_Data_Form_Element_Select($conf);
		$select->setId($id);
		$this->getForm()->addElement($select);
		
		return $select->getElementHtml();
	}
	
	public function getRenderSelectHtml($id) {
		$conf = array(
			"values" => $this->getRendererValues(),
		);
		$select = new Varien_Data_Form_Element_Select($conf);
		$select->setId($id);
		$this->getForm()->addElement($select);
		
		return $select->getElementHtml();
	}
	
	/**
	 * @return array
	 */
	public function getRendererValues() {
		return Mage::getSingleton("zolagocatalog/system_layer_filter_source")->toOptionHash(true);
	}
	
	/**
	 * return Zolago_Catalog_Model_Resource_Category_Filter_Collection
	 */
	public function getFilterCollection(){
		$collection = Mage::getResourceModel('zolagocatalog/category_filter_collection');
		/* @var $collection Zolago_Catalog_Model_Resource_Category_Filter_Collection */
		$collection->addCategoryFilter($this->getCategory());
		return $collection;
	}


	/**
	 * @return Mage_Catalog_Model_Category
	 */
	public function getCategory(){
		return Mage::registry("current_category");
	}
	
    protected $_idSuffix;
    public function resetIdSuffix()
    {
        $this->_idSuffix = null;
        return $this;
    }
    public function getIdSuffix()
    {
        if ($this->_idSuffix === null) {
            $this->_idSuffix = $this->prepareIdSuffix($this->getFieldName());
        }
        return $this->_idSuffix;
    }

    public function prepareIdSuffix($id)
    {
        return preg_replace('/[^a-zA-Z0-9\$]/', '_', $id);
    }

    public function suffixId($id)
    {
        return $id.$this->getIdSuffix();
    }
}
