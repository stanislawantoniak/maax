<?php
/**
 * Poczta Polska
 */
class Orba_Shipping_Model_Post extends Orba_Shipping_Model_Carrier_Abstract {

    const CODE = "zolagopp";
    protected $_code = self::CODE;
    protected $_client;

    public function prepareSettings($params,$shipment,$udpo) {
        $size = $params->getParam('specify_post_size');
        $category = $params->getParam('specify_post_category');
        $weight = $params->getParam('weight');
        $vendor = Mage::helper('udropship')->getVendor($udpo->getUdropshipVendor());
        $pos = $udpo->getDefaultPos();
        $shipmentSettings = array(
                                'size' => $size,
                                'category' => $category,
                                'weight' => $weight,
                                'pos' => $pos,
                                'udpo' => $udpo,
                                
                            );

        $settings = array();
        foreach ($shipmentSettings as $key=>$val) {
            $settings[$key] = $val;
        }
        $this->setShipmentSettings($settings);
    }
    public function isActive() {
        return Mage::helper('orbashipping/post')->isActive();
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
        $client = Mage::helper('orbashipping/post')->startClient($settings);
        if (!$client) {
            throw new Mage_Core_Exception(Mage::helper('orbashipping')->__('Cant connect to %s server','poczta-polska'));
        }
        return $client;
    }

    /**
     * shipments for pp
     */

    public function createShipments() {
        try {
            $settings = $this->_settings;
            // get dispatch point name
            $client = $this->getClient();
            $client->setShipperAddress($this->_senderAddress);
            $client->setShipmentSettings($settings);
            $postResult = $this->getClient()->createDeliveryPacks($settings);
            Mage::log($postResult);
/*            if (empty($inpostResult['pack']['packcode'])) {
                if (!empty($inpostResult['error'])) {
                    $error = $inpostResult['error'];
                } elseif (!empty($inpostResult['pack']['error'])) {
                    $error = $inpostResult['pack']['error'];                
                } else {
                    $error = Mage::helper('orbashipping')->__('Cant create package');
                }
                Mage::throwException($error);
            }
*/            
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
}