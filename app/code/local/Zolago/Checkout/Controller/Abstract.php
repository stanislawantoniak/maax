<?php
/**
 * Define multi logic here
 */
require_once Mage::getConfig()->getModuleDir("controllers", "Mage_Checkout") . DS . "OnepageController.php";

abstract class Zolago_Checkout_Controller_Abstract extends Mage_Checkout_OnepageController{
	public function saveAddresses() {
		$this->importPostData();
		
	}
	
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
		$this->importPostData();
		parent::saveOrderAction();
		
		$response = Mage::helper('core')->jsonDecode(
				$this->getResponse()->getBody());
		
		$newResponse = array(
			"status" => (!isset($response['error']) || $response['status']==false),
			"content" => $response
		);
		
		$this->getResponse()->
				setBody( Mage::helper('core')->jsonEncode($newResponse));
		
	}
	
	public function importPostData(){
		$request = $this->getRequest();
		$onepage = $this->getOnepage();
		
		/**
		method:guest
		 */
		$method	= $request->getData("method"); // chekcout method
		if($method){
			$onepage->saveCheckoutMethod($method);
			$onepage->getQuote()->setTotalsCollectedFlag(false);
		}
		/**
		 account[firstname]:abc
		 account[lastname]:abc
		 account[email]:abc
		 account[phone]:abc
		 account[password]:abc
		 account[password_confirmation]:abc
		 */
		$accountData = $request->getData("account");
		if(is_array($accountData)){
			$onepage->saveAccountData($accountData);
		}
		
		/**
		billing_address_id:1
		 */
		$billingAddressId = $request->getData("billing_address_id");
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
		$billing = $request->getData("billing");
		if(is_array($billing)){
			$onepage->saveBilling($billing, $billingAddressId);
			$onepage->getQuote()->setTotalsCollectedFlag(false);
		}
		
		
		/**
		shipping_address_id:1
		 */
		$shippingAddressId = $request->getData("shipping_address_id");
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
		$shipping = $request->getData("shipping");
		if(is_array($shipping)){
			$onepage->saveShipping($shipping, $shippingAddressId);
			$onepage->getQuote()->setTotalsCollectedFlag(false);
		}
		
		/**
		shipping_method[4]:udtiership_1
		 */
		if($shippingMethod = $request->getData("shipping_method")){
			$onepage->saveShippingMethod($shippingMethod);
			$onepage->getQuote()->setTotalsCollectedFlag(false);
		}
		
		/**
		payment[method]:zolagopayment
		payment[additional_information][provider]:m
		 */
		$payment = $request->getData("payment");
		if(is_array($payment)){
			$onepage->savePayment($payment);
			$onepage->getQuote()->setTotalsCollectedFlag(false);
		}
		// Override collect totals - make final collect totals
		$onepage->getQuote()->collectTotals()->save();
	}
	
	/**
	 * Save addresses
	 */
	public function saveAddressesAction() {
		$response = array(
			"status"=>true,
			"content" => array()
		);
		try{
			$this->importPostData();
		} catch (Exception $ex) {
			$response = array(
				"status"=>0,
				"content"=>array(
					"error_message"=>$ex->getMessage()
				)
			);
		}
		$this->_prepareJsonResponse($response);
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
}