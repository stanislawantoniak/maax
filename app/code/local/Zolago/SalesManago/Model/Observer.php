<?php

/**
 * @method Zolago_SalesManago_Helper_Data _getHelper()
 * Class Zolago_SalesManago_Model_Observer
 */
class Zolago_SalesManago_Model_Observer extends SalesManago_Tracking_Model_Observer {

    public function customer_register_success($observer)
    {
        $active = Mage::getStoreConfig('salesmanago_tracking/general/active');
        if ($active == 1) {
            $customer = $observer->getCustomer()->getData();

            if (is_array($customer) && !empty($customer)) {
                $data = $this->_getHelper()->_setCustomerData($customer);

                $r = $this->_getHelper()->salesmanagoContactSync($data);

                if ($r == false || (isset($r['success']) && $r['success'] == false)) {
                    $data['status'] = 0;
                    $data['action'] = 1; //rejestracja
                    $this->_getHelper()->setSalesmanagoCustomerSyncStatus($data);
                }

                if (isset($r['contactId']) && !empty($r['contactId'])) {
                    try {
                        //Save single attribute salesmanago_contact_id without saving the entity
                        //to avoid call Zolago_Newsletter_Model_Observer::subscribeCustomer twice
                        //$observer->getCustomer()->setData('salesmanago_contact_id', $r['contactId'])->save();
                        $observer->getCustomer()
                            ->setData('salesmanago_contact_id', $r['contactId'])
                            ->getResource()
                            ->saveAttribute($observer->getCustomer(), "salesmanago_contact_id");
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                }
            }
        }
    }


    public function newsletter_subscriber_save_after($observer)
    {
	    $key = Zolago_SalesManago_Helper_Data::SALESMANAGO_NO_NEWSLETTER_POST_REGISTRY_KEY;
	    if(Mage::registry($key) === true) {
		    Mage::unregister($key);
		    return array();
	    }
        $active = Mage::getStoreConfig('salesmanago_tracking/general/active');
        if ($active == 1) {
            $request = Mage::app()->getRequest();
            $moduleName = $request->getModuleName();
            $controllerName = $request->getControllerName();
            $actionName = $request->getActionName();

            if (($moduleName == 'newsletter' && $controllerName == 'manage' && $actionName == 'save') ||
                ($moduleName == 'newsletter' && $controllerName == 'subscribe' && $actionName == 'unsubscribe') ||
                ($moduleName == 'newsletter' && $controllerName == 'subscriber' && $actionName == 'confirm') ||
                ($moduleName == 'newsletter' && $controllerName == 'subscriber' && $actionName == 'invitation') ||
                ($moduleName == 'checkout' && $controllerName == 'guest' && $actionName == 'saveOrder')
            ) {

                $clientId = Mage::getStoreConfig('salesmanago_tracking/general/client_id');
                $apiSecret = Mage::getStoreConfig('salesmanago_tracking/general/api_secret');
                $ownerEmail = Mage::getStoreConfig('salesmanago_tracking/general/email');
                $tags = Mage::getStoreConfig('salesmanago_tracking/general/tags');
                $endPoint = Mage::getStoreConfig('salesmanago_tracking/general/endpoint');

                $subscriber = $observer->getEvent()->getDataObject();
                $data = $subscriber->getData();
                $email = $data['subscriber_email'];
                $statusChange = $subscriber->getIsStatusChanged();

                $id = (int)Mage::app()->getFrontController()->getRequest()->getParam('id');
                $code = (string)Mage::app()->getFrontController()->getRequest()->getParam('code');

	            $time = time();

                $apiKey = md5($time . $apiSecret);

                $data_to_json = array(
                    'apiKey' => $apiKey,
                    'clientId' => $clientId,
                    'requestTime' => $time,
                    'contact' => array(
                        'email' => $email,
                        'state' => 'CUSTOMER',
                    ),
                    'sha' => sha1($apiKey . $clientId . $apiSecret),
                    'owner' => $ownerEmail,
                );

                if ($data['subscriber_status'] == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED) {
                    $data_to_json['forceOptIn'] = true;
                    $data_to_json['forceOptOut'] = false;
                } else {
                    $data_to_json['forceOptOut'] = true;
                    $data_to_json['forceOptIn'] = false;
                }

                $json = json_encode($data_to_json);
                $result = $this->_getHelper()->_doPostRequest('https://' . $endPoint . '/api/contact/upsert', $json);

                $r = json_decode($result, true);

                if ($r == false || (isset($r['success']) && $r['success'] == false)) {
                    $data['customerEmail'] = $email;
                    $data['status'] = 0;
                    $data['action'] = 4; //zapis / wypis z newslettera
                    $this->_getHelper()->setSalesmanagoCustomerSyncStatus($data);
                }

                return $r;
            }
        }
    }

    /**
     * Dodanie (oraz na biezaco modyfikowanie) zdarzenia w koszyku addContactExtEvent z typem CART
     * @see SalesManago_Tracking_Model_Observer::checkout_cart_save_after
     * @see app/code/community/SalesManago/Tracking/Model/Observer.php -> checkout_cart_save_after
     * @param $observer
     */
    public function add_cart_event($observer)
    {
        $active = Mage::getStoreConfig('salesmanago_tracking/general/active');
        if ($active == 1) {
            $cartHelper = Mage::getModel('checkout/cart')->getQuote();
            $items = $cartHelper->getAllVisibleItems();
            $itemsNamesList = array();
            foreach ($items as $item) {
                array_push($itemsNamesList, $item->getProduct()->getId());
            }


            $clientId = Mage::getStoreConfig('salesmanago_tracking/general/client_id');
            $apiSecret = Mage::getStoreConfig('salesmanago_tracking/general/api_secret');
            $ownerEmail = Mage::getStoreConfig('salesmanago_tracking/general/email');
            $endPoint = Mage::getStoreConfig('salesmanago_tracking/general/endpoint');

	        /** @var Zolago_Customer_Model_Session $customerSession */
	        $customerSession = Mage::getSingleton('customer/session');

	        /** @var Mage_Persistent_Helper_Session $persistentHelper */
	        $persistentHelper = Mage::helper('persistent/session');

            $customerEmail = $customerSession->getCustomer()->getEmail();
            $isLoggedIn = $customerSession->isLoggedIn();
            //if (!empty($customerEmail)) {
            $apiKey = md5(time() . $apiSecret);
            $dateTime = new DateTime('NOW');

            $data_to_json = array(
                'apiKey' => $apiKey,
                'clientId' => $clientId,
                'requestTime' => time(),
                'sha' => sha1($apiKey . $clientId . $apiSecret),
                'owner' => $ownerEmail,
                'contactEvent' => array(
                    'date' => $dateTime->format('c'),
                    'contactExtEventType' => 'CART',
                    'products' => implode(',', $itemsNamesList),
                    'value' => $cartHelper->getGrandTotal(),
                ),
            );


	        /** @var Zolago_SalesManago_Helper_Data $salesmanagoHelper */
	        $salesmanagoHelper = Mage::helper('tracking');

	        $eventId = false;
	        $cookieKey = 'smCartEventId';

	        if($customerSession->isLoggedIn()) {
		        $customer = $customerSession->getCustomer();
	        } elseif($persistentHelper->isPersistent() && $persistentHelper->getSession()->getCustomerId()) {
		        $customer = $persistentHelper->getCustomer();
	        } else {
		        $customer = false;
	        }

	        if($customer !== false && $customer->getId()) {
		        if($customer->getData('salesmanago_cart_event_id')) {
			        $eventId = $customer->getData('salesmanago_cart_event_id');
		        }

		        if($eventId && (!isset($_COOKIE[$cookieKey]) || empty($_COOKIE[$cookieKey]) || $_COOKIE[$cookieKey] != $eventId)) {
			        $salesmanagoHelper->addCookie($cookieKey,$eventId);
		        }
	        } elseif(isset($_COOKIE[$cookieKey]) && !empty($_COOKIE[$cookieKey])) {
		        $eventId = $_COOKIE[$cookieKey];
	        }

            if (isset($eventId) && !empty($eventId)) {
                $data_to_json['contactEvent']['eventId'] = $eventId;
                $json = json_encode($data_to_json);
                $result = $this->_getHelper()->_doPostRequest('https://' . $endPoint . '/api/contact/updateContactExtEvent', $json);
            } else {
                if ($isLoggedIn) {
                    $data_to_json['email'] = $customerEmail;
                } else {
                    if (!empty($_COOKIE['smclient']))
                        $data_to_json['contactId'] = $_COOKIE['smclient'];
                }

                $json = json_encode($data_to_json);
                $result = $this->_getHelper()->_doPostRequest('https://' . $endPoint . '/api/contact/addContactExtEvent', $json);
            }

            $r = json_decode($result, true);

            if (!$eventId && isset($r['eventId']) && !empty($r['eventId'])) {
	            $eventId = $r['eventId'];
                if($customer !== false && $customer->getId()) {
	                $customer->setData('salesmanago_cart_event_id', $eventId)
		                ->getResource()
		                ->saveAttribute($customer, 'salesmanago_cart_event_id');
                }
	            if(!isset($_COOKIE[$cookieKey]) || empty($_COOKIE[$cookieKey]) || $_COOKIE[$cookieKey] != $eventId) {
		            $salesmanagoHelper->addCookie($cookieKey,$eventId);
	            }
            }
        }
    }
}