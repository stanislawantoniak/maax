<?php
class Zolago_Mapper_Adminhtml_MapperController 
	extends Mage_Adminhtml_Controller_Action{
	
	public function runAllAction() {
	    var_export(Mage::getResourceSingleton("zolagomapper/index")->reindexForProducts());
		Mage::getResourceSingleton("zolagomapper/index")->assignWithCatalog();
	}
	
	public function runAction() {
		$model = $this->_registerModel();
		
		$mathedIds = $model->getMatchingProductIds();
		$storeId = $model->getDefaultStoreId();
		$productColl = Mage::getResourceModel('catalog/product_collection');
		
		Varien_Profiler::start("ZolagoMapper::Run");
		/* @var $productColl Mage_Catalog_Model_Resource_Product_Collection */
		$productColl->setStoreId($storeId);
		$productColl->addIdFilter($mathedIds);
		$productColl->addAttributeToSelect("price");
		$productColl->addAttributeToSelect("name");
		
		$categoryColl = Mage::getResourceModel('catalog/category_collection');
		/* @var $categoryColl Mage_Catalog_Model_Resource_Category_Collection */
		$categoryColl->addAttributeToSelect("name");
		$categoryColl->addFieldToFilter('entity_id', $model->getCategoryIds());
		$this->loadLayout();
		$this->getLayout()->
				getBlock("zolagomapper_mapper")->
				setProducts($productColl)->
				setCategories($categoryColl)->
				setStore(Mage::app()->getStore($storeId));
		
		Varien_Profiler::stop("ZolagoMapper::Run");
		$this->renderLayout();
	}
	
	public function saveAction(){
        $request = $this->getRequest();
		if (!$request->isPost()) {
            $this->getResponse()->setRedirect($this->getUrl('*/*/index'));
        }
        $mapper = $this->_registerModel();
		
        try {
            $data = $request->getPost();
            $data['conditions'] = $data['rule']['conditions'];
            unset($data['rule']);
			$categoryIds = array();
			// Set category Ids
			if(isset($data['category_ids_as_string']) && !empty($data['category_ids_as_string'])){
				$categoryIds = explode(",", $data['category_ids_as_string']);
			}
			
			$data['category_ids'] = $categoryIds;
            $mapper->addData($data);
            $mapper->loadPost($data);
            $mapper->save();
            $this->_getSession()->addSuccess(
					Mage::helper('zolagomapper')->__('The mapper has been saved.'));
			$this->_getSession()->setData('mapper_form_data', null);
			return $this->_redirect('*/*/index');
   
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $e->getMessage());
            $this->_getSession()->setData('mapper_form_data', $this->getRequest()->getParams());
        } catch (Exception $e) {
            $this->_getSession()->addException($e, 
					Mage::helper('zolagomapper')->__('An error occurred while saving this mapping.'));
            $this->_getSession()->setData('mapper_form_data', $this->getRequest()->getParams());
        }
        $this->_redirectReferer();
        
	}
	
	public function editAction() {
		$model = $this->_registerModel();
		if(!$model->getId() && $this->_getId()){
			$this->_getSession()->addError(Mage::helper("zolagomapper")->__("Invaild mapper Id"));
			return $this->_redirectReferer();
		}
		
		if ($values = $this->_getSession()->getData('mapper_form_data', true)) {
			if(isset($values['category_ids_as_string'])){
				$model->setCategoryIds(explode(",",$values['category_ids_as_string']));
			}
			if(isset($values['rule']['conditions'])){
				$values['conditions'] = $values['rule']['conditions'];
				unset($values['rule']);
				$model->loadPost($values);
			}
			$model->addData($values);
        }
		
		$this->loadLayout();
		$model->
				getConditions()->
				setJsFormObject('conditions_fieldset');
	
		
		$this->getLayout()->getBlock('head')->
				setCanLoadExtJs(true)->
				setCanLoadRulesJs(true);
		$this->renderLayout();
	}
	
	public function deleteAction() {
        $model = $this->_registerModel();
        if ($model->getId()) {
            $success = false;
            try {
                $model->delete();
                $success = true;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException($e, Mage::helper('zolagomapper')->__('An error occurred while deleting this mapping.'));
            }
            if ($success) {
                $this->_getSession()->addSuccess(Mage::helper('zolagomapper')->__('The mapper has been deleted.'));
            }
        }
        $this->_redirect('*/*/index');
    }
	
	public function newAction() {
		return $this->_forward("edit");
	}
	
	public function indexAction() {
		$this->loadLayout();
		$this->renderLayout();
	}
	

	

	
	/**
	 * New condition action
	 */
    public function newConditionHtmlAction() {
		;
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];;
		$ruleModel  = $this->_registerModel();
		
		// Register for below constructor...
		if($this->getRequest()->getParam("attribute_set_id")){
			$ruleModel->setAttributeSetId($this->getRequest()->getParam("attribute_set_id"));
		}
		
        $model = Mage::getModel($type, $ruleModel)
                ->setRule($ruleModel)
                ->setId($id)
                ->setType($type)
                ->setPrefix('conditions');
		
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }
        if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }
	
	/**
     * Get tree node (Ajax version)
     */
    public function categoriesJsonAction()
    {
        $this->_registerModel();
        if ($this->getRequest()->getParam('expand_all')) {
            Mage::getSingleton('admin/session')->setIsTreeWasExpanded(true);
        } else {
            Mage::getSingleton('admin/session')->setIsTreeWasExpanded(false);
        }
        if ($categoryId = (int) $this->getRequest()->getPost('category')) {
            
            $category= Mage::getModel("catalog/category")->load($categoryId);

            if (!$category->getId()) {
                return;
            }
            
            $this->getResponse()->setBody(
                $this->getLayout()->createBlock('zolagomapper/adminhtml_mapper_edit_form_categories')
                    ->getCategoryChildrenJson($category)
            );
        }
    }

	/**
	 * @return Zolago_Mapper_Model_Mapper
	 */
	protected function _registerModel() {
		if(!Mage::registry("zolagomapper_current_mapper")){
			$model = Mage::getModel("zolagomapper/mapper");
			if($id = $this->_getId()){
				$model->load($id);
			}else{
				$model->setDefaults();
			}
			Mage::register("zolagomapper_current_mapper", $model);
		}
		return Mage::registry("zolagomapper_current_mapper");
	}
	
	 /**
	 * @return mixed
	 */
	protected function _getId() {
		return $this->getRequest()->getParam("mapper_id");
	}
	
}