<?php
/**
 * dhl 
 */
class Zolago_Dhl_Model_Client extends Mage_Core_Model_Abstract {
    protected $_auth;
    protected $_pos;
    protected $_operator;   


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
        $this->_init('zolagodhl/dhl'); 
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
    protected function _createShipper($vendorId) {
        $out = array();
        $data = $this->_pos->getData();
        $obj = new StdClass();
        $obj->name = $data['name'];
        $obj->postalCode = $data['postcode'];
        $obj->city = $data['city'];
        $obj->street = $data['street'];
        $obj->contactPhone = $data['phone'];
        return $out;
    }     
    protected function _createReceiver($orderId) {
        // WARNING!!! HARDCODE!
        // ONLY FOR TESTING
        $model = Mage::getModel('udpo/po');
        $collection = $model->getCollection();
        $collection->addFieldToFilter('order_id',$orderId);
        $po = $collection->getFirstItem();
        $shippingId = $po->getShippingAddressId();
        $model = Mage::getModel('sales/order_address');
        $address = $model->load($shippingId);
        
        $obj = new StdClass();
        $data = $address->getData();
        $obj->name = $data['firstname'].' '.$data['lastname'].($data['company']? ' '.$data['company']:'');
        $obj->postalCode = $data['postcode'];
        $obj->city = $data['city'];
        $obj->street = $data['street'];
        $obj->contactPerson = $data['firstname'].' '.$data['lastname'];
        $obj->contactPhone = $data['telephone'];
        $obj->contactEmail = $data['email'];
        return $obj;
    }     
    protected function _createPieceList($orderId) {
        // WARNING!!! HARDCODE!
        $obj = new StdClass();
        $obj->type = 'PACKAGE';
        $obj->widht = 80;
        $obj->height = 40;
        $obj->length = 40;
        $obj->quantity = 1;
        $obj->nonStandard = 'false';
        $ret = new StdClass();
        $ret->item[] = $obj;
        return $ret;
    }     
    protected function _createPayment($orderId) {
        // WARNING!!! HARDCODE!
        $obj = new StdClass();
        $obj->paymentMethod = 'BANK_TRANSFER';
        $obj->payerType = 'SHIPPER';
        $obj->accountNumber = null;
        $obj->costsCenter = null;
        return $obj;
    }     
    protected function _createService($orderId) {
        $obj = new StdClass();
        
        
        
        return $out;
    }     
    /**
     * create shipments
     *
     * @param array Mage_Sales_Model_Order_Shipment_Track
     * @todo: not finish yet
     */
    public function createShipment($track) {
        if (empty($track)) {
            return false;
        }
        if (!is_array($track)) {
            $track = array($track);
        }
        $message = new StdClass();
        $message->authData = $this->_auth;        
        $shipment = new StdClass();
        foreach ($track as $elem) {
           $obj = new StdClass();
           $shipment = $elem->getShipment();
           $orderId = $elem->getOrderId();
           $vendorId = $elem->getShipment()->getUdropshipVendor();
           $obj->shipper = $this->_createShipper($vendorId);
           $obj->receiver = $this->_createReceiver($orderId);
           $obj->pieceList = $this->_createPieceList($orderId);
           $obj->payment = $this->_createPayment($orderId);
           $obj->service = $this->_createService($orderId);
           $shipment->item[] = $obj;
        }
        $message->shipments = $shipment;
        $return = $this->_sendMessage('createShipment',$message);
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
         $message->shipmentId($shipmentId);
         $return = $this->_sendMessage('getTrackAndTraceInfo',$message);
         return $return;
     }
     
    /**
     * labels to print NOT FINISHED YET!
     */
     public function getLabels($shipment) {  
          if (empty($shipment)) {
              return false;
          }
          if (!is_array($shipment)) {   
              $shipment = array($shipment);
          }
          if (count($shipment) > 3) {   
               Mage::throwException('Too many shipments in one query');
          }
          $message = new StdClass();
          $message->authData = $this->_auth;
          $print = new StdClass();
          foreach ($shipment as $item) {
              $obj = new StdClass();
              $obj->labelType = 'LP';
              $obj->shipmentId = $item;
              $print->item[] = $obj;
          }
          $message->itemsToPrint = $print;
          $return = $this->_sendMessage('getLabels',$message);
          print_R($return);
     }
     
}