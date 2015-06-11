<?php

class Zolago_SalesManago_Helper_Data extends SalesManago_Tracking_Helper_Data
{

    /**
     * Calling Upsert (api/contact/upsert) after new customer registration, customer login
     * and for customer, who make purchase without registration
     * @param $data
     * @param bool $register
     * @return mixed
     */
    public function salesmanagoContactSync($data, $register = false)
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
            );
            $data_to_json = array_filter($data_to_json);
            $data_to_json['async'] = false;

            if ($register || !isset($data['is_subscribed'])) {
                $data_to_json['forceOptIn'] = false;
                $data_to_json['forceOptOut'] = true;
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
        $subscription_status = 0;

        //nie trzeba sprawdzac, bo customer zawsze ma email i id
        $data['customerEmail'] = $customer['email'];
        $data['entity_id'] = $customer['entity_id'];

        $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($customer['email']);
        $subscription_status = $subscriber->isSubscribed();
        //Mage::log("subscription_status: ". (int)$subscription_status);

        if(isset($customer['firstname']) && isset($customer['lastname'])){
            $data['name'] = $customer['firstname'].' '.$customer['lastname'];
        }

        if($subscription_status == 1){
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
}