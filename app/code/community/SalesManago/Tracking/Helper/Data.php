<?php
class SalesManago_Tracking_Helper_Data extends Mage_Core_Helper_Abstract{

    public function setSalesmanagoCustomerSyncStatus($data = array()){
        $active = Mage::getStoreConfig('salesmanago_tracking/general/active');
        if($active == 1) {
            $customersyncModel = Mage::getModel('tracking/customersync');
            $dateTime = new DateTime('NOW');

            $insert_data = array(
                'email' => $data['customerEmail'],
                'hash' => sha1($data['customerEmail']),
                'status' => $data['status'],
                'action' => $data['action'],
                'counter' => 1,
                'created_time' => $dateTime->format('c')
            );
            if (isset($data['entity_id']) && !empty($data['entity_id'])) {
                $insert_data['customer_id'] = $data['entity_id'];
            }

            if (isset($data['order_id']) && !empty($data['order_id'])) {
                $insert_data['order_id'] = $data['order_id'];
            }

            $customersyncModel->setData($insert_data);

            try {
                $customersyncModel->save();
            } catch (Exception $e) {
                Mage::log($e->getMessage());
            }
        }
    }

    public function salesmanagoOrderSync($orderDetails){
        $active = Mage::getStoreConfig('salesmanago_tracking/general/active');
        if($active == 1) {
            $clientId = Mage::getStoreConfig('salesmanago_tracking/general/client_id');
            $apiSecret = Mage::getStoreConfig('salesmanago_tracking/general/api_secret');
            $ownerEmail = Mage::getStoreConfig('salesmanago_tracking/general/email');
            $endPoint = Mage::getStoreConfig('salesmanago_tracking/general/endpoint');

            $items = $orderDetails->getAllVisibleItems();
            $itemsNamesList = array();
            foreach ($items as $item) {
                array_push($itemsNamesList, $item->getProduct()->getId());
            }

            $customerEmail = $orderDetails->getCustomerEmail();
            $customerFirstname = $orderDetails->getCustomerFirstname();
            $customerLastname = $orderDetails->getCustomerLastname();
            $grandTotal = $orderDetails->getBaseGrandTotal();
            $incrementOrderId = $orderDetails->getIncrementId();


            $customer = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($customerEmail)->getData();


            $orderData = $orderDetails->getData();


            $customerDetails = $orderDetails->getBillingAddress();

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


            if (isset($customer['salesmanago_contact_id']) && !empty($customer['salesmanago_contact_id'])) {
                $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($customerEmail);
                $status = $subscriber->isSubscribed();
                if ($status) $data['is_subscribed'] = true;
            } else {
                $data['is_subscribed'] = true; //after purchase
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


            $apiKey = md5(time() . $apiSecret);
            $dateTime = new DateTime('NOW');

            $data_to_json = array(
                'apiKey' => $apiKey,
                'clientId' => $clientId,
                'requestTime' => time(),
                'sha' => sha1($apiKey . $clientId . $apiSecret),
                'owner' => $ownerEmail,
                'email' => $customerEmail,
                'contactEvent' => array(
                    'date' => $dateTime->format('c'),
                    'products' => implode(',', $itemsNamesList),
                    'contactExtEventType' => 'PURCHASE',
                    'value' => $grandTotal,
                    'externalId' => $incrementOrderId,
                ),
            );

            $json = json_encode($data_to_json);
            $result = $this->_doPostRequest('https://' . $endPoint . '/api/contact/addContactExtEvent', $json);
            $r = json_decode($result, true);

            return $r;
        }
    }

    /*
    * Zdarzenie Upsert wykonywane przy rejestracji nowego uzytkownika, logowaniu uzytkownika 
    * oraz dla uzytkownika robiacego zakupy bez rejestracji
    */
    public function salesmanagoContactSync($data){
        $active = Mage::getStoreConfig('salesmanago_tracking/general/active');
        if($active == 1) {
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
                    'name' => $data['name'],
                    'fax' => $data['fax'],
                    'company' => $data['company'],
                    'phone' => $data['phone'],
                    'address' => array(
                        'streetAddress' => $data['address']['streetAddress'],
                        'zipCode' => $data['address']['zipCode'],
                        'city' => $data['address']['city'],
                        'country' => $data['address']['country'],
                    ),
                ),
                'owner' => $ownerEmail,
                'async' => false,
            );

            if (!isset($data['is_subscribed'])) {
                $data_to_json['forceOptOut'] = true;
                $data_to_json['forceOptIn'] = false;
            } else {
                $data_to_json['forceOptIn'] = true;
                $data_to_json['forceOptOut'] = false;
            }

            if (isset($data['birthday'])) {
                $data_to_json['birthday'] = $data['birthday'];
            }

            $customer = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($data['customerEmail'])->getData();

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

    public function salesmanagoSubscriberSync(){

    }

    public function _setCustomerData($customer){
        $data = array();
        $subscription_status = 0;

        $data['customerEmail'] = $customer['email'];
        $data['entity_id'] = $customer['entity_id'];

        $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($customer['email']);
        $subscription_status = $subscriber->isSubscribed();

        if(isset($customer['firstname']) && isset($customer['lastname'])){
            $data['name'] = $customer['firstname'].' '.$customer['lastname'];
        }

        if(isset($customer['is_subscribed']) || $subscription_status == 1){
            $data['is_subscribed'] = true;
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

    public function _doPostRequest($url, $data) {
        $active = Mage::getStoreConfig('salesmanago_tracking/general/active');
        if($active == 1) {

            $connection_timeout = Mage::getStoreConfig('salesmanago_tracking/general/connection_timeout');

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if(isset($connection_timeout) && !empty($connection_timeout)){
                curl_setopt($ch, CURLOPT_TIMEOUT_MS, $connection_timeout);
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($data)));

            $result = curl_exec($ch);

            if(curl_errno($ch) > 0) {

                Mage::log("===============================", null, 'salesmanago_error.log');
                Mage::log("ERROR (".curl_errno($ch) ." ". curl_error($ch) . ") ". Mage::getModel('core/date')->date('Y-m-d H:i:s'), null, 'salesmanago_error.log');
                Mage::log("URL: " . $url, null, 'salesmanago_error.log');
                Mage::log("START POST DATA:", null, 'salesmanago_error.log');
                Mage::log(json_decode($data), null, 'salesmanago_error.log');
                Mage::log("END POST DATA", null, 'salesmanago_error.log');
                Mage::log("START RETURNED TRANSFER DATA:", null, 'salesmanago_error.log');
                Mage::log($result, null, 'salesmanago_error.log');
                Mage::log("ENDRETURNED TRANSFER DATA", null, 'salesmanago_error.log');

                return false;
            }

            return $result;

        }
        else {

            return false;

        }
    }
    function sm_create_cookie($name, $value, $period){
        $url = parse_url(Mage::getBaseUrl());
        setcookie($name, $value, $period, '/', '.'.$this -> sm_get_domain($url['host']));

    }
    function sm_get_domain($domain, $debug = false){
        $original = $domain = strtolower($domain);

        if (filter_var($domain, FILTER_VALIDATE_IP)) { return $domain; }

        $debug ? Mage::Log('<strong style="color:green">&raquo;</strong> Parsing: '.$original, null, 'get_domain.log') : false;

        $arr = array_slice(array_filter(explode('.', $domain, 4), function($value){
            return $value !== 'www';
        }), 0); //rebuild array indexes

        if (count($arr) > 2)
        {
            $count = count($arr);
            $_sub = explode('.', $count === 4 ? $arr[3] : $arr[2]);

            $debug ? Mage::Log(" (parts count: {$count})", null, 'get_domain.log') : false;

            if (count($_sub) === 2) // two level TLD
            {
                $removed = array_shift($arr);
                if ($count === 4) // got a subdomain acting as a domain
                {
                    $removed = array_shift($arr);
                }
                $debug ? Mage::Log("<br>\n" . '[*] Two level TLD: <strong>' . join('.', $_sub) . '</strong> ', null, 'get_domain.log') : false;
            }
            elseif (count($_sub) === 1) // one level TLD
            {
                $removed = array_shift($arr); //remove the subdomain

                if (strlen($_sub[0]) === 2 && $count === 3) // TLD domain must be 2 letters
                {
                    array_unshift($arr, $removed);
                }
                else
                {
                    // non country TLD according to IANA
                    $tlds = array(
                        'aero',
                        'arpa',
                        'asia',
                        'biz',
                        'cat',
                        'com',
                        'coop',
                        'edu',
                        'gov',
                        'info',
                        'jobs',
                        'mil',
                        'mobi',
                        'museum',
                        'name',
                        'net',
                        'org',
                        'post',
                        'pro',
                        'tel',
                        'travel',
                        'xxx',
                    );

                    if (count($arr) > 2 && in_array($_sub[0], $tlds) !== false) //special TLD don't have a country
                    {
                        array_shift($arr);
                    }
                }
                $debug ? Mage::Log("<br>\n" .'[*] One level TLD: <strong>'.join('.', $_sub).'</strong> ', null, 'get_domain.log') : false;
            }
            else // more than 3 levels, something is wrong
            {
                for ($i = count($_sub); $i > 1; $i--)
                {
                    $removed = array_shift($arr);
                }
                $debug ? Mage::Log("<br>\n" . '[*] Three level TLD: <strong>' . join('.', $_sub) . '</strong> ', null, 'get_domain.log') : false;
            }
        }
        elseif (count($arr) === 2)
        {
            $arr0 = array_shift($arr);

            if (strpos(join('.', $arr), '.') === false
                && in_array($arr[0], array('localhost','test','invalid')) === false) // not a reserved domain
            {
                $debug ? Mage::Log("<br>\n" .'Seems invalid domain: <strong>'.join('.', $arr).'</strong> re-adding: <strong>'.$arr0.'</strong> ', null, 'get_domain.log') : false;
                // seems invalid domain, restore it
                array_unshift($arr, $arr0);
            }
        }

        $debug ? Mage::Log("<br>\n".'<strong style="color:gray">&laquo;</strong> Done parsing: <span style="color:red">' . $original . '</span> as <span style="color:blue">'. join('.', $arr) ."</span><br>\n", null, 'get_domain.log') : false;

        return join('.', $arr);
    }
}