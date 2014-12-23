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
        if ($this->getSession()->isLoggedIn()) {
            $customer = $this->getSession()->getCustomer();

            if ($customer instanceof Mage_Customer_Model_Customer) {
                $info = $customer->getLastUsedPayment();
                if (is_array($info) && isset($info['method'])) {
                    $method = $info['method'];
                    return $method;
                }
            }
        }
        return false;
    }
	
	/**
	 * @param Mage_Payment_Model_Method_Abstract $method
	 * @return bool
	 */
	public function getIsOnline(Mage_Payment_Model_Method_Abstract $method){
		return $method instanceof Zolago_Payment_Model_Method;
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
            case 'zolagopayment':
                $icon = array($this->getSkinUrl('images/payment_methods/payment_methods.png'));
                break;
            case 'cashondelivery':
                $icon = array($this->getSkinUrl('images/payment_methods/payment_methods-03.png'));
                break;
            case 'banktransfer':
                $icon = array($this->getSkinUrl('images/payment_methods/payment_methods-02.png'));
                break;
            case 'ccsave':
                $icon = array(
                    $this->getSkinUrl('images/payment_methods/payment_methods-05.png'),
                    $this->getSkinUrl('images/payment_methods/payment_methods-04.png')
                );
                break;
        }
        return $icon;
    }

}
