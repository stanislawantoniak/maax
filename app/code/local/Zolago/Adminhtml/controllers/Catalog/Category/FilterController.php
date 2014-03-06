<?php
class Zolago_Adminhtml_Catalog_Category_FilterController 
	extends Mage_Adminhtml_Controller_Action
{
    
    public function editAction(){
		$category = $this->_registerObject();
		
		if(!$category->getId()){
			$this->_getSession()->addError(Mage::helper("zolagoadminhtml")->__("Category doesn't exists"));
			return $this->_redirectReferer();
		}
		
        $this->loadLayout();
        $this->renderLayout();
    }
	
	public function getAttributeOptionsAction(){
		
		$attributeId = $this->getRequest()->getParam("attribute_id");

		$responseError = array(
			"status"=>0,
			"content" => ""
		);
		
		$eav = Mage::getSingleton('eav/config');
		/* @var $eav Mage_Eav_Model_Config */
		
		$model = $eav->getAttribute(
				Mage_Catalog_Model_Product::ENTITY, 
				(int)$attributeId
		);
		
		/* @var $model Mage_Eav_Model_Resource_Attribute */
		
		if(!($model instanceof Mage_Eav_Model_Entity_Attribute_Interface) || !$model->getId()){
			$responseError['content'] = Mage::helper('zolagoadminhtml')->__("Wrong attribute id");
			return $this->_sendJson($responseError);
		}
		
		if(!in_array($model->getFrontendInput(), array("select", "multiselect", "boolean"))){
			$responseError['content'] = Mage::helper('zolagoadminhtml')->__("Attribute is not enum");
			return $this->_sendJson($responseError);
		}
		
		if(!$model->getSource()){
			$responseError['content'] = Mage::helper('zolagoadminhtml')->__("Attribute has no source");
			return $this->_sendJson($responseError);
		}
		
		$hashOptions = array();
		foreach($model->getSource()->getAllOptions(false) as $option){
			$hashOptions[$option['value']] = $option['label'];
		}
		
		$responseSuccess = array(
			"status"=>1,
			"content" => array(
				"attribute_id"	=> $attributeId,
				"options"		=> $hashOptions
			)
		);
		return $this->_sendJson($responseSuccess);
	}
	
	protected function _sendJson($json) {
		$this->getResponse()->setHeader("content-type", "application/json");
		$this->getResponse()->setBody(
			Mage::helper('core')->jsonEncode($json)
		);
	}
	
	/**
	 * @return Mage_Catalog_Model_Category
	 */
	protected function _registerObject(){
		if(!Mage::registry("current_category")){
			$category = Mage::getModel("catalog/category");
			/* @var $category Mage_Catalog_Model_Category */
			$paramId = $this->getRequest()->getParam("category_id");
			if($paramId){
				$category->load($paramId);
			}
			
			Mage::register("current_category", $category);
		}
		return Mage::registry("current_category");
	}
        
}
