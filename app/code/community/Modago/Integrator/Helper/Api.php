<?php

/**
 * Class Modago_Integrator_Helper_Api
 */
class Modago_Integrator_Helper_Api extends Mage_Core_Helper_Abstract
{


    const CONFIG_PATH             = 'modagointegrator/orders/';
    const CONFIG_PATH_ENABLED     = 'modagointegrator/orders/enabled';
    const CONFIG_PATH_LOGIN       = 'modagointegrator/orders/login';
    const CONFIG_PATH_PASSWORD    = 'modagointegrator/orders/password';
    const CONFIG_PATH_API_KEY     = 'modagointegrator/orders/api_key';
    const CONFIG_PATH_BATCH_SIZE  = 'modagointegrator/orders/batch_size';
    const CONFIG_PATH_API_URL     = 'modagointegrator/orders/api_url';
    const CONFIG_PATH_STORE       = 'modagointegrator/orders/store';
    const CONFIG_PATH_LOG_DAYS    = 'modagointegrator/orders/log_days';
    const CONFIG_PATH_MAPPED_COD  = 'modagointegrator/orders/mapped_cod';

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
	 * Return storeId where orders will be created
	 *
	 * @return int
	 */
	public function getStoreId() {
		return (int) Mage::getStoreConfig(self::CONFIG_PATH_STORE);
	}

	/**
	 * True if in integration config for creating orders is enabled
	 *
	 * @return bool
	 */
	public function isEnabled() {
		return (bool) Mage::getStoreConfig(self::CONFIG_PATH_ENABLED);
	}

	/**
	 * For how long records with logs will be keep
	 *
	 * @return int
	 */
	public function getLogDays() {
		return (int) Mage::getStoreConfig(self::CONFIG_PATH_LOG_DAYS);
	}

	/**
	 * Mapped payment method 'cash on delivery' to witch payment method in vendor shop
	 * By default 'cashondelivery'
	 * @return string
	 */
	public function getMappedCodPaymentCode() {
		return Mage::getStoreConfig(self::CONFIG_PATH_LOG_DAYS);
	}

    /**
     * Return api wsdl url
     *
     * @return string
     */
    public function getApiUrl() {
        $url = Modago_Integrator_Model_Soap_Client::MODAGO_API_WSDL;
        $testUrl = Mage::getStoreConfig(self::CONFIG_PATH_API_URL);
        if (!empty($testUrl)) {
            $url = $testUrl;
        }
        return $url;
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
                $this->log($ret->message);
            }
        }
        return $key;
    }

	/**
	 * Get mapped Modago carrier name
	 *
	 * @param string $carrierCode
	 * @return string
	 */
	public function getCarrier($carrierCode) {
		$fieldName = 'carrier_' . $carrierCode;
		$value = Mage::getStoreConfig(self::CONFIG_PATH . $fieldName);
		return $value;
	}

	/**
	 * gets store shipping method based on modago shipping method set up in config
	 * @param string $modago_shipping_method
	 * @return string
	 */
	public function getShippingMethodByApi($modago_shipping_method) {
		//todo: map modago shipping method to store shipping method;
		return $modago_shipping_method;
	}

	/**
	 * gets store shipping method based on modago shipping method set up in config
	 * @param string $modago_payment_method
	 * @return string
	 */
	public function getPaymentMethodByApi($modago_payment_method) {
		if($modago_payment_method == 'cash_on_delivery') {
			return Mage::getStoreConfig(self::CONFIG_PATH_MAPPED_COD);
		} else {
			return Modago_Integrator_Model_Payment_Zolagopayment::PAYMENT_METHOD_CODE;
		}
	}

	/**
	 * Save log into table and remove outdated
	 *
	 * @param $text
	 * @throws Exception
	 * @return $this
	 */
	public function log($text) {
		if (!empty($text)) {
			/** @var Modago_Integrator_Model_Log $log */
			$log = Mage::getModel('modagointegrator/log');
			$log->setText($text);
			$log->save();
		}
		/** @var Modago_Integrator_Model_Resource_Log $resModel */
		$resModel = Mage::getResourceModel('modagointegrator/log');
		$resModel->removeOldLogs();
		return $this;
	}
}