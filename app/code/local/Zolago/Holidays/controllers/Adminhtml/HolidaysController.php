<?php
class Zolago_Holidays_Adminhtml_HolidaysController extends Mage_Adminhtml_Controller_Action{
	
	public function indexAction(){
		
		$this->loadLayout();
		$this->renderLayout();
		return $this;
	}
	
	public function deleteAction(){
		
		$helper = Mage::helper('zolagoholidays');
		
		try{
			$model_id = $this->getRequest()->getParam('holiday_id');
			if($model_id){
				
				if($model = Mage::getModel('zolagoholidays/holiday')->load($model_id)){
					
					$model->delete();
					$this->_getSession()->addSuccess($helper->__("Holiday has been successfully deleted."));
					
				}else{
					
					$this->_getSession()->addError($helper->__("There was a problem deleting the registry."));
					$this->_getSession()->setFormData($data);
				}
			}
			
		} catch (Mage_Core_Exception $e) {
			$this->_getSession()->addError($e->getMessage());
			$this->_getSession()->setFormData($data);
			return $this->_redirectReferer();
		} catch (Exception $e) {
			$this->_getSession()->addError($helper->__("Some error occure"));
			$this->_getSession()->setFormData($data);
			Mage::logException($e);
			return $this->_redirectReferer();
		}
		
		return $this->_redirect("*/*");
	}
	
	public function newAction(){
		
		$this->_forward('edit');
		
	}
	
	public function editAction(){
		
        // Get id if available
        $holiday_id  = $this->getRequest()->getParam('holiday_id');
        $model = Mage::getModel('zolagoholidays/holiday');
     
        if ($holiday_id) {
            // Load record
            $model->load($holiday_id);
     
            // Check if record is loaded
            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('This holiday no longer exists.'));
                $this->_redirect('*/*/');
     
                return;
            }  
        }  
     
        $this->_title($model->getId() ? $model->getName() : $this->__('New Holiday'));
     
        $data = Mage::getSingleton('adminhtml/session')->getHolidayData(true);
        if (!empty($data)) {
            $model->updateModelData($data);
        }  
     	
		
		if($model->getType() == 1){
			
			$current_year = strftime('%Y', time());
			$model->setDate($model->getDate() . "/" . $current_year);
		}
		
        Mage::register('holiday', $model);
     
        $this->loadLayout()
             ->renderLayout();
	}
	
	public function saveAction(){
		
		$model = Mage::getModel("zolagoholidays/holiday");
        $helper = Mage::helper('zolagoholidays');
        $data = $this->getRequest()->getParams();
        $modelId = $this->getRequest()->getParam("holiday_id");
        
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
                        throw new Mage_Core_Exception($helper->__("Holiday not found"));
                    }
                }
                $model->updateModelData($data);
				$model->save();
                // $validErrors = $model->validate();
                // if($validErrors===true){
                    // $model->save();
                // }else{
                    // $this->_getSession()->setFormData($data);
                    // foreach($validErrors as $error){
                        // $this->_getSession()->addError($error);
                    // }
                    // return $this->_redirectReferer();
                // }
                $this->_getSession()->addSuccess($helper->__("Holiday Saved"));
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
	
	public function gridAction(){
		
		$this->loadLayout();
	    $this->getResponse()->setBody(
	           $this->getLayout()->createBlock('zolagoholidays/adminhtml_holidays_grid')->toHtml()
	    ); 
			 
	}

    /**
     * Acl check for this controller
     *
     * @return bool
     */
    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('admin/vendors/vendor_general_config/holiday');
    }
}
