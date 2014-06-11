<?php
/**
 * dhl request for rma
 */
class Zolago_Rma_Model_Rma_Request extends Mage_Core_Model_Abstract {
    protected $_rma;

    public function setRma($rma) {
        $this->_rma = $rma;
    }
    protected function _prepareDhlSettings() {
        $vendorId = $this->_rma->getUdropshipVendor();
        $vendor = Mage::getModel('udropship/vendor')->load($vendorId);
        if (!$account = $vendor->getDhlRmaAccount()) {
            if (!$account = $vendor->getDhlAccount()) {
                $account = Mage::helper('zolagodhl')->getDhlAccount();
            }
        }
        if (!$login = $vendor->getDhlRmaLogin()) {
            if (!$login = $vendor->getDhlLogin()) {
                $login = Mage::helper('zolagodhl')->getDhlLogin();
            }
        }

        if (!$password = $vendor->getDhlRmaPassword()) {
            if (!$password = $vendor->getDhlPassword()) {
                $password = Mage::helper('zolagodhl')->getDhlPassword();
            }
        }
        $dhlSettings = array (
            'login' => $login,
            'password' => $password,
            'account' => $account,            
            'weight' => 2,
            'height' => 1,
            'length' => 1,
            'width' => 1,
            'quantity' => 1,            
            'type' => Zolago_Dhl_Model_Client::SHIPMENT_TYPE_PACKAGE,
        );
        return $dhlSettings;
    }
    public function prepareRequest($rma = null) {
        if ($rma) {
            $this->setRma($rma);
        }
        $dhlSettings = $this->_prepareDhlSettings();
        $client = Mage::helper('zolagodhl')->startDhlClient($dhlSettings);
        $client->setRma($this->_rma);
        print_R($client->createShipmentAtOnce($dhlSettings));
    }
}