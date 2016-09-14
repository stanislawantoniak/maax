<?php

class Orba_Shipping_Model_Packstation_Client_Pwr extends Orba_Shipping_Model_Client_Soap {

    protected function _construct() {
        $this->_init('orbashipping/packstation_client_pwr');
    }

    /**
     * WSDL url
     *
     * @return string
     */
    protected function _getWsdlUrl() {
        return $this->getHelper()->getApiWsdl();
    }

    /**
     * @return array
     */
    protected function _getSoapMode() {
        $mode = array (
                    'soap_version' => SOAP_1_1,  // use soap 1.1 client
                    'trace' => 1,
                );
        // for tests disable
        $testFlag = (bool)Mage::getConfig()->getNode('global/test_server');
        if ($testFlag) {
            // for tests servers skip ssl security
            $mode['stream_context'] = stream_context_create(
                                          array(
                                              'ssl' => array(
                                                  // set some SSL/TLS specific options
                                                  'verify_peer' => false,
                                                  'verify_peer_name' => false,
                                                  'allow_self_signed' => true
                                              )
                                          )
                                      );
        }
        return $mode;
    }

    /**
     * @return ZolagoOs_Pwr_Helper_Data
     */
    public function getHelper() {
        /** @var ZolagoOs_Pwr_Helper_Data $helper */
        $helper = Mage::helper("zospwr");
        return $helper;
    }

    /**
     * There's location too
     * @return mixed|array
     */
    public function giveMeAllRUCH() {
        $message = new StdClass();
        $message->PartnerID = $this->getHelper()->getPartnerId();
        $message->PartnerKey = $this->getHelper()->getPartnerKey();
        $data = $this->_sendMessage("GiveMeAllRUCHZipcode", $message);
        $result = $this->_prepareResult($data);
        return $result['NewDataSet']['AllRUCHZipcode'];
    }
    /**
     * Prepare answer
     *
     * @param $data
     * @return mixed|array
     */
    protected function _prepareResult($data) {
        $xml = simplexml_load_string($data->GiveMeAllRUCHZipcodeResult->any, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $result = json_decode($json,true);
        return $result;
    }
}

