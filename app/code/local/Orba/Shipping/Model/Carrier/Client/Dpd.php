<?php
/**
 * client dpd
 */
class Orba_Shipping_Model_Carrier_Client_Dpd extends Orba_Shipping_Model_Client_Abstract {
    
    /**
     * construct
     */
    protected function _construct() {
        $this->_init('orbashipping/carrier_client_dpd');
    }

    /**
     * tracking info
     */
    public function getTrackAndTraceInfo($shipmentId) {
        $trackingUrl = Mage::getStoreConfig('carriers/zolagodpd/tracking_gateway');
        $trackingUrl = $trackingUrl."?q=".trim($shipmentId)."&typ=1";

        $header[] = "Accept-Charset: utf-8;q=0.7,*;q=0.7";
        $header[] = "Accept-Language: en-us,en;q=0.7";
        $header[] = "Content-Type: text/html; charset=utf-8";

        //parse logic
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $trackingUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:13.0) Gecko/20100101 Firefox/13.0.1'  );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_ENCODING ,"");
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec ($ch);

        if (($error = curl_error($ch)))  {
            Mage::throwException(Mage::helper('orbashipping')->__('Error connecting to DPD Response Page: %s', $error));
        }
        curl_close($ch);

        $dom = new DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($response, 'HTML-ENTITIES', 'UTF-8'));

        $tableData = $dom->getElementsByTagName('tbody');
        if(!$tableData){
            Mage::throwException(Mage::helper('orbashipping')->__('DPD does not have a response for tracking number '.$shipmentId));
        }
        return $tableData;
    }

    /**
     * api url
     */
    protected function _getApiUrl() {
        if (!$url = Mage::getStoreConfig('carriers/zolagodpd/api')) {
            Mage::throwException(Mage::helper('orbashipping')->__('Api DPD not configured'));
        }
        return $url;
    }
}