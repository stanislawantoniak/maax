<?php
/**
 * parent class for integration
 */
abstract class ZolagoOs_IAIShop_Model_Integrator_Abstract extends Varien_Object {
    protected $_vendor;
    protected $_token;
    protected $_apiConnector;
    protected $_iaiConnector;
    protected $_helper;



    public function getHelper() {
        if (!$this->_helper) {
            $this->_helper = Mage::helper('zosiaishop');
        }
        return $this->_helper;
    }

    public function setVendor($vendor) {
        $this->_vendor = $vendor;
    }

    public function setApiConnector($connector) {
        $this->_apiConnector = $connector;
    }
    public function setIaiConnector($connector) {
        $this->_iaiConnector = $connector;
    }

    public function getVendor() {
        return $this->_vendor;
    }
    public function getApiConnector() {
        return $this->_apiConnector;
    }
    public function getIaiConnector() {
        return $this->_iaiConnector;
    }
    /**
     * prepare session for vendor
     *
     * @return string
     */

    protected function _getToken() {
        $vendor = $this->getVendor();
        $id = $vendor->getId();
        if (empty($this->_token)) {
            $session = Mage::getModel('ghapi/session');
            $token = $session->generateToken($id);
            $ghapiUser = Mage::getModel('ghapi/user')->loadByVendorId($id);
            if (!$ghapiUser->getId()) {
                Mage::throwException(Mage::helper('zosiaishop')->__('GH Api user for vendor %s does not exists',$vendor->getName()));
            }
            // set session for api
            $session->setUserId($ghapiUser->getId())
            ->setToken($token)
            ->setCreatedAt(Mage::helper('ghapi')->getDate())
            ->save();
            $this->_token = $token;
        }
        return $this->_token;
    }

    /**
     * start process
     */

    abstract public function sync();

    /**
     * confirm messages from list
     */
    public function confirmMessages($toConfirm) {
        if (count($toConfirm)) {
            $connector = $this->getApiConnector();
            $token = $this->_getToken();
            $params = new StdClass();
            $params->sessionToken = $token;
            $params->messageID = new StdClass();
            $params->messageID->ID = $toConfirm;
            $connector->setChangeOrderMessageConfirmation($params);
        }
    }
    
    /**
     * logs 
     *
     * @param string
     */

    public function log($mess) {
        $vendorId = $this->getVendor()->getId();
        $this->getHelper()->log($vendorId,$mess);
    }
    
}