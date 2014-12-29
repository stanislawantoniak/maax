<?php

/**
 * Licentia Fidelitas - Advanced Email and SMS Marketing Automation for E-Goi
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/4.0/
 *
 * @title      Advanced Email and SMS Marketing Automation
 * @category   Marketing
 * @package    Licentia
 * @author     Bento Vilas Boas <bento@licentia.pt>
 * @copyright  Copyright (c) 2012 Licentia - http://licentia.pt
 * @license    Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International
 */
class Licentia_Fidelitas_Model_Observer {

    public function autoMatchCustomer($event) {

        $customer = $event->getDataObject();

        $list = Mage::getModel('fidelitas/lists')->getListForStore($customer->getStoreId());

        if ($list->getId()) {
            $subscriber = Mage::getModel('fidelitas/subscribers')->subscriberExists('email', $customer->getEmail(), $list->getListnum());

            try {
                if ($subscriber && $subscriber->getId()) {
                    $subscriber->setData('customer_id', $customer->getId())->save();
                }
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }

    public function addEmailFromAddress($event) {

        $place = Mage::getStoreConfig('fidelitas/config/place');
        if ($place != 'address') {
            return false;
        }

        $address = $event->getDataObject();
        if (!$address->getEmail() || $address->getAddressType() != 'billing') {
            return;
        }

        $email = $address->getEmail();

        $list = Mage::getModel('fidelitas/lists')->getListForStore(Mage::app()->getStore()->getId());

        if (!$list->getAuto()) {
            return;
        }

        try {
            Mage::getModel('fidelitas/subscribers')->setData(array('email' => $email, 'listID' => $list->getListnum()))->save();
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    public function loadVariables($event) {

        $referer = Mage::app()->getRequest()->getServer('HTTP_REFERER');

        if (stripos($referer, 'fidelitas_') === false) {
            return;
        }

        $customVariables = Mage::getModel('core/variable')->getVariablesOptionArray(true);
        $storeContactVariabls = Mage::getModel('core/source_email_variables')->toOptionArray(true);

        $fidelitas = array('label' => 'E-Goi Variables',
            'value' => array(
                array('label' => 'First Name', 'value' => '!fname'),
                array('label' => 'Last Name', 'value' => '!lname'),
                array('label' => 'Email', 'value' => '!email'),
                array('label' => 'Date of Birth', 'value' => '!birth_date'),
                array('label' => 'Cellphone', 'value' => '!cellphone'),
            )
        );

        $variables = array($storeContactVariabls, $customVariables, $fidelitas);
        echo Zend_Json::encode($variables);
        die();
    }

    public function notifyBuild() {
        $admin = Mage::getSingleton('admin/session')->getUser();

        if (!$admin) {
            return;
        }

        if (version_compare(Mage::getVersion(), '1.7') == -1) {
            $parent = Mage::getSingleton('admin/config')->getAdminhtmlConfig()->getNode('menu');
            list($element) = $parent->xpath('fidelitas/children/report/children/coupons');
            unset($element->{0});
        }

        $user = $admin->getId();

        $req = Mage::app()->getFrontController()->getRequest();
        if (stripos($req->getControllerName(), 'fidelitas_') !== false) {
            Mage::register('fidelitas_time_form_element', true, true);
        }

        $account = Mage::getModel('fidelitas/account')->load(1);
        if ($account->getNotifyUser() == $user && $account->getCron() == 0) {
            $account->setNotifyUser(0)->save();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('fidelitas')->__('Your background data sync updates have finished.'));
        }


        $segments = Mage::getModel('fidelitas/segments')
                ->getCollection()
                ->addFieldToSelect('notify_user')
                ->addFieldToSelect('segment_id')
                ->addFieldToFilter('notify_user', $user)
                ->addFieldToFilter('build', 0);

        if ($segments->count() == 0) {
            return;
        }

        foreach ($segments as $segment) {
            $segmentId = $segment->getId();
            $segment->setData('notify_user', 0)->save();
        }

        $url = Mage::helper('adminhtml')->getUrl('*/fidelitas_segments/records', array('id' => $segmentId));

        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('fidelitas')->__('Your background segments updates have finished. Click <a href="%s">here</a> to view the results', $url));
    }

    public function validateCoupon($event) {
        $request = $event->getControllerAction()->getRequest();

        $coupon = $request->getParam('coupon_code');

        $model = Mage::getModel('fidelitas/coupons');

        if (!$model->validateCoupon($coupon)) {
            $request->setparam('coupon_code', 'INVALID_COUPON_' . time());
        }
    }

    public function sendSmsNewContact($event) {

        $request = $event->getControllerAction()->getRequest();

        if (!$request->isPost()) {
            return false;
        }

        $post = $request->getPost();

        $error = false;

        if (!Zend_Validate::is(trim($post['name']), 'NotEmpty')) {
            $error = true;
        }

        if (!Zend_Validate::is(trim($post['comment']), 'NotEmpty')) {
            $error = true;
        }

        if (!Zend_Validate::is(trim($post['email']), 'EmailAddress')) {
            $error = true;
        }

        if (Zend_Validate::is(trim($post['hideit']), 'NotEmpty')) {
            $error = true;
        }

        if ($error) {
            return;
        }

        $storeName = Mage::app()->getStore()->getName();
        $config = Mage::getStoreConfig('fidelitas/notifications', Mage::app()->getStore()->getId());

        if (!$config['contact'] || !$config['sender'] || !$config['recipients']) {
            return;
        }
        try {
            $list = Mage::getModel('fidelitas/lists')->getAdminList();

            $msg = Mage::helper('fidelitas')->__('New contact submission. From: %s. Email: %s. Store: %s', $post['name'], $post['email'], $storeName);

            $data = array();
            $data['cellphone'] = explode(',', $config['recipients']);
            $data['message'] = substr($msg, 0, 160);
            $data['auto'] = '1';
            $data['fromID'] = $config['sender'];
            $data['subject'] = 'Contact Form Notification';
            $data['listID'] = $list->getListnum();

            $result = Mage::getModel('fidelitas/egoi')->setData($data)->sendSMS();
        } catch (Exception $e) {
            Mage::logException($e);
            $result = false;
        }

        return $result;
    }

    public function sendSmsNewTag($event) {


        $tag = $event->getObject();

        $storeName = Mage::app()->getStore($tag->getStoreId())->getName();
        $config = Mage::getStoreConfig('fidelitas/notifications', $tag->getStoreId());

        if (!$config['tag'] || !$config['sender'] || !$config['recipients']) {
            return;
        }


        try {
            $list = Mage::getModel('fidelitas/lists')->getAdminList();

            $msg = Mage::helper('fidelitas')->__('New tag submitted: %s. Store: %s', $tag->getName(), $storeName);

            $data = array();
            $data['cellphone'] = explode(',', $config['recipients']);
            $data['message'] = substr($msg, 0, 160);
            $data['auto'] = '1';
            $data['fromID'] = $config['sender'];
            $data['subject'] = 'New Tag Notification';
            $data['listID'] = $list->getListnum();

            $result = Mage::getModel('fidelitas/egoi')->setData($data)->sendSMS();
        } catch (Exception $e) {
            Mage::logException($e);
            $result = false;
        }

        return $result;
    }

    public function sendSmsNewReview($event) {
        $review = $event->getObject();

        $storeName = Mage::app()->getStore($review->getStoreId())->getName();
        $config = Mage::getStoreConfig('fidelitas/notifications', $review->getStoreId());

        if (!$config['review'] || !$config['sender'] || !$config['recipients']) {
            return;
        }
        try {
            $list = Mage::getModel('fidelitas/lists')->getAdminList();

            $msg = Mage::helper('fidelitas')->__('New review from %s. Store: %s. Title: %s', $review->getNickname(), $storeName, $review->getTitle());

            $data = array();
            $data['cellphone'] = explode(',', $config['recipients']);
            $data['message'] = substr($msg, 0, 160);
            $data['auto'] = '1';
            $data['fromID'] = $config['sender'];
            $data['subject'] = 'New Review Notification';
            $data['listID'] = $list->getListnum();

            $result = Mage::getModel('fidelitas/egoi')->setData($data)->sendSMS();
        } catch (Exception $e) {
            Mage::logException($e);
            $result = false;
        }

        return $result;
    }

    public function sendSmsNewOrder($event) {

        $order = $event->getEvent()->getOrder();

        $config = Mage::getStoreConfig('fidelitas/notifications', $order->getStoreId());

        if (!$config['order'] || !$config['sender'] || !$config['recipients']) {
            return;
        }

        try {
            $list = Mage::getModel('fidelitas/lists')->getAdminList();

            $msg = Mage::helper('fidelitas')->__('New order for: %s. Order total: %s. Base subtotal: %s', $order->getStoreName(), $order->getBaseGrandTotal(), $order->getBaseSubtotal());

            $data = array();
            $data['cellphone'] = explode(',', $config['recipients']);
            $data['message'] = substr($msg, 0, 160);
            $data['auto'] = '1';
            $data['fromID'] = $config['sender'];
            $data['subject'] = 'New Order Notification';
            $data['listID'] = $list->getListnum();


            Mage::getModel('fidelitas/egoi')->setData($data)->sendSMS();
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    public function notifySms($event) {

        $request = $event->getControllerAction()->getRequest()->getParams();
        try {
            Mage::getModel('fidelitas/campaigns')->sendSmsComment($request);
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    public function customerDeleted($event) {

        $customer = $event->getEvent()->getCustomer();
        try {
            Mage::getModel('fidelitas/subscribers')->processDeletedCustomer($customer);
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    public function newCustomer($event) {

        $customer = $event->getEvent()->getCustomer();
        $storeId = $customer->getStoreId();

        $list = Mage::getModel('fidelitas/lists')->getListForStore($storeId);

        if (!$list->getAuto()) {
            return false;
        }

        try {
            Mage::getModel('fidelitas/subscribers')->addCustomerToList($customer->getId(), $list->getListnum());
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    public function addToAutoList($event) {

        $order = $event->getEvent()->getOrder();

        try {

            $place = Mage::getStoreConfig('fidelitas/config/place');
            if ($place == 'order') {
                $email = $order->getCustomerEmail();
                $list = Mage::getModel('fidelitas/lists')->getListForStore($order->getStoreId());
                if ($list->getAuto()) {
                    Mage::getModel('fidelitas/subscribers')->setData(array('email' => $email, 'listID' => $list->getListnum()))->save();
                }
            }


            $customer = Mage::getModel('fidelitas/subscribers')
                    ->findCustomer($order->getCustomerEmail(), 'email');

            if (!$customer) {
                return false;
            }

            $storeId = $customer->getStoreId();

            $list = Mage::getModel('fidelitas/lists')->getListForStore($storeId);

            if (!$list->getAuto()) {
                return false;
            }

            Mage::getModel('fidelitas/subscribers')
                    ->addCustomerToList($customer->getId(), $list->getListnum());
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

}
