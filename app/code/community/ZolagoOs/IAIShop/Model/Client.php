<?php

class ZolagoOs_IAIShop_Model_Client
{
    //authentication config

    private $_inputFormat = 'json'; //other options: xml (default) and php (serialized php array),
    private $_outputFormat = 'json'; //other options as above
    private $_allowedDataFormats = array('xml', 'json', 'php');


    private $_vendorId = false;
    protected $_vendor = false;
    private $_apiUrl = false;
    private $_login = false;
    private $_password = false;
    private $_systemKey = false;
    private $_shopId = false;

    private $_helper;

    public function __construct()
    {
        //do nothing for now
    }

    public function getVendorId()
    {
        return $this->_vendorId;
    }

    public function setVendorId($vendorId)
    {
        return $this->_vendorId = $vendorId;
    }

    private function getVendor()
    {
        if (!$this->_vendor) {
            $this->_vendor = Mage::getModel("udropship/vendor")->load($this->getVendorId());
        }
        return $this->_vendor;
    }

    private function getLogin()
    {
        if (!$this->_login) {
            /* @$vendor Zolago_Dropship_Model_Vendor  */
            $vendor = $this->getVendor();
            $this->_login = $vendor->getIaishopLogin();
        }
        return $this->_login;
    }

    private function getPassword()
    {
        if (!$this->_password) {
            /* @$vendor Zolago_Dropship_Model_Vendor  */
            $vendor = $this->getVendor();
            $this->_password = $vendor->getIaishopPass();
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

    protected function getShopId()
    {
        if (!$this->_shopId) {
            /* @$vendor Zolago_Dropship_Model_Vendor  */
            $vendor = $this->getVendor();
            $this->_shopId = $vendor->getIaishopId();
        }
        return $this->_shopId;
    }


    private function getApiUrl()
    {
        if (!$this->_apiUrl) {
            /* @$vendor Zolago_Dropship_Model_Vendor  */
            $vendor = $this->getVendor();
            $address = 'http://' . $vendor->getIaishopUrl() . '/api/';
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

    protected function doRequest($gate, $action, $request = null)
    {
        $address = $this->getApiUrl() . '?gate=' . $gate . "/" . $action . '/0/soap';
        $wsdl = $address . '/wsdl'; //@DODO make as constant

        $binding = array();
        $binding['location'] = $address;
        $binding['trace'] = true;
        $client = new SoapClient($wsdl, $binding);


        $request[$action]['authenticate']['system_key'] = $this->getSystemKey();
        $request[$action]['authenticate']['system_login'] = $this->getLogin();

        $response = $client->__call($action, $request);
        
        return $response;
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