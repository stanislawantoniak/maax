<?php

require_once Mage::getModuleDir('controllers', "ManaPro_FilterAdmin") . DS . "Mana" .DS ."FiltersController.php";

class Zolago_ManaProFilterAdmin_Mana_FiltersController extends ManaPro_FilterAdmin_Mana_FiltersController
{
    public function saveAction()
    {
        // data
        $fields = $this->getRequest()->getPost('fields');
        $useDefault = $this->getRequest()->getPost('use_default');

        if ($fields != null) $fields["display"] = "colors";
        else $fields["display"] = "list";

        if (Mage::helper('mana_admin')->isGlobal()) {
            $model = Mage::getModel('mana_filters/filter2')->load($this->getRequest()->getParam('id'));
        } else {
            $model = Mage::getModel('mana_filters/filter2_store')->loadByGlobalId($this->getRequest()->getParam('id'),
                Mage::helper('mana_admin')->getStore()->getId());
        }

        $response = new Varien_Object();
        $update = array();
        /* @var $messages Mage_Adminhtml_Block_Messages */
        $messages = $this->getLayout()->createBlock('adminhtml/messages');

        try {
            // processing
            $model->addEditedData($fields, $useDefault);
            $model->addEditedDetails($this->getRequest());
            $model->validateKeys();
            Mage::helper('mana_db')->replicateObject($model, array(
                $model->getEntityName() => array('saved' => array($model->getId()))
            ));
            $model->validate();

            // do save
            $model->save();
            Mage::dispatchEvent('m_saved', array('object' => $model));
            $messages->addSuccess($this->__('Your changes are successfully saved.'));
        } catch (Mana_Db_Exception_Validation $e) {
            foreach ($e->getErrors() as $error) {
                $messages->addError($error);
            }
            $response->setError(true);
        } catch (Exception $e) {
            $messages->addError($e->getMessage());
            $response->setError(true);
        }

        $update[] = array('selector' => '#messages', 'html' => $messages->getGroupedHtml());
        $response->setUpdate($update);
        $this->getResponse()->setBody($response->toJson());
    }
}