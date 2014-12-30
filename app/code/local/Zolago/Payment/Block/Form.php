<?php

/**
 * Block for Bank Transfer payment method form
 */
class Zolago_Payment_Block_Form extends Mage_Payment_Block_Form
{

    /**
     * Instructions text
     *
     * @var string
     */
    protected $_instructions;

    /**
     * Block construction. Set block template.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('zolagopayment/form.phtml');
    }

    /**
     * Get instructions text from config
     *
     * @return string
     */
    public function getInstructions()
    {
        if (is_null($this->_instructions)) {
            $this->_instructions = $this->getMethod()->getInstructions();
        }
        return $this->_instructions;
    }
	
	public function getProviderCollection() {
		return Mage::getResourceModel("zolagopayment/provider_collection");
	}
	
	public function getProviderLogoUrl(Zolago_Payment_Model_Provider $_provider) {
		return $this->getSkinUrl("images/zolagopayment/on-" . $_provider->getCode().".gif") ; 
	}

	public function isChecked(Zolago_Payment_Model_Provider $_provider) {
		/** @var Zolago_Checkout_Helper_Data $helper */
		$helper = Mage::helper("zolagocheckout");
		$payment = $helper->getPaymentFromSession();

		if(!is_null($payment)) {
			if(is_array($payment) && isset($payment['method']) && isset($payment['additional_information'])){
				$method = $payment['method'];
				$providerCode = isset($payment['additional_information']['provider']) ?
					$payment['additional_information']['provider'] : null;
				return $method==$this->getMethodCode() && $providerCode==$_provider->getCode();
			}
		} elseif($this->getSession()->isLoggedIn()){
			$customer = $this->getSession()->getCustomer();
			if($customer instanceof Mage_Customer_Model_Customer){
				$info = $customer->getLastUsedPayment();
				if(is_array($info) && isset($info['method']) && isset($info['additional_information'])){
					$method = $info['method'];
					$providerCode = isset($info['additional_information']['provider']) ? 
							$info['additional_information']['provider'] : null;
					return $method==$this->getMethodCode() && $providerCode==$_provider->getCode();
				}
			}
		}
		return false;
	}
	
	/**
	 * @return Mage_Customer_Model_Session
	 */
	public function getSession() {
		return Mage::getSingleton('customer/session');
	}
	
	
}
