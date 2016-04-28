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
        $vendor = Mage::helper('udropship')->getVendor($udpo->getUdropshipVendor());
        $pos = $udpo->getDefaultPos();
        $shipmentSettings = array(
                                'size' => $size,
                                'lockerName' => $udpo->getInpostLockerName(),
                                'pos' => $pos,
                                'udpo' => $udpo,
                                
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
    public function setClient($client) {
        $this->_client = $client;
    }
    public function getClient() {
        if (!$this->_client) {
            $this->setClient($this->_startClient());
        }
        return $this->_client;
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
        $name = empty($settings['pos'])? '':$settings['pos']->getName();
        $point = $client->getDispatchPoint($name);
        if (!empty($point['error'])) {
            Mage::throwException($point['error']);
        }
        if (empty($point['count'])) {
            $pos = $settings['pos'];
            $ret = $client->createDispatchPoint($pos);
            if (empty($ret['status']) ||
                $ret['status'] != 'OK') {
                Mage::throwException(Mage::helper('ghinpost')->__('Cannot create dispatch point'));
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
            $inpostResult = $this->getClient()->createDeliveryPacks($settings);
            $message = print_r($inpostResult,1);
        } catch (Exception $xt) {
            $message = $xt->getMessage();
        }
        $result = array(
                      'shipmentId' => 0,
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
}