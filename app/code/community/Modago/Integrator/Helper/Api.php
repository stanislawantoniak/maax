<?php

/**
 * Class Modago_Integrator_Helper_Api
 */
class Modago_Integrator_Helper_Api extends Mage_Core_Helper_Abstract
{


    const CONFIG_PATH_CARRIERS       = 'modagointegrator/carriers/';
    const CONFIG_PATH_ENABLED        = 'modagointegrator/api_settings/enabled';
    const CONFIG_PATH_LOGIN          = 'modagointegrator/api_settings/login';
    const CONFIG_PATH_PASSWORD       = 'modagointegrator/api_settings/password';
    const CONFIG_PATH_API_KEY        = 'modagointegrator/api_settings/api_key';
    const CONFIG_PATH_API_URL        = 'modagointegrator/api_settings/api_url';
    const CONFIG_PATH_BATCH_SIZE     = 'modagointegrator/api_advanced_settings/batch_size';
    const CONFIG_PATH_LOG_DAYS       = 'modagointegrator/api_advanced_settings/log_days';
    const CONFIG_PATH_BLOCK_SHIPPING = 'modagointegrator/api_advanced_settings/block_shipping';
    const CONFIG_PATH_STORE          = 'modagointegrator/orders/store';
    const CONFIG_PATH_MAPPED_COD     = 'modagointegrator/orders/mapped_cod';
    const CONFIG_PATH_SIMPLE_ONLY	 = 'modagointegrator/orders/simple_only';

    /**
     * Return login for api (vendor id)
     *
     * @return mixed
     */
    public function getLogin() {
        return Mage::getStoreConfig(self::CONFIG_PATH_LOGIN);
    }
    /**
     * Only simple products in basket
     *
     * @return mixed
     */
    public function isSimpleOnly() {
        return Mage::getStoreConfig(self::CONFIG_PATH_SIMPLE_ONLY);
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
	 * Block shipping after order change (if true)
	 *
	 * @return bool
	 */
	public function getBlockShipping() {
		return (bool) Mage::getStoreConfig(self::CONFIG_PATH_BLOCK_SHIPPING);
	}

	/**
	 * Mapped payment method 'cash on delivery' to witch payment method in vendor shop
	 * By default 'cashondelivery'
	 * @return string
	 */
	public function getMappedCodPaymentCode() {
		return Mage::getStoreConfig(self::CONFIG_PATH_MAPPED_COD);
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
                $this->log($this->__('Error: Getting token failed (%s)', $ret->message));
            }
        }
        return $key;
    }

	/**
	 * Test connection to API
	 *
	 * @param $vendorId
	 * @param $password
	 * @param $apiKey
	 * @return array ( status => bool, msg => 'message for error' )
	 */
	public function testConnection($vendorId, $password, $apiKey) {
		/** @var Modago_Integrator_Model_Soap_Client $client */
		$client = Mage::getModel('modagointegrator/soap_client');
		$ret = $client->doLogin($vendorId, $password, $apiKey);
		if (!isset($ret->status)) {
			$data = array(
				'status' => false,
				'msg'    => $this->__("No response from API server")
			);
		} else {
			if ($ret->status) {
				$msg = '';
			} else {
				// Possible problems
				// error_password_invalid
				// error_webapikey_invalid
				// error_vendor_inactive
				$msg = $this->__($ret->message);
			}
			$data = array(
				'status' => $ret->status,
				'msg'    => $msg
			);
		}
		return $data;
	}

	/**
	 * Get mapped Modago carrier name
	 *
	 * @param string $carrierCode
	 * @return string
	 */
	public function getCarrier($carrierCode) {
		$fieldName = 'carrier_' . $carrierCode;
		$value = Mage::getStoreConfig(self::CONFIG_PATH_CARRIERS . $fieldName);
		return $value;
	}

	/**
	 * gets store shipping carrier based on modago shipping method set up in config
	 * @param string $modago_shipping_carrier
	 * @return string
	 */
	public function getShippingCarrierByApi($apiShippingCarrier) {
		//todo: in future we should add more handling here
		return Modago_Integrator_Model_Shipping_Zolagoshipment::SHIPPING_CARRIER_CODE;
	}

	public function getShippingMethodByApi($apiShippingMethod) {
		//todo: in future we should add more handling here
		return Modago_Integrator_Model_Shipping_Zolagoshipment::getShippingMethodCode();
	}

	/**
	 * gets store shipping method based on modago shipping method set up in config
	 * @param string $modago_payment_method
	 * @return string
	 */
	public function getPaymentMethodByApi($apiPaymentMethod) {
		if($apiPaymentMethod == 'cash_on_delivery') {
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
	
    /**
     * translation of api responses
     *
     * @param string $text
     * @return string
     */
     public function translate($text) {
         
         $translate = array (
             'error_order_invalid_status' 	=> $this->__('Order status is invalid'),
             'error_message_id_list_empty' 	=> $this->__('Empty message id list'),
             'error_wrong_datetime_format' 	=> $this->__('Wrong date and time format'),
             'error_order_id_wrong' 		=> $this->__('Wrong order ID'),
             'error_order_id_list_empty'    => $this->__('Empty order ID list'),
             'error_wrong_courier_name' 	=> $this->__('Wrong courier name'),             
         );         
         $from = array_keys($translate);
         $to = array_values($translate);         
         return str_replace($from,$to,$text);
     }
}