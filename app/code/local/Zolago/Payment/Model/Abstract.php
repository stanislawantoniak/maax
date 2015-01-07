<?php

abstract class Zolago_Payment_Model_Abstract extends Mage_Payment_Model_Method_Abstract {

    protected $_formBlockType = "zolagopayment/form";
    protected $_isGateway     = true;

    const ZOLAGOPAYMENT_PROVIDER_TYPE_GATEWAY =  "gateway";
    const ZOLAGOPAYMENT_PROVIDER_TYPE_CC =  "cc";
	
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
		$website = $this->getQuote()->getStore()->getWebsite();
		
		$type = self::ZOLAGOPAYMENT_PROVIDER_TYPE_GATEWAY;
		if($this instanceof Zolago_Payment_Model_Cc){
			$type = self::ZOLAGOPAYMENT_PROVIDER_TYPE_CC;
		}
		
		return $this->getConfig()->getProviderConfig(
			$website, 
			$provider, 
			$type
		);
	}
	
	
	/**
	 * @return Zolago_Payment_Model_Config
	 */
	public function getConfig() {
		return Mage::getSingleton('zolagopayment/config');
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
			
			$providerType = self::ZOLAGOPAYMENT_PROVIDER_TYPE_GATEWAY;
			if($this instanceof Zolago_Payment_Model_Cc){
				$providerType = self::ZOLAGOPAYMENT_PROVIDER_TYPE_CC;
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

	/**
	 * @return Zolago_Payment_Model_Resource_Provider_Collection
	 */
    public function getProviderCollection() {

        $type = self::ZOLAGOPAYMENT_PROVIDER_TYPE_GATEWAY;
        if($this instanceof Zolago_Payment_Model_Cc){
            $type = self::ZOLAGOPAYMENT_PROVIDER_TYPE_CC;
        }
        return Mage::getResourceModel("zolagopayment/provider_collection")
            ->addFilterToSelect("type", $type)
            ->addFilterToSelect("is_active", 1);
    }
}