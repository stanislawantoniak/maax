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
                if (
                    $this->_canUseMethod($method)
                    && $method->isApplicableToQuote($quote, Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL)
                    && $this->_getIsVisibleInCheckout($method)

                ) {
                    $this->_assignMethod($method);
                    if ($method->getCode() !== Mage::getModel("payment/method_cashondelivery")->getCode()){
                        $methods[] = $method;
                    } else {
                        if ($codTitle = $this->_getIsCODCompatibleWithShippingMethod($method)) {
                            $method->setCodShippingDependentTitle($codTitle);
                            $methods[] = $method;
                        }

                    }

                }

            }
            $this->setData('methods', $methods);
        }
        return $methods;
    }


    protected function _canUseMethod($method)
    {
        return $method && $method->canUseCheckout() && parent::_canUseMethod($method);
    }

    /**
     * Return method title for payment selection page
     *
     * @param Mage_Payment_Model_Method_Abstract $method
     * @return string
     */
    public function getMethodTitle(Mage_Payment_Model_Method_Abstract $method)
    {
        if ($method->getCode() == Mage::getModel("payment/method_cashondelivery")->getCode())
            return $method->getCodShippingDependentTitle();

        $form = $this->getChild('payment.method.' . $method->getCode());
        if ($form && $form->hasMethodTitle()) {
            return $form->getMethodTitle();
        }

        return $method->getTitle();
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
     * @param $_method
     * @return bool
     */
    protected function _getIsCODCompatibleWithShippingMethod()
    {
        $storeId = Mage::app()->getStore()->getId();

        $codCode = Mage::getModel("payment/method_cashondelivery")->getCode();
        $pathTitleDefault = 'payment/' . $codCode . '/title';
        $codCheckoutTitleDefault = (string)Mage::getStoreConfig($pathTitleDefault, $storeId);

        $selectedShipping = Mage::helper("zolagocheckout")->getSelectedShipping();

        $selectedMethods = $selectedShipping["methods"];
        if (empty($selectedMethods))     //Case: no session, no quota yet
            return $codCheckoutTitleDefault;

        $methods = array_values($selectedMethods);

        $udropshipMethod = array_shift($methods);

        $info = $this->getOmniChannelMethodInfoByMethod($udropshipMethod);
        $carrier = $info->getDeliveryCode();

        //COD availability defined only for Poczta Polska and inPost and  Pickup Point
        if (!in_array($carrier,
            array(
                Orba_Shipping_Model_Carrier_Default::CODE,
                Orba_Shipping_Model_Post::CODE,
                GH_Inpost_Model_Carrier::CODE,
                ZolagoOs_PickupPoint_Helper_Data::CODE,
                Orba_Shipping_Model_Packstation_Pwr::CODE
            )
        )
        ) {
            return $codCheckoutTitleDefault;
        }

        if( $carrier == Orba_Shipping_Model_Packstation_Pwr::CODE){
            $carrier = 'zospwr';
        }
        $path = 'carriers/' . $carrier . '/' . "cod_allowed";
        $codAllowed = (bool)Mage::getStoreConfig($path, $storeId);
        if (!$codAllowed)
            return false;

        $pathTitle = 'carriers/' . $carrier . '/' . "cod_checkout_title";
        $codCheckoutTitle = (string)Mage::getStoreConfig($pathTitle, $storeId);

        if (!empty($codCheckoutTitle))
            return $codCheckoutTitle;

        return $codCheckoutTitleDefault;
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



    /**
     * @return Varien_Object
     */
    public function getOmniChannelMethodInfoByMethod($udropshipMethod)
    {
        if(empty($udropshipMethod))
            return FALSE;

        // udropship_method (example udtiership_1)
        $storeId = Mage::app()->getStore()->getId();
        return Mage::helper("udropship")->getOmniChannelMethodInfoByMethod($storeId, $udropshipMethod);
    }

}
