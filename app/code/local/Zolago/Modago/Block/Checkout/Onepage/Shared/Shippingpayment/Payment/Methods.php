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
                    if ($method->getCode() !== "cashondelivery"){
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

        $selectedShipping = Mage::helper("zolagocheckout")->getSelectedShipping();
        $methods = array_values($selectedShipping["methods"]);

        $udropshipMethod = array_shift($methods);

        $info = $this->getOmniChannelMethodInfoByMethod($udropshipMethod);
        $carrier = $info->getDeliveryCode();

        $storeId = Mage::app()->getStore()->getId();

        $pathTitleDefault = 'payment/cashondelivery/title';
        $codCheckoutTitleDefault = (string)Mage::getStoreConfig($pathTitleDefault, $storeId);
        if(!in_array($carrier, array("zolagopp", "ghinpost")))
            return $codCheckoutTitleDefault;


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
        // udropship_method (example udtiership_1)
        $storeId = Mage::app()->getStore()->getId();

        $collection = Mage::getModel("udropship/shipping")->getCollection();
        $collection->getSelect()
            ->join(
                array('udropship_shipping_method' => $collection->getTable('udropship/shipping_method')),
                "main_table.shipping_id = udropship_shipping_method.shipping_id",
                array(
                    'udropship_method' => new Zend_Db_Expr('CONCAT_WS(\'_\',    udropship_shipping_method.carrier_code ,udropship_shipping_method.method_code)'),
                    "udropship_method_title" => "IF(udropship_shipping_title_store.title IS NOT NULL, udropship_shipping_title_store.title, udropship_shipping_title_default.title)"
                )
            );
        $collection->getSelect()->join(
            array('udtiership_delivery_type' => $collection->getTable('udtiership/delivery_type')),
            "udropship_shipping_method.method_code = udtiership_delivery_type.delivery_type_id",
            array("delivery_code")
        );

        $collection->getSelect()->joinLeft(
            array('udropship_shipping_title_default' => $collection->getTable('udropship/shipping_title')),
            "main_table.shipping_id = udropship_shipping_title_default.shipping_id AND udropship_shipping_title_default.store_id=0",
            array()
        );
        $collection->getSelect()->joinLeft(
            array('udropship_shipping_title_store' => $collection->getTable('udropship/shipping_title')),
            "main_table.shipping_id = udropship_shipping_title_store.shipping_id AND udropship_shipping_title_store.store_id={$storeId}",
            array()
        );

        $collection->getSelect()->having("udropship_method=?", $udropshipMethod);

        return $collection->getFirstItem();
    }

}
