<?php
class Zolago_Mapper_Adminhtml_MapperController 
	extends Mage_Adminhtml_Controller_Action{
	
	public function editAction() {
		$model = $this->_registerModel();
		if(!$model->getId() && $this->_getId()){
			$this->_getSession()->addError(Mage::helper("zolagomapper")->__("Invaild mapper Id"));
			return $this->_redirectReferer();
		}
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
	
}