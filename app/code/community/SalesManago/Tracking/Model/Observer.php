<?php
class SalesManago_Tracking_Model_Observer {
 
	protected function _getHelper(){
        return Mage::helper('tracking');
    }
	
    public function customer_login($observer) {
        Mage::log("SalesManago_Tracking_Model_Observer customer_login", null, "salesmanago.log");
		$customer = $observer->getCustomer()->getData();

        if(is_array($customer) && !empty($customer)){
            if(!isset($customer['salesmanago_contact_id']) || empty($customer['salesmanago_contact_id'])){
                $data = $this->_getHelper()->_setCustomerData($customer);

                $r = $this->_getHelper()->salesmanagoContactSync($data);


				if($r==false || (isset($r['success']) && $r['success']==false)){
					$data['status'] = 0;
					$data['action'] = 2; //logowanie
					$this->_getHelper()->setSalesmanagoCustomerSyncStatus($data);
				}
            
                if(isset($r['contactId']) && !empty($r['contactId'])){
                    try{
						$observer->getCustomer()->setData('salesmanago_contact_id', $r['contactId'])->save();
					} catch(Exception $e){
						Mage::log($e->getMessage());
					}
                }
            }			
		}
    }
  
    public function customer_register_success($observer) {
        $customer = $observer->getCustomer()->getData();

        if(is_array($customer) && !empty($customer)){
            $data = $this->_getHelper()->_setCustomerData($customer);
			
            $r = $this->_getHelper()->salesmanagoContactSync($data);
			
			if($r==false || (isset($r['success']) && $r['success']==false)){
				$data['status'] = 0;
				$data['action'] = 1; //rejestracja
				$this->_getHelper()->setSalesmanagoCustomerSyncStatus($data);
			}
        
            if(isset($r['contactId']) && !empty($r['contactId'])){
				try{
					$observer->getCustomer()->setData('salesmanago_contact_id', $r['contactId'])->save();
				} catch(Exception $e){
					Mage::log($e->getMessage());
				}
            }
        }
		
	}
	
    /*
    * Dodanie zdarzenia addContactExtEvent na podsumowaniu zam�wienia z typem PURCHASE
    */
	public function checkout_onepage_controller_success_action($observer){
		
		$orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
		$orderDetails = Mage::getModel('sales/order')->load($orderId);
		$r = $this->_getHelper()->salesmanagoOrderSync($orderDetails);

		if($r==false || (isset($r['success']) && $r['success']==false)){
			$data = array(
				'customerEmail' => $orderDetails->getCustomerEmail(),
				'entity_id' => $orderDetails->getCustomerId(),
				'order_id' => $orderDetails->getEntityId(),
				'status' => 0,
				'action' => 3, //zlozenie zamowienia: addContactExtEvent - PURCHASE
			);
			$this->_getHelper()->setSalesmanagoCustomerSyncStatus($data);
		}
		
		$eventId = Mage::getSingleton('core/session')->getEventId();
		if(isset($eventId) && !empty($eventId)){
			Mage::getSingleton('core/session')->unsEventId();
		}
	}
	
    /*
    * Dodanie (oraz na biezaco modyfikowanie) zdarzenia w koszyku addContactExtEvent z typem CART
    */
	public function checkout_cart_save_after($observer){
		$cartHelper = Mage::getModel('checkout/cart')->getQuote();
		$items = $cartHelper->getAllItems();
		$itemsNamesList = array();
		foreach ($items as $item) {
			array_push($itemsNamesList, $item->getProduct()->getId());
		}
		
		
		$clientId = Mage::getStoreConfig('salesmanago_tracking/general/client_id');
		$apiSecret = Mage::getStoreConfig('salesmanago_tracking/general/api_secret');
		$ownerEmail = Mage::getStoreConfig('salesmanago_tracking/general/email');
		$endPoint = Mage::getStoreConfig('salesmanago_tracking/general/endpoint');
		
		$customerEmail = Mage::getSingleton('customer/session')->getCustomer()->getEmail();
		
		if(!empty($customerEmail)){
			$apiKey = md5(time().$apiSecret);
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
				),
			);

			$eventId = Mage::getSingleton('core/session')->getEventId();
			
			if(isset($eventId) && !empty($eventId)){
				$data_to_json['contactEvent']['eventId'] = $eventId;
				$json = json_encode($data_to_json);
				$result = $this->_getHelper()->_doPostRequest('https://'.$endPoint.'/api/contact/updateContactExtEvent', $json);
			} else{
				$data_to_json['email'] = $customerEmail;
				$json = json_encode($data_to_json);
				$result = $this->_getHelper()->_doPostRequest('https://'.$endPoint.'/api/contact/addContactExtEvent', $json);
			}
			
			$r = json_decode($result, true);

			if(!isset($eventId) && isset($r['eventId'])){
				Mage::getSingleton('core/session')->setEventId($r['eventId']);
			}
		}
	}
	
	public function newsletter_subscriber_save_before($observer){
		$request        = Mage::app()->getRequest();
		$moduleName     = $request->getModuleName();
		$controllerName = $request->getControllerName();
		$actionName     = $request->getActionName();
		
		if( ($moduleName=='newsletter' && $controllerName=='manage' && $actionName=='save') ||
			($moduleName=='newsletter' && $controllerName=='subscribe' && $actionName=='unsubscribe')){

			$clientId = Mage::getStoreConfig('salesmanago_tracking/general/client_id');
			$apiSecret = Mage::getStoreConfig('salesmanago_tracking/general/api_secret');
			$ownerEmail = Mage::getStoreConfig('salesmanago_tracking/general/email');
			$tags = Mage::getStoreConfig('salesmanago_tracking/general/tags');
			$endPoint = Mage::getStoreConfig('salesmanago_tracking/general/endpoint');
			
			$subscriber = $observer->getEvent()->getDataObject();
			$data = $subscriber->getData();
			$email = $data['subscriber_email'];
			$statusChange = $subscriber->getIsStatusChanged();
			
			$id    = (int) Mage::app()->getFrontController()->getRequest()->getParam('id');
			$code  = (string) Mage::app()->getFrontController()->getRequest()->getParam('code');

			$apiKey = md5(time().$apiSecret);

			$data_to_json = array(
				'apiKey' => $apiKey,
				'clientId' => $clientId,
				'requestTime' => time(),
				'email' => $email,
				'sha' => sha1($apiKey . $clientId . $apiSecret),
				'owner' => $ownerEmail,
			);
			
			if ($data['subscriber_status'] == "1" && ($statusChange == true || ($id && $code))) {
				$data_to_json['forceOptIn'] = true;
			} elseif ($data['subscriber_status'] == "3" && ($statusChange == true || ($id && $code))) {
				$data_to_json['forceOptOut'] = true;
			}

			$json = json_encode($data_to_json);
			$result = $this->_getHelper()->_doPostRequest('https://'.$endPoint.'/api/contact/update', $json);
			
			$r = json_decode($result, true);
			
			if($r==false || (isset($r['success']) && $r['success']==false)){
				$data['customerEmail'] = $email;
				$data['status'] = 0;
				$data['action'] = 4; //zapis / wypis z newslettera
				$this->_getHelper()->setSalesmanagoCustomerSyncStatus($data);
			}
			
			return $r;
		}
	}
}
