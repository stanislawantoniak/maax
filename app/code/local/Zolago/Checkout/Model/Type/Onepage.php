<?php
class Zolago_Checkout_Model_Type_Onepage extends  Mage_Checkout_Model_Type_Onepage
{
	protected $_customerForm;

	/**
	 * Error message of "customer already exists"
	 * @var string
	 */
	private $_customerEmailExistsMessage = '';
	
    /**
     * Create order based on checkout type. Create customer if necessary.
     *
     * @return Zolago_Checkout_Model_Type_Onepage
     * @throws Exception
     */
	public function saveOrder() {
		try{
			$quote = $this->getQuote();
			$payment = $quote->getPayment();
			$methodInstance = $payment->getMethodInstance();

			// Do map payment here...
			if($methodInstance instanceof Zolago_Payment_Model_Abstract){
				$methodInstance->setQuote($this->getQuote());
				if($newData = $methodInstance->getMappedPayment()){
					// Instatize new payemnt instance
					$instance = Mage::helper('payment')->getMethodInstance($newData['method']);
					$instance->setInfoInstance($payment);
					$payment->setMethodInstance($instance);
					$this->savePayment($newData);
					// Save additional data - import in this model do not save the payment
					// directly after import
					if(isset($newData['additional_information'])){
						$payment->setAdditionalInformation($newData['additional_information']);
						$payment->save();
					}
				}
			}

			// Parent save order
			$return = parent::saveOrder();

            // force send email no metter if there is $redirectUrl
            // in parent::saveOrder() line ~812
            $order = Mage::getModel('sales/order');
            $order->load($this->getCheckout()->getLastOrderId());
            if ($order) {
                if ($order->getCanSendNewEmailFlag()) {
                    try {
                        $order->sendNewOrderEmail();
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                }
            }

            if(Mage::getSingleton('customer/session')->isLoggedIn()
                && $this->getQuote()->getCustomerId()) {
                // Update customer data
                $customerPayment = $this->_checkoutSession->getPayment(true);
                if (!is_null($customerPayment)
                    && is_array($customerPayment)) {
                    if (isset($customerPayment['method'])) {
                        $this->getQuote()->getCustomer()->setLastUsedPayment($customerPayment);
                    }
                }

                //save customer object
                $this->getQuote()->getCustomer()->save();
            }

            //newsletter actions
			$agreements = $this->_checkoutSession->getAgreements(true);
            /** @var Zolago_Newsletter_Model_Inviter $model */
            $model = Mage::getModel('zolagonewsletter/inviter');
			if(isset($agreements['agreement_newsletter']) && $agreements['agreement_newsletter'] == 1) {
                $model->addSubscriber($this->getQuote()->getCustomerEmail(),Zolago_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED);
			} elseif(isset($agreements['agreement_newsletter']) && $agreements['agreement_newsletter'] == 0) {
				// send invitation mail, model takes care of handling everything
                if (Mage::helper("zolagonewsletter")->isModuleActive()){
                    $model->sendInvitationEmail($this->getQuote()->getCustomerEmail());
                }

			}
			
		} catch (Exception $ex) {
			throw $ex;
		}
		
		return $return;
	}
	/**
     * Initialize quote state to be valid for one page checkout
	 * Modificaton: do not handle presisten data if not logged in
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function initCheckout()
    {
        $checkout = $this->getCheckout();
        $customerSession = $this->getCustomerSession();

        /**
         * Reset multishipping flag before any manipulations xwith quote address
         * addAddress method for quote object related on this flag
         */
        if ($this->getQuote()->getIsMultiShipping()) {
            $this->getQuote()->setIsMultiShipping(false);
            $this->getQuote()->save();
        }
		
		
		$quote = $this->getQuote();

		$billing = $quote->getBillingAddress();
		$shipping = $quote->getShippingAddress();

        /*
        * want to load the correct customer information by assigning to address
        * instead of just loading from sales/quote_address
		* assign only if logged in!
        */
        $customer = $customerSession->getCustomer();
		
        /* @var $customer Mage_Customer_Model_Customer */

		if($customer && Mage::getSingleton('customer/session')->isLoggedIn()){
			
			

			$defaultBilling = $customer->getDefaultBillingAddress();
			$defaultShipping = $customer->getDefaultBillingAddress();


			// Import defualt billing
			if($defaultBilling && $this->_isAddressNotFilled($billing)){
				$billing = null;
			}

			// Import defualt shipping
			if($defaultShipping && $this->_isAddressNotFilled($shipping)){
				$shipping = null;
			}
			
			// Do import of personal data
			$quote->setCustomerEmail(null);
			//$quote->setCustomerFirstname(null);
			//$quote->setCustomerLastname(null);
			
			if(is_null($billing) || is_null($shipping)){
				// Funciton setting the customer
				$quote->assignCustomerWithAddressChange($customer, $billing, $shipping);
			}else{
				// If not set only customer
				$quote->setCustomer($customer);
			}
			
			// Do save 
			$quote->save();

			
        }
        return $this;
    }
	
	/**
	 * Is the address filed with some data?
	 * @param Mage_Sales_Model_Quote_Address $adress
	 * @return bool
	 */
	protected function _isAddressNotFilled(Mage_Sales_Model_Quote_Address $adress) {
		return !$adress->getId() || !$adress->getFirstname() || !$adress->getLastname();
	}
	
	
	/**
     * Specify checkout method
	 * Modificaton: removed quote save
     *
     * @param   string $method
     * @return  array
     */
    public function saveCheckoutMethod($method)
    {
        if (empty($method)) {
            return array('error' => -1, 'message' => Mage::helper('checkout')->__('Invalid data.'));
        }

        $this->getQuote()->setCheckoutMethod($method);//->save();
        return array();
    }
	
	/**
     * Specify quote shipping method
	 * Modificaton: removed quote save
     *
     * @param   string $shippingMethod
     * @return  array
     */
    public function saveShippingMethod($shippingMethod)
    {
        if (empty($shippingMethod)) {
            return array('error' => -1, 'message' => Mage::helper('checkout')->__('Invalid shipping method.'));
        }
        $rate = $this->getQuote()->getShippingAddress()->getShippingRateByCode($shippingMethod);
        if (!$rate) {
            return array('error' => -1, 'message' => Mage::helper('checkout')->__('Invalid shipping method.'));
        }
        $this->getQuote()->getShippingAddress()
            ->setShippingMethod($shippingMethod);
        return array();
    }
	
	
    /**
     * Specify quote payment method
     *
     * @param   array $data
     * @return  array
     */
    public function savePayment($data)
    {
        if (empty($data)) {
            return array('error' => -1, 'message' => Mage::helper('checkout')->__('Invalid data.'));
        }
        $quote = $this->getQuote();
        if ($quote->isVirtual()) {
            $quote->getBillingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
        } else {
            $quote->getShippingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
        }

        // shipping totals may be affected by payment method
        if (!$quote->isVirtual() && $quote->getShippingAddress()) {
            $quote->getShippingAddress()->setCollectShippingRates(true);
        }

        $data['checks'] = Mage_Payment_Model_Method_Abstract::CHECK_USE_CHECKOUT
            | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY
            | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY
            | Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX
            | Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL;

        $payment = $quote->getPayment();
        $payment->importData($data);

        //$quote->save();

        return array();
    }
	
    /**
     * Save checkout shipping address
	 * Modification: removed quote save
     *
     * @param   array $data
     * @param   int $customerAddressId
     * @return  Mage_Checkout_Model_Type_Onepage
     */
    public function saveShipping($data, $customerAddressId)
    {
        if (empty($data)) {
            return array('error' => -1, 'message' => Mage::helper('checkout')->__('Invalid data.'));
        }
        $address = $this->getQuote()->getShippingAddress();

        /* @var $addressForm Mage_Customer_Model_Form */
        $addressForm    = Mage::getModel('customer/form');
        $addressForm->setFormCode('customer_address_edit')
            ->setEntityType('customer_address')
            ->setIsAjaxRequest(Mage::app()->getRequest()->isAjax());

        if (!empty($customerAddressId)) {
            $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
            if ($customerAddress->getId()) {
                if ($customerAddress->getCustomerId() != $this->getQuote()->getCustomerId()) {
                    return array('error' => 1,
                        'message' => Mage::helper('checkout')->__('Customer Address is not valid.')
                    );
                }

                $address->importCustomerAddress($customerAddress)->setSaveInAddressBook(0);
                $addressForm->setEntity($address);
                $addressErrors  = $addressForm->validateData($address->getData());
                if ($addressErrors !== true) {
                    return array('error' => 1, 'message' => $addressErrors);
                }
            }
        } else {
            $addressForm->setEntity($address);
            // emulate request object
            $addressData    = $addressForm->extractData($addressForm->prepareRequest($data));
            $addressErrors  = $addressForm->validateData($addressData);
            if ($addressErrors !== true) {
                return array('error' => 1, 'message' => $addressErrors);
            }
            $addressForm->compactData($addressData);
            // unset shipping address attributes which were not shown in form
            foreach ($addressForm->getAttributes() as $attribute) {
                if (!isset($data[$attribute->getAttributeCode()])) {
                    $address->setData($attribute->getAttributeCode(), NULL);
                }
            }

            $address->setCustomerAddressId(null);
            // Additional form data, not fetched by extractData (as it fetches only attributes)
            $address->setSaveInAddressBook(empty($data['save_in_address_book']) ? 0 : 1);
            $address->setSameAsBilling(empty($data['same_as_billing']) ? 0 : 1);
        }

        $address->implodeStreetAddress();
        $address->setCollectShippingRates(true);

        if (($validateRes = $address->validate())!==true) {
            return array('error' => 1, 'message' => $validateRes);
        }
		
		
        //$this->getQuote()->collectTotals()->save();

        return array();
    }
	
	/**
     * Save billing address information to quote
     * This method is called by One Page Checkout JS (AJAX) while saving the billing information.
     *
     * @param   array $data
     * @param   int $customerAddressId
     * @return  Mage_Checkout_Model_Type_Onepage
     */
    public function saveBilling($data, $customerAddressId)
    {
        if (empty($data)) {
            return array('error' => -1, 'message' => Mage::helper('checkout')->__('Invalid data.'));
        }

        /** @var Zolago_Checkout_Helper_Data $helper */
        $helper = Mage::helper("zolagocheckout");
        $deliveryPointAddress = $helper->getDeliveryPointShippingAddress();

        $address = $this->getQuote()->getBillingAddress();
        /* @var $addressForm Mage_Customer_Model_Form */
        $addressForm = Mage::getModel('customer/form');
        $addressForm->setFormCode('customer_address_edit')
            ->setEntityType('customer_address')
            ->setIsAjaxRequest(Mage::app()->getRequest()->isAjax());

        if (!empty($customerAddressId)) {
            $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
            if ($customerAddress->getId()) {
                if ($customerAddress->getCustomerId() != $this->getQuote()->getCustomerId()) {
                    return array('error' => 1,
                        'message' => Mage::helper('checkout')->__('Customer Address is not valid.')
                    );
                }

                $address->importCustomerAddress($customerAddress)->setSaveInAddressBook(0);
                $addressForm->setEntity($address);
                $addressErrors  = $addressForm->validateData($address->getData());
                if ($addressErrors !== true) {
                    return array('error' => 1, 'message' => $addressErrors);
                }
            }
        } else {
            $addressForm->setEntity($address);
            // emulate request object
            $addressData    = $addressForm->extractData($addressForm->prepareRequest($data));
            $addressErrors  = $addressForm->validateData($addressData);
            if ($addressErrors !== true && empty($deliveryPointAddress)) {
                return array('error' => 1, 'message' => array_values($addressErrors));
            }
            $addressForm->compactData($addressData);
            //unset billing address attributes which were not shown in form
            foreach ($addressForm->getAttributes() as $attribute) {
                if (!isset($data[$attribute->getAttributeCode()])) {
                    $address->setData($attribute->getAttributeCode(), NULL);
                }
            }
            $address->setCustomerAddressId(null);
            // Additional form data, not fetched by extractData (as it fetches only attributes)
            $address->setSaveInAddressBook(empty($data['save_in_address_book']) ? 0 : 1);
        }
		

        // set email for newly created user
        if (!$address->getEmail() && $this->getQuote()->getCustomerEmail()) {
            $address->setEmail($this->getQuote()->getCustomerEmail());
        }

        // validate billing address
        /*if (($validateRes = $address->validate()) !== true) {
            //return array('error' => 1, 'message' => $validateRes);
        }*/

        $address->implodeStreetAddress();

        if (true !== ($result = $this->_validateCustomerData($data))) {
            return $result;
        }

        if (!$this->getQuote()->getCustomerId() && self::METHOD_REGISTER == $this->getQuote()->getCheckoutMethod()) {
            if ($this->_customerEmailExists($address->getEmail(), Mage::app()->getWebsite()->getId())) {
                return array('error' => 1, 'message' => $this->_customerEmailExistsMessage);
            }
        }

        if (!$this->getQuote()->isVirtual()) {
            /**
             * Billing address using otions
             */
            $usingCase = isset($data['use_for_shipping']) ? (int)$data['use_for_shipping'] : 0;

            switch ($usingCase) {
                case 0:
                    $shipping = $this->getQuote()->getShippingAddress();
                    $shipping->setSameAsBilling(0);
                    break;
                case 1:
                    $billing = clone $address;
                    $billing->unsAddressId()->unsAddressType();
                    $shipping = $this->getQuote()->getShippingAddress();
                    $shippingMethod = $shipping->getShippingMethod();

                    // Billing address properties that must be always copied to shipping address
                    $requiredBillingAttributes = array('customer_address_id');

                    // don't reset original shipping data, if it was not changed by customer
                    foreach ($shipping->getData() as $shippingKey => $shippingValue) {
                        if (!is_null($shippingValue) && !is_null($billing->getData($shippingKey))
                            && !isset($data[$shippingKey]) && !in_array($shippingKey, $requiredBillingAttributes)
                        ) {
                            $billing->unsetData($shippingKey);
                        }
                    }
                    $shipping->addData($billing->getData())
                        ->setSameAsBilling(1)
                        ->setSaveInAddressBook(0)
                        ->setShippingMethod($shippingMethod)
                        ->setCollectShippingRates(true);
					
                    break;
            }
        }
        
		/*$this->getQuote()->collectTotals();
        $this->getQuote()->save();*/

        if (!$this->getQuote()->isVirtual() && $this->getCheckout()->getStepData('shipping', 'complete') == true) {
            //Recollect Shipping rates for shipping methods
            $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
        }
        return array();
    }

	public function saveAgreements(array $agreementsData) {
		$data = array();
		foreach ($agreementsData as $attribute=>$value) {
			$data['agreement_'.$attribute] = $value ? 1 : 0;
		}
		$this->_checkoutSession->setAgreements($data);
		return $this;
	}
	
	
	/**
	 * Save account data and transfer same to quote
	 * @param array $accountData
	 * @return Zolago_Checkout_Model_Type_Onepage
	 * @throws Mage_Core_Exception
	 */
	public function saveAccountData(array $accountData) {
		$quote = $this->getQuote();
		$customer = $this->getQuote()->getCustomer();
		
        $form = $this->_getCustomerForm();
        $form->setEntity($customer);
		
		// Cannot change email durgin checout within registered customer
		if($this->getCheckoutMethod() == self::METHOD_CUSTOMER){
			if(isset($accountData['email'])){
				unset($accountData['email']);
			}
		}
		
		// check email exists if transfered
		if(isset($accountData['email'])){
			$websiteId = null;
			if(Mage::getStoreConfig("customer/account_share/scope")==1){
				$websiteId = Mage::app()->getWebsite()->getId();
			}
			if( $this->_customerEmailExists($accountData['email'], $websiteId)){
				throw new Mage_Core_Exception("Email already exists");
			}
		}

        // emulate request
        $request = $form->prepareRequest($accountData);
        $data    = $form->extractData($request);

        $form->restoreData($data);
        $data = array();
		
		// Add all form attributes - There is no password
        foreach ($form->getAttributes() as $attribute) {
            $code = sprintf('customer_%s', $attribute->getAttributeCode());
            $data[$code] = $customer->getData($attribute->getAttributeCode());
        }

        if (isset($data['customer_group_id'])) {
            $groupModel = Mage::getModel('customer/group')->load($data['customer_group_id']);
            $data['customer_tax_class_id'] = $groupModel->getTaxClassId();
        }
		
        if ($quote->getCheckoutMethod(true) == self::METHOD_REGISTER) {
            // save customer encrypted password in quote
			$password = isset($accountData['password']) ? $accountData['password'] : "";
			if(empty($password)){
				throw new Mage_Core_Exception("No password transfered");
			}
			// Set customer password
			$customer->setPassword($password);
            $quote->setPasswordHash($customer->encryptPassword($customer->getPassword()));
		}
        $telephone = isset($accountData['telephone']) ? $accountData['telephone'] : "";
        if(!empty($telephone)){
            $customer->setPhone($telephone);
        }
        $this->getQuote()->addData($data);//->save();
        return $this;
		
	}


    public function getInpostLocker(){
        $onepageShipping = new Zolago_Modago_Block_Checkout_Cart_Sidebar_Shipping();
        return $onepageShipping->getInpostLocker();
    }
	
    /**
     * Prepare quote for customer registration and customer order submit
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    protected function _prepareNewCustomerQuote()
    {
        $quote      = $this->getQuote();

        $billing    = $quote->getBillingAddress();
        $shipping   = $quote->isVirtual() ? null : $quote->getShippingAddress();


        /** @var Zolago_Checkout_Helper_Data $helper */
        $helper = Mage::helper("zolagocheckout");
        $deliveryPointAddress = $helper->getDeliveryPointShippingAddress();

		// Customer should be new object - even persistence
		
        $customer = Mage::getModel('customer/customer');
		
        /* @var $customer Mage_Customer_Model_Customer */
        $customerBilling = $billing->exportCustomerAddress();
        $needInvoice = $billing->getNeedInvoice();

        if(empty($deliveryPointAddress) || $needInvoice){
            $customer->addAddress($customerBilling);
        }
        
        $billing->setCustomerAddress($customerBilling);
        $customerBilling->setIsDefaultBilling(true);
        if ($shipping && !$shipping->getSameAsBilling()) {
            $customerShipping = $shipping->exportCustomerAddress();
            if(empty($deliveryPointAddress)){
                $customer->addAddress($customerShipping);
            }

            $shipping->setCustomerAddress($customerShipping);
            $customerShipping->setIsDefaultShipping(true);
        } else {
            $customerBilling->setIsDefaultShipping(true);
        }

        Mage::helper('core')->copyFieldset('checkout_onepage_quote', 'to_customer', $quote, $customer);
        $customer->setPassword($customer->decryptPassword($quote->getPasswordHash()));
        $customer->setPasswordHash($customer->hashPassword($customer->getPassword()));
        $telephone = $billing->getTelephone();
        if(!empty($telephone)){
            $customer->setData('phone', $telephone);
        }
        $quote->setCustomer($customer)
            ->setCustomerId(true);
    }

    /**
     * Prepare quote for customer order submit
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    protected function _prepareCustomerQuote()
    {
        $quote      = $this->getQuote();
        $billing    = $quote->getBillingAddress();
        $shipping   = $quote->isVirtual() ? null : $quote->getShippingAddress();

        /** @var Zolago_Checkout_Helper_Data $helper */
        $helper = Mage::helper("zolagocheckout");
        $deliveryPointAddress = $helper->getDeliveryPointShippingAddress();

        $customer = $this->getCustomerSession()->getCustomer();
        if (!$billing->getCustomerId() || $billing->getSaveInAddressBook()) {
            $customerBilling = $billing->exportCustomerAddress();
            if(empty($deliveryPointAddress)){
               $customer->addAddress($customerBilling); 
            }


            $billing->setCustomerAddress($customerBilling);
        }
        if ($shipping && !$shipping->getSameAsBilling() &&
            (!$shipping->getCustomerId() || $shipping->getSaveInAddressBook())) {
            $customerShipping = $shipping->exportCustomerAddress();
            if(empty($deliveryPointAddress)){
                $customer->addAddress($customerShipping);
            }
            $shipping->setCustomerAddress($customerShipping);
        }

        if (isset($customerBilling) && !$customer->getDefaultBilling()) {
            $customerBilling->setIsDefaultBilling(true);
        }
        if ($shipping && isset($customerShipping) && !$customer->getDefaultShipping()) {
            $customerShipping->setIsDefaultShipping(true);
        } else if (isset($customerBilling) && !$customer->getDefaultShipping()) {
            $customerBilling->setIsDefaultShipping(true);
        }
        $quote->setCustomer($customer);
    }

	/**
     * Override true flag in getter quote method - original method name not logged in...
     *
     * @return string
     */
    public function getCheckoutMethod()
    {
        if ($this->getCustomerSession()->isLoggedIn()) {
			// Logged user - force customer method
            $this->getQuote()->setCheckoutMethod(self::METHOD_CUSTOMER);
        }else{
			// Method name is not init
			// OR
			// Not logged user and current method = customer
			if(!$this->getQuote()->getCheckoutMethod(true) || 
			   $this->getQuote()->getCheckoutMethod(true)==self::METHOD_CUSTOMER){
				if ($this->_helper->isAllowedGuestCheckout($this->getQuote())) {
					$this->getQuote()->setCheckoutMethod(self::METHOD_GUEST);
				} else {
					$this->getQuote()->setCheckoutMethod(self::METHOD_REGISTER);
				}
			}
        }
        return $this->getQuote()->getCheckoutMethod(true);
    }
	
	
	/**
	 * @return Mage_Customer_Model_Form
	 */
	protected function _getCustomerForm()
    {
        if (is_null($this->_customerForm)) {
            $addressForm = Mage::getModel('customer/form');
			$this->_customerForm = $addressForm->setFormCode('checkout_register')
				->setEntityType('customer')
				->setIsAjaxRequest(Mage::app()->getRequest()->isAjax());
        }
        return $this->_customerForm;
    }

    public function customerEmailExists($email, $websiteId = null)
    {
        return $this->_customerEmailExists($email, $websiteId);
    }

    public function customerZipExists($country, $zip)
    {
        return $this->_customerZipExists($country, $zip);
    }
    protected function _customerZipExists($country, $zip)
    {
        /* @var $helper Orba_Shipping_Helper_Carrier_Dhl */
        $helper = Mage::helper("orbashipping/carrier_dhl");
        $isValidZip = $helper->isDHLValidZip($country, $zip);
        return $isValidZip;
    }
}
