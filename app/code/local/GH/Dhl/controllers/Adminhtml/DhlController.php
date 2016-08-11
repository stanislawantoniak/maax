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


                if($model->isObjectNew()){
                    $modelE = Mage::getModel("ghdhl/dhl");
                    $new = $modelE->load($data["dhl_account"],"dhl_account");
                    $newId = $new->getId();

                    if(!empty($newId)){
                        $this->_getSession()->addError($helper->__("DHL Account %s already exist", $data["dhl_account"]));
                        $this->_getSession()->setFormData($data);
                        return $this->_redirectReferer();
                    }
                }

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

    public function vendorAction()
    {
        $block = $this->getLayout()
            ->createBlock(
                'ghdhl/adminhtml_dropship_settings_dhl_grid',
                'admin.ghdhl.settings.dhl'
            );
        $block->setVendorId($this->getRequest()->getParam('id'));
        $block->setUseAjax(true);
        $this->getResponse()->setBody($block->toHtml());
    }

    /**
     * Acl check for this controller
     *
     * @return bool
     */
    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('admin/vendors/vendor_general_config/ghdhl');
    }
}
