<?php
class Zolago_Pos_Adminhtml_PosController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction(){
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function newAction() {
        $this->_forward("edit");
    }
    
    public function editAction(){
        $posId = $this->getRequest()->getParam("pos_id");
        
        try{
            $model = Mage::getModel("zolagopos/pos")->load($posId);
            if(!$model->getId()){
                // Default values for form
				$model->setDefaults();
            }
            $sessionData = $this->_getSession()->getFormData();
            if(!empty($sessionData)){
                $model->addData($sessionData);
                $this->getRequest()->setParam("entityCollection", Mage::helper('adminhtml/js')->decodeGridSerializedInput($sessionData['post_vendor_ids']));
                $this->_getSession()->setFormData(null);
            }
            Mage::register("zolagopos_current_pos", $model);
        }catch(Mage_Core_Exception $e){
            $this->_getSession()->addError($e->getMessage());
            return $this->_redirectReferer();
        }catch(Exception $e){
            $this->_getSession()->addError(Mage::helper("zolagopos")->__("Some error occure"));
            Mage::logException($e);
            return $this->_redirectReferer();
        }
        $this->loadLayout();
        $this->renderLayout();
    }
   
    
    public function saveAction() {
        
        $model = Mage::getModel("zolagopos/pos");
        $helper = Mage::helper('zolagopos');
        $data = $this->getRequest()->getParams();
        $modelId = $this->getRequest()->getParam("pos_id");
        
        $this->_getSession()->setFormData(null);
        
        try{
            if($this->getRequest()->isPost()){
                // Edit ?
                if($modelId!==null){
                    $model->load($modelId);
                    if(!$model->getId()){
                        throw new Mage_Core_Exception($helper->__("POS not found"));
                    }
                }
                $model->setData($data);
                $validErrors = $model->validate();
                if($validErrors===true){
                    $model->save();
                }else{
                    $this->_getSession()->setFormData($data);
                    foreach($validErrors as $error){
                        $this->_getSession()->addError($error);
                    }
                    return $this->_redirectReferer();
                }
                $this->_getSession()->addSuccess($helper->__("POS Saved"));
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
    
    
    public function deleteAction() {
        $posId = $this->getRequest()->getParam("pos_id");
        
        try{
            $model = Mage::getModel("zolagopos/pos")->load($posId);
            if(!$model->getId()){
                throw new Mage_Core_Exception(Mage::helper('zolagopos')->__("POS not found"));
            }
            $model->delete();
            $this->_getSession()->addSuccess(Mage::helper('zolagopos')->__("POS Deleted"));
        }catch(Mage_Core_Exception $e){
            $this->_getSession()->addError($e->getMessage());
            return $this->_redirectReferer();
        }catch(Exception $e){
            $this->_getSession()->addError(Mage::helper('zolagopos')->__("Some error occure"));
            Mage::logException($e);
        }
        return $this->_redirect("*/*");
    }
        
    public function vendorgridAction() {
        $posId = $this->getRequest()->getParam("pos_id");
        try{
            $model = Mage::getModel("zolagopos/pos")->load($posId);
            Mage::register("zolagopos_current_pos", $model);
        }catch(Mage_Core_Exception $e){
            Mage::logException($e);
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
    * Acl check for this controller
    *
    * @return bool
    */
    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('admin/vendors/vendor_general_config/zolagopos');
    }
}
