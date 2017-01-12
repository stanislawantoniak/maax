<?php
/**
 * client dhl
 */
class Orba_Shipping_Model_Carrier_Client_Dhl extends Orba_Shipping_Model_Client_Soap {

    const ADDRESS_HOUSE_NUMBER		= '.';
    const SHIPMENT_TYPE_PACKAGE		= 'PACKAGE';
    const SHIPMENT_TYPE_ENVELOPE	= 'ENVELOPE';
    const SHIPMENT_QTY				= 1;
    const SHIPMENT_DOMESTIC			= 'AH';

    const PAYMENT_TYPE				= 'BANK_TRANSFER';
    const PAYMENT_TYPE_CASH			= 'CASH';
    const PAYER_TYPE_SHIPPER		= 'SHIPPER';
    const PAYER_TYPE_RECEIVER		= 'RECEIVER';
    const PAYER_TYPE_USER			= 'USER';
    const SHIPMENT_RMA_CONTENT      = 'Reklamacyjny zwrot do nadawcy';

    const DHL_LABEL_TYPE			= 'LP';
    protected $_default_params = array (
        'dropOffType' => 'REQUEST_COURIER',
        'serviceType' => 'AH',
        'shippingPaymentType' => self::PAYER_TYPE_SHIPPER,
        'paymentType'   => self::PAYMENT_TYPE,
        'labelType' => self::DHL_LABEL_TYPE,
        
        
    );        
    

    /**
     *
     */
    protected function _construct() {
        $this->_init('orbashipping/carrier_dhl_client');
    }

    public function getDhlAccount() {
        if (!empty($this->_auth) && 
            !empty($this->_auth->account)) {
            $account = $this->_auth->account;
        } else {
            $account = Mage::helper('orbashipping/carrier_dhl')->getDhlAccount();        
        }
        return $account;
    }
    
    
    protected function _getWsdlUrl() {
        return Mage::getStoreConfig('carriers/orbadhl/gateway');
    }
    protected function _getSoapMode() {
        return array (
            'trace' => 1,
        );
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
    /**
     * shipments count
     */
    public function getMyShipmentsCount($from,$to) {
        $message = new StdClass();
        $message->authData = $this->_auth;
        $message->createdFrom = $from;
        $message->createdTo = $to;
        $return = $this->_sendMessage('getMyShipmentsCount',$message);
        return $return;
    }

    protected function _prepareShipmentOrderInfo() {
        $shipper = new StdClass();
        // todo
    }
    protected function _createShipper() {
        $data = $this->_shipperAddress;
        $obj = new StdClass();
        $obj->name = $data['name'];
        $obj->postalCode = $this->formatDhlPostCode($data['postcode']);
        $obj->city = $data['city'];
        $obj->street = $data['street'];
        $obj->houseNumber = self::ADDRESS_HOUSE_NUMBER;
        $obj->contactPhone = $data['phone'];
        return $obj;
    }    
    protected function _createShipperAtOnce() {
        return $this->_createAddressAtOnce($this->_shipperAddress);
    }
    protected function _createReceiverAtOnce() {
        return $this->_createAddressAtOnce($this->_receiverAddress);
    }
    protected function _createAddressAtOnce($data) {
        $message = new StdClass;
        $address = new StdClass;
        $address->name = $data['name'];
        $address->city = substr($data['city'],0,17);
        $address->postalCode = $this->formatDhlPostCode($data['postcode']);
        $address->street = $data['street'];
        $address->houseNumber = self::ADDRESS_HOUSE_NUMBER;
        $address->country = $data['country'];
        $contact = new StdClass;
        $contact->personName = $data['personName'];
        $contact->phoneNumber = $data['phone'];
        $contact->emailAddress = $data['email'];
        $message->address = $address;
        $message->contact = $contact;        
//        $message->preaviso = $contact;
        return $message;

    }
    protected function _createReceiver() {
        $data = $this->_receiverAddress;
        $obj = new StdClass();
        $obj->country = $data['country'];
        $obj->name = $data['name'];
        $obj->postalCode = $this->formatDhlPostCode($data['postcode']);
        $obj->city = $data['city'];
        $obj->street = $data['street'];
        $obj->houseNumber = self::ADDRESS_HOUSE_NUMBER;
        $obj->contactPerson = $data['contact_person'];
        $obj->contactPhone = $data['contact_phone'];
        $obj->contactEmail = $data['contact_email'];
        $this->_address = null;
        return $obj;
    }
    
    protected function _createPieceList() {
        $shipmentSettings = $this->_settings;
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
        $obj->nonStandard = (empty($shipmentSettings['nonStandard']))? false:$shipmentSettings['nonStandard'];
        $ret = new StdClass();
        $ret->item[] = $obj;
        return $ret;
    }
    protected function _createPayment() {
        $obj = new StdClass();
        $obj->paymentMethod = self::PAYMENT_TYPE;
        $obj->payerType		= self::PAYER_TYPE_SHIPPER;
        $obj->accountNumber = $this->getDhlAccount();
        $obj->costsCenter = null;
        return $obj;
    }
    protected function _createService() {
        $obj = new StdClass();
        $obj->product = self::SHIPMENT_DOMESTIC;
        $collectOnDeliveryValue = $this->_settings['deliveryValue'];
        if ($collectOnDeliveryValue > 0) {
            $obj->collectOnDelivery			= true;
            $obj->collectOnDeliveryValue	= $collectOnDeliveryValue;
            $obj->collectOnDeliveryForm		= self::PAYMENT_TYPE;
            $obj->insurance					= true;
            $obj->insuranceValue			= $collectOnDeliveryValue;
        }
//        $obj->predeliveryInformation = true;
        $obj->preaviso = true;
        return $obj;
    }
    /**
     * Create Shipments
     */
    public function createShipments() {
        $shipmentSettings = $this->_settings;
        $message = new StdClass();
        $message->authData = $this->_auth;
        $shipmentObject = new StdClass();
        $obj = new StdClass();
        $obj->shipper = $this->_createShipper();
        $obj->receiver = $this->_createReceiver();
        $obj->pieceList = $this->_createPieceList();
        $obj->payment = $this->_createPayment();
        $obj->service = $this->_createService();
        $obj->skipRestrictionCheck = false;
        $obj->shipmentDate = $this->_processDhlDate($shipmentSettings['shipmentDate']);
        $obj->content = $shipmentSettings['content'];
        if (!empty($shipmentSettings['comment'])) {
            $obj->comment = $shipmentSettings['comment'];
        }
        $shipmentObject->item[] = $obj;

        $message->shipments = $shipmentObject;
        $messageResult = $this->_sendMessage('createShipments', $message);
        return $messageResult;
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
        $wsdl = Mage::getStoreConfig('carriers/orbadhl/tracking_gateway');
        $soapClient = new SoapClient($wsdl);
        $shipments = array($shipmentId); // tu wpisujemy listę szukanych nr przesyłek
        $remoteCallParams = array("shipmentNumbers" => $shipments);
        $remoteCallResult = $soapClient->GetShipments($remoteCallParams);
        return $remoteCallResult;
    }

    /**
     * tracking info (old version)
     */
    public function getTrackAndTraceInfo2($shipmentId) {
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
            if ($track->getCarrierCode() == Orba_Shipping_Model_Carrier_Dhl::CODE) {
                $obj = new StdClass();
                $obj->labelType = $this->getParam('labelType');
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
        $helper = Mage::helper('zolagopo');
        if (is_array($dhlResult) && array_key_exists('error', $dhlResult)) {
            //Dhl Error Scenario
            Mage::helper('orbashipping/carrier_dhl')->_log('DHL Service Error: ' .$dhlResult['error']);
            $result['shipmentId']	= false;
            $result['message']		= $helper->__('DHL Service Error: %s',$dhlResult['error']);
        }
        elseif (property_exists($dhlResult, 'createShipmentsResult') && property_exists($dhlResult->createShipmentsResult, 'item')) {
            $item = $dhlResult->createShipmentsResult->item;
            $result['shipmentId']	= $item->shipmentId;
            $result['message']		= $helper->__('Tracking ID: %s ', $item->shipmentId);
        }
        else {
            Mage::helper('orbashipping/carrier_dhl')->_log('DHL Service Error: ' .$method);
            $result['shipmentId']	= false;
            $result['message']		= $helper->__('DHL Service Error: %s', $method);
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
    public function processLabelsResult($method, $dhlResult)
    {
        $result = array(
                      'status'	=> false
                  );

        if (is_array($dhlResult) && array_key_exists('error', $dhlResult)) {
            //Dhl Error Scenario
            Mage::helper('orbashipping/carrier_dhl')->_log('DHL Service Error: ' .$dhlResult['error']);
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
            Mage::helper('orbashipping/carrier_dhl')->_log('DHL Service Error: ' .$method);
            $result['status']		= false;
            $result['message']		= 'DHL Service Error: ' .$method;
        }
        return $result;
    }

    protected function _getRmaAccountNumber() {        
        if (!$account = $this->_vendor->getDhlRmaAccount()) {
            if (!$account = $this->_vendor->getDhlAccount()) {
                $account = Mage::helper('orbashipping/carrier_dhl')->getDhlAccount();
            }
        }
        return $account;
    }
    protected function _prepareBiling() {
        $message = new StdClass;
        $message->shippingPaymentType = $this->getParam('shippingPaymentType');
        $message->billingAccountNumber = $this->_auth->account;
        $message->paymentType = $this->getParam('paymentType');        
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
        $message->item[] = $this->_addSpecialItem('UBEZP',$this->_settings['deliveryValue']);
        return $message;
    }
    protected function _prepareShipmentInfoAtOnce() {
        $shipmentSettings = $this->_shipmentSettings;
        $message = new StdClass;
        $message->dropOffType = $this->getParam('dropOffType');
        $message->serviceType = $this->getParam('serviceType');        
        $message->labelType   = $this->getParam('labelType');
        $message->billing = $this->_prepareBiling();
        $message->specialServices = $this->_prepareSpecialServices();
        $message->shipmentTime = $this->_prepareShipmentTime();
        $message->labelType = $this->getParam('labelType');
        return $message;       
    }
    protected function _prepareShipmentAtOnce() {
        $message = new StdClass;
        $message->shipper = $this->_createShipperAtOnce();
        $message->receiver = $this->_createReceiverAtOnce();
        return $message;
    }
    protected function _prepareShipmentTime() {
        $time = Mage::getModel('core/date')->timestamp(time());
        $message = new StdClass;        
        $message->shipmentDate = empty($this->_settings['shipmentDate'])? date('Y-m-d',$time+3600*24):date('Y-m-d',strtotime($this->_settings['shipmentDate']));
        $message->shipmentStartHour = empty($this->_settings['shipmentStartHour'])? '9:00':$this->_settings['shipmentStartHour'];
        $message->shipmentEndHour = empty($this->_settings['shipmentEndHour'])? '15:00':$this->_settings['shipmentEndHour'];
        return $message;
    }
    /**
     * creating shipment and book courier in one request
     */
    public function createShipmentAtOnce() {
        $message = new StdClass;
        $message->authData = $this->_auth;
        $shipment = new stdClass;
        $shipment->shipmentInfo = $this->_prepareShipmentInfoAtOnce(); 
        $shipment->ship = $this->_prepareShipmentAtOnce();
        $shipment->content = empty($this->_settings['content'])? self::SHIPMENT_RMA_CONTENT:$this->_settings['content'];
        if (!empty($this->_settings['comment'])) {
            $shipment->comment = $this->_settings['comment'];
        }
        $shipment->pieceList = $this->_createPieceList();
        $message->shipment = $shipment;
        $ret =  $this->_sendMessage('createShipment',$message);
        return $ret;
    }
    
    /**
     * change date into dhl accepted format
     * @param string $date
     */

    protected function _processDhlDate($date) {
        $_date = explode("-", $date);
        if(count($_date)==3) {
            if(count($_date[0])==4) {
                return $date;
            }
            return $_date[2] . "-" . $_date[1] . "-" . $_date[0];
        }
    }

    protected function _getHelper() {
        return Mage::helper('orbashipping/carrier_dhl');
    }
}
