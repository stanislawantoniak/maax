<?php

class Zolago_Modago_Block_Checkout_Onepage_Shared_Shippingpayment_Payment_Methods 
	extends Mage_Checkout_Block_Onepage_Payment_Methods
{

    /**
     * @return Mage_Customer_Model_Session
     */
    public function getSession() {
        return Mage::getSingleton('customer/session');
    }

    /**
     * @return bool|mixed
     */
    public function getSelectedMethodCode()
    {
        $method = false;
        $payment = Mage::helper("zolagocheckout")->getPaymentFromSession();

        if(!is_null($payment) && is_array($payment)) {
            $method = $payment['method'];
        } elseif ($this->getSession()->isLoggedIn()) {
            $customer = $this->getSession()->getCustomer();

            if ($customer instanceof Mage_Customer_Model_Customer) {
                $info = $customer->getLastUsedPayment();
                if (is_array($info) && isset($info['method'])) {
                    $method = $info['method'];
                }
            }
        }

        return $method ? $method : false;
    }

    /**
     * Retrieve available payment methods
     *
     * @return array
     */
    public function getMethods()
    {
        $methods = $this->getData('methods');
        if ($methods === null) {
            $quote = $this->getQuote();
            $store = $quote ? $quote->getStoreId() : null;
            $methods = array();
            foreach ($this->helper('payment')->getStoreMethods($store, $quote) as $method) {
                if ($this->_canUseMethod($method) && $method->isApplicableToQuote(
                        $quote,
                        Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL
                    ) && $this->_getIsVisibleInCheckout($method)
                ) {
                    $this->_assignMethod($method);
                    $methods[] = $method;
                }
            }
            $this->setData('methods', $methods);
        }
        return $methods;
    }


    protected function _canUseMethod($method)
    {
        Mage::log($method->getData(), null, "payment.log");
        return $method && $method->canUseCheckout() && parent::_canUseMethod($method);
    }
	
	/**
	 * @param Mage_Payment_Model_Method_Abstract $method
	 * @return bool
	 */
	public function getIsOnline(Mage_Payment_Model_Method_Abstract $method){
		return $method->isGateway();
	}

    /**
     * @param Mage_Payment_Model_Method_Abstract $method
     * @return bool
     */
    public function _getMethodCheckoutDescription(Mage_Payment_Model_Method_Abstract $_method)
    {
        return (string)$_method->getConfigData("checkout_description");
    }

    /**
     * @param Mage_Payment_Model_Method_Abstract $method
     * @return bool
     */
    public function getIsCreditCard(Mage_Payment_Model_Method_Abstract $method)
    {
        return $method instanceof Zolago_Payment_Model_Cc;
    }

    /**
     * @param $_method
     * @return bool
     */
    protected function _getIsVisibleInCheckout(Mage_Payment_Model_Method_Abstract $_method)
    {
        return (bool)$_method->getConfigData("visible");
    }


    /**
     * @param $code
     * @return array
     */
    public function getPaymentMethodIcon($code)
    {
        $icon = array();
        if (empty($code)) {
            return $icon;
        }
        switch ($code) {
            case 'zolagopayment_gateway':
                $icon = array($this->getSkinUrl('images/payment_methods/payment_methods.png'));
                break;
            case 'cashondelivery':
                $icon = array($this->getSkinUrl('images/payment_methods/payment_methods-03.png'));
                break;
            case 'banktransfer':
                $icon = array($this->getSkinUrl('images/payment_methods/payment_methods-02.png'));
                break;
            case 'zolagopayment_cc':
                $icon = array($this->getSkinUrl('images/payment_methods/payment_methods-07.png') );
                break;
        }
        return $icon;
    }

}
