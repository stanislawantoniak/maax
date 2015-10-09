<?php
/**
 * controller for crud with documents kind
 */
class GH_Regulation_Adminhtml_RegulationController extends Mage_Adminhtml_Controller_Action {

    
    /**
     * get model from registry 
     *
     * @param string $name registry key
     * @param string $modelName 
     * @param int|null $id 
     * @return Mage_Core_Model_Abstract
     */
    protected function _getModel($name,$modelName,$id) {
		if(!Mage::registry($name)){
			$model = Mage::getModel($modelName);
			if($id){
				$model->load($id);
        		if(!$model->getId()){
		        	$this->_getSession()->addError(Mage::helper("ghregulation")->__("Invaild %s object Id",$modelName));
        			return $this->_redirectReferer();
                }
			} else {
				$model->setDefaults();
			}
			Mage::register($name, $model);
		}
		return Mage::registry($name);

    }

    /**
     * show kind grid
     */
     public function kindAction() {
         $this->loadLayout();
         $this->renderLayout();
     }
     
    /**
     * new kind
     */
     public function newKindAction() {
         $this->_forward('editKind');
     }
     
    /**
     * save edited kind of documents
     */
     public function saveKindAction() { 
         return $this->_saveAction('regulation_kind_id','ghregulation_current_kind','ghregulation/regulation_kind','ghregulation_kind_form_data','*/*/kind');  
     }
     
    /**
     * save edited type of document
     */
     public function saveTypeAction() { 
         return $this->_saveAction('regulation_type_id','ghregulation_current_type','ghregulation/regulation_type','ghregulation_type_form_data','*/*/type');  
     }

    /**
     * saving forms
     *
     * @param string $requestParam name of object primary key
     * @param string $registerKey 
     * @param string $modelName
     * @param string $formData name of html edit form 
     * @param string $urlPattern redirect url after success save
     * @return Mage_Adminhtml_Controller_Action
     */
     protected function _saveAction($requestParam,$registerKey,$modelName,$formData,$urlPattern) {     
         try {
             $request = $this->getRequest();
             $id = $request->getParam($requestParam,null);
             $model = $this->_getModel($registerKey,$modelName,$id);
             $data = $request->getPost();
             $model->addData($data);
             $model->save();
             $this->_getSession()->setData($formData, null);             
             $url = $this->getUrl($urlPattern);
             return $this->_redirectUrl($url);
         } catch (Exception $e) {
             Mage::logException($e);
             $this->_getSession()->addException($e, $e->getMessage());
             $this->_getSession()->setData($formData, $this->getRequest()->getParams());                                     
         }
         return $this->_redirectReferer();
     }
     
    /**
     * delete kind
     */
    public function deleteKindAction() {
        $id = $this->getRequest()->getParam('regulation_kind_id',null);
        $model = $this->_getModel('ghregulation_current_kind','ghregulation/regulation_kind',$id);
        if ($model->getId()) {
            $success = false;
            try {
                $name = $model->getData('name');
                $model->delete();
                $success = true;
            } catch (Mage_Core_Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addException($e, Mage::helper('ghregulation')->__('An error occurred while deleting this kind of document.'));
            }
            if ($success) {
                $this->_getSession()->addSuccess(Mage::helper('ghregulation')->__('Document kind &quot;%s&quot; was deleted.', $name));
            }
        }
        return $this->_redirect('*/*/kind');
    }

    /**
     * edit kind
     */
    public function editKindAction() {
        $this->_editAction('regulation_kind_id','ghregulation_current_kind','ghregulation/regulation_kind','regulation_kind_form_data');
    }

    /**
     * new type
     */
     public function newTypeAction() {
         $this->_forward('editType');
     }

    /**
     * edit type
     */
    public function editTypeAction() {
        $this->_editAction('regulation_type_id','ghregulation_current_type','ghregulation/regulation_type','regulation_type_form_data');
    }
    
    /**
     * edit type of documents 
     *
     * @param string $paramName request param with object id
     * @param string $registerKey
     * @param string $modelName
     * @param string $formData
     */
    protected function _editAction($paramName,$registerKey,$modelName,$formData) {
        $id = $this->getRequest()->getParam($paramName,null);
        $model = $this->_getModel($registerKey,$modelName,$id);
		if ($values = $this->_getSession()->getData($formData, true)) {
			$model->addData($values);
        }		
		$this->loadLayout();
		$this->renderLayout();
	}

    /**
     * show type grid
     */
     public function typeAction() {
         $this->loadLayout();
         $this->renderLayout();
     }

    /**
     * show list grid
     */
     public function listAction() {
         $this->loadLayout();
         $this->renderLayout();
     }

    public function newDocumentAction() {
        $this->_forward('editDocument');
    }

    public function editDocumentAction() {
        $this->_editAction('id', 'ghregulation_current_document', 'ghregulation/regulation_document', 'regulation_document_form_data');
    }
}