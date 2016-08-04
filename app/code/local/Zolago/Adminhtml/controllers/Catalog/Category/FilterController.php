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
	
	public function saveAction() {
		$category = $this->_registerObject();
		
		if(!$category->getId()){
			$this->_getSession()->addError(Mage::helper("zolagoadminhtml")->__("Category doesn't exists"));
			return $this->_redirectReferer();
		}
		
		if(!$this->getRequest()->isPost()){
			$this->_getSession()->addError(Mage::helper("zolagoadminhtml")->__("No POST request"));
			return $this->_redirectReferer();
		}
		
		$data = $this->getRequest()->getPost();
		
		$filters = array();
		if(isset($data['filters']) && is_array($data['filters'])){
			$filters = $data['filters'];
		}
		
		if(isset($filters['$$ROW'])){
			unset($filters['$$ROW']);
		}
		
		$connection = Mage::getModel('core/resource')->getConnection("core_write");
		/* @var $connection Varien_Db_Adapter_Interface */
		
		$newFilters = array();
		$newFiltersIds = array();
		
		$oldFilters = Mage::getResourceModel('zolagocatalog/category_filter_collection');
		/* @var $oldFilters Zolago_Catalog_Model_Resource_Category_Filter_Collection */
		$oldFilters->addCategoryFilter($category);
		$oldFiltersIds = $oldFilters->getAllIds();
		
		$connection->beginTransaction();
		
		try{
			foreach($filters as $filter){
				if(!is_array($filter)){
					throw new Mage_Core_Exception(Mage::helper("zolagoadminhtml")->__("Wrong input format"));
				}
				if(isset($filter['specified_options']) && empty($filter['specified_options'])){
					$filter['specified_options'] = null;
				}
				if(isset($filter['parent_attribute_id']) && empty($filter['parent_attribute_id'])){
					$filter['parent_attribute_id'] = null;
				}
				if(is_array($filter['specified_options'])){
					$filter['specified_options'] = array_map(
						function($element){
							return (int)$element;	
						}, 
						$filter['specified_options']
					);
				}
				
				$model = Mage::getModel("zolagocatalog/category_filter");
				$model->addData($filter);
				$model->setCategoryId($category->getId());

				if(isset($filter['filter_id']) && !empty($filter['filter_id'])){
					$model->setId($filter['filter_id']);
					$newFiltersIds[] = $model->getId();
				}else{
					$model->setId(null);
				}

				$model->save();
			}
			$toDelete = array_diff($oldFiltersIds, $newFiltersIds);
			// Sth to remove?
			if(count($toDelete)){
				Mage::getResourceModel("zolagocatalog/category_filter")->deleteMultitply($toDelete);
			}
			$connection->commit();
		}  catch (Exception $e){
			$this->_getSession()->addException($e, Mage::helper("zolagoadminhtml")->__("Some error occure"));
			$connection->rollBack();
			return $this->_redirectReferer();
		}
		$this->_getSession()->addSuccess(Mage::helper("zolagoadminhtml")->__("Filters saved"));
		
		$backUrl = $this->getUrl("*/catalog_category");
		if($this->getRequest()->getParam('save_and_edit')){
			$backUrl = $this->getUrl("*/*/edit", array("_current"=>true));
		}
		
		return $this->_redirectUrl($backUrl);
		
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
		
		$responseSuccess = array(
			"status"=>1,
			"content" => array(
				"attribute_id"	=> $attributeId,
				"frontend_label"=> $model->getFrontendLabel(),
				"attribute_code"=> $model->getAttributeCode(),
				"default_value"	=> $model->getDefaultValue(),
				"options"		=> $model->getSource()->getAllOptions(false)
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
        
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/categories');  
                
    }
}
