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
        $value = $udpo->getSubtotalInclTax();
        $insurance = $params->getParam('insurance');
        $order = $shipment->getOrder();
        if ($order->getPayment()->getMethod() == 'cashondelivery') {
            $codValue = $udpo->getGrandTotalInclTax()-$udpo->getPaymentAmount();
        }

        $weight = $params->getParam('weight');
        $vendor = Mage::helper('udropship')->getVendor($udpo->getUdropshipVendor());
        $pos = $udpo->getDefaultPos();
        $shipmentSettings = array(
                                'size' => $size,
                                'category' => $category,
                                'weight' => $weight,
                                'pos' => $pos,
                                'udpo' => $udpo,
                                'value' => $value,
                                'cod' => $codValue,
                                'insurance' => $insurance,
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
    protected function _startClient() {
        $settings = $this->_settings;
        $client = Mage::helper('orbashipping/post')->startClient($settings);
        if (!$client) {
            throw new Mage_Core_Exception(Mage::helper('orbashipping')->__('Cant connect to %s server','poczta-polska'));
        }
        return $client;
    }

    /**
     * try clear envelope if needed
     */
    protected function _clearEnvelope() {
        $settings = $this->_settings;
        $lastDate = Mage::getStoreConfig('carriers/zolagopp/last_date',0);
        if ($lastDate != date('Y-m-d')) {
            if ($this->getClient()->clearEnvelope($settings)) {
                Mage::getConfig()->saveConfig('carriers/zolagopp/last_date',date('Y-m-d'),'default',0);
                Mage::getConfig()->reinit();
                Mage::app()->reinitStores();
                // @todo should create new dispatch
                
            }
        }
    }
    
    /**
     * created dispatch if not exists and assign po 
     */
    protected function _assignAggregatedDispatch() {
        $helper = Mage::helper('zolagopo');
        $po = $this->_settings['udpo'];
        $vendor = $po->getUdropshipVendor();
        $item = $helper->getZolagoPPAggregated($vendor)->getFirstItem();
        if (!$item->getId()) {
            $poId = $po->getId();
            $helper->createAggregated(array($poId),$vendor);
        } else {
            $po->setAggregatedId($item->getId());
            $po->getResource()->saveAttribute($po, "aggregated_id");
        }
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
            $client->setReceiverAddress($this->_receiverAddress);
            $client->setShipmentSettings($settings);
            $this->_clearEnvelope();
            $this->_assignAggregatedDispatch();
            $retval = $client->createDeliveryPacks($settings);
            $code = empty($retval->guid)? 0:$retval->guid;
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

    /**
     * remove pack from buffer
     */
    public function cancelTrack($track) {
        try {
            if ($number = $track->getTrackNumber()) {
                $client = $this->getClient();
                $client->cancelPack($number);
            }
        } catch (Exception $xt) {
            Mage::logException($xt); // nie przerywamy procesu
        }
    }
        
    /**
     * action after shipped po
     */
    public function setShipped() {
            $postOfficeId = Mage::app()->getRequest()->getParam('post_office');
            $client = $this->getClient();
            $client->setParam('postOffice',$postOfficeId);            
            $result = $client->sendEnvelope();
            // @todo process statuses 
            // @see Orba_Shipping_Model_Post_Client_Wsdl
            // @see envelopeStatusType
    }
    public function getShippingModal() {
        return Mage::app()->getLayout()->createBlock('zolagopo/vendor_po_edit_shipping_zolagopp');
    }
    public function isLetterable() {
        return true;
    }
    public function getLetterUrl() {
        return 'orbashipping/post/lp';
    }
}