<?php
/**
 * dhl
 */
class Zolago_Dhl_Model_Client extends Mage_Core_Model_Abstract {
    protected $_auth;
    protected $_pos;
    protected $_operator;
    protected $_address;

    const ADDRESS_HOUSE_NUMBER		= '.';
    const SHIPMENT_TYPE_PACKAGE		= 'PACKAGE';
    const SHIPMENT_TYPE_ENVELOPE	= 'ENVELOPE';
    const SHIPMENT_QTY				= 1;
    const SHIPMENT_DOMESTIC			= 'AH';

    const PAYMENT_TYPE				= 'BANK_TRANSFER';
    const PAYER_TYPE				= 'SHIPPER';

    const DHL_LABEL_TYPE			= 'LP';

    /**
     * @param Zolago_Pos_Model_Pos $pos
     */
    public function setPos($pos) {
        if (!empty($pos)) {
            $this->_pos = $pos;
        }
    }

    /**
     * @param Zolago_Operator_Model_Operator $operator
     */
    public function setOperator($operator) {
        if (!empty($operator)) {
            $this->_operator = $operator;
        }
    }

    public function __construct($pos = null,$operator = null )  {
        $this->setPos($pos);
        $this->setOperator($operator);
    }

    /**
     * @param Zolago_Pos_Model_Pos $pos
     * @param Zolago_Operator_Model_Operator $operator
     */
    protected function _construct() {
        $this->_init('zolagodhl/client');
    }


    public function setAuth($user,$password) {
        $auth = new StdClass();
        $auth->username = $user;
        $auth->password = $password;
        $this->_auth = $auth;
    }


    /**
     * message via soap
     */
    protected function _sendMessage($method,$message = null) {
        try {
            $wsdl = Mage::getStoreConfig('carriers/zolagodhl/gateway');
            $soap = new SoapClient($wsdl,array());
            $result = $soap->$method($message);
        } catch (Exception $xt) {
            $result = array (
                          'error' => $xt->getMessage()
                      );
        }
        return $result;
    }
    /**
     * shipments list
     */
    public function getMyShipments($from,$to,$offset = 0) {
        $message = new StdClass();
        $message->authData = $this->_auth;
        $message->createdFrom = $from;
        $message->createdTo = $to;
        $message->offset = $offset;
        $return = $this->_sendMessage('getMyShipments',$message);
        return $return;
    }

    protected function _prepareShipmentOrderInfo() {
        $shipper = new StdClass();
        // todo
    }
    protected function _createShipper() {
        $data = $this->_pos->getData();
        $obj = new StdClass();
        $obj->name = $data['name'];
        $obj->postalCode = $this->formatDhlPostCode($data['postcode']);
        $obj->city = $data['city'];
        $obj->street = $data['street'];
        $obj->houseNumber = self::ADDRESS_HOUSE_NUMBER;
        $obj->contactPhone = $data['phone'];
        return $obj;
    }
    public function setAddressData($data) {
        $this->_address = $data;
    }
    protected function _getAddressData($shipment) {
        if (!$this->_address) {
            $orderId = $shipment->getOrderId();
            $model = Mage::getModel('udpo/po');
            $collection = $model->getCollection();
            $collection->addFieldToFilter('order_id',$orderId);
            $po = $collection->getFirstItem();
            $shippingId = $po->getShippingAddressId();
            $model = Mage::getModel('sales/order_address');
            $address = $model->load($shippingId);
            $data = $address->getData();
            $this->setAddressData($data);
        }
        return $this->_address;
    }
    protected function _createReceiver($shipment) {
        $data = $this->_getAddressData($shipment);
        $obj = new StdClass();
        $obj->name = $data['firstname'].' '.$data['lastname'].($data['company'] ? ' '.$data['company'] : '');
        $obj->postalCode = $this->formatDhlPostCode($data['postcode']);
        $obj->city = $data['city'];
        $obj->street = $data['street'];
        $obj->houseNumber = self::ADDRESS_HOUSE_NUMBER;
        $obj->contactPerson = $data['firstname'].' '.$data['lastname'];
        $obj->contactPhone = $data['telephone'];
        $obj->contactEmail = $data['email'];
        return $obj;
    }
    protected function _createPieceList($shipmentSettings) {
        $obj = new StdClass();
        $obj->type				= $shipmentSettings['type'];
        switch ($shipmentSettings['type']) {
        case self::SHIPMENT_TYPE_PACKAGE:
            $obj->width		= $shipmentSettings['width'];
            $obj->height	= $shipmentSettings['height'];
            $obj->length	= $shipmentSettings['length'];
            $obj->weight	= $shipmentSettings['weight'];
            $obj->quantity	= $shipmentSettings['quantity'];
            break;
        default:
            $obj->quantity	= $shipmentSettings['quantity'];
            break;
        }
        $obj->nonStandard = $shipmentSettings['nonStandard'];
        $ret = new StdClass();
        $ret->item[] = $obj;
        return $ret;
    }
    protected function _createPayment() {
        $obj = new StdClass();
        $obj->paymentMethod = self::PAYMENT_TYPE;
        $obj->payerType		= self::PAYER_TYPE;
        $obj->accountNumber = Mage::helper('zolagodhl')->getDhlAccount();
        $obj->costsCenter = null;
        return $obj;
    }
    protected function _createService($shipment) {
        $order = $shipment->getOrder();
        $collectOnDeliveryValue = $this->_getCollectOnDeliveryValue($shipment);
        $obj = new StdClass();
        $obj->product = self::SHIPMENT_DOMESTIC;
        if ($order->getPayment()->getMethod() == 'cashondelivery') {
            $obj->collectOnDelivery			= true;
            $obj->collectOnDeliveryValue	= $collectOnDeliveryValue;
            $obj->collectOnDeliveryForm		= self::PAYMENT_TYPE;
            $obj->insurance					= true;
            $obj->insuranceValue			= $collectOnDeliveryValue;
        }
        return $obj;
    }
    /**
     * Create Shipments
     *
     * @param array Mage_Sales_Model_Order_Shipment
     */
    public function createShipments($shipment, $shipmentSettings) {
        if (empty($shipment)) {
            return false;
        }

        $message = new StdClass();
        $message->authData = $this->_auth;
        $shipmentObject = new StdClass();
        $obj = new StdClass();
        $obj->shipper = $this->_createShipper();
        $obj->receiver = $this->_createReceiver($shipment);
        $obj->pieceList = $this->_createPieceList($shipmentSettings);
        $obj->payment = $this->_createPayment();
        $obj->service = $this->_createService($shipment);
        $obj->shipmentDate = $shipmentSettings['shipmentDate'];
        $obj->content = $shipment->getUdpoIncrementId();
        $shipmentObject->item[] = $obj;

        $message->shipments = $shipmentObject;

        return $this->_sendMessage('createShipments', $message);
    }
    /**
     * booking courier
     */
    public function bookCourier($date,$timeFrom,$timeTo,$additionalInfo = null) {
        $message = new StdClass();
        $message->authData = $this->_auth;
        $message->pickupDate = $date;
        $message->pickupTimeFrom($timeFrom);
        $message->pickupTimeTo($timeTo);
        $message->additionalInfo = $additionalInfo;
        $message->shipmentOrderInfo = $this->_prepareShipmentOrderInfo();
        // not finished
    }

    /**
     * tracking info
     */
    public function getTrackAndTraceInfo($shipmentId) {
        $message = new StdClass();
        $message->authData = $this->_auth;
        $message->shipmentId = $shipmentId;
        $return = $this->_sendMessage('getTrackAndTraceInfo',$message);
        return $return;
    }

    /**
     * labels to print
     */
    public function getLabels($tracking) {
        if (empty($tracking)) {
            return false;
        }
        if (!is_array($tracking)) {
            $tracking = array($tracking);
        }
        if (count($tracking) > 3) {
            Mage::throwException('Too many shipments in one query');
        }
        $message = new StdClass();
        $message->authData = $this->_auth;
        $print = new StdClass();
        foreach ($tracking as $track) {
            if ($track->getCarrierCode() == Zolago_Dhl_Helper_Data::DHL_CARRIER_CODE) {
                $obj = new StdClass();
                $obj->labelType = self::DHL_LABEL_TYPE;
                $obj->shipmentId = $track->getNumber();
                $print->item[] = $obj;
            }
        }
        $message->itemsToPrint = $print;
        return $this->_sendMessage('getLabels', $message);
    }

    /**
     * Prepare Post Code - DHL Format
     *
     * @param string $postCode Input Post Code
     *
     * @return string Formated Post Code
     */
    public function formatDhlPostCode($postCode)
    {
        return preg_replace('/[^0-9,]|,[0-9]*$/','',$postCode);
    }

    /**
     * Process DHL Web API Shipments Result
     *
     * @param string $method
     * @param object $dhlResult
     *
     * @return array $result Default: array('shipmentId' => false, 'message' => '');
     */
    public function processDhlShipmentsResult($method, $dhlResult)
    {
        $result = array(
                      'shipmentId'	=> false,
                      'message'		=> ''
                  );

        if (is_array($dhlResult) && array_key_exists('error', $dhlResult)) {
            //Dhl Error Scenario
            Mage::helper('zolagodhl')->_log('DHL Service Error: ' .$dhlResult['error']);
            $result['shipmentId']	= false;
            $result['message']		= 'DHL Service Error: ' .$dhlResult['error'];
        }
        elseif (property_exists($dhlResult, 'createShipmentsResult') && property_exists($dhlResult->createShipmentsResult, 'item')) {
            $item = $dhlResult->createShipmentsResult->item;
            $result['shipmentId']	= $item->shipmentId;
            $result['message']		= 'Tracking ID: ' . $item->shipmentId;
        }
        else {
            Mage::helper('zolagodhl')->_log('DHL Service Error: ' .$method);
            $result['shipmentId']	= false;
            $result['message']		= 'DHL Service Error: ' .$method;
        }

        return array_unique($result);
    }

    /**
     * Process DHL Web API Labels Result
     *
     * @param object $dhlResult
     *
     * @return array $result Default: array('status' => false);
     */
    public function processDhlLabelsResult($method, $dhlResult)
    {
        $result = array(
                      'status'	=> false
                  );

        if (is_array($dhlResult) && array_key_exists('error', $dhlResult)) {
            //Dhl Error Scenario
            Mage::helper('zolagodhl')->_log('DHL Service Error: ' .$dhlResult['error']);
            $result['status']	= false;
            $result['message']		= 'DHL Service Error: ' .$dhlResult['error'];
        }
        elseif (property_exists($dhlResult, 'getLabelsResult') && property_exists($dhlResult->getLabelsResult, 'item')) {
            $item = $dhlResult->getLabelsResult->item;
            $result['status']		= $item->shipmentId;
            $result['message']		= 'Shipment ID: ' . $item->shipmentId;
            $result['labelName']	= $item->labelName;
            $result['labelData']	= base64_decode($item->labelData);
        }
        else {
            Mage::helper('zolagodhl')->_log('DHL Service Error: ' .$method);
            $result['status']		= false;
            $result['message']		= 'DHL Service Error: ' .$method;
        }

        return array_unique($result);
    }

    /**
     * Get COD Value for DHL Service per Shipment
     *
     * @param type $shipment
     *
     * @return float COD Value
     */
    protected function _getCollectOnDeliveryValue($shipment)
    {
        return $shipment->getTotalValue() + $shipment->getBaseTaxAmount() + $shipment->getShippingAmountIncl();
    }
}