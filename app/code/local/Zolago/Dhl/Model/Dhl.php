<?php
/**
 * dhl 
 */
class Zolago_Dhl_Model_Dhl extends Mage_Core_Model_Abstract {
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
        $soap = new SoapClient(Mage::getStoreConfig('zolagodhl/wsdl_path'),true);
        $result = $soap->$method($message);
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
}