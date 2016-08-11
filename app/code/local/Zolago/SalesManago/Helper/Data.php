<?php

class Zolago_SalesManago_Helper_Data extends SalesManago_Tracking_Helper_Data
{

	const SALESMANAGO_NO_NEWSLETTER_POST_REGISTRY_KEY = 'salesmanago_no_newsletter_post';

    /**
     * Calling Upsert (api/contact/upsert) after new customer registration, customer login
     * and for customer, who make purchase without registration
     * @param $data
     * @return mixed
     */
    public function salesmanagoContactSync($data)
    {
        $active = Mage::getStoreConfig('salesmanago_tracking/general/active');
        if ($active == 1) {
            $clientId = Mage::getStoreConfig('salesmanago_tracking/general/client_id');
            $apiSecret = Mage::getStoreConfig('salesmanago_tracking/general/api_secret');
            $ownerEmail = Mage::getStoreConfig('salesmanago_tracking/general/email');
            $tags = Mage::getStoreConfig('salesmanago_tracking/general/tags');
            $endPoint = Mage::getStoreConfig('salesmanago_tracking/general/endpoint');

            $apiKey = md5(time() . $apiSecret);

            $data_to_json = array(
                'apiKey' => $apiKey,
                'clientId' => $clientId,
                'requestTime' => time(),
                'sha' => sha1($apiKey . $clientId . $apiSecret),
                'contact' => array(
                    'email' => $data['customerEmail'],
                    'name' => !empty($data['name']) ? $data['name'] : '',
                    'fax' => !empty($data['fax']) ? $data['fax'] : '',
                    'company' => !empty($data['company']) ? $data['company'] : '',
                    'phone' => !empty($data['phone']) ? $data['phone'] : '',
                    'address' => array(
                        'streetAddress' => !empty($data['address']['streetAddress']) ? $data['address']['streetAddress'] : '',
                        'zipCode' => !empty($data['address']['zipCode']) ? $data['address']['zipCode'] : '',
                        'city' => !empty($data['address']['city']) ? $data['address']['city'] : '',
                        'country' => !empty($data['address']['country']) ? $data['address']['country'] : '',
                    ),
                ),
                'owner' => $ownerEmail,
                'async' => false,
            );



            if (isset($data['is_subscribed']) && !$data['is_subscribed']) {
                $data_to_json['forceOptIn'] = false;
                $data_to_json['forceOptOut'] = true;
            } else {
                $data_to_json['forceOptIn'] = true;
                $data_to_json['forceOptOut'] = false;
            }

            if (isset($data['birthday'])) {
                $data_to_json['birthday'] = $data['birthday'];
            }

            //$customer = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($data['customerEmail'])->getData();

            if (!empty($tags)) {
                $tags = explode(",", $tags);
                if (is_array($tags) && count($tags) > 0) {
                    $tags = array_map('trim', $tags);
                    $data_to_json['tags'] = $tags;
                }
            }
            $json = json_encode($data_to_json);
            $result = $this->_doPostRequest('https://' . $endPoint . '/api/contact/upsert', $json);
            $r = json_decode($result, true);

            return $r;
        }
    }

    public function _setCustomerData($customer){
        $data = array();

        //nie trzeba sprawdzac, bo customer zawsze ma email i id
        $data['customerEmail'] = $customer['email'];
        $data['entity_id'] = $customer['entity_id'];

        // Stupid thing below but here customer is $customer->getData() not object
        /** @var Zolago_Customer_Model_Customer $customerModel */
        $customerModel = Mage::getModel('customer/customer');
        $customerModel->load($customer['entity_id']);

        /** @var Zolago_Newsletter_Model_Subscriber $model */
        $model = Mage::getModel('zolagonewsletter/subscriber');
        $subscriber = $model->rawLoadByCustomer($customerModel);
        $data['is_subscribed'] = $subscriber->isSubscribed();

        if(isset($customer['firstname']) && isset($customer['lastname'])){
            $data['name'] = $customer['firstname'].' '.$customer['lastname'];
        }

        if(isset($customer['dob'])){
            $dataArray = date_parse($customer['dob']);
            $month  = ($dataArray['month'] < 10) ? "0".$dataArray['month'] : $dataArray['month'];
            $day  = ($dataArray['day'] < 10) ? "0".$dataArray['day'] : $dataArray['day'];
            $year = $dataArray['year'];
            $data['birthday'] = $year . $month .  $day;
        }

        return $data;
    }

    /**
     * @param $orderDetails Zolago_Sales_Model_Order
     * @return mixed
     */
    public function salesmanagoOrderSync($orderDetails){
        $active = Mage::getStoreConfig('salesmanago_tracking/general/active');
        if($active == 1) {
            $clientId = Mage::getStoreConfig('salesmanago_tracking/general/client_id');
            $apiSecret = Mage::getStoreConfig('salesmanago_tracking/general/api_secret');
            $ownerEmail = Mage::getStoreConfig('salesmanago_tracking/general/email');
            $endPoint = Mage::getStoreConfig('salesmanago_tracking/general/endpoint');

            $items = $orderDetails->getAllItems();
            $itemsNamesList = array();
            foreach ($items as $item) {
                array_push($itemsNamesList, $item->getProduct()->getId());
            }

            $customerEmail = $orderDetails->getCustomerEmail();
            $subtotalIncTax = $orderDetails->getSubtotalInclTax();
            $incrementOrderId = $orderDetails->getIncrementId();
            $orderStoreId = $orderDetails->getStoreId();
            $customer = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($customerEmail)->getData();
            $orderData = $orderDetails->getData();
            $customerDetails = $orderDetails->getBillingAddress();

            /** @var Zolago_Po_Model_Po $po */
            $po = $orderDetails->getPoListByOrder()->getFirstItem();
            $paymentMethod = str_replace('_',' ',$po->ghapiPaymentMethod());
            $shippingAdress = $orderDetails->getShippingAddress();
            $street = htmlentities(trim($shippingAdress->getStreet(-1)));
            $zip = htmlentities(trim($shippingAdress->getPostcode()));
            $city = htmlentities(trim($shippingAdress->getCity()));

            $data = array();
            $data['name'] = $customerDetails->getFirstname() . ' ' . $customerDetails->getLastname();
            $data['phone'] = $customerDetails->getTelephone();
            $data['company'] = $customerDetails->getCompany();
            $data['fax'] = $customerDetails->getFax();
            $data['address']['streetAddress'] = implode($customerDetails->getStreet(), ' ');
            $data['address']['zipCode'] = $customerDetails->getPostcode();
            $data['address']['city'] = $customerDetails->getCity();
            $data['address']['country'] = $customerDetails->getCountryId();
            $data['customerEmail'] = $customerEmail;


            if (isset($orderData['customer_dob']) && !empty($orderData['customer_dob'])) {
                $dataArray = date_parse($orderData['customer_dob']);
                $month = ($dataArray['month'] < 10) ? "0" . $dataArray['month'] : $dataArray['month'];
                $day = ($dataArray['day'] < 10) ? "0" . $dataArray['day'] : $dataArray['day'];
                $year = $dataArray['year'];
                $data['birthday'] = $year . $month . $day;
            }

            /** @var Zolago_Newsletter_Model_Subscriber $model */
            $model = Mage::getModel('zolagonewsletter/subscriber');
            $subscriber = $model->rawLoadByEmail($customerEmail, $orderStoreId);

            if (isset($customer['salesmanago_contact_id']) && !empty($customer['salesmanago_contact_id'])) {
                $data['is_subscribed'] = $subscriber->isSubscribed();
            } else {
                $data['is_subscribed'] = false;
            }


            $r = $this->salesmanagoContactSync($data);
            if ($r == false || (isset($r['success']) && $r['success'] == false)) {
                $data['status'] = 0;
                $data['action'] = 1; //rejestracja
                //$this->_getHelper()->setSalesmanagoCustomerSyncStatus($data);
            }

            if ($orderDetails->getCustomerIsGuest() && $r['success'] == true) {
                $period = time() + 3650 * 86400;
                $this->sm_create_cookie('smclient', $r['contactId'], $period);
            }

	        $time = time();

            $apiKey = md5($time . $apiSecret);
            $dateTime = new DateTime('NOW');

            $data_to_json = array(
                'apiKey' => $apiKey,
                'clientId' => $clientId,
                'requestTime' => $time,
                'sha' => sha1($apiKey . $clientId . $apiSecret),
                'owner' => $ownerEmail,
                'email' => $customerEmail,
                'contactEvent' => array(
                    'date' => $dateTime->format('c'),
                    'products' => implode(',', $itemsNamesList),
                    'contactExtEventType' => 'PURCHASE',
                    'value' => $subtotalIncTax,
                    'externalId' => $incrementOrderId,
                    'detail1' => $paymentMethod, // Payment Method
                    'location' => $street.' '.$zip.' '.$city,  // Location of shipping
                ),
            );

            $json = json_encode($data_to_json);
            $result = $this->_doPostRequest('https://' . $endPoint . '/api/contact/addContactExtEvent', $json);
            $r = json_decode($result, true);

            return $r;
        }
    }

	public function addCookie($name,$value) {
		$this->sm_create_cookie($name,$value,time()+(60*60*24*365*10)); //10years
	}

	public function removeCookie($name) {
		$url = parse_url(Mage::getBaseUrl());
		unset($_COOKIE[$name]);
		setcookie($name,null,-1,$this->sm_get_domain($url['host']));
	}
}