<?php
/**
  
 */

require_once "app/code/community/ZolagoOs/OmniChannel/controllers/VendorController.php";

class ZolagoOs_OmniChannelVendorAskQuestion_VendorController extends ZolagoOs_OmniChannel_VendorController
{
    public function indexAction()
    {
        $this->_forward('questions');
    }
    public function questionsAction()
    {
        $session = Mage::getSingleton('udropship/session');
        $session->setUdqaLastQuestionsGridUrl(Mage::getUrl('*/*/*', array('_current'=>true)));
        $this->_renderPage(null, 'udqa');
    }
    public function questionEditAction()
    {
        $this->_renderPage(null, 'udqa');
    }
    public function questionPostAction()
    {
        $session = Mage::getSingleton('udropship/session');

        if ($data = $this->getRequest()->getPost('question')) {
            $id = $this->getRequest()->getParam('id');

            try {
                $question = Mage::getModel('udqa/question')->load($id);

                if (!$question->validateVendor($session->getVendorId())) {
                    Mage::throwException('Question not found');
                }

                if ($this->getRequest()->getParam('send_email')) {
                    $question->setIsCustomerNotified(0);
                }

                $question->addData($data)->save();

                $session->addSuccess($this->__('Question was successfully saved'));
                $session->setUdqaData(false);

                $this->_redirectQuestionAfterPost();

                return;
            } catch (Exception $e) {
                $session->addError($e->getMessage());
                Mage::logException($e);
                $session->setUdqaData($data);
                $this->_redirectQuestionAfterPost();
                return;
            }
        }
        $session->addError($this->__('Unable to find a data to save'));
        $this->_redirectQuestionAfterPost();
    }
    protected function _redirectQuestionAfterPost()
    {
        $session = Mage::getSingleton('udropship/session');
        if ($session->getUdqaLastQuestionsGridUrl()) {
            $this->_redirectUrl($session->getUdqaLastQuestionsGridUrl());
        } else {
            $this->_redirect('udqa/vendor/questions');
        }
    }
}