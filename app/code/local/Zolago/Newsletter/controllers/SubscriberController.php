<?php
require_once Mage::getModuleDir('controllers', 'SalesManago_Tracking') . DS . "Newsletter". DS ."SubscriberController.php";
class Zolago_Newsletter_SubscriberController extends SalesManago_Tracking_Newsletter_SubscriberController
{
    public function invitationAction()
    {
        $_helper = Mage::helper("zolagonewsletter");
        $error = $this->confirm(
            $_helper->__('Invalid newsletter invitation code.'),
            $_helper->__('Invalid subscription ID.')
        );
        $this->redirectByError($error);
    }

    /**
     * Subscription confirm action
     */
    public function confirmAction()
    {
        $_helper = Mage::helper("zolagonewsletter");
        $error = $this->confirm(
            $this->__('Invalid subscription confirmation code.'),
            $_helper->__('Invalid subscription ID.')
        );
        $this->redirectByError($error);
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
                    try {
                        $model->sendConfirmationSuccessEmail($id);
                    } catch(Exception $e) {
                        $errors = true;
                        $_helper = Mage::helper("zolagonewsletter");
                        $session->addError($_helper->__("Some newsletter error occurred"));
                    }
                } else {
                    $session->addError($error1);
                }
            } else {
                $session->addError($error2);
            }
        }
        return $errors;
    }

    /**
     * @param bool $error
     */
    protected function redirectByError($error=true) {
        if(!$error) {
            $this->_redirectUrl(Mage::getUrl("newsletter/thankyou"));
        } else {
            $this->_redirectUrl(Mage::getBaseUrl());
        }
    }
}