<?php
/**
  
 */

class ZolagoOs_OmniChannelVendorAskQuestion_CustomerController extends Mage_Core_Controller_Front_Action
{
    public function preDispatch()
    {
        parent::preDispatch();
        if ($this->getRequest()->getActionName() != 'post') {
            if (!Mage::getSingleton('customer/session')->authenticate($this)) {
                $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            }
        }
    }

    protected function _saveFormData($data=null, $id=null)
    {
        Mage::helper('udqa')->saveFormData($data, $id);
    }

    protected function _fetchFormData($id=null)
    {
        return Mage::helper('udqa')->fetchFormData($id);
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('udqa/session');
        if ($navigationBlock = $this->getLayout()->getBlock('customer_account_navigation')) {
            $navigationBlock->setActive('udqa/customer');
        }
        $this->getLayout()->getBlock('head')->setTitle($this->__('My Vendor Questions'));
        $this->renderLayout();
    }

    public function viewAction()
    {
        $question = Mage::getModel('udqa/question')->load($this->getRequest()->getParam('question_id'));
        if (!$question->getId() || !$question->validateCustomer(Mage::getSingleton('customer/session')->getCustomerId())) {
            Mage::getSingleton('udqa/session')->addError(
                Mage::helper('udqa')->__('Question not found.'));
            $this->_redirect('*/*/index');
            return $this;
        } else {
            Mage::register('udqa_question', $question);
            $this->loadLayout();
            $this->_initLayoutMessages('customer/session');
            $this->_initLayoutMessages('udqa/session');
            $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
            if ($navigationBlock) {
                $navigationBlock->setActive('udqa/customer');
            }
            $this->renderLayout();
        }
    }

    public function editAction()
    {
        $this->_forward('form');
    }

    public function newAction()
    {
        $this->_forward('form');
    }

    public function formAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('udqa/session');
        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('udqa/customer');
        }
        $this->renderLayout();
    }

    public function postAction()
    {
        if (!$this->getRequest()->isPost() && ($data = $this->_fetchFormData())) {
            $question = array();
            if (isset($data['question']) && is_array($data['question'])) {
                $question = $data['question'];
            }
        } else {
            $data   = $this->getRequest()->getPost();
            $question = $this->getRequest()->getParam('question', array());
        }

        $cSess = Mage::getSingleton('customer/session');

        $customer = $cSess->getCustomer();

        $error = false;
        if (!empty($data)) {
            $session = empty($question['product_id'])
                ? Mage::getSingleton('udqa/session')
                : Mage::getSingleton('catalog/session');
            unset($question['question_id']);
            $qModel   = Mage::getModel('udqa/question')
                ->setData($question)
                ->setQuestionDate(now());
            if ($cSess->isLoggedIn()) {
                $qModel
                    ->setCustomerEmail($customer->getEmail())
                    ->setCustomerName($customer->getFirstname().' '.$customer->getLastname())
                    ->setCustomerId($customer->getId());
            }
            $validate = $qModel->validate();
            if ($validate === true) {
                try {
                    $qModel->save();
                    $session->addSuccess($this->__('Your question has been accepted for moderation.'));
                }
                catch (Exception $e) {
                    $error = true;
                    $this->_saveFormData($data);
                    $session->addError($this->__('Unable to post the question.'));
                }
            }
            else {
                $error = true;
                $this->_saveFormData($data);
                if (is_array($validate)) {
                    foreach ($validate as $errorMessage) {
                        $session->addError($errorMessage);
                    }
                }
                else {
                    $session->addError($this->__('Unable to post the question.'));
                }
            }
        }

//        empty($question['product_id'])
//            ? $this->_redirect('*/*/index')
//            : $this->_redirectReferer();

		$this->_redirectReferer();
        return $this;

        !$error && isset($qModel) && $qModel->getId()
            ? $this->_redirect('*/*/view', array('question_id'=>$qModel->getId()))
            : $this->_redirect('*/*/index');
    }
}