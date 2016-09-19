<?php
/**
 * client gls
 */
class Orba_Shipping_Model_Carrier_Client_Gls extends Orba_Shipping_Model_Client_Rest {

    protected $_default_params = array (	
        'api' => ''
    );
    /**
     * construct
     */
    protected function _construct() {
        $this->_init('orbashipping/carrier_client_gls');
    }
    


    /**
     * tracking info
     */
    public function getTrackAndTraceInfo($shipmentId) {
        $trackingUrl = Mage::getStoreConfig('carriers/zolagogls/tracking_gateway');
        $this->setParam('api',$trackingUrl);
        $data = array (
            'match' => $shipmentId
        );
        $return = $this->_sendMessage($data,array());
        $out = json_decode($return);
        if ($out) {
            return $out;
        }
        // not json
        return $return;
    }
    
    /**
     * api url
     */

    protected function _getApiUrl() {    
            if (!$url = Mage::getStoreConfig('carriers/zolagogls/api')) {
                Mage::throwException(Mage::helper('orbashipping')->__('Api GLS not configured'));
            }
            return $url;
    }

    protected function _getHelper() {
        return Mage::helper('orbashipping/carrier_gls');
    }
}
