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
     * 
     * @param Mage_Core_Model_Website | string | int $website      
     * @param Zolago_Payment_Model_Provider | string $provider
     * @param string $type
     * @return array | null
     */
    public function getProviderConfig($website, $provider, $type) {
        if($provider instanceof Zolago_Payment_Model_Provider) {
            $provider = $provider->getCode();
        }
        $website = Mage::app()->getWebsite($website)->getCode();        
        $configKeys = array(
                          'method',
                          'deny',
                          'additional_information'
                      );
        $path = "$type/$provider";
        $data = $this->getXpath($path);
        if (is_array($data) && isset($data[0])) {
            $config = array();
            $out = $data[0]->asArray();
            foreach ($out as $key => $val) {
                if (in_array($key,$configKeys)) {
                    $config[$key] = $val;
                }
            }
            
            // override by website
            $path = "$type/$provider/websites/$website";
            $data = $this->getXpath($path);
            if (is_array($data) && isset($data[0])) {
                foreach ($data[0]->asArray() as $key=>$val) {
                    if (in_array($key,$configKeys)) {
                        if (is_array($val)) { // additional_information
                            foreach ($val as $keyVal => $valVal) {
                                $config[$key][$keyVal] = $valVal;
                            }
                        } else {
                            $config[$key] = $val;
                        }
                    }
                }
            }
        } else {
            return null;
        }
        return $config;
    }
}


