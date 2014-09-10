<?php
/**
 * Define multi logic here
 */
require_once Mage::getConfig()->getModuleDir("controllers", "Mage_Checkout") . DS . "OnepageController.php";

abstract class Zolago_Checkout_Controller_Abstract extends Mage_Checkout_OnepageController{
	/**
	 * Process place order action
	 * Make parial save of all data to quote
	 * Then make save process
	 */
	public function placeOrderAction() {
		$this->_transferDataToQuote();
		
	}
	
	protected function _transferDataToQuote(){
		$request = $this->getRequest();
		$onepage = $this->getOnepage();
		
		/**
		method:guest
		 */
		$method	= $request->getData("method"); // chekcout method
		$onepage->saveCheckoutMethod($method);
		
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
		 */
		$billing = $request->getData("billing");
		$onepage->saveBilling($billing, $billingAddressId);
		
		
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
		$onepage->saveShipping($shipping, $shippingAddressId);
		
		/**
		shipping_method[4]:udtiership_1
		 */
		$shippingMethod = $request->getData("shipping_method");
		$onepage->saveShippingMethod($shippingMethod);
		
		/**
		payment[method]:zolagopayment
		payment[additional_information][provider]:m
		 */
		$payment = $request->getData("payment");
		$onepage->savePayment($payment);
	}
	
	public function saveAddressesAction() {
		$onepage = $this->getOnepage();
		
		$response = array(
			"status"=>1,
			"content" => true
		);
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