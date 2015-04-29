<?php

require_once Mage::getModuleDir('controllers', 'SalesManago_Tracking') . DS . "Newsletter". DS ."SubscriberController.php";

class Zolago_Newsletter_SubscriberController extends Mage_Newsletter_SubscriberController
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


    public function sm_newsletter_unsubscribeAction(){
        $email = $this->getRequest()->getParam('email');
        $key = $this->getRequest()->getParam('key');
        $result = array();

        $apiSecret = Mage::getStoreConfig('salesmanago_tracking/general/api_secret');
        $sha1 = sha1($email.$apiSecret);

        if (isset($email) && isset($key) && isset($sha1) && filter_var($email, FILTER_VALIDATE_EMAIL) && $key==$sha1){
            try {
                $status = Mage::getModel('newsletter/subscriber')->loadByEmail($email)->isSubscribed();
                if($status){
                    Mage::getModel('newsletter/subscriber')->loadByEmail($email)->unsubscribe();
                    $result['success'] = true;
                    $result['message'] = 'Email succesfully unsubscribed';
                } else{
                    $result['success'] = false;
                    $result['message'] = 'Email already unsubscribed';
                }
            }
            catch (Mage_Core_Exception $e) {
                $result['success'] = false;
                $result['message'] = 'General error';
            }
            catch (Exception $e) {
                $result['success'] = false;
                $result['message'] = 'General error';
            }
        } else{
            $result['success'] = false;
            $result['message'] = 'Validation failed';
        }

        $json = json_encode($result);
        return $json;
    }
}