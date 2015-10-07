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
         try {
             $request = $this->getRequest();
             $id = $request->getParam('regulation_kind_id',null);
             $model = $this->_getModel('ghregulation_current_kind','ghregulation/regulation_kind',$id);
             $data = $request->getPost();
             $model->addData($data);
             $model->save();
             $this->_getSession()->setData('ghregulation_kind_form_data', null);             
             $url = $this->getUrl('*/*/kind');
             return $this->_redirectUrl($url);
         } catch (Exception $e) {
             Mage::logException($e);
             $this->_getSession()->addException($e, $e->getMessage());
             $this->_getSession()->setData('ghregulation_kind_form_data', $this->getRequest()->getParams());                                     
         }
         $this->_redirectReferer();
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
                $this->_getSession()->addSuccess(Mage::helper('ghregulation')->__('Document kind "%s" was deleted.',$name));
            }
        }
        return $this->_redirect('*/*/kind');
    }
    /**
     * edit kind
     */
    public function editKindAction() {
        $id = $this->getRequest()->getParam('regulation_kind_id',null);
        $model = $this->_getModel('ghregulation_current_kind','ghregulation/regulation_kind',$id);
		if ($values = $this->_getSession()->getData('regulation_kind_form_data', true)) {
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

}