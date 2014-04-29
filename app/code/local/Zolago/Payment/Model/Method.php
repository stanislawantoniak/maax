<?php

class Zolago_Payment_Model_Method extends Mage_Payment_Model_Method_Abstract {

    const PAYMENT_METHOD_CODE = 'zolagopayment';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_CODE;
    protected $_formBlockType = "zolagopayment/form";
	
	/**
	 * Zolago_Payment_Model_Provider
	 * @var type 
	 */
	protected $_provider;
	
    public function isAvailable($quote = null) {
		return true;
    }
    
    /**
     * Get instructions text from config
     *
     * @return string
     */
    public function getInstructions()
    {
        return trim($this->getConfigData('instructions'));
    }
    
    /**
	 * 
	 * @return text
	 */
    public function getTitle() {
		// Add provider info
        return parent::getTitle();
    }
    
    
    /**
     * @return Mage_Sales_Model_Quote|null
     */
    public function getQuote() {
        return $this->_quote;
    }
    
    
    /**
     * @return Mage_Sales_Model_Quote
     */
    public function setQuote($quote) {
        $this->_quote = $quote;
        return $this;
    }
    
    /**
     * @return Zolago_Payment_Model_Provider|false
     */
    public function getProvider() {
        if($this->_provider===null){
            $this->_provider = false;
            
            $additionalInformation = $providerCode = null;
			
            if($this->getQuote()){
				$additionalInformation = $this->
						getQuote()->
						getPayment()->
						getAdditionalInformation();
            }elseif($this->getInfoInstance ()){
                $additionalInformation = $this->
						getInfoInstance()->
						getAdditionalInformation();
            }
            
			if($additionalInformation && $additionalInformation['provider_code']){
				$providerCode = $additionalInformation['provider_code'];
			}
			
            if($providerCode!==null){
                $model = Mage::getModel("zolagopayment/provider")->load($providerCode, "code");
                if($model->getId()){
                    $this->_provider = $model;
                }
            }
        }
        return $this->_provider;
    }
}