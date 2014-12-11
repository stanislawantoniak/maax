<?php
/**
 * Define multi logic here
 */
require_once Mage::getConfig()->getModuleDir("controllers", "Mage_Checkout") . 
		DS . "OnepageController.php";

abstract class Zolago_Checkout_Controller_Abstract 
	extends Mage_Checkout_OnepageController{
	
	/**
	 * @return Zolago_Checkout_Model_Type_Onepage
	 */
	public function getOnepage() {
		return Mage::getSingleton('zolagocheckout/type_onepage');
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
		
		try{
			$this->importPostData();
		} catch (Exception $ex) {
			$response = array(
				"status" => false,
				"content" => array(
					"redirect"	=> null,
					"message"	=> $ex->getMessage()
				)
			);
			return $this->_prepareJsonResponse($response);
		}
		
		parent::saveOrderAction();
		
		$helper = Mage::helper('core');
		$oldResponse  = $helper->jsonDecode($this->getResponse()->getBody());
		
		$success = isset($oldResponse['success']) ? $oldResponse['success'] : false;
		$logged = Mage::getSingleton('customer/session')->isLoggedIn();
		
		if(!isset($oldResponse['redirect'])){
			$urlArray = array(
				"*",
				$logged ? "singlepage" : "guest",
				$success ? "success" : "error"
			);
			$redirect = Mage::getUrl(implode("/", $urlArray));
		}else{
			$redirect = $oldResponse['redirect'];
		}
		
		$newResponse = array(
			"status" => $success,
			"content" => array(
				"redirect"	=> $redirect,
				"message"	=> isset($oldResponse['error_messages']) ? $oldResponse['error_messages'] : false
			)
		);
				
		$this->getResponse()->
				setHeader("content-type", "application/json")->
				setBody($helper->jsonEncode($newResponse));
	}
	
	/**
	 * Save post data to quote
	 * @throws Mage_Core_Exception
	 */
	public function importPostData(){
		$request = $this->getRequest();
		$onepage = $this->getOnepage();
		
		/**
		method:guest | register | customer
		 */
		$method	= $request->getParam("method"); // chekcout method
		if($method && $method!=$this->getOnepage()->getCheckoutMehod()){
			$methodResponse = $onepage->saveCheckoutMethod($method);
			if(isset($methodResponse['error']) && $methodResponse['error']==1){
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
		if(is_array($accountData)){
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
		if(is_array($billing)){
			$billingResponse = $onepage->saveBilling($billing, $billingAddressId);
			if(isset($billingResponse['error']) && $billingResponse['error']==1){
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
		if(is_array($shipping)){
			$shippingResponse = $onepage->saveShipping($shipping, $shippingAddressId);
			if(isset($shippingResponse['error']) && $shippingResponse['error']==1){
				throw new Mage_Core_Exception(implode("\n", $shippingResponse['message']));
			}
		}
		
		/**
		shipping_method[4]:udtiership_1
		 */
		
		if($shippingMethod = $request->getParam("shipping_method")){
			$shippingMethodResponse = $onepage->saveShippingMethod($shippingMethod);
			if(isset($shippingMethodResponse['error']) && $shippingMethodResponse['error']==1){
				throw new Mage_Core_Exception($shippingMethodResponse['message']);
			}
		}
		
		/**
		payment[method]:zolagopayment
		payment[additional_information][provider]:m
		 */
		$payment = $request->getParam("payment");
		if(is_array($payment)){
			$paymentResponse = $onepage->savePayment($payment);
			if(isset($paymentResponse['error']) && $paymentResponse['error']==1){
				throw new Mage_Core_Exception($paymentResponse['message']);
			}
			// Set default payment?
			$defaultPayment = $request->getParam("default_pay");
			if($defaultPayment===null || $defaultPayment=="1"){
				$this->_getCustomerSession()->setTransferPayment(true);
			}else{
				$this->_getCustomerSession()->setTransferPayment(null);
			}
		}
		

		// Override collect totals - make final collect totals
		$onepage->getQuote()->
			setTotalsCollectedFlag(false)->
			collectTotals()->
			save();
		
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
		try{
		    $this->importPostData();
		} catch (Exception $ex) {
			$response = array(
				"status"=>0,
				"content"=>$ex->getMessage()
			);
		}
		if($this->getRequest()->isAjax()){
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
		try{
		    $this->importPostData();
		} catch (Exception $ex) {
			$response = array(
				"status"=>0,
				"content"=>$ex->getMessage()
			);
		}
		if($this->getRequest()->isAjax()){
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
    public function checkExistingAccountAction(){

        if (!$this->_validateFormKey()) {
            return;
        }

        $onepage = $this->getOnepage();
        $email = $this->getRequest()->getParam("email");
        $isExits = $onepage->customerEmailExists($email, Mage::app()->getWebsite()->getId());
        $isExits = $isExits === false ? false : true;

        $response = array(
            "status"=>$isExits,
            "content" => ''
        );

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