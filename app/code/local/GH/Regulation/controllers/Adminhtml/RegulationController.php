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
     * Delete document type
     *
     * @return $this|Mage_Core_Controller_Varien_Action
     */
    public function deleteTypeAction() {
        $id = $this->getRequest()->getParam('regulation_type_id', null);
        $model = $this->_getModel('ghregulation_current_type', 'ghregulation/regulation_type', $id);
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
                $this->_getSession()->addException($e, Mage::helper('ghregulation')->__('An error occurred while deleting this type of document.'));
            }
            if ($success) {
                $this->_getSession()->addSuccess(Mage::helper('ghregulation')->__('Document type &quot;%s&quot; was deleted.', $name));
            }
        }
        return $this->_redirect('*/*/type');
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
    protected function _saveAction($requestParam, $registerKey, $modelName, $formData, $urlPattern) {
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
     * show list grid (documents list)
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

    /**
     * Save document
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    public function saveDocumentAction() {
        /** @var GH_Regulation_Helper_Data $hlp */
        $hlp = Mage::helper("ghregulation");
        try {
            $request = $this->getRequest();
            $id = $request->getParam('id', null);
            /** @var GH_Regulation_Model_Regulation_Document $model */
            $model = $this->_getModel('ghregulation_current_document', 'ghregulation/regulation_document', $id);
            $data = $request->getPost();
            $model->addData($data);

            if (isset($_FILES['file']) && isset($_FILES['file']['name']) && !empty($_FILES['file']['name'])) {

                $file    = $_FILES['file'];
                $folder  = $hlp::REGULATION_DOCUMENT_ADMIN_FOLDER;
                $allowed = $hlp::getAllowedRegulationDocumentTypes();
                $allowed[] = 'other'; // other files also allowed
                $result  = $hlp->saveRegulationDocument($file, $folder, $allowed, true);
                $path    = $result["content"]["path"];
                $newName = $result["content"]["new_name"];

                if(!$path) {
                    Mage::throwException("Invalid file type (".$file['type'].")");
                }

                $dl = array(
                    "file_name" => $newName,
                    "path"      => $path
                );
                $model->setData("document_link", serialize($dl));
            } elseif (empty($id)) {
                Mage::throwException("Wrong document file");
            }

            $model->save();
            $url = $this->getUrl('*/*/list');
            $this->_getSession()->setData('ghregulation_document_form_data', null);
            return $this->_redirectUrl($url);
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addException($e, $hlp->__($e->getMessage()));
            $this->_getSession()->setData('ghregulation_document_form_data', $this->getRequest()->getParams());
        }
        return $this->_redirectReferer();
    }

    /**
     * Delete document
     *
     * @return $this|Mage_Core_Controller_Varien_Action
     */
    public function deleteDocumentAction() {
        $id = $this->getRequest()->getParam('id', null);
        /** @var GH_Regulation_Model_Regulation_Document $model */
        $model = $this->_getModel('ghregulation_current_document', 'ghregulation/regulation_document', $id);
        if ($model->getId()) {
            $success = false;
            try {
                $name = $model->getFileName();
                $model->delete();
                $success = true;
            } catch (Mage_Core_Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addException($e, Mage::helper('ghregulation')->__('An error occurred while deleting this document.'));
            }
            if ($success) {
                $this->_getSession()->addSuccess(Mage::helper('ghregulation')->__('Document &quot;%s&quot; was deleted.', $name));
            }
        }
        return $this->_redirect('*/*/list');
    }

    /**
     * Get regulation document uploaded by admin
     */
    public function getDocumentAction() {
        $documentId = $this->getRequest()->getParam('id');
        if($documentId) {
            /** @var Gh_Regulation_Model_Regulation_Document $document */
            $document = Mage::getModel('ghregulation/regulation_document')->load($documentId);
            if($document->getId()) {
                $path = $document->getFullPath();
                if(is_file($path) && is_readable ($path)) {
                    $this->_sendFile($path, $document->getFileName());
                    return;
                }
            }
        }
        $this->norouteAction(); //404
        return;
    }

    /**
     * Get regulation document uploaded by vendor
     */
    public function getVendorUploadedDocumentAction() {
        $req = $this->getRequest();
        $vendorId = $req->getParam('vendor', false);
        $fileName = $req->getParam('file', false);

        if (!empty($vendorId) && !empty($fileName)) {
            $path  = Mage::getBaseDir('media') . DS . GH_Regulation_Helper_Data::REGULATION_DOCUMENT_FOLDER . DS . "accept_" . (int)$vendorId . DS;
            $image = md5($fileName);
            $path .= $image[0] . "/" . $image[1] . "/" . $fileName;
            if (is_file($path) && is_readable($path)) {
                $this->_sendFile($path, $fileName);
                return;
            }
        }
        $this->norouteAction(); //404
        return;
    }

    protected function _sendFile($filepath,$filename = null) {
        $filename = is_null($filename) ? basename($filepath) : $filename;

        $this->getResponse()
            ->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            //->setHeader ( 'Content-type', 'application/pdf', true ) /*  View in browser */
            ->setHeader('Content-type', 'application/force-download') /*  Download        */
            ->setHeader('Content-Length', filesize($filepath))
            ->setHeader('Content-Disposition', 'inline' . '; filename=' . $filename);
        $this->getResponse()->clearBody();
        $this->getResponse()->sendHeaders();
        readfile($filepath);
    }

    /**
     * Acl check for this controller
     *
     * @return bool
     */
    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('admin/vendors/vendor_general_config/ghregulation');
    }
}