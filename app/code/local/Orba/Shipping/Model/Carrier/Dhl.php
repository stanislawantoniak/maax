<?php
/**
 * Dhl carrier 
 */
class Orba_Shipping_Model_Carrier_Dhl extends Orba_Shipping_Model_Carrier_Abstract {
    			
	const CODE = "orbadhl";
    protected $_code = self::CODE;

    public function prepareRmaSettings($request,$vendor,$rma) {
        $vendorId = $vendor->getId();
	    /** @var Orba_Shipping_Helper_Carrier_Dhl $dhlHelper */
	    $dhlHelper = Mage::helper('orbashipping/carrier_dhl');
        $settings = $dhlHelper->getDhlRmaSettings($vendorId);

	    $pkgDimensions = $dhlHelper->getDhlParcelDimensionsByKey($request->getParam('specify_orbadhl_size'));
        $width = (float)$pkgDimensions[0];
        $height = (float)$pkgDimensions[1];
        $length = (float)$pkgDimensions[2];
        $date = $request->getParam('specify_orbadhl_shipping_date');
        $rateType = $request->getParam('specify_orbadhl_rate_type');
	    $dhlType = $dhlHelper->getDhlParcelTypeByKey($rateType);
	    $weight = $dhlHelper->getDhlParcelWeightByKey($rateType);

	    $dhlParams = array (
            'width' => $width,
            'height' => $height,            
            'length' => $length,
            'shipmentDate' => $date,
            'weight' => $weight,
            'type' => $dhlType,
        );
        if ($request->getParam('specify_orbadhl_custom_dim',false)) {
            $dhlParams['nonStandard'] = true;
        }
        $dhlParams['deliveryValue'] = (string)$rma->getTotalValue();
        foreach ($dhlParams as $key => $param) {
            $settings[$key] = $param;
        }
        $this->setShipmentSettings($settings);
        return $settings;
                 
    }
    public function prepareSettings($params,$shipment,$udpo) {
        $pos = $udpo->getDefaultPos();
        $vendor = Mage::helper('udropship')->getVendor($udpo->getUdropshipVendor());

        /* @var $udpo Zolago_Po_Helper_Data */
        $udpo = Mage::helper('udpo');
        $settings = $udpo->getDhlSettings($vendor, $pos->getId());

        if(!$settings){
            //No settings for POS (Konfiguracja DHL) and no settings for vendor (Sposoby dostawy - Konfiguracja DHL)
            throw new Mage_Core_Exception(Mage::helper("zolagorma")->__("Check your DHL Account Settings"));
        }

        /** @var Orba_Shipping_Helper_Carrier_Dhl $dhlHelper */
	    $dhlHelper = Mage::helper('orbashipping/carrier_dhl');

	    $pkgDimensions = $dhlHelper->getDhlParcelDimensionsByKey($params->getParam('specify_orbadhl_size'));
	    $width = (float)$pkgDimensions[0];
	    $height = (float)$pkgDimensions[1];
	    $length = (float)$pkgDimensions[2];


	    $rateType = $params->getParam('specify_orbadhl_rate_type');
	    $dhlType = $dhlHelper->getDhlParcelTypeByKey($rateType);
	    $weight = $dhlHelper->getDhlParcelWeightByKey($rateType);


        $shipment->setTotalWeight($weight);
        $order = $shipment->getOrder();
        if ($order->getPayment()->getMethod() == 'cashondelivery') {
            $deliveryValue = $udpo->getGrandTotalInclTax()-$udpo->getPaymentAmount();
        } else {
            $deliveryValue = 0;
        }
        $shipmentSettings = array(
                                    'type'			=> $dhlType,
                                    'width'			=> $width,
                                    'height'		=> $height,
                                    'length'		=> $length,
                                    'weight'		=> $weight,
                                    'quantity'		=> Orba_Shipping_Model_Carrier_Client_Dhl::SHIPMENT_QTY,
                                    'shipmentDate'  => $params->getParam('specify_orbadhl_shipping_date'),
                                    'shippingAmount'=> $params->getParam('shipping_amount'),
                                    'deliveryValue' => ($deliveryValue>0)? $deliveryValue:0,
                                    'content'		=> Mage::helper('zolagopo')->__('Shipment') . ': ' . $shipment->getIncrementId(),
                                );
        // add shipment settings
        foreach ($shipmentSettings as $key => $val) {
            $settings[$key] = $val;
        }
        $this->setShipmentSettings($settings);
        return $settings;

    }
    public function setReceiverCustomerAddress($data) {
        $params = array (
            'country' => $data['country_id'],
            'name' => $data['firstname'].' '.$data['lastname'].($data['company'] ? ' '.$data['company'] : ''),
            'postcode' =>$data['postcode'],
            'city' => $data['city'],
            'street' => $data['street'],
            'contact_person' => $data['firstname'].' '.$data['lastname'],
            'contact_phone' => $data['telephone'], 
            'contact_email' => $data['email'],
        );
        $this->setReceiverAddress($params);
    }
    protected function _startClient() {
        $settings = $this->_settings;
        $client = Mage::helper('orbashipping/carrier_dhl')->startClient($settings);
        if (!$client) {
            throw new Mage_Core_Exception(Mage::helper('orbashipping')->__('Cant connect to %s server','DHL'));
        }
        $client->setShipmentSettings($settings);
        $client->setShipperAddress($this->_senderAddress);
        $client->setReceiverAddress($this->_receiverAddress);
        return $client;
    }
    public function createShipments() {
        $client = $this->_startClient();
        $dhlResult = $client->createShipments();
        $results = $client->processDhlShipmentsResult('createShipments',$dhlResult);
        return $results;
    }
    public function createShipmentAtOnce() {
        $client = $this->_startClient();
        $out = $client->createShipmentAtOnce();
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
			$fileLocation		= Mage::helper('orbashipping/carrier_dhl')->getDhlFileDir() . $fileName;
			$result = @$ioAdapter->filePutContent($fileLocation, $fileContent);
			if (!$result) {
    		    Mage::throwException(Mage::helper('orbashipping')->__('Print label error'));
			}
			return array (
			    'trackingNumber' => $out->createShipmentResult->shipmentTrackingNumber,
			    'file' => $fileLocation,
			    'size' => $result,
            );
		} else {
                Mage::throwException(Mage::helper('orbashipping')->__('Create shipment error'));
		}		
    }
            	    
}