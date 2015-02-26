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
    
    /**
     * prepare default dhl settings
     * @return array
     */
    protected function _prepareDhlSettings() {
        $vendorId = $this->_rma->getUdropshipVendor();
        return Mage::helper('orbashipping/carrier_dhl')->getDhlRmaSettings($vendorId);
    }
    public function prepareRequest($rma = null) {
        if ($rma) {
            $this->setRma($rma);
        }
        if (!$dhlSettings = $this->_prepareDhlSettings()) {
            return false;
        }
        
        $carrierManager = Mage::helper('orbashipping')->getShippingManager(Orba_Shipping_Model_Carrier_Dhl::CODE);
        
        foreach ($this->_params as $key=>$param) {
            $dhlSettings[$key] = $param;
        }
        $carrierManager->setShipmentSettings($dhlSettings);        
        // sender customer, receiver vendor
        $address = $vendor->getRmaAddress();
        
        $client = Mage::helper('zolagodhl')->startDhlClient($dhlSettings);        
        // overwriting default params
        
        
        $client->setRma($this->_rma);
        $out = $client->createShipmentAtOnce($dhlSettings);
		if ($out) {
		    if (is_array($out) && !empty($out['error'])) {
			    $_helper = Mage::helper('zolagorma');
			    if($out['error'] == "Błędy walidacji zamówienia: W zadanych godzinach realizacji przybycie kuriera jest niemożliwe") {
					$error = $_helper->__("There was an error when booking courier for you. On the date that you chose courier cannot pick up the shipment. Please try some other date or hour.");
			    } else {
				    $error = $out['error'];
			    }
    		    Mage::throwException($error);
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