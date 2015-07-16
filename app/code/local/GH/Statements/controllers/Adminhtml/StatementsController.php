<?php

class GH_Statements_Adminhtml_StatementsController extends Mage_Adminhtml_Controller_Action
{
    public function calendarAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function calendar_itemAction()        
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    public function calendar_newAction() {
        $this->_forward("calendar_edit");
    }
    public function calendar_editAction() {
        $id = $this->getRequest()->getParam("id");
        try {
            $model = Mage::getModel("ghstatements/calendar")->load($id);
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
            Mage::register("ghstatements_current_calendar", $model);
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            return $this->_redirectReferer();
        } catch (Exception $e) {
            $this->_getSession()->addError(Mage::helper("ghstatements")->__("Some error occurred!"));
            Mage::logException($e);
            return $this->_redirectReferer();
        }

        $this->loadLayout();
        $this->renderLayout();
    }
    public function calendar_edit_itemAction() {
        $this->loadLayout();
        $this->renderLayout();
    }
    public function calendar_saveAction() {
        $model = Mage::getModel("ghstatements/calendar");
        $helper = Mage::helper('ghstatements');
        $data = $this->getRequest()->getParams();
        $modelId = $this->getRequest()->getParam("id");

        $this->_getSession()->setFormData(null);

        try {
            if ($this->getRequest()->isPost()) {
                // Edit ?
                if ($modelId !== null) {
                    $model->load($modelId);
                    if (!$model->getId()) {
                        throw new Mage_Core_Exception($helper->__("Calendar not found"));
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
                $this->_getSession()->addSuccess($helper->__("Calendar saved"));
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
        $id = $model->getId();
        return $this->_redirect("*/*/calendar_item",array('id' => $id));

    }
    public function calendar_deleteAction() {
        $id = $this->getRequest()->getParam("id");
        try {
            $model = Mage::getModel("ghstatements/calendar")->load($id);
            if (!$model->getId()) {
                throw new Mage_Core_Exception(Mage::helper('ghstatements')->__("Calendar not found"));
            }
            $model->delete();
            $this->_getSession()->addSuccess(Mage::helper('ghstatements')->__("Calendar deleted"));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            return $this->_redirectReferer();
        } catch (Exception $e) {
            $this->_getSession()->addError(Mage::helper('ghstatements')->__("Some error occurred!"));
            Mage::logException($e);
        }
        return $this->_redirect("*/*/calendar");

    }

}
