<?php
class SalesManago_Tracking_Helper_Data extends Mage_Core_Helper_Abstract{

	public function setSalesmanagoCustomerSyncStatus($data = array()){
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
		if(isset($data['entity_id']) && !empty($data['entity_id'])){
			$insert_data['customer_id'] = $data['entity_id'];
		}
		
		if(isset($data['order_id']) && !empty($data['order_id'])){
			$insert_data['order_id'] = $data['order_id'];
		}
		
		$customersyncModel->setData($insert_data);
		
		try{
			$customersyncModel->save();
		} catch(Exception $e){
			Mage::log($e->getMessage());
		}
	}
	
	public function salesmanagoOrderSync($orderDetails){
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
		if(!isset($customer['salesmanago_contact_id']) || empty($customer['salesmanago_contact_id'])){
			$data['customerEmail'] = $customerEmail;
			$r = $this->salesmanagoContactSync($data);
			if($r==false || (isset($r['success']) && $r['success']==false)){
				$data['status'] = 0;
				$data['action'] = 1; //rejestracja
				$this->setSalesmanagoCustomerSyncStatus($data);
			}
		}
		
		$apiKey = md5(time().$apiSecret);
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
		$result = $this->_doPostRequest('https://'.$endPoint.'/api/contact/addContactExtEvent', $json);
		
		$r = json_decode($result, true);
		
		return $r;
	}
	
	/*
    * Zdarzenie Upsert wykonywane przy rejestracji nowego uzytkownika, logowaniu uzytkownika 
    * oraz dla uzytkownika robiacego zakupy bez rejestracji
    */
	public function salesmanagoContactSync($data){
		$clientId = Mage::getStoreConfig('salesmanago_tracking/general/client_id');
		$apiSecret = Mage::getStoreConfig('salesmanago_tracking/general/api_secret');
		$ownerEmail = Mage::getStoreConfig('salesmanago_tracking/general/email');
		$tags = Mage::getStoreConfig('salesmanago_tracking/general/tags');
		$endPoint = Mage::getStoreConfig('salesmanago_tracking/general/endpoint');		
		
		$apiKey = md5(time().$apiSecret);

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
		
		if(isset($data['name']) && !empty($data['name'])){
			$data_to_json['contact']['name'] = $data['name'];
		}
		
		if(!isset($data['is_subscribed'])){
			$data_to_json['forceOptOut'] = true;
		}
		
		if(!empty($tags)){
			$tags = explode(",", $tags);
			if(is_array($tags) && count($tags)>0){
				$tags = array_map('trim', $tags);
				$data_to_json['tags'] = $tags;
			}			
		}

		$json = json_encode($data_to_json);
		$result = $this->_doPostRequest('https://'.$endPoint.'/api/contact/upsert', $json);
		$r = json_decode($result, true);
		
        return $r;
	}
	
	public function salesmanagoSubscriberSync(){
		
	}
	
	public function _setCustomerData($customer){
        $data = array();
		$subscription_status = 0;
        
		//nie trzeba sprawdzac, bo customer zawsze ma email i id
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
        
        return $data;
    }
	
	public function _doPostRequest($url, $data) {
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
        Mage::log($result);
		if(curl_errno($ch) > 0){
			if(curl_errno($ch)==28){
				Mage::log("TIMEOUT ERROR NO: " . curl_errno($ch));
			} else{
				Mage::log("ERROR NO: " . curl_errno($ch));
			}
			return false;
		}
		
		return $result;
	}
}
