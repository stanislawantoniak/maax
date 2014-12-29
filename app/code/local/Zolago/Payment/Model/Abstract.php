<?php

abstract class Zolago_Payment_Model_Abstract extends Mage_Payment_Model_Method_Abstract {

    protected $_formBlockType = "zolagopayment/form";
    protected $_isGateway     = true;
	
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
	 * @return boolean
	 */
	public function hasProvider() {
		return true;
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
	 * @return false | array(
	 *  	"method" => "code",
	 *  	"additional_information" => array()
	 * )
	 */
	public function getMappedPayment(Zolago_Payment_Model_Provider $provider = null) {
		if(null===$provider){
			$provider = $this->getProvider();
		}
		
		if(!$provider instanceof Zolago_Payment_Model_Provider){
			return false;
		}
		
		return array(
			"method" => "banktransfer",
			"additional_information" => array("somekey"=>"value")
		);
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
            
			if($additionalInformation && isset($additionalInformation['provider'])){
				$providerCode = $additionalInformation['provider'];
			}
			
			$providerType = "gateway";
			if($this instanceof Zolago_Payment_Model_Cc){
				$providerType = "cc";
			}
			
            if($providerCode!==null){
                $model = Mage::getResourceModel("zolagopayment/provider_collection")
						->addFieldToFilter("code", $providerCode)
						->addFieldToFilter("type", $providerType)
						->getFirstItem();
						
                if(is_object($model) && $model->getId()){
                    $this->_provider = $model;
                }
            }
        }
        return $this->_provider;
    }
}