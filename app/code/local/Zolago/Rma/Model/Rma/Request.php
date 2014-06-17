<?php
/**
 * dhl request for rma
 */
class Zolago_Rma_Model_Rma_Request extends Mage_Core_Model_Abstract {
    protected $_rma;
    protected $_params = array();
    
    public function setParam($key,$val) {
        $this->_params[$key] = $val;
    }
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
        // default params
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
        // overwriting default params
        foreach ($this->_params as $key=>$param) {
            $dhlSettings[$key] = $param;
        }
        $client->setRma($this->_rma);
        $out = $client->createShipmentAtOnce($dhlSettings);
		if ($out) {
		    if (is_array($out) && !empty($out['error'])) {
    		    Mage::throwException($out['error']);		        
		    }
			$ioAdapter			= new Varien_Io_File();
			$fileName			= $out->createShipmentResult->shipmentTrackingNumber.'.pdf';
			$fileContent		= base64_decode($out->createShipmentResult->label->labelContent);
			$fileLocation		= Mage::helper('zolagodhl')->getDhlFileDir() . $fileName;
			$result = @$ioAdapter->filePutContent($fileLocation, $fileContent);
			if (!$result) {
    		    Mage::throwException(Mage::helper('zolagodhl')->__('Print label error'));
			}
			return array (
			    'trackingNumber' => $out->createShipmentResult->shipmentTrackingNumber,
			    'file' => $fileLocation,
			    'size' => $result,
            );
		} else {
                Mage::throwException(Mage::helper('zolagodhl')->__('Create shipment error'));
		}		
    }
}