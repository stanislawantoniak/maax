<?php
/**
 * dhl
 */
class Zolago_Dhl_Model_Client extends Mage_Core_Model_Abstract {
    protected $_auth;
    protected $_pos;
    protected $_rma;
    protected $_operator;
    protected $_address;
    protected $_settings;

    const ADDRESS_HOUSE_NUMBER		= '.';
    const SHIPMENT_TYPE_PACKAGE		= 'PACKAGE';
    const SHIPMENT_TYPE_ENVELOPE	= 'ENVELOPE';
    const SHIPMENT_QTY				= 1;
    const SHIPMENT_DOMESTIC			= 'AH';

    const PAYMENT_TYPE				= 'BANK_TRANSFER';
    const PAYER_TYPE				= 'SHIPPER';
    const SHIPMENT_RMA_CONTENT      = 'Reklamacyjny zwrot do nadawcy';

    const DHL_LABEL_TYPE			= 'LP';
    protected $_default_params = array (
        'dropOffType' => 'REQUEST_COURIER',
        'serviceType' => 'AH',
        'labelType' => self::DHL_LABEL_TYPE,
        'shippingPaymentType' => self::PAYER_TYPE,
        'paymentType'   => self::PAYMENT_TYPE,
        'labelType' => self::DHL_LABEL_TYPE,
        
        
    );        
    
    /**
     *  @param Zolago_Rma_Model_Rma
     */
    public function setRma($rma) {
        if (!empty($rma)) {
            $this->_rma = $rma;
        }
        
    }
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

    public function getDhlAccount() {
        if (!empty($this->_auth) && 
            !empty($this->_auth->account)) {
            $account = $this->_auth->account;
        } else {
            $account = Mage::helper('zolagodhl')->getDhlAccount();        
        }
        return $account;
    }
    
    public function setAuth($user,$password,$account = null) {
        $auth = new StdClass();
        $auth->username = $user;
        $auth->password = $password;
        $auth->account = $account;
        $this->_auth = $auth;
    }


    /**
     * message via soap
     */
    protected function _sendMessage($method, $message = null)
    {
        try {
            $wsdl = Mage::getStoreConfig('carriers/zolagodhl/gateway');
            $soap = new SoapClient($wsdl, array());
            $result = $soap->$method($message);
        } catch (Exception $xt) {
            $result = array(
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
			$shippingId = $shipment->getShippingAddressId();
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
		$this->_address = null;
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
        $obj->nonStandard = (empty($shipmentSettings['nonStandard']))? null:$shipmentSettings['nonStandard'];
        $ret = new StdClass();
        $ret->item[] = $obj;
        return $ret;
    }
    protected function _createPayment() {
        $obj = new StdClass();
        $obj->paymentMethod = self::PAYMENT_TYPE;
        $obj->payerType		= self::PAYER_TYPE;
        $obj->accountNumber = $this->getDhlAccount();
        $obj->costsCenter = null;
        return $obj;
    }
    protected function _createService($shipment, $shippingAmount) {
        $order = $shipment->getOrder();
        $collectOnDeliveryValue = $this->_getCollectOnDeliveryValue($shipment, $shippingAmount);
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
        $obj->service = $this->_createService($shipment, $shipmentSettings['shippingAmount']);
        $obj->shipmentDate = $shipmentSettings['shipmentDate'];
        $obj->content = Mage::helper('zolagopo')->__('Shipment') . ': ' . $shipment->getIncrementId();
        $shipmentObject->item[] = $obj;

        $message->shipments = $shipmentObject;

        return $this->_sendMessage('createShipments', $message);
    }
    /**
     * @param $postCode
     * @param $pickupDate
     *
     * @return array
     */
    public function getPostalCodeServices($postCode, $pickupDate, $country = 'PL')
    {
        $return = array();
        $message = new StdClass();
        $message->authData = $this->_auth;
        $message->postCode = $this->formatDhlPostCode($postCode);
        $message->pickupDate = $pickupDate;
        try {
            $return = $this->_sendMessage('getPostalCodeServices', $message);
        } catch (Exception $e) {
            Mage::throwException("getPostalCodeServices");
        }
        return $return;
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

        return $result;
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
        return $result;
    }

    /**
     * Get COD Value for DHL Service per Shipment
     *
     * @param type $shipment
     *
     * @return float COD Value
     */
    protected function _getCollectOnDeliveryValue($shipment, $shippingAmount)
    {
        return $shipment->getTotalValue() + $shipment->getBaseTaxAmount() + $shippingAmount;
    }
    protected function _getRmaAccountNumber() {        
        if (!$account = $this->_vendor->getDhlRmaAccount()) {
            if (!$account = $this->_vendor->getDhlAccount()) {
                $account = Mage::helper('zolagodhl')->getDhlAccount();
            }
        }
        return $account;
    }
    protected function _prepareBiling() {
        $message = new StdClass;
        $message->shippingPaymentType = $this->_default_params['shippingPaymentType'];
        $message->billingAccountNumber = $this->_auth->account;
        $message->paymentType = $this->_default_params['paymentType'];        
        return $message;
    }
    protected function _addSpecialItem($type,$value = null) {
        $message = new StdClass();
        $message->serviceType = $type;
        $message->serviceValue = $value;
        return $message;
    }
    protected function _prepareSpecialServices() {
        $message = new StdClass();
        $message->item[] = $this->_addSpecialItem('UBEZP',$this->_rma->getTotalValue());
        return $message;
    }
    protected function _prepareShipmentAtOnce() {
        $message = new StdClass;
        $message->dropOffType = $this->_default_params['dropOffType'];
        $message->serviceType = $this->_default_params['serviceType'];        
        $message->labelType   = $this->_default_params['labelType'];
        $message->billing = $this->_prepareBiling();
        $message->specialServices = $this->_prepareSpecialServices();
        $message->shipmentTime = $this->_prepareShipmentTime();
        $message->labelType = $this->_default_params['labelType'];
        return $message;       
    }
    protected function _prepareClientAddress() {
        $address = $this->_rma->getShippingAddress();
        $data = $address->getData();
        $message = new StdClass();
        $message->name = $data['firstname'].' '.$data['lastname'];
        $message->postalCode = $this->formatDhlPostCode($data['postcode']);
        $message->city = substr($data['city'],0,17);
        $message->street = $data['street'];
        $message->houseNumber = self::ADDRESS_HOUSE_NUMBER;
        $contact = new StdClass;
        $contact->personName = $message->name;
        $contact->phoneNumber = $data['telephone'];
        $order = $this->_rma->getOrder();
        $contact->emailAddress = $order->getCustomerEmail();
        $out = new StdClass;
        $out->contact = $contact;
        $out->address = $message;
        return $out;
    }
    protected function _prepareVendorAddress() {
        $vendorId = $this->_rma->getUdropshipVendor();
        $vendor = Mage::getModel('udropship/vendor')->load($vendorId);
        $data = $vendor->getData();
        $message = new StdClass;
        $address = new StdClass;
        $address->name = $data['company_name'];
        $address->city = substr($data['city'],0,17);
        $address->postalCode = $this->formatDhlPostCode($data['zip']);
        $address->street = $data['street'];
        $address->houseNumber = self::ADDRESS_HOUSE_NUMBER;
        $contact = new StdClass;
        $contact->personName = $address->name;
        $contact->phoneNumber = $data['telephone'];
        $contact->emailAddress = $data['email'];
        $message->address = $address;
        $message->contact = $contact;
        return $message;
    }
    protected function _prepareShipClient() {
        $message = new StdClass;
        $message->shipper = $this->_prepareClientAddress();
        $message->receiver = $this->_prepareVendorAddress();
        return $message;
    }
    protected function _prepareShipVendor() {
        $message = new StdClass;
        $message->shipper = $this->_prepareVendorAddress();
        $message->receiver = $this->_prepareClientAddress();
        return $message;
    }
    protected function _prepareShipmentTime() {
        $message = new StdClass;
        $message->shipmentDate = empty($this->_settings['shipmentDate'])? date('Y-m-d',time()+3600*24):date('Y-m-d',strtotime($this->_settings['shipmentDate']));
        $message->shipmentStartHour = empty($this->_settings['shipmentStartHour'])? '9:00':$this->_settings['shipmentStartHour'];
        $message->shipmentEndHour = empty($this->_settings['shipmentEndHour'])? '15:00':$this->_settings['shipmentEndHour'];
        return $message;
    }
    /**
     * creating shipment and book courier in one request
     */
    public function createShipmentAtOnce($dhlSettings) {
        $this->_settings = $dhlSettings;
        $message = new StdClass;
        $message->authData = $this->_auth;
        $shipment = new stdClass;
        $shipment->shipmentInfo = $this->_prepareShipmentAtOnce(); 
        if (empty($dhlSettings['vendor'])) {
            $shipment->ship = $this->_prepareShipClient();        
        } else {
            $shipment->ship = $this->_prepareShipVendor();
        }
        $shipment->content = self::SHIPMENT_RMA_CONTENT;
        $shipment->pieceList = $this->_createPieceList($dhlSettings);
        $message->shipment = $shipment;
        return $this->_sendMessage('createShipment',$message);
    }
    public function setParam($param,$value) {
        if (!isset($this->_default_params[$param])) {
            Mage::throwException(sprintf('Wrong param name: %s',$param));
        }
        $this->_default_params[$param] = $value;
    }
}
