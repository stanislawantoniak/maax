<?php

/**
 * Description of Config
 */
class Zolago_Payment_Model_Config extends Varien_Simplexml_Config
{
    /**
     * Id for config cache
     */
    const CACHE_ID  = 'zolagopayment_config';

    /**
     * Tag name for config cache
     */
    const CACHE_TAG = 'CONFIG';
	
	/**
	 * Xml file
	 */
	const FILE = "payment.xml";

    /**
     * Constructor
     * Initializes XML for this configuration
     * Local cache configuration
	 * 
	 * @todo Add Dotpay and another APIs call to get only active channels
     *
     * @param string|Varien_Simplexml_Element|null $sourceData
     */
    public function __construct($sourceData = null)
    {
        parent::__construct($sourceData);

        $canUserCache = Mage::app()->useCache('config');
        if ($canUserCache) {
            $this->setCacheId(self::CACHE_ID)
                ->setCacheTags(array(self::CACHE_TAG))
                ->setCacheChecksum(null)
                ->setCache(Mage::app()->getCache());

            if ($this->loadCache()) {
                return;
            }
        }

        $config = Mage::getConfig()->loadModulesConfiguration(self::FILE);
        $this->setXml($config->getNode('global/zolagopayment'));

        if ($canUserCache) {
            $this->saveCache();
        }
    }
	
	
	/**
	 * Get provider config
	 * @param Mage_Core_Model_Website | string | int $website
	 * @param Zolago_Payment_Model_Provider | string $provider
	 * @param string $type
	 * @return array | null
	 */
	public function getProviderConfig($website, $provider, $type){
		if($provider instanceof Zolago_Payment_Model_Provider){
			$provider = $provider->getCode();
		}
        Mage::log($website, null, "providers_2.log");
		$website = Mage::app()->getWebsite($website)->getCode();
        Mage::log($provider, null, "providers_2.log");
		$path = "$type/$provider/$website";
		$data = $this->getXpath($path);
        Mage::log($data, null, "providers_2.log");
		return (is_array($data) && isset($data[0])) ? $data[0]->asArray() : null;
	}
}

?>
