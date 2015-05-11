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
    {Mage::log("Zolago_SalesManago_Helper_Data", null, "salesmanago.log");
        Mage::log("Zolago_SalesManago_Helper_Data register ". (int)$register, null, "salesmanago.log");
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
            ),
            'owner' => $ownerEmail,
        );

        if (isset($data['name']) && !empty($data['name'])) {
            $data_to_json['contact']['name'] = $data['name'];
        }

        if ($register || !isset($data['is_subscribed'])) {
            $data_to_json['forceOptIn'] = false;
            $data_to_json['forceOptOut'] = true;
        }


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