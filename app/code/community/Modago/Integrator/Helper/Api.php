<?php

/**
 * Class Modago_Integrator_Helper_Api
 */
class Modago_Integrator_Helper_Api extends Mage_Core_Helper_Abstract
{


    const CONFIG_PATH_LOGIN       = 'modagointegrator/orders/login';
    const CONFIG_PATH_PASSWORD    = 'modagointegrator/orders/password';
    const CONFIG_PATH_API_KEY     = 'modagointegrator/orders/api_key';
    const CONFIG_PATH_BATCH_SIZE  = 'modagointegrator/orders/batch_size';

    /**
     * Return login for api (vendor id)
     *
     * @return mixed
     */
    public function getLogin() {
        return Mage::getStoreConfig(self::CONFIG_PATH_LOGIN);
    }

    /**
     * Return password for api
     *
     * @return mixed
     */
    public function getPassword() {
        return Mage::getStoreConfig(self::CONFIG_PATH_PASSWORD);
    }

    /**
     * Return api key
     *
     * @return mixed
     */
    public function getApiKey() {
        return Mage::getStoreConfig(self::CONFIG_PATH_API_KEY);
    }

    /**
     * Get size of batch
     *
     * @return mixed
     */
    public function getBatchSize() {
        return Mage::getStoreConfig(self::CONFIG_PATH_BATCH_SIZE);
    }

    /**
     * get token from server (login)
     *
     * @param Modago_Integrator_Model_Soap_Client $client
     * @return string
     */
    public function getKey($client) {
        $vendorId = $this->getLogin();
        $password = $this->getPassword();
        $apiKey   = $this->getApiKey();
        $ret = $client->doLogin($vendorId,$password,$apiKey);
        $key = -1;
        if (!empty($ret->token)) {
            $key = $ret->token;
        } else {
            if (!empty($ret->message)) {
                Modago_Integrator_Model_Log::log($ret->message);
            }
        }
        return $key;
    }
    
    /**
     * Get mapped Modago carrier name
     *
     * @param string $name
     * @return string
     */
     public function getCarrier($name) {


         // dummy
         return 'dhl';
         // todo
     }
}