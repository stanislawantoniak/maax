<?php
require_once Mage::getModuleDir('controllers', 'SalesManago_Tracking') . DS . "Newsletter". DS ."SubscriberController.php";
class Zolago_Newsletter_SubscriberController extends SalesManago_Tracking_Newsletter_SubscriberController
{
    public function invitationAction()
    {
        Mage::log("invitationAction", null, "Zolago_Newsletter_SubscriberController.log");
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
        Mage::log("confirm ID {$id}, code {$code}", null, "Zolago_Newsletter_SubscriberController.log");

        $errors = true;
        if ($id && $code) {
            $subscriber = Mage::getModel('zolagonewsletter/subscriber')->load($id);
            $session = Mage::getSingleton('core/session');
            if($subscriber->getId() && $subscriber->getCode()) {
                Mage::log("subscriber->confirm", null, "Zolago_Newsletter_SubscriberController.log");
                if($subscriber->confirm($code)) {
                    $errors = false;
                    /** @var Zolago_Newsletter_Model_Subscriber $model */
                    $model = Mage::getModel("zolagonewsletter/subscriber");
                    try {
                        Mage::log("sendConfirmationSuccessEmail", null, "Zolago_Newsletter_SubscriberController.log");
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

    /**
     * After SalesManago unsubscribe subscriber
     * system redirect to this thank you page with some marketing info about
     * why it is better to be subscribed
     */
    public function sm_unsubscribe_redirectAction() {
        $this->loadLayout()->renderLayout();
    }

    /**
     * Subscribing again after SalesManago unsubscribe
     */
    public function subscribeAgainAction() {
        $hlp    = Mage::helper('zolagonewsletter');
        $email  = str_replace(' ', '+', $this->getRequest()->getParam('email'));
        $key = $this->getRequest()->getParam('key');
        $isValid = false;

        $active = Mage::getStoreConfig('salesmanago_tracking/general/active');
        if($active == 1) {
            $apiSecret = Mage::getStoreConfig('salesmanago_tracking/general/api_secret');
            $sha1 = sha1($email . $apiSecret);
            if (isset($email) && isset($key) && isset($sha1) && filter_var($email, FILTER_VALIDATE_EMAIL) && $key == $sha1) {
                $isValid = true;
            }
        }

        if (!$this->_validateFormKey() || !$isValid) {
            $session = Mage::getSingleton('customer/session');
            $session->addError($hlp->__('An error occurred while saving your subscription.'));
            return $this->_redirect('');
        }
        try {
            /** @var Zolago_Newsletter_Model_Subscriber $subscriber */
            $subscriber = Mage::getModel('newsletter/subscriber');
            $subscriber->loadByEmail($email);
            if ($subscriber->getId()) {
                $subscriber->setSubscriberStatus(Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED);
                $subscriber->save();
                Mage::getSingleton('customer/session')->addSuccess($hlp->__("The subscription has been saved."));
            } else {
                Mage::getSingleton('customer/session')->addSuccess($hlp->__('An error occurred while saving your subscription.'));
            }
        }
        catch (Exception $e) {
            Mage::logException($e);
            Mage::getSingleton('customer/session')->addError($hlp->__('An error occurred while saving your subscription.'));
        }
        $this->_redirect('');
    }

    /**
     * Function for coll back opt-out from SalesManago
     * It tell us that in SM system subscriber change his state to unsubscribed
     * @return string
     */
    public function sm_newsletter_unsubscribeAction() {
        $active = Mage::getStoreConfig('salesmanago_tracking/general/active');
        if($active == 1) {
            $email = $this->getRequest()->getParam('email');
            $key = $this->getRequest()->getParam('key');
            $result = array();

            $apiSecret = Mage::getStoreConfig('salesmanago_tracking/general/api_secret');
            $sha1 = sha1($email . $apiSecret);

            if (isset($email) && isset($key) && isset($sha1) && filter_var($email, FILTER_VALIDATE_EMAIL) && $key == $sha1) {
                try {
                    /** @var Zolago_Newsletter_Model_Subscriber $subscriber */
                    $subscriber = Mage::getModel('zolagonewsletter/subscriber')->loadByEmail($email);
                    $status     = $subscriber->isSubscribed();

                    if ($status) {
                        $subscriber->unsubscribe();
                        $result['success'] = true;
                        $result['message'] = 'Email succesfully unsubscribed';

                    } else {
                        $result['success'] = false;
                        $result['message'] = 'Email already unsubscribed';
                    }
                } catch (Mage_Core_Exception $e) {
                    $result['success'] = false;
                    $result['message'] = 'General error';
                } catch (Exception $e) {
                    $result['success'] = false;
                    $result['message'] = 'General error';
                }
            } else {
                $result['success'] = false;
                $result['message'] = 'Validation failed';
            }

            $json = json_encode($result);
            return $json;
        }
    }

    /**
     * Function for coll back opt-in from SalesManago
     * It tell us that in SM system subscriber change his state to subscribed
     * @return string
     */
    public function sm_newsletter_subscribeAction(){
        $active = Mage::getStoreConfig('salesmanago_tracking/general/active');
        if($active == 1) {
            $email = $this->getRequest()->getParam('email');
            $key = $this->getRequest()->getParam('key');
            $result = array();

            $apiSecret = Mage::getStoreConfig('salesmanago_tracking/general/api_secret');
            $sha1 = sha1($email . $apiSecret);

            if (isset($email) && isset($key) && isset($sha1) && filter_var($email, FILTER_VALIDATE_EMAIL) && $key == $sha1) {
                try {
                    /** @var Zolago_Newsletter_Model_Subscriber $subscriber */
                    $subscriber = Mage::getModel('zolagonewsletter/subscriber')->loadByEmail($email);
                    $status     = $subscriber->isSubscribed();

                    if (!$status) {
                        $subscriber->simpleSubscribe();
                        $result['success'] = true;
                        $result['message'] = 'Email succesfully subscribed';

                    } else {
                        $result['success'] = false;
                        $result['message'] = 'Email already subscribed';
                    }
                } catch (Mage_Core_Exception $e) {
                    $result['success'] = false;
                    $result['message'] = 'General error';
                } catch (Exception $e) {
                    $result['success'] = false;
                    $result['message'] = 'General error';
                }
            } else {
                $result['success'] = false;
                $result['message'] = 'Validation failed';
            }

            $json = json_encode($result);
            return $json;
        }
    }
}