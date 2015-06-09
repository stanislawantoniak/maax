<?php

class Zolago_SalesManago_Model_Observer extends SalesManago_Tracking_Model_Observer {

    public function customer_register_success($observer) {
        $customer = $observer->getCustomer()->getData();

        if(is_array($customer) && !empty($customer)){
            $data = $this->_getHelper()->_setCustomerData($customer);

            $r = $this->_getHelper()->salesmanagoContactSync($data, true);

            if($r==false || (isset($r['success']) && $r['success']==false)){
                $data['status'] = 0;
                $data['action'] = 1; //rejestracja
                $this->_getHelper()->setSalesmanagoCustomerSyncStatus($data);
            }

            if(isset($r['contactId']) && !empty($r['contactId'])){
                try{
                    //Save single attribute salesmanago_contact_id without saving the entity
                    //to avoid call Zolago_Newsletter_Model_Observer::subscribeCustomer twice
                    //$observer->getCustomer()->setData('salesmanago_contact_id', $r['contactId'])->save();
                    $observer->getCustomer()
                        ->setData('salesmanago_contact_id', $r['contactId'])
                        ->getResource()
                        ->saveAttribute($observer->getCustomer(), "salesmanago_contact_id");
                } catch(Exception $e){
                    Mage::log($e->getMessage());
                }
            }
        }

    }



    public function newsletter_subscriber_save_after($observer){
        $request        = Mage::app()->getRequest();
        $moduleName     = $request->getModuleName();
        $controllerName = $request->getControllerName();
        $actionName     = $request->getActionName();

        if( ($moduleName=='newsletter' && $controllerName=='manage' && $actionName=='save') ||
            ($moduleName=='newsletter' && $controllerName=='subscribe' && $actionName=='unsubscribe') ||
            ($moduleName=='newsletter' && $controllerName=='subscriber' && $actionName=='confirm') ||
            ($moduleName=='newsletter' && $controllerName=='subscriber' && $actionName=='invitation')
        ){

            $clientId = Mage::getStoreConfig('salesmanago_tracking/general/client_id');
            $apiSecret = Mage::getStoreConfig('salesmanago_tracking/general/api_secret');
            $ownerEmail = Mage::getStoreConfig('salesmanago_tracking/general/email');
            $tags = Mage::getStoreConfig('salesmanago_tracking/general/tags');
            $endPoint = Mage::getStoreConfig('salesmanago_tracking/general/endpoint');

            $subscriber = $observer->getEvent()->getDataObject();
            $data = $subscriber->getData();
            $email = $data['subscriber_email'];

            $apiKey = md5(time().$apiSecret);

            $data_to_json = array(
                'apiKey' => $apiKey,
                'clientId' => $clientId,
                'requestTime' => time(),
                'email' => $email,
                'sha' => sha1($apiKey . $clientId . $apiSecret),
                'owner' => $ownerEmail,
            );

            if ($data['subscriber_status'] == "1") {
                $data_to_json['forceOptIn'] = true;
                $data_to_json['forceOptOut'] = false;
            } else {
                $data_to_json['forceOptOut'] = true;
                $data_to_json['forceOptIn'] = false;
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



    /*
* Dodanie (oraz na biezaco modyfikowanie) zdarzenia w koszyku addContactExtEvent z typem CART
*/
    public function add_cart_event($observer){
        Mage::log($observer->getEvent()->getName(), null, "salesmanago.log");
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
}