<?php
class Zolago_Mapper_Adminhtml_MapperController 
	extends Mage_Adminhtml_Controller_Action{

	public function runAction() {
		$model = $this->_registerModel();
		if (!($id = $model->getId())) {
            $this->_getSession()->addError(Mage::helper("zolagomapper")->__("No mapper to rebuild"));
            $this->_redirectReferer();
            return;
		} 

		Varien_Profiler::start("ZolagoMapper::Run");
		$indexer = Mage::getResourceModel('zolagomapper/index');
		$oldProducts = $indexer->getAssignedProducts(array($id),$model->getWebsiteId());
		$mathedIds = $indexer->reindexForMappers($id);
		$final = array_merge($oldProducts,$mathedIds);
		$final = array_unique($final);
		$indexer->assignWithCatalog($final);
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
        if (count($model->getCategoryIds()) > 0) {            
            $categoryColl->addFieldToFilter('entity_id', array('in' => $model->getCategoryIds()));
        } else {
            $categoryColl->addFieldToFilter('entity_id',-1);
        }
        
        

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
			$this->_getSession()->setData('mapper_form_data', null);

            $this->_getSession()->addSuccess(
                Mage::helper('zolagomapper')->__('The mapper has been saved.'));

			// Forward run or index
			$doRun = $request->getParam("do_run");
			$doSaveAndQueue = $request->getParam("do_saveAndQueue");
            if ($doSaveAndQueue) {
                // Add to queue
                $queue = Mage::getModel('zolagomapper/queue_mapper');
                $queue->push($mapper->getId());
                $this->_getSession()->addSuccess(Mage::helper("zolagomapper")->__("Mapper added to rebuild queue"));
                $backUrl = null;
            }elseif(!$doRun){
                // Save
				$backUrl = $this->getUrl("*/*");
			}else{
                // Run
				$backUrl = $this->getUrl("*/*/run", array("mapper_id"=>$mapper->getId()));
			}

			return $this->_redirectUrl($backUrl);
   
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

    public function massQueueAction() {

        $ids = $this->getRequest()->getParam('custom_ids');
        $_ids = array();
        // Filtering ids with mapper_id (id is like 'attribute_set_id-mapper_id')
        foreach($ids as $id) {
            $arr = preg_split('/\d*-\d*/',$id);
            $asid = isset($arr[0]) ? $arr[0] : 0;
            $mid  = isset($arr[1]) ? $arr[1] : 0;
            if ($mid) {
                $_ids[] = $mid;
            }
        }

        if(!is_array($ids) || !count($_ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('zolagomapper')->__('Please select valid mappers'));
        } else {
            try {
                foreach($_ids as $id){
                    $queue = Mage::getModel('zolagomapper/queue_mapper');
                    $queue->push($id);
                }
                $this->_getSession()->addSuccess(Mage::helper("zolagomapper")->__("%s Mappers added to rebuild queue", count($_ids)));

            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException($e, Mage::helper('zolagomapper')->__('An error occurred while adding mappers to queue'));
            }
        }


        $this->_redirect('*/*/index');
    }
}