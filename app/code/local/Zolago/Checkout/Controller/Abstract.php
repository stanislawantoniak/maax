<?php
/**
 * Define multi logic here
 */
require_once Mage::getConfig()->getModuleDir("controllers", "Mage_Checkout") .
    DS . "OnepageController.php";

abstract class Zolago_Checkout_Controller_Abstract
    extends Mage_Checkout_OnepageController {

    /**
     * @return Zolago_Checkout_Model_Type_Onepage
     */
    public function getOnepage() {
        return Mage::getSingleton('zolagocheckout/type_onepage');
    }


    /**
     * @param $extraCharge
     */
    public function setUdropshipShippingDetailsExtraChargeToAddress($extraCharge)
    {
        $quote = Mage::getModel("checkout/cart")->getQuote();
        $address = $quote->getShippingAddress();

        $details = $address->getUdropshipShippingDetails();
        $details = $details ? Zend_Json::decode($details) : array();

        if (!empty($details) && isset($details['methods'])) {
            foreach ($details['methods'] as $vId => $detail) {
                $details['methods'][$vId]['cost'] = $details['methods'][$vId]['cost'] + $extraCharge;
                $details['methods'][$vId]['price'] = $details['methods'][$vId]['price'] + $extraCharge;
                $details['methods'][$vId]['price_excl'] = $details['methods'][$vId]['price_excl'] + $extraCharge;
                $details['methods'][$vId]['price_incl'] = $details['methods'][$vId]['price_incl'] + $extraCharge;
            }
            $address->setUdropshipShippingDetails(Zend_Json::encode($details));
        }
    }

    /**
     * Process place order action
     * Make parial save of all data to quote
     * Then make save process
     */
    public function saveOrderAction() {
        if (!$this->_validateFormKey()) {
            $this->_redirect('*/*');
            return;
        }

        $key = Zolago_SalesManago_Helper_Data::SALESMANAGO_NO_NEWSLETTER_POST_REGISTRY_KEY;
        if(Mage::registry($key)) {
            Mage::unregister($key);
        }
        Mage::register($key,true);

        // Check if customer has name and phone number set in account if not set it now
        /** @var Mage_Customer_Model_Session $session */
        $session = Mage::getSingleton('customer/session');
        $logged = $session->isLoggedIn();
        if($logged) {
            /** @var Zolago_Customer_Model_Customer $customer */
            $customer = $session->getCustomer();
            if(is_null($customer->getFirstname()) || is_null($customer->getLastname()) || is_null($customer->getPhone())) {
                $request = $this->getRequest();
                $data = $request->getParam('billing')['use_for_shipping'] ? $request->getParam('billing') : $request->getParam('shipping');
                $customer
                    ->setFirstname($data['firstname'])
                    ->setLastname($data['lastname'])
                    ->setPhone($data['telephone']);
                $session->setCustomer($customer);
            }
        }

        try {
            $this->importPostData();
        } catch (Exception $ex) {
            $response = array(
                "status" => false,
                "content" => array(
                    "redirect"	=> null,
                    "message"	=> $ex->getMessage()
                )
            );
            Mage::logException($ex);
            return $this->_prepareJsonResponse($response);
        }


        $onepage = $this->getOnepage();


        $request = $this->getRequest();
        /**
        payment[method]:zolagopayment
        payment[additional_information][provider]:m
         */
        if ($payment = $request->getParam("payment")) {

            if ($payment['method'] == 'cashondelivery') {
                /**
                 * shipping_method[4]:udtiership_1
                 */

                if ($shippingMethod = $request->getParam("shipping_method")) {
                    $udropshipMethod = array_shift($shippingMethod);
                    $storeId = Mage::app()->getStore()->getId();

                    $info = Mage::helper("udropship")->getOmniChannelMethodInfoByMethod($storeId, $udropshipMethod);
                    $carrier = $info->getDeliveryCode();


                    if (in_array($carrier,
                        array(
                            Orba_Shipping_Model_Carrier_Default::CODE,
                            Orba_Shipping_Model_Post::CODE,
                            GH_Inpost_Model_Carrier::CODE,
                            ZolagoOs_PickupPoint_Helper_Data::CODE,
                            Orba_Shipping_Model_Packstation_Pwr::CODE
                        )
                    )
                    ) {
                        /** @var Zolago_Checkout_Helper_Data $helper */
                        $helper = Mage::helper("zolagocheckout");

                        $extraCharges = $helper->getUdropshipMethodExtraCharges(array($udropshipMethod));

                        $extraCharge = isset($extraCharges[$udropshipMethod]) ? $extraCharges[$udropshipMethod] : 0;

                        if ($extraCharge > 0) {
                            $address = $onepage->getQuote()->getShippingAddress();
                            $costVal = $address->getShippingInclTax();
                            $baseCostVal = $address->getBaseShippingInclTax();
                            $costVal = $costVal + $extraCharge;
                            $baseCostVal = $baseCostVal + $extraCharge;
                            $address->setShippingInclTax($costVal);
                            $address->setBaseShippingInclTax($baseCostVal);
                            $address->setGrandTotal($address->getGrandTotal() + $extraCharge);
                            $address->setBaseGrandTotal($address->getBaseGrandTotal() + $extraCharge);

                            //Save sales_flat_quote_address.udropship_shipping_details
                            $this->setUdropshipShippingDetailsExtraChargeToAddress($extraCharge);
                            //Save sales_flat_quote_address.udropship_shipping_details

                            $address->save();
                        }
                    }


                }
            }


        }



        parent::saveOrderAction();

        $helper = Mage::helper('core');
        $oldResponse  = $helper->jsonDecode($this->getResponse()->getBody());

        $success = isset($oldResponse['success']) ? $oldResponse['success'] : false;

        if(!isset($oldResponse['redirect'])) {
            $urlArray = array(
                "*",
                $logged ? "singlepage" : "guest",
                $success ? "success" : "error"
            );
            $redirect = Mage::getUrl(implode("/", $urlArray));
        } else {
            $redirect = $oldResponse['redirect'];
        }

        $newResponse = array(
            "status" => $success,
            "content" => array(
                "redirect"	=> $redirect,
                "message"	=> isset($oldResponse['error_messages']) ? $oldResponse['error_messages'] : false
            )
        );

        // Part for tag manager script and GA
        if ($success) {
            $session = $this->getOnepage()->getCheckout();
            $orderIds = array($session->getLastOrderId());
            /** @var GH_GTM_Block_Gtm $block */
            $block = Mage::app()->getLayout()->createBlock('ghgtm/gtm','google_tag_manager');
            $block->setOrderIds($orderIds);
            $newResponse['dataLayer'] = $block->getRawDataLayer();
        }

        /* clear salesmanago cart event id after successful checkout */
        if($success) {
            if($logged && isset($customer)) {
                $customer->setData('salesmanago_cart_event_id', '')
                    ->getResource()
                    ->saveAttribute($customer, 'salesmanago_cart_event_id');
            }
            if(isset($_COOKIE['smCartEventId'])) {
                /** @var Zolago_SalesManago_Helper_Data $salesmanagoHelper */
                $salesmanagoHelper = Mage::helper('tracking');
                $salesmanagoHelper->removeCookie('smCartEventId');
            }
        }

        $this->getResponse()->
        setHeader("content-type", "application/json")->
        setBody($helper->jsonEncode($newResponse));
    }


    /**
     * METHOD IMPLEMENTED WHEN WE SELECT SHIPPING IN CART ONLY!!!
     * We know for sure in cart if customer logged in or not (Mage_Checkout_Model_Type_Onepage::METHOD_CUSTOMER)
     * but we don't know if if customer guest (Mage_Checkout_Model_Type_Onepage::METHOD_GUEST) or he is a new customer (Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER)
     * so in CART we suppose that he is a guest
     * but if he will enter a password on the 1st checkout step
     * then checkout method will be switched to Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER
     * @return string
     */
    public function getCheckoutMethodForCart()
    {
        //Mage::log($onepage, null, "importPostShippingData_1.log");
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            return Mage_Checkout_Model_Type_Onepage::METHOD_CUSTOMER;
        }
        return Mage_Checkout_Model_Type_Onepage::METHOD_GUEST;
    }

    /**
     * Save post shipping (got from /checkout/cart/ page) data to quote
     * @throws Mage_Core_Exception
     */
    public function importPostShippingData() {
        $request = $this->getRequest();


        $onepage = $this->getOnepage();
        $quote = $onepage->getQuote();

        $method	= $this->getCheckoutMethodForCart(); // checkout method

        $methodResponse = $onepage->saveCheckoutMethod($method);
        if(isset($methodResponse['error']) && $methodResponse['error']==1) {
            throw new Mage_Core_Exception($methodResponse['message']);
        }
        /**
        shipping_method[vendor_id]:udtiership_1
         */
        if ($shippingMethod = $request->getParam("shipping_method")) {

            $shippingMethodResponse = $onepage->saveShippingMethod($shippingMethod);
            if (isset($shippingMethodResponse['error']) && $shippingMethodResponse['error'] == 1) {
                throw new Mage_Core_Exception($shippingMethodResponse['message']);
            }


            //Save sales_flat_quote_address.udropship_shipping_details
            $this->setUdropshipShippingDetailsToAddress($shippingMethod);
            //Save sales_flat_quote_address.udropship_shipping_details

            $this->_getCheckoutSession()->setShippingMethod($shippingMethod);
        }


        $quote = Mage::getModel("checkout/cart")->getQuote();
        $address = $quote->getShippingAddress();
        if ($shippingPointCode = $request->getParam("shipping_point_code")) {
            $address->setDeliveryPointName($shippingPointCode);
            $this->_getCheckoutSession()->setDeliveryPointName($shippingPointCode);
        } else {
            //Clear locker address in the sales_flat_quote_address
            $address->setCity("");
            $address->setStreet("");
            $address->setPostcode("");
            $address->setDeliveryPointName("");
            $this->_getCheckoutSession()->setDeliveryPointName();
        }

        $onepage->getQuote()->setTotalsCollectedFlag(false)->collectTotals()->save();
    }

    /**
     * Save post data to quote
     * @throws Mage_Core_Exception
     */
    public function importPostData() {
        $request = $this->getRequest();

        $onepage = $this->getOnepage();

        /**
        method:guest | register | customer
         */
        $method	= $request->getParam("method"); // checkout method
        if($method && $method!=$this->getOnepage()->getCheckoutMehod()) {
            $methodResponse = $onepage->saveCheckoutMethod($method);
            if(isset($methodResponse['error']) && $methodResponse['error']==1) {
                throw new Mage_Core_Exception($methodResponse['message']);
            }
        }
        /**
        account[firstname]:abc
        account[lastname]:abc
        account[email]:abc
        account[phone]:abc
        account[password]:abc
        account[password_confirmation]:abc
         */
        $accountData = $request->getParam("account");
        if(is_array($accountData)) {
            $onepage->saveAccountData($accountData);
        }

        /**
         * agreement[newsletter]
         * agreement[tos]
         */
        $agreementData = $request->getParam("agreement");
        if(is_array($agreementData)) {
            $onepage->saveAgreements($agreementData);
        }


        /**
        billing_address_id:1
         */
        $billingAddressId = $request->getParam("billing_address_id");
        /**
        billing[address_id]:52
        billing[firstname]:mciej
        billing[lastname]:babol
        billing[company]:
        billing[street][]:asdfajsdfkl
        billing[street][]:aslkdjflk
        billing[city]:askdlfjasd
        billing[region_id]:487
        billing[region]:
        billing[postcode]:20-153
        billing[country_id]:PL
        billing[telephone]:605308690
        billing[fax]:maciej.babol@gmail.com
        billing[use_for_shipping]:1
        billing[save_in_address_book]:1
        billing[vat_id]:1
         */
        $billing = $request->getParam("billing");
        $billingAddressId = isset($billing["entity_id"]) ? $billing["entity_id"] : 0;
        if(is_array($billing)) {
            $billingResponse = $onepage->saveBilling($billing, $billingAddressId);
            if(isset($billingResponse['error']) && $billingResponse['error']==1) {
                if (!is_array($billingResponse['message'])) {
                    $billingResponse['message'] = array($billingResponse['message']);
                }
                throw new Mage_Core_Exception(implode("\n", $billingResponse['message']));
            }
        }


        /**
        shipping_address_id:1
         */
        $shippingAddressId = $request->getParam("shipping_address_id");
        /**
        shipping[address_id]:
        shipping[firstname]:asdfa
        shipping[lastname]:asdf
        shipping[company]:asdf
        shipping[street][]:asdfasdf
        shipping[street][]:
        shipping[city]:asdf
        shipping[region_id]:487
        shipping[region]:
        shipping[postcode]:23-234
        shipping[country_id]:PL
        shipping[telephone]:123234234
        shipping[fax]:
        shipping[save_in_address_book]:1
         */
        $shipping = $request->getParam("shipping");
        // If there is locker InPost or Pick-Up point etc.
        // we need to setup correct shipping address
        $deliveryPointData = $request->getParam("delivery_point");
        if (isset($deliveryPointData['name'])) {

            /** @var Zolago_Checkout_Helper_Data $helper */
            $helper = Mage::helper("zolagocheckout");
            $deliveryPoint = $helper->getDeliveryPointShippingAddress();



            $shipping = array_merge($shipping, $deliveryPoint);
            if (isset($shipping['telephone']) && !empty($shipping['telephone'])) {
                $checkoutSession = $onepage->getCheckout();
                $checkoutSession->setLastTelephoneForLocker($shipping['telephone']);
            } else {
                throw new Mage_Core_Exception("Telephone number for the selected delivery method is required");
            }
            $customer = $onepage->getQuote()->getCustomer();
            $shipping['firstname']	= $customer->getFirstname();
            $shipping['lastname']	= $customer->getLastname();
        }

        if(is_array($shipping)) {
            $shippingResponse = $onepage->saveShipping($shipping, $shippingAddressId);
            if(isset($shippingResponse['error']) && $shippingResponse['error']==1) {
                throw new Mage_Core_Exception(implode("\n", $shippingResponse['message']));
            }
        }

        /**
        shipping_method[4]:udtiership_1
         */

        if($shippingMethod = $request->getParam("shipping_method")) {
            $shippingMethodResponse = $onepage->saveShippingMethod($shippingMethod);
            if(isset($shippingMethodResponse['error']) && $shippingMethodResponse['error']==1) {
                throw new Mage_Core_Exception($shippingMethodResponse['message']);
            }
            //Save sales_flat_quote_address.udropship_shipping_details
            $this->setUdropshipShippingDetailsToAddress($shippingMethod);
            //Save sales_flat_quote_address.udropship_shipping_details
            $this->_getCheckoutSession()->setShippingMethod($shippingMethod);
        }


        $address = $onepage->getQuote()->getShippingAddress();
        if ($shippingPointCode = $request->getParam("shipping_point_code")) {
            $address->setDeliveryPointName($shippingPointCode);
            $this->_getCheckoutSession()->setDeliveryPointName($shippingPointCode);
        } else {
            //Clear locker address in the sales_flat_quote_address
//			$address->setCity("");
//			$address->setStreet("");
//			$address->setPostcode("");
            $address->setDeliveryPointName("");
            $this->_getCheckoutSession()->setDeliveryPointName();
        }

        /**
        payment[method]:zolagopayment
        payment[additional_information][provider]:m
         */
        $payment = $request->getParam("payment");
        if(is_array($payment)) {
            $paymentResponse = $onepage->savePayment($payment);
            if(isset($paymentResponse['error']) && $paymentResponse['error']==1) {
                throw new Mage_Core_Exception($paymentResponse['message']);
            }

            //save selected payment to session in order to retrieve it after page refresh
            $this->_getCheckoutSession()->setPayment($payment);
        }

        // Override collect totals - make final collect totals
        $onepage->getQuote()->
        setTotalsCollectedFlag(false)->
        collectTotals()->
        save();

        //adding extra charge
        //todo select carrier
        $extraCharge = (int)Mage::getStoreConfig('carriers/zolagopp/cod_extra_charge');
        if($extraCharge && $payment['method'] == 'cashondelivery') {
            $costVal = $address->getShippingInclTax();
            $baseCostVal = $address->getBaseShippingInclTax();
            $costVal = $costVal + $extraCharge;
            $baseCostVal = $baseCostVal + $extraCharge;
            $address->setShippingInclTax($costVal);
            $address->setBaseShippingInclTax($baseCostVal);
            $address->save();
        }
    }


    /**
     * @param $shippingMethod
     * @throws Zend_Json_Exception
     */
    public function setUdropshipShippingDetailsToAddress($shippingMethod)
    {
        $quote = Mage::getModel("checkout/cart")->getQuote();
        $address = $quote->getShippingAddress();

        $details = $address->getUdropshipShippingDetails();
        $details = $details ? Zend_Json::decode($details) : array();

		$hl = Mage::helper('udropship');
		foreach ($shippingMethod as $vId => $code) {
			$vendor = $hl->getVendor($vId);
			$rate = $address->getShippingRateByCode($code);
			if (!$rate) {
				continue;
			}
			Mage::log("price_excl: ". Mage::helper('udropship')->getShippingPrice($rate->getPrice(), $vendor, $address, 'base'), null, "checkout.log");
			Mage::log("price_incl: ". Mage::helper('udropship')->getShippingPrice($rate->getPrice(), $vendor, $address, 'incl'), null, "checkout.log");
			Mage::log("tax: ". Mage::helper('udropship')->getShippingPrice($rate->getPrice(), $vendor, $address, 'tax'), null, "checkout.log");

			$details['methods'][$vId] = array(
				'code' => $code,
				'cost' => (float)$rate->getCost(),
				'price' => (float)$rate->getPrice(),
				'price_excl' => (float)Mage::helper('udropship')->getShippingPrice($rate->getPrice(), $vendor, $address, 'base'),
				'price_incl' => (float)Mage::helper('udropship')->getShippingPrice($rate->getPrice(), $vendor, $address, 'incl'),
				'tax' => (float)Mage::helper('udropship')->getShippingPrice($rate->getPrice(), $vendor, $address, 'tax'),
				'carrier_title' => $rate->getCarrierTitle(),
				'method_title' => $rate->getMethodTitle(),
				'is_free_shipping' => (int)$rate->getIsFwFreeShipping()
			);
		}


        $address->setUdropshipShippingDetails(Zend_Json::encode($details));
    }

    /**
     * Save addresses
     */
    public function saveAddressesAction() {
        if (!$this->_validateFormKey()) {
            $this->_redirect('*/*');
            return;
        }
        $response = array(
            "status"=>true,
            "content" => array()
        );
        try {
            $this->importPostData();
        } catch (Exception $ex) {
            $response = array(
                "status"=>0,
                "content"=>$ex->getMessage()
            );
        }

        // part for GTM datalayer
        /** @var Mage_Checkout_Model_Session $checkoutSession */
        $checkoutSession = Mage::getSingleton('checkout/session');
        $checkoutData = $checkoutSession->getData();
        /** @var GH_GTM_Helper_Data $gtmHelper */
        $gtmHelper = Mage::helper("ghgtm");
        $data = array();

        if(isset($checkoutData['shipping_method'])) {
            $shippingMethod = $gtmHelper->getShippingMethodName(current($checkoutData['shipping_method']));
            if (!empty($shippingMethod)) {
                $data['basket_shipping_method'] = $shippingMethod;
            }
        }
        if (!empty($data)) {
            $response["dataLayer"] = $data;
        }

        if($this->getRequest()->isAjax()) {
            $this->_prepareJsonResponse($response);
        }
    }

    /**
     * Save shipping data
     */
    public function saveShippingpaymentAction() {
        if (!$this->_validateFormKey()) {
            $this->_redirect('*/*');
            return;
        }
        $response = array(
            "status"=>true,
            "content" => array()
        );

        try {
            $this->importPostData();
        } catch (Exception $ex) {
            $response = array(
                "status"=>0,
                "content"=>$ex->getMessage()
            );
        }

        // part for GTM dataLayer
        /** @var Mage_Checkout_Model_Session $checkoutSession */
        $checkoutSession = Mage::getSingleton('checkout/session');
        $checkoutData = $checkoutSession->getData();
        /** @var GH_GTM_Helper_Data $gtmHelper */
        $gtmHelper = Mage::helper("ghgtm");
        $data = array();

        if(isset($checkoutData['payment']['method'])) {
            $paymentMethod = $gtmHelper->getPaymentMethodName($checkoutData['payment']['method']);
            if (!empty($paymentMethod)) {
                $data['basket_payment_method'] = $paymentMethod;
            }
        }

        if(isset($checkoutData['payment']['additional_information']['provider'])) {
            $paymentDetails = $checkoutData['payment']['additional_information']['provider'];
            if (!empty($paymentDetails)) {
                $data['basket_payment_details'] = $paymentDetails;
            }
        }
        if (!empty($data)) {
            $response["dataLayer"] = $data;
        }

        if($this->getRequest()->isAjax()) {
            $this->_prepareJsonResponse($response);
        }
    }


    public function saveBasketShippingAction() {

        $response = array(
            "status"=>true,
            "content" => array()
        );

        try {
            $this->importPostShippingData();
        } catch (Exception $ex) {
            $response = array(
                "status"=>0,
                "content"=>$ex->getMessage()
            );
        }

        if($this->getRequest()->isAjax()) {
            $this->_prepareJsonResponse($response);
        }
    }

    /**
     * @param mixed $response
     */
    protected function _prepareJsonResponse($response) {
        $this->getResponse()->setHeader('Content-type', 'application/x-json');
        $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($response));
    }

    /**
     * @return Mage_Customer_Model_Session
     */
    protected function _getCustomerSession() {
        return Mage::getSingleton('customer/session');
    }

    /**
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckoutSession() {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Checking does account (email) exist in DB with ajax
     */
    public function checkExistingAccountAction() {

        if (!$this->_validateFormKey()) {
            return;
        }

        $onepage = $this->getOnepage();
        $email = trim($this->getRequest()->getParam("email"));
        $isExits = $onepage->customerEmailExists($email, Mage::app()->getWebsite()->getId());
        $isExits = $isExits === false ? false : true;

        $isSubscribed = Mage::getModel("zolagonewsletter/inviter")->isEmailSubscribed($email);

        $response = array(
            "status"=>$isExits,
            "content" => '',
            "subscribed" =>$isSubscribed
        );

        $this->_prepareJsonResponse($response);

    }

    /**
     * Checking if ZIP exist in DB with ajax
     */
    public function checkZipAction()
    {
        $onepage = $this->getOnepage();

        $zip = trim($this->getRequest()->getParam("zip"));
        $country = trim($this->getRequest()->getParam("country"));

        $isExitsZip = $onepage->customerZipExists($country, $zip);
        $isExits = $isExitsZip === false ? false : true;

        $response = array(
            "status" => $isExits,
            "content" => '');

        $this->_prepareJsonResponse($response);
    }

    public function successAction()
    {
        $session = $this->getOnepage()->getCheckout();
        if (!$session->getLastSuccessQuoteId()) {
            $this->_redirect('checkout/cart');
            return;
        }

        $lastQuoteId = $session->getLastQuoteId();
        $lastOrderId = $session->getLastOrderId();
        $lastRecurringProfiles = $session->getLastRecurringProfileIds();
        if (!$lastQuoteId || (!$lastOrderId && empty($lastRecurringProfiles))) {
            $this->_redirect('checkout/cart');
            return;
        }

        $session->clear();
        $this->loadLayout();
        $this->_initLayoutMessages('checkout/session');
        Mage::dispatchEvent('checkout_onepage_controller_success_action', array('order_ids' => array($lastOrderId)));
        $this->renderLayout();
    }
}