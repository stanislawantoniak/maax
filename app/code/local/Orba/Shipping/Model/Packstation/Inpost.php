<?php
/**
 * Inpost packstation
 */
class Orba_Shipping_Model_Packstation_Inpost extends Orba_Shipping_Model_Carrier_Abstract {

    const CODE = "ghinpost";
    protected $_code = self::CODE;
    protected $_client;

    public function prepareSettings($params,$shipment,$udpo) {
        $size = $params->getParam('specify_inpost_size');
        $order = $shipment->getOrder();
        $vendor = Mage::helper('udropship')->getVendor($udpo->getUdropshipVendor());
        $pos = $udpo->getDefaultPos();
        if ($order->getPayment()->getMethod() == 'cashondelivery') {
            $deliveryValue = $udpo->getGrandTotalInclTax()-$udpo->getPaymentAmount();
        } else {
            $deliveryValue = 0;
        }
        $insuranceValue = $udpo->getSubTotalInclTax(); 
        if ($insuranceValue < Mage::getStoreConfig('carriers/ghinpost/min_insurance_value')) {
            $insuranceValue = 0; 
        }

        $shipmentSettings = array(
                                'size' => $size,
                                'pos' => $pos,
                                'udpo' => $udpo,
                                'cod' => $deliveryValue,                                
                                'insurance' => $insuranceValue,
                            );

        $settings = Mage::helper('ghinpost')->getApiSettings($vendor,$pos);
        foreach ($shipmentSettings as $key=>$val) {
            $settings[$key] = $val;
        }
        $this->setShipmentSettings($settings);
    }

    public function setReceiverCustomerAddress($data) {
        $this->setReceiverAddress($data);
    }
    protected function _startClient() {
        $settings = $this->_settings;
        $client = Mage::helper('orbashipping/packstation_inpost')->startClient($settings);
        if (!$client) {
            throw new Mage_Core_Exception(Mage::helper('orbashipping')->__('Cant connect to %s server','INPOST'));
        }
        return $client;
    }

    /**
     * prepare dispatch point (if not exists)
     */
    protected function _getDispatchPointName() {
        $client = $this->getClient();
        $settings = $this->_settings;
        $name = empty($settings['pos'])? '':(empty($settings['pos']->getExternalId())? $settings['pos']->getName():$settings['pos']->getExternalId());
        $point = $client->getDispatchPoint($name);
        if (!empty($point['error'])) {
            Mage::throwException($point['error']);
        }
        if (empty($point['count'])) {
            $pos = $settings['pos'];
            $ret = $client->createDispatchPoint($pos);
            if (empty($ret['status']) ||
                $ret['status'] != 'OK') {
                if (!empty($ret['error'])) {
                    Mage::throwException($ret['error']);
                } else {
                    Mage::throwException(Mage::helper('ghinpost')->__('Cannot create dispatch point'));
                }
            }
        }
        return $name;        
    }
    
    /**
     * shipments for inpost
     */

    public function createShipments() {
        try {
            $settings = $this->_settings;
            // get dispatch point name
            $client = $this->getClient();
            $client->setShipmentSettings($settings);
            $dispatchPointName = $this->_getDispatchPointName();
            $settings['dispatchPointName'] = $dispatchPointName;
            $receiverAddress = $this->_receiverAddress;
            $settings['phoneNumber'] = $receiverAddress['telephone'];
            $inpostResult = $this->getClient()->createDeliveryPacks($settings);
            if (empty($inpostResult['pack']['packcode'])) {
                if (!empty($inpostResult['error'])) {
                    $error = $inpostResult['error'];
                } elseif (!empty($inpostResult['pack']['error'])) {
                    $error = $inpostResult['pack']['error'];                
                } else {
                    $error = Mage::helper('orbashipping')->__('Cant create package');
                }
                Mage::throwException($error);
            }
            $code = $inpostResult['pack']['packcode'];
            $message = 'OK';
        } catch (Exception $xt) {
            Mage::logException($xt);
            $message = $xt->getMessage();
            $code = 0;
        }
        $result = array(
                      'shipmentId' => $code,
                      'message' => $message
                  );
        return $result;
    }
    public function createShipmentAtOnce() {
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

    }
    
    /**
     * cancel track by inpost api
     */

    public function cancelTrack($track) {
        try {
            if ($number = $track->getTrackNumber()) {
                $shipment = $track->getShipment();
                $po = Mage::getModel("zolagopo/po")->load($shipment->getUdpoId());
                $vendor = $po->getVendor();
                $pos = $po->getPos();
                $settings = Mage::helper('ghinpost')->getApiSettings($vendor,$pos);
                $this->setShipmentSettings($settings);
                $client = $this->getClient();
                $out = $client->cancelPack($number);
                if (!$out === '1') {
                    if (!empty($out['error'])) {
                        $message = $out['error'];
                    } else {
                        $message = Mage::helper('orbashipping')->__('Cant cancel package %s',$number);
                    }
                    Mage::throwException($message);
                }
            }
        } catch (Exception $xt) {
            Mage::logException($xt); // if something wrong - only log exception (not break process)
        }
    }
    public function getShippingModal() {
        return Mage::app()->getLayout()->createBlock('zolagopo/vendor_po_edit_shipping_inpost');
    }
    public function isLetterable() {
        return true;
    }
    public function getLetterUrl() {
        return 'orbashipping/inpost/lp';
    }
}