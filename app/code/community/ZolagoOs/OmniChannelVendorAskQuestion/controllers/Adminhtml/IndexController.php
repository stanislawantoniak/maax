<?php

class ZolagoOs_OmniChannelVendorAskQuestion_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{
    protected $_publicActions = array('edit');

    public function preDispatch()
    {
        parent::preDispatch();
    }

    public function indexAction()
    {
        $this->_title($this->__('Sales'))
             ->_title($this->__('Dropship'))
             ->_title($this->__('Vendor Questions'))
             ->_title($this->__('All Questions/Answers'));

        if ($this->getRequest()->getParam('ajax')) {
            return $this->_forward('questionGrid');
        }

        $this->loadLayout();
        $this->_setActiveMenu('sales/udropship/udqa');

        $this->_addContent($this->getLayout()->createBlock('udqa/adminhtml_question_main'));

        $this->renderLayout();
    }

    public function pendingAction()
    {
        $this->_title($this->__('Sales'))
             ->_title($this->__('Dropship'))
             ->_title($this->__('Vendor Questions'))
             ->_title($this->__('Pending Questions/Answers'));

        if ($this->getRequest()->getParam('ajax')) {
            Mage::register('usePendingFilter', true);
            return $this->_forward('questionGrid');
        }

        $this->loadLayout();
        $this->_setActiveMenu('sales/udropship/udqa');

        Mage::register('usePendingFilter', true);
        $this->_addContent($this->getLayout()->createBlock('udqa/adminhtml_question_main'));

        $this->renderLayout();
    }

    public function editAction()
    {
        $this->_title($this->__('Sales'))
             ->_title($this->__('Dropship'))
             ->_title($this->__('Vendor Questions'))
             ->_title($this->__('Edit Vendor Question'));

        $this->loadLayout();
        $this->_setActiveMenu('sales/udropship/udqa');

        $this->_addContent($this->getLayout()->createBlock('udqa/adminhtml_question_edit'));

        $this->renderLayout();
    }

    public function saveAction()
    {
        if (($data = $this->getRequest()->getPost()) && ($questionId = $this->getRequest()->getParam('id'))) {
            $question = Mage::getModel('udqa/question')->load($questionId);

            if (! $question->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('udqa')->__('The question was removed by another user or does not exist.'));
            } else {
                try {
                    $question->setIsAdminChanges(true);
                    $question->addData($data)->save();
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('udqa')->__('The question has been saved.'));
                } catch (Exception $e){
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }
            }

            return $this->getResponse()->setRedirect($this->getUrl($this->getRequest()->getParam('ret') == 'pending' ? '*/*/pending' : '*/*/'));
        }
        $this->_redirectReferer();
    }

    public function deleteAction()
    {
        $questionId = $this->getRequest()->getParam('id', false);

        try {
            Mage::getModel('udqa/question')->setId($questionId)->delete();

            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('udqa')->__('The question has been deleted'));
            if( $this->getRequest()->getParam('ret') == 'pending' ) {
                $this->getResponse()->setRedirect($this->getUrl('*/*/pending'));
            } else {
                $this->getResponse()->setRedirect($this->getUrl('*/*/'));
            }
            return;
        } catch (Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirectReferer();
    }

    public function massDeleteAction()
    {
        $questionsIds = $this->getRequest()->getParam('questions');
        if(!is_array($questionsIds)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select question(s).'));
        } else {
            try {
                foreach ($questionsIds as $questionId) {
                    $model = Mage::getModel('udqa/question')->load($questionId);
                    $model->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) have been deleted.', count($questionsIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/' . $this->getRequest()->getParam('ret', 'index'));
    }

    public function massUpdateQuestionStatusAction()
    {
        $questionsIds = $this->getRequest()->getParam('questions');
        if(!is_array($questionsIds)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select question(s).'));
        } else {
            $session = Mage::getSingleton('adminhtml/session');
            try {
                $status = $this->getRequest()->getParam('status');
                foreach ($questionsIds as $questionId) {
                    $model = Mage::getModel('udqa/question')->load($questionId);
                    $model->setIsAdminChanges(true);
                    $model->setQuestionStatus($status)->save();
                }
                $session->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) have been updated.', count($questionsIds))
                );
            }
            catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
            }
            catch (Exception $e) {
                $session->addError(Mage::helper('adminhtml')->__('An error occurred while updating the selected question(s).'));
            }
        }

        $this->_redirect('*/*/' . $this->getRequest()->getParam('ret', 'index'));
    }

    public function massUpdateAnswerStatusAction()
    {
        $questionsIds = $this->getRequest()->getParam('questions');
        if(!is_array($questionsIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select question(s).'));
        } else {
            $session = Mage::getSingleton('adminhtml/session');
            try {
                $status = $this->getRequest()->getParam('status');
                foreach ($questionsIds as $questionId) {
                    $model = Mage::getModel('udqa/question')->load($questionId);
                    $model->setAnswerStatus($status)->save();
                }
                $session->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) have been updated.', count($questionsIds))
                );
            }
            catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
            }
            catch (Exception $e) {
                $session->addError(Mage::helper('adminhtml')->__('An error occurred while updating the selected question(s).'));
            }
        }

        $this->_redirect('*/*/' . $this->getRequest()->getParam('ret', 'index'));
    }

    public function massSendCustomerAction()
    {
        $questionsIds = $this->getRequest()->getParam('questions');
        if(!is_array($questionsIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select question(s).'));
        } else {
            $session = Mage::getSingleton('adminhtml/session');
            try {
                foreach ($questionsIds as $questionId) {
                    $model = Mage::getModel('udqa/question')->load($questionId);
                    if ($model->getId()) {
                        $model->setForcedCustomerNotificationFlag(1);
                        Mage::helper('udqa')->notifyCustomer($model);
                    }
                }
                $session->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d customer notification(s) have been sent.', count($questionsIds))
                );
            }
            catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
            }
            catch (Exception $e) {
                $session->addError(Mage::helper('adminhtml')->__('An error occurred while sending customer notification(s).'));
            }
        }

        $this->_redirect('*/*/' . $this->getRequest()->getParam('ret', 'index'));
    }

    public function massSendVendorAction()
    {
        $questionsIds = $this->getRequest()->getParam('questions');
        if(!is_array($questionsIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select question(s).'));
        } else {
            $session = Mage::getSingleton('adminhtml/session');
            try {
                foreach ($questionsIds as $questionId) {
                    $model = Mage::getModel('udqa/question')->load($questionId);
                    if ($model->getId()) {
                        $model->setForcedVendorNotificationFlag(1);
                        Mage::helper('udqa')->notifyVendor($model);
                    }
                }
                $session->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d vendor notification(s) have been sent.', count($questionsIds))
                );
            }
            catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
            }
            catch (Exception $e) {
                die("$e");
                $session->addError(Mage::helper('adminhtml')->__('An error occurred while sending vendor notification(s).'));
            }
        }

        $this->_redirect('*/*/' . $this->getRequest()->getParam('ret', 'index'));
    }

    public function questionGridAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('udqa/adminhtml_question_grid')->toHtml());
    }

    public function customerQuestionsAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('udqa/adminhtml_question_grid', 'admin.customer.questions')
                ->setCustomerId($this->getRequest()->getParam('id'))
                ->setUisMassactionAvailable(false)
                ->setUseAjax(true)
                ->toHtml()
        );
    }

    public function vendorQuestionsAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('udqa/adminhtml_question_grid', 'admin.vendor.questions')
                ->setVendorId($this->getRequest()->getParam('id'))
                ->setUisMassactionAvailable(false)
                ->setUseAjax(true)
                ->toHtml()
        );
    }

    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'pending':
                return Mage::getSingleton('admin/session')->isAllowed('sales/udropship/question/question_pending');
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('sales/udropship/question/question_all');
                break;
        }
    }
}
