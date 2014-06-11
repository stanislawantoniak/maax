<?php

require_once(Mage::getModuleDir('controllers', 'Zolago_VendorGlobalSettings'). DS . 'Adminhtml' . DS . 'VendorGlobalSettingsController.php');
class Zolago_Holidays_Adminhtml_VendorGlobalSettingsController 
	extends Zolago_VendorGlobalSettings_Adminhtml_VendorGlobalSettingsController{
	
	public function indexAction(){
		
		$collection = Mage::getModel('zolagoholidays/processingtime')->getCollection();
		$collection->addFieldToFilter('type', 1);
		$model = $collection->getFirstItem();
		
		Mage::register('zolagoholidays_current_processingtime', $model);
		
		$this->loadLayout();
		$this->renderLayout();
		return $this;
	}
	
	public function saveAction(){
		
		$model = Mage::getModel("zolagoholidays/processingtime");
        $helper = Mage::helper('zolagoholidays');
        $data = $this->getRequest()->getParams();
        $modelId = $this->getRequest()->getParam("processingtime_id");
        
        $this->_getSession()->setFormData(null);
		
		// Form key valid?
		$formKey = Mage::getSingleton('core/session')->getFormKey();
		$formKeyPost = $this->getRequest()->getParam('form_key');
		if ($formKey != $formKeyPost) {
			return $this->_redirectReferer();
		}
		
		try{
            if($this->getRequest()->isPost()){
                // Edit ?
                if($modelId!==null){
                    $model->load($modelId);
                    if(!$model->getId()){
                        throw new Mage_Core_Exception($helper->__("Entry not found"));
                    }
                }
                $model->updateModelData($data);
				$model->save();
				
                $this->_getSession()->addSuccess($helper->__("Processing times have been save."));
            }           
        }catch(Mage_Core_Exception $e){
            $this->_getSession()->addError($e->getMessage());
            $this->_getSession()->setFormData($data);
            return $this->_redirectReferer();
        }catch(Exception $e){
            $this->_getSession()->addError($helper->__("Some error occure"));
            $this->_getSession()->setFormData($data);
            Mage::logException($e);
            return $this->_redirectReferer();
        }
        return $this->_redirect("*/*");
	}
	
}
