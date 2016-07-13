<?php

class ZolagoOs_IAIShop_Model_Client
{
    //authentication config
    const IAISHOP_SHOP_NAME = "demo1-pl.iai-shop.com";
    const IAISHOP_PANEL_LOGIN = "staant813";
    const IAISHOP_PASS = "8b6e6bbb";

    private $_inputFormat = 'json'; //other options: xml (default) and php (serialized php array),
    private $_outputFormat = 'json'; //other options as above
    private $_allowedDataFormats = array('xml', 'json', 'php');

    private $_apiUrl = false;
    private $_login = false;
    private $_password = false;
    private $_systemKey = false;

    private $_helper;

    public function __construct()
    {
        //do nothing for now
    }

    private function getLogin()
    {
        if (!$this->_login) {
            $login = self::IAISHOP_PANEL_LOGIN; //DODO make as constant
            $this->_login = $login;
        }
        return $this->_login;
    }

    private function getPassword()
    {
        if (!$this->_password) {
            $password = self::IAISHOP_PASS; //DODO make as constant
            $this->_password = $password;
        }
        return $this->_password;
    }

    private function getSystemKey()
    {
        if (!$this->_systemKey) {
            $password = sha1(date('Ymd') . sha1($this->getPassword())); // klucz wygenerowany na podstawie hasla i daty
            $this->_systemKey = $password;
        }
        return $this->_systemKey;
    }


    private function getApiUrl()
    {
        if (!$this->_apiUrl) {
            $address = 'http://' . self:: IAISHOP_SHOP_NAME . '/api/';
            $this->_apiUrl = $address;
        }
        return $this->_apiUrl;
    }


    public static function throwException($msg)
    {
        throw Mage::exception('ZolagoOs_IAIShop', $msg);
    }

    public static function log($data)
    {
        Mage::log($data, null, 'zosiaishop_client.log');
    }

    public static function logException($exception)
    {
        Mage::logException($exception);
    }

    private function doRequest($gate, $action, $request = null)
    {
        $address = $this->getApiUrl() . '?gate=' . $gate . "/" . $action . '/0/soap';
        $wsdl = $address . '/wsdl'; //@DODO make as constant

        $binding = array();
        $binding['location'] = $address;
        $binding['trace'] = true;
        $client = new SoapClient($wsdl, $binding);

        $request[$action]['authenticate'] = array();
        $request[$action]['authenticate']['system_key'] = $this->getSystemKey();
        $request[$action]['authenticate']['system_login'] = $this->getLogin();

        $response = $client->__call($action, $request);

        return $response;
    }

    /**
     * @see http://www.iai-shop.com/api.phtml?action=documentation&function=addorders
     */
    public function getProducts($post)
    {
        //dummy data
        return $this->doRequest("getproducts", "getProducts", $post);
    }


    /**
     * @see http://www.iai-shop.com/api.phtml?action=documentation&function=addorders
     */
    public function addOrders($post)
    {
        //dummy data
        return $this->doRequest("addorders", "addOrders", $post);
    }

    /**
     * @return ZolagoOs_IAIShop_Helper_Data
     */
    public function getHelper()
    {
        if (!$this->_helper) {
            $this->_helper = Mage::helper("zosiaishop");
        }
        return $this->_helper;
    }
}