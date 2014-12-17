<?php
require_once Mage::getModuleDir('controllers', 'Mage_Newsletter') . "/SubscriberController.php";

class Zolago_Newsletter_SubscriberController extends Mage_Newsletter_SubscriberController
{
    public function invitationAction()
    {
        $_helper = Mage::helper("zolagonewsletter");
        $this->confirm(
            $_helper->__('Invalid newsletter invitation code.'),
            $_helper->__('Invalid subscription ID.')
        );
    }

    /**
     * Subscription confirm action
     */
    public function confirmAction()
    {
        $_helper = Mage::helper("zolagonewsletter");
        $this->confirm(
            $this->__('Invalid subscription confirmation code.'),
            $_helper->__('Invalid subscription ID.')
        );
    }

    protected function confirm($error1,$error2) {
        $id    = (int) $this->getRequest()->getParam('id');
        $code  = (string) $this->getRequest()->getParam('code');
        $errors = true;

        if ($id && $code) {
            $subscriber = Mage::getModel('newsletter/subscriber')->load($id);
            $session = Mage::getSingleton('core/session');

            if($subscriber->getId() && $subscriber->getCode()) {
                if($subscriber->confirm($code)) {
                    $errors = false;

                    /** @var Zolago_Newsletter_Model_Subscriber $model */
                    $model = Mage::getModel("zolagonewsletter/subscriber");
                    $model->sendConfirmationSuccessEmail($id);
                } else {
                    $session->addError($error1);
                }
            } else {
                $session->addError($error2);
            }
        }
        if(!$errors) {
            $this->_redirectUrl(Mage::getUrl("newsletter/thankyou"));
        } else {
            $this->_redirectUrl(Mage::getBaseUrl());
        }
    }
}
