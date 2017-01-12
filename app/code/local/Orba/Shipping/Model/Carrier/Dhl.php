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
        $dhlParams['content'] = Mage::helper('zolagorma')->__('RMA: %s',$rma->getIncrementId());
        $dhlParams['comment'] = Mage::helper('zolagorma')->__('RMA number: %s, order number: %s',$rma->getIncrementId(),$rma->getUdpoIncrementId());
        foreach ($dhlParams as $key => $param) {
            $settings[$key] = $param;
        }
        $this->setShipmentSettings($settings);
        return $settings;

    }
    public function prepareSettings($params,$shipment,$udpo) {
        $pos = $udpo->getDefaultPos();
        $vendor = Mage::helper('udropship')->getVendor($udpo->getUdropshipVendor());

        /* @var $udpoH Zolago_Po_Helper_Data */
        $udpoH = Mage::helper('udpo');
        $settings = $udpoH->getDhlSettings($vendor, $pos->getId());

        if(!$settings) {
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
                                'comment'              => Mage::helper('zolagopo')->__('Order number') . ': ' . $udpo->getIncrementId(),                
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

        /** @var Orba_Shipping_Model_Carrier_Client_Dhl $client */
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
            $fileLocation		= Mage::helper('orbashipping/carrier_dhl')->getFileDir() . $fileName;
            $result = @$ioAdapter->filePutContent($fileLocation, $fileContent);
            if (!$result) {
                Mage::throwException(Mage::helper('orbashipping')->__('Print label error'));
            }
            return array (
                       'trackingNumber' => $out->createShipmentResult->shipmentTrackingNumber,
                       'file' => $fileLocation,
                       'size' => $result,
                       'orderNumber' => $out->createShipmentResult->dispatchNotificationNumber,
                   );
        } else {
            Mage::throwException(Mage::helper('orbashipping')->__('Create shipment error'));
        }
    }

    /**
     * fill charge fields
     *
     * @param Mage_Sales_Model_Order_Shipment_Track|ZolagoOs_Rma_Model_Rma_Track $track
     * @param int $rate dhl parcel rate
     * @param ZolagoOs_OmniChannel_Model_Vendor $vendor
     * @param float $packageValue total value
     * @param bool $isCod shipment with COD
     */

    public function calculateCharge($track,$rate,$vendor,$packageValue,$codValue) {
        $localVendor = Mage::getModel('udropship/vendor')->load(Mage::getStoreConfig('udropship/vendor/local_vendor'));
        $galleryChargeShipment = floatval(str_replace(',','.',$localVendor->getData($rate)));
        $chargeShipment = floatval(str_replace(',','.',$vendor->getData($rate)));
        $chargeFuelList = Mage::app()->getStore()->getConfig('carriers/orbadhl/fuel_charge');
        if ($chargeFuelList) {
            $chargeFuelList = json_decode($chargeFuelList);
        } else {
            $chargeFuelList = array();
        }
        $fuelPercent = 0;
        $lastDate = 0;
        foreach ($chargeFuelList as $item) {
            if (!is_object($item)) {
                continue;
            }
            $newDate = strtotime($item->fuel_percent_date_from);
            if ($newDate > $lastDate) {
                $lastDate = $newDate;
                $fuelPercent = floatval(str_replace(',','.',$item->fuel_percent));

            }
        }
        $chargeFuel = round($chargeShipment*$fuelPercent/100,2);
        $galleryChargeFuel = round($galleryChargeShipment*$fuelPercent/100,2);
        $threshold = Mage::app()->getStore()->getConfig('carriers/orbadhl/parcel_value_threshold');
        $addCod = Mage::app()->getStore()->getConfig('carriers/orbadhl/charge_always_for_cod');
        $chargeInsurance = 0;
        $galleryChargeInsurance = 0;
        if (($addCod && ($codValue > 0)) ||
                ($packageValue >= $threshold)) {
            $chargeInsurance = round(floatval(str_replace(',','.',$vendor->getData('dhl_insurance_charge_amount')))+floatval(str_replace(',','.',$vendor->getData('dhl_insurance_charge_percent')))*$packageValue/100,2);
            $galleryChargeInsurance = round(floatval(str_replace(',','.',$localVendor->getData('dhl_insurance_charge_amount')))+floatval(str_replace(',','.',$localVendor->getData('dhl_insurance_charge_percent')))*$packageValue/100,2);
        }
        $chargeCod = 0;
        $galleryChargeCod = 0;
        if ($codValue > 0) {
            $chargeCod = round(floatval(str_replace(',','.',$vendor->getData('dhl_cod_charge_amount')))+floatval(str_replace(',','.',$vendor->getData('dhl_cod_charge_percent')))*$codValue/100,2);
            $galleryChargeCod = round(floatval(str_replace(',','.',$localVendor->getData('dhl_cod_charge_amount')))+floatval(str_replace(',','.',$localVendor->getData('dhl_cod_charge_percent')))*$codValue/100,2);
        }
        $chargeTotal = $chargeShipment + $chargeFuel + $chargeInsurance + $chargeCod;
        $galleryChargeTotal = $galleryChargeShipment + $galleryChargeFuel + $galleryChargeInsurance + $galleryChargeCod;
        // setting track values
        $track->setChargeTotal($chargeTotal);
        $track->setChargeShipment($chargeShipment);
        $track->setChargeFuel($chargeFuel);
        $track->setChargeInsurance($chargeInsurance);
        $track->setChargeCod($chargeCod);

        $track->setGalleryChargeTotal($galleryChargeTotal);
        $track->setGalleryChargeShipment($galleryChargeShipment);
        $track->setGalleryChargeFuel($galleryChargeFuel);
        $track->setGalleryChargeInsurance($galleryChargeInsurance);
        $track->setGalleryChargeCod($galleryChargeCod);

    }

    /**
     * processing new track
     */
    public function processTrack($track,$data) {
        /** @var Orba_Shipping_Helper_Carrier_Dhl $_dhlHlp */
        $_dhlHlp = Mage::helper('orbashipping/carrier_dhl');


        if(isset($data['shipping_source_account'])) {
            $shipping_source_account = $data['shipping_source_account'];

            $track->setData("shipping_source_account",$shipping_source_account);
        }
        $weight = $_dhlHlp->getDhlParcelWeightByKey($data['specify_orbadhl_rate_type']);
        $track->setWeight($weight);
        if(isset($data['specify_orbadhl_size'])) {
            $dimensions = $_dhlHlp->getDhlParcelDimensionsByKey($data['specify_orbadhl_size']);
            $track
            ->setWidth($dimensions[0])
            ->setHeight($dimensions[1])
            ->setLength($dimensions[2]);
        } else {
            $track
            ->setWidth(0)
            ->setHeight(0)
            ->setLength(0);
        }
        if(isset($data['gallery_shipping_source']) && $data['gallery_shipping_source'] == 1) {
            $track->setGalleryShippingSource(1);
        }

    }
    
    /**
     * object for generate pdf - exists in dhl
     */

    public function getAggregatedPdfObject() {
        return Mage::getModel('zolagopo/aggregated_pdf');
    }
    public function isLetterable() {
        return true;
    }
    public function getLetterUrl() {
        return 'orbashipping/dhl/lp';
    }
}