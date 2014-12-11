<?php
require_once Mage::getModuleDir('controllers', 'Mage_Newsletter') . "/SubscriberController.php";

class Zolago_Newsletter_SubscriberController extends Mage_Newsletter_SubscriberController
{
    public function invitationAction()
    {
        $id    = (int) $this->getRequest()->getParam('id');
        $code  = (string) $this->getRequest()->getParam('code');

        if ($id && $code) {
            $subscriber = Mage::getModel('newsletter/subscriber')->load($id);
            $session = Mage::getSingleton('core/session');

            if($subscriber->getId() && $subscriber->getCode()) {
                if($subscriber->confirm($code)) {
                    $session->addSuccess($this->__('Your have been successfully subscribed to our newsletter.'));
                } else {
                    $session->addError($this->__('Invalid newsletter invitation code.'));
                }
            } else {
                $session->addError($this->__('Invalid subscription ID.'));
            }
        }
        $this->_redirectUrl(Mage::getBaseUrl());
    }
}
