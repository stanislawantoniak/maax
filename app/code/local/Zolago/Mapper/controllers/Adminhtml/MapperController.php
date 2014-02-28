<?php
class Zolago_Mapper_Adminhtml_MapperController 
	extends Mage_Adminhtml_Controller_Action{
	
	public function saveAction(){
		var_export($this->getRequest()->getParams());
//		$data = $request->getPost();
//		$data['conditions'] = $data['rule']['conditions'];
//		unset($data['rule']);
//		$mapping->addData($data);
//		$mapping->loadPost($data);
	}
	
	public function editAction() {
		$model = $this->_registerModel();
		if(!$model->getId() && $this->_getId()){
			$this->_getSession()->addError(Mage::helper("zolagomapper")->__("Invaild mapper Id"));
			return $this->_redirectReferer();
		}
		$model->
				getConditions()->
				setJsFormObject('conditions_fieldset');
		
		$this->loadLayout();
		$this->getLayout()->getBlock('head')->
				setCanLoadExtJs(true)->
				setCanLoadRulesJs(true);
		$this->renderLayout();
	}
	
	public function newAction() {
		return $this->_forward("edit");
	}
	
	public function indexAction() {
		$this->loadLayout();
		$this->renderLayout();
	}
	
	/**
	 * @return mixed
	 */
	protected function _getId() {
		return $this->getRequest()->getParam("mapper_id");
	}
	
	/**
	 * @return Zolago_Mapper_Model_Mapper
	 */
	protected function _registerModel() {
		if(!Mage::registry("zolagomapper_current_mapper")){
			$model = Mage::getModel("zolagomapper/mapper");
			if($id = $this->_getId()){
				$model->load($id);
			}
			Mage::register("zolagomapper_current_mapper", $model);
		}
		return Mage::registry("zolagomapper_current_mapper");
	}
	
	/**
	 * New condition action
	 */
    public function newConditionHtmlAction() {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];
        $model = Mage::getModel($type)
                ->setId($id)
                ->setType($type)
                ->setRule(Mage::getModel('catalogrule/rule'))
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
	
}