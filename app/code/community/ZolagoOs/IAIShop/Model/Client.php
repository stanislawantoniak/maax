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
        krumo($request);

        $request[$action]['authenticate']['system_key'] = $this->getSystemKey();
        $request[$action]['authenticate']['system_login'] = $this->getLogin();
        krumo($request);
        $response = $client->__call($action, $request);

        return $response;
    }

    /**
     * @see http://www.iai-shop.com/api.phtml?action=method&function=getproducts&method=getProducts
     */
    public function getProducts($params)
    {
        $request = array();
        $action = "getProducts";
        $request[$action]['params'] = $params;
        return $this->doRequest("getproducts", $action, $request);
    }


    /**
     * @see http://www.iai-shop.com/api.phtml?action=method&function=addorders&method=addOrders
     */
    public function addOrders($params)
    {

        $action = "addOrders";

        $request = array();
        $request['addOrders'] = array();

        $request['addOrders']['params'] = array();
        $request['addOrders']['params']['orders'] = array();
        $request['addOrders']['params']['orders'][0] = array();
        $request['addOrders']['params']['orders'][0]['order_type'] = "retail";
        $request['addOrders']['params']['orders'][0]['shop_id'] = 1;
        $request['addOrders']['params']['orders'][0]['stock_id'] = 5;
        $request['addOrders']['params']['orders'][0]['payment_type'] = 'cash_on_delivery';
        $request['addOrders']['params']['orders'][0]['currency'] = "PLN";
        $request['addOrders']['params']['orders'][0]['client_once'] = "y";
        $request['addOrders']['params']['orders'][0]['client_once_data'] = array();
        $request['addOrders']['params']['orders'][0]['client_once_data']['firstname'] = "Klient";
        $request['addOrders']['params']['orders'][0]['client_once_data']['lastname'] = "Testowy";
        $request['addOrders']['params']['orders'][0]['client_once_data']['firm'] = "firm";
        $request['addOrders']['params']['orders'][0]['client_once_data']['nip'] = "nip";
        $request['addOrders']['params']['orders'][0]['client_once_data']['street'] = "street";
        $request['addOrders']['params']['orders'][0]['client_once_data']['zip_code'] = "05-270";
        $request['addOrders']['params']['orders'][0]['client_once_data']['city'] = "city";
        $request['addOrders']['params']['orders'][0]['client_once_data']['country'] = "Polska";
        $request['addOrders']['params']['orders'][0]['client_once_data']['email'] = "admin@iai-sa.com";
        $request['addOrders']['params']['orders'][0]['client_once_data']['phone1'] = "2345234234234";
        $request['addOrders']['params']['orders'][0]['client_once_data']['phone2'] = "23423423423423";
        $request['addOrders']['params']['orders'][0]['client_once_data']['language_id'] = 1;
        $request['addOrders']['params']['orders'][0]['client_login'] = "iaiadmin1";
        $request['addOrders']['params']['orders'][0]['deliverer_id'] = 3;
        $request['addOrders']['params']['orders'][0]['delivery_cost'] = 4.0;
        $request['addOrders']['params']['orders'][0]['delivery_address'] = array();
        $request['addOrders']['params']['orders'][0]['delivery_address']['firstname'] = "Klient";
        $request['addOrders']['params']['orders'][0]['delivery_address']['lastname'] = "lastname";
        $request['addOrders']['params']['orders'][0]['delivery_address']['additional'] = "additional";
        $request['addOrders']['params']['orders'][0]['delivery_address']['street'] = "street";
        $request['addOrders']['params']['orders'][0]['delivery_address']['zip_code'] = "05-270";
        $request['addOrders']['params']['orders'][0]['delivery_address']['city'] = "city";
        $request['addOrders']['params']['orders'][0]['delivery_address']['country'] = "Polska";
        $request['addOrders']['params']['orders'][0]['delivery_address']['phone'] = "4534563456345";
        $request['addOrders']['params']['orders'][0]['products'] = array();
        $request['addOrders']['params']['orders'][0]['products'][0] = array();
        $request['addOrders']['params']['orders'][0]['products'][0]['id'] = 4;
        $request['addOrders']['params']['orders'][0]['products'][0]['size_id'] = "1";
        //$request['addOrders']['params']['orders'][0]['products'][0]['product_sizecode'] = "product_sizecode";
        //$request['addOrders']['params']['orders'][0]['products'][0]['stock_id'] = 6;
        $request['addOrders']['params']['orders'][0]['products'][0]['quantity'] = 1;
        $request['addOrders']['params']['orders'][0]['products'][0]['price'] = 200;
        //$request['addOrders']['params']['orders'][0]['products'][0]['remarks'] = "remarks";
//        $request['addOrders']['params']['orders'][0]['wrappers'] = array();
//        $request['addOrders']['params']['orders'][0]['wrappers'][0] = array();
//        $request['addOrders']['params']['orders'][0]['wrappers'][0]['id'] = 10;
//        $request['addOrders']['params']['orders'][0]['wrappers'][0]['stock_id'] = 11;
//        $request['addOrders']['params']['orders'][0]['wrappers'][0]['quantity'] = 12.0;
//        $request['addOrders']['params']['orders'][0]['wrappers'][0]['price'] = 13.0;
        $request['addOrders']['params']['orders'][0]['rebate'] = 14.0;
        $request['addOrders']['params']['orders'][0]['order_operator'] = "MODAGO";
        $request['addOrders']['params']['orders'][0]['ignore_bridge'] = true;
        $request['addOrders']['params']['orders'][0]['settings'] = array();
        $request['addOrders']['params']['orders'][0]['settings']['send_mail'] = false;
        $request['addOrders']['params']['orders'][0]['settings']['send_sms'] = false;
        $request['addOrders']['params']['orders'][0]['invoice_requested'] = "invoice_requested";

        krumo($request);

        return $this->doRequest("addorders", $action, $request);
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