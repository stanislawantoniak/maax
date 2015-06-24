<?php

class GH_Dhl_Adminhtml_DhlController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward("edit");
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam("id");

        try {
            $model = Mage::getModel("ghdhl/dhl")->load($id);
            if (!$model->getId()) {
                // Default values for form
                $model->setDefaults();
            }
            $sessionData = $this->_getSession()->getFormData();
            if (!empty($sessionData)) {
                $model->addData($sessionData);
                //$this->getRequest()->setParam("entityCollection", Mage::helper('adminhtml/js')->decodeGridSerializedInput($sessionData['post_vendor_ids']));
                $this->_getSession()->setFormData(null);
            }
            Mage::register("ghdhl_current_dhl", $model);
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            return $this->_redirectReferer();
        } catch (Exception $e) {
            $this->_getSession()->addError(Mage::helper("ghdhl")->__("Some error occurred!"));
            Mage::logException($e);
            return $this->_redirectReferer();
        }
        $this->loadLayout();
        $this->renderLayout();
    }


    public function saveAction()
    {
        $model = Mage::getModel("ghdhl/dhl");
        $helper = Mage::helper('ghdhl');
        $data = $this->getRequest()->getParams();
        $modelId = $this->getRequest()->getParam("id");

        $this->_getSession()->setFormData(null);

        try {
            if ($this->getRequest()->isPost()) {
                // Edit ?
                if ($modelId !== null) {
                    $model->load($modelId);
                    if (!$model->getId()) {
                        throw new Mage_Core_Exception($helper->__("DHL Account not found"));
                    }
                }
                $model->setData($data);
                $validErrors = $model->validate();
                if ($validErrors === true) {
                    $model->save();
                } else {
                    $this->_getSession()->setFormData($data);
                    foreach ($validErrors as $error) {
                        $this->_getSession()->addError($error);
                    }
                    return $this->_redirectReferer();
                }
                $this->_getSession()->addSuccess($helper->__("DHL Account Saved"));
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_getSession()->setFormData($data);
            return $this->_redirectReferer();
        } catch (Exception $e) {
            Mage::log($e->getMessage());
            $this->_getSession()->addError($helper->__("Some error occurred!"));
            $this->_getSession()->setFormData($data);
            Mage::logException($e);
            return $this->_redirectReferer();
        }
        return $this->_redirect("*/*");
    }


    public function deleteAction()
    {
        $id = $this->getRequest()->getParam("id");

        try {
            $model = Mage::getModel("ghdhl/dhl")->load($id);
            if (!$model->getId()) {
                throw new Mage_Core_Exception(Mage::helper('ghdhl')->__("DHL Account not found"));
            }
            $model->delete();
            $this->_getSession()->addSuccess(Mage::helper('ghdhl')->__("DHL Account Deleted"));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            return $this->_redirectReferer();
        } catch (Exception $e) {
            $this->_getSession()->addError(Mage::helper('ghdhl')->__("Some error occurred!"));
            Mage::logException($e);
        }
        return $this->_redirect("*/*");
    }

}
