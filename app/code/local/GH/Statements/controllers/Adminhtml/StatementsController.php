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
    public function calendar_item_newAction() {
        $this->_forward("calendar_item_edit");
    }
    public function calendar_editAction() {
        $model = Mage::getModel("ghstatements/calendar");
        $registerKey = 'ghstatements_current_calendar';
        $this->_edit($model,$registerKey);
    }
    protected function _edit($object,$registerKey) {
        $id = $this->getRequest()->getParam("id");
        try {
            $model = $object->load($id);
            if (!$model->getId()) {
                // Default values for form
                $model->setDefaults();
            } else {
                $this->getRequest()->setParam('calendar_id',$model->getCalendarId());
            }
            $sessionData = $this->_getSession()->getFormData();
            if (!empty($sessionData)) {
                $model->addData($sessionData);
                //$this->getRequest()->setParam("entityCollection", Mage::helper('adminhtml/js')->decodeGridSerializedInput($sessionData['post_vendor_ids']));
                $this->_getSession()->setFormData(null);
            }
            Mage::register($registerKey, $model);
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
    public function calendar_item_editAction() {
        $model = Mage::getModel("ghstatements/calendar_item");
        $registerKey = 'ghstatements_current_calendar_item';
        $this->_edit($model,$registerKey);
    }
    protected function _save($model,$type) {        
        $this->_getSession()->setFormData(null);
        $helper = Mage::helper('ghstatements');
        $modelId = $this->getRequest()->getParam("id");
        $data = $this->getRequest()->getParams();
        try {
            if ($this->getRequest()->isPost()) {
                // Edit ?
                if ($modelId !== null) {
                    $model->load($modelId);
                    if (!$model->getId()) {
                        throw new Mage_Core_Exception($helper->__($type." not found"));
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
                    return null;
                }
                $this->_getSession()->addSuccess($helper->__($type." saved"));
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
        return $id;
    }
    public function calendar_saveAction() {
        $model = Mage::getModel("ghstatements/calendar");
        $oldId =  $this->getRequest()->getParam("id");        
        if (!($id = $this->_save($model,'Calendar'))) {
            return $this->_redirectReferer();
        }
        
        if ($oldId != $id) {
            return $this->_redirect("*/*/calendar_item_new",array('calendar_id' => $id));
        } else {
            return $this->_redirect("*/*/calendar");
        }

    }
    public function calendar_item_saveAction() {
        $model = Mage::getModel("ghstatements/calendar_item");
        $itemId = $this->getRequest()->getParam("id");
        if ($itemId) {
            $this->getRequest()->setParam('item_id',$itemId);
        }
        if (!$this->_save($model,'Event')) {
            return $this->_redirectReferer();
        }
        $id = $this->getRequest()->getParam('calendar_id');
        return $this->_redirect("*/*/calendar_item",array('id' => $id));

    }
    protected function _delete($model,$type) {
        $id = $this->getRequest()->getParam("id");
        try {
            $model->load($id);
            if (!$model->getId()) {
                throw new Mage_Core_Exception(Mage::helper('ghstatements')->__($type." not found"));
            }
            $model->delete();
            $this->_getSession()->addSuccess(Mage::helper('ghstatements')->__($type." deleted"));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            return $this->_redirectReferer();
        } catch (Exception $e) {
            $this->_getSession()->addError(Mage::helper('ghstatements')->__("Some error occurred!"));
            Mage::logException($e);
        }
    }    
    public function calendar_deleteAction() {
        $model = Mage::getModel("ghstatements/calendar");
        $this->_delete($model,'Calendar');
        // remove vendor settings
        $id = $this->getRequest()->getParam('id');
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $table = $write->getTableName('udropship_vendor');
        $write->update(
                $table,
                array("statements_calendar" => NULL),
                "statements_calendar=".$id
        );
        return $this->_redirect("*/*/calendar");

    }
    public function calendar_item_deleteAction() {
        $model = Mage::getModel("ghstatements/calendar_item");
        $this->_delete($model,'Event');
        $calendar_id = $this->getRequest()->getParam('calendar_id');
        return $this->_redirect("*/*/calendar_item",array('id'=>$calendar_id));

    }

    /**
     * Acl check for this controller
     *
     * @return bool
     */
    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('admin/vendors/ghstatements_vendor');
    }

}
