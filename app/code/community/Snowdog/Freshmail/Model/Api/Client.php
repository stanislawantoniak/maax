<?php

class Snowdog_Freshmail_Model_Api_Client
{
    /**
     * Api domain and path
     *
     * @const string
     */
    const API_DOMAIN = 'https://app.freshmail.pl';
    const API_PATH = '/rest/';

    /**
     * Api connection client instance
     *
     * @var Varien_Http_Client
     */
    protected $_client;

    /**
     * Logger instance
     *
     * @var Snowdog_Freshmail_Model_Log_Adapter
     */
    protected $_logger;

    /**
     * Api key value
     *
     * @var string
     */
    protected $_apiKey;

    /**
     * Api secret value
     *
     * @var string
     */
    protected $_apiSecret;

    /**
     * Init api client
     */
    public function __construct()
    {
        $this->_client = new Varien_Http_Client();
        $this->_logger = Mage::getModel(
            'snowfreshmail/log_adapter',
            'snowfreshmail.log'
        );
    }

    /**
     * Make api call
     *
     * @param string    $methodName
     * @param array     $params
     *
     * @return array
     *
     * @throws Exception
     */
    public function call($methodName, $params = array())
    {
        try {
            $methodPath = self::API_PATH . $methodName;
            $this->_client->setUri(self::API_DOMAIN . $methodPath);
            $this->_client->setHeaders(array(
                'X-Rest-ApiKey' => $this->getApiKey(),
                'X-Rest-ApiSign' => $this->_getApiSignature(
                    $methodPath,
                    $params
                ),
                'Content-Type' => 'application/json',
            ));
            $method = 'GET';
            $this->_debug($this->_client);
            if (!empty($params)) {
                $method = 'POST';
                $this->_client->setRawData(json_encode($params));
                $this->_debug($params);
            }
            $response = $this->_client->request($method);
        } catch (Exception $e) {
            $this->_debug($e);
            throw $e;
        }

        $this->_debug($response);
        $response = json_decode($response->getBody(), true);
        if ($this->_isSuccessfulCall($response)) {
            return $response;
        }
        $this->_handleErrors($response);
    }

    /**
     * Handle errors in response body
     *
     * @param array $response
     *
     * @throws Exception
     */
    protected function _handleErrors($response)
    {
        if ($this->_extractStatusFromResponse($response) != 'ERROR') {
            return;
        }

        if (isset($response['errors'])) {
            foreach ($response['errors'] as $error) {
                throw new Snowdog_Freshmail_Exception(
                    $error['message'],
                    $error['code']
                );
            }
        }

        Mage::throwException('No error details');
    }

    /**
     * Extract status from response body
     *
     * @param array $response
     *
     * @return null|string
     */
    protected function _extractStatusFromResponse($response)
    {
        if (!isset($response['status'])) {
            return null;
        }
        return strtoupper($response['status']);
    }

    /**
     * Check is success status in response body
     *
     * @param array $response
     *
     * @return bool
     */
    protected function _isSuccessfulCall($response)
    {
        if ($this->_extractStatusFromResponse($response) == 'OK') {
            return true;
        }
        return false;
    }

    /**
     * Retrieve api signature
     *
     * @param string    $path
     * @param mixed     $data
     *
     * @return string
     */
    protected function _getApiSignature($path, $data)
    {
        $str = array();
        $str[] = $this->getApiKey();
        $str[] = $path;
        if (!empty($data)) {
            $str[] = json_encode($data);
        }
        $str[] = $this->getApiSecret();
        return sha1(implode($str));
    }

    /**
     * Log debug data
     *
     * @param mixed $object
     */
    protected function _debug($object)
    {
        if ($object instanceof Zend_Http_Response) {
            $this->_logger->log(
                sprintf(
                    'RESPONSE: %s %s',
                    $object->getStatus(),
                    $object->getBody()
                )
            );
        } elseif ($object instanceof Exception) {
            $this->_logger->log(
                sprintf(
                    'RESPONSE ERROR: %s %s',
                    $object->getCode(),
                    $object->getMessage()
                )
            );
        } elseif ($object instanceof Varien_Http_Client) {
            $this->_logger->log(
                sprintf(
                    'REQUEST: %s %s',
                    $object->getUri(),
                    $object->getHeader('X-Rest-ApiSign')
                )
            );
        } elseif (is_array($object)) {
            $this->_logger->log(json_encode($object));
        } else {
            $this->_logger->log((string) $object);
        }
    }

    /**
     * Retrieve api key
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->_apiKey;
    }

    /**
     * Set api key
     *
     * @param string $apiKey
     *
     * @return $this
     */
    public function setApiKey($apiKey)
    {
        $this->_apiKey = $apiKey;
        return $this;
    }

    /**
     * Retrieve api secret
     *
     * @return string
     */
    public function getApiSecret()
    {
        return $this->_apiSecret;
    }

    /**
     * Set api secret
     *
     * @param string $apiSecret
     *
     * @return $this
     */
    public function setApiSecret($apiSecret)
    {
        $this->_apiSecret = $apiSecret;
        return $this;
    }
}
