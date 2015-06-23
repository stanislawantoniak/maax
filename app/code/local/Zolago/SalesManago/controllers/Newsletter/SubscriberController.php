<?php

require_once Mage::getConfig()->getModuleDir('controllers', "SalesManago_Tracking") . DS . "SubscriberController.php";

class Zolago_Tracking_Newsletter_SubscriberController extends SalesManago_Tracking_Newsletter_SubscriberController {


    public function sm_newsletter_unsubscribeAction() {
        Mage::log(__METHOD__ . '(' . __LINE__ . ')', null, 'mylog.log');
        $active = Mage::getStoreConfig('salesmanago_tracking/general/active');
        if($active == 1) {
            $email = $this->getRequest()->getParam('email');
            $key = $this->getRequest()->getParam('key');
            $result = array();

            $apiSecret = Mage::getStoreConfig('salesmanago_tracking/general/api_secret');
            $sha1 = sha1($email . $apiSecret);

            if (isset($email) && isset($key) && isset($sha1) && filter_var($email, FILTER_VALIDATE_EMAIL) && $key == $sha1) {
                try {
                    $status = Mage::getModel('newsletter/subscriber')->loadByEmail($email)->isSubscribed();

                    if ($status) {
                        Mage::getModel('newsletter/subscriber')->loadByEmail($email)->unsubscribe();
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
            Mage::log($result, null, 'mylog.log');

            $json = json_encode($result);
            return $json;
        }
    }
    public function sm_newsletter_subscribeAction(){
        Mage::log(__METHOD__ . '(' . __LINE__ . ')', null, 'mylog.log');
        $active = Mage::getStoreConfig('salesmanago_tracking/general/active');
        if($active == 1) {
            $email = $this->getRequest()->getParam('email');
            $key = $this->getRequest()->getParam('key');
            $result = array();

            $apiSecret = Mage::getStoreConfig('salesmanago_tracking/general/api_secret');
            $sha1 = sha1($email . $apiSecret);

            if (isset($email) && isset($key) && isset($sha1) && filter_var($email, FILTER_VALIDATE_EMAIL) && $key == $sha1) {
                try {
                    $status = Mage::getModel('newsletter/subscriber')->loadByEmail($email)->isSubscribed();

                    if (!$status) {
                        Mage::getModel('newsletter/subscriber')->loadByEmail($email)->simpleSubscribe();
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
            Mage::log($result, null, 'mylog.log');
            $json = json_encode($result);
            return $json;
        }
    }
}