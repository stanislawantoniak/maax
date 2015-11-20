<?php

class Zolago_Rma_Adminhtml_ReturnController extends Mage_Adminhtml_Controller_Action
{
	
	public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
	}

	public function deleteAction(){

        $helper = Mage::helper('zolagorma');

        try{
            $model_id = $this->getRequest()->getParam('return_reason_id');
            if($model_id){

                if($model = Mage::getModel('zolagorma/rma_reason')->load($model_id)){

                    $model->delete();
                    $this->_getSession()->addSuccess($helper->__("Return Reason has been successfully deleted."));
					
					$data = array( 'model' => $model);
					Mage::dispatchEvent('zolagorma_global_return_reson_delete_after', $data);

                }else{

                    $this->_getSession()->addError($helper->__("There was a problem deleting the selected item."));
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
        $model_id  = $this->getRequest()->getParam('return_reason_id');
        $model = Mage::getModel('zolagorma/rma_reason');

        if ($model_id) {
            // Load record
            $model->load($model_id);

            // Check if record is loaded
            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('This return reason no longer exists.'));
                $this->_redirect('*/*/');

                return;
            }
        }

        $this->_title($model->getId() ? $model->getName() : $this->__('New Return Reason'));

        $data = Mage::getSingleton('adminhtml/session')->getReturnReasonData(true);
        if (!empty($data)) {
            $model->updateModelData($data);
        }

        Mage::register('returnreason', $model);

        $this->loadLayout()
             ->renderLayout();
    }

    public function saveAction(){

        $model = Mage::getModel("zolagorma/rma_reason");
        $helper = Mage::helper('zolagorma');
        $data = $this->getRequest()->getParams();
        $modelId = $this->getRequest()->getParam("return_reason_id");

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
                        throw new Mage_Core_Exception($helper->__("Return Reason not found"));
                    }
                }
                $model->updateModelData($data);
                $model->save();
				
				$data = array( 'model' => $model);
				Mage::dispatchEvent('zolagorma_global_return_reson_save_after', $data);
				
                $this->_getSession()->addSuccess($helper->__("Return Reason Saved"));
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

    /**
     * Acl check for this controller
     *
     * @return bool
     */
    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('admin/vendors/vendor_general_config/reasons');
    }
}