<?php
/**
 * soap methods handler
 */
class GH_Api_Model_Soap_Test extends GH_Api_Model_Soap {
    
    protected $_connection;
    
    /**
     * database transactions functions
     * @param 
     * @return 
     */

    protected function _getConnection() {
        if (!$this->_connection) {            
            $this->_connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        }
        return $this->_connection;
    }
    protected function _begin() {
        $this->_getConnection()->beginTransaction();
    }
    protected function _rollback() {
        $this->_getConnection()->rollback();
    }
    /**
     * message list
     *
     * @param stdClass $changeOrderMessageParameters
     * @return stdClass
     */
    public function getChangeOrderMessage($changeOrderMessageParameters) {
        $this->_begin();
        $obj = parent::getChangeOrderMessage($changeOrderMessageParameters);
        $this->_rollback();
        return $obj;
    }

    /**
     * confirm messages
     *
     * @param stdClass $setChangeOrderMessageConfirmationParameters
     * @return stdClass
     */
    public function setChangeOrderMessageConfirmation($setChangeOrderMessageConfirmationParameters) {
        $this->_begin();
        $obj = parent::setChangeOrderMessageConfirmation($setChangeOrderMessageConfirmationParameters);
        $this->_rollback();
        return $obj;
    }



    /**
     * Show PO for given increment id (or ids)
     *
     * @param stdClass $getOrdersByIDRequestParameters
     * @return StdClass
     */
    public function getOrdersByID($getOrdersByIDRequestParameters) {
        $this->_begin();
        $obj = parent::getOrdersByID($getOrdersByIDRequestParameters);
        $this->_rollback();
        return $obj;
    }

    /**
     * Set collected status
     *
     * @param $setOrderAsCollectedParameters
     * @return StdClass
     */
    public function setOrderAsCollected($setOrderAsCollectedRequestParameters) {
        $this->_begin();
        $obj = parent::setOrderAsCollected($setOrderAsCollectedRequestParameters);
        $this->_rollback();    
        return $obj;
    }

    public function setOrderShipment($setOrderShipmentRequestParameters) {
        $request  = $setOrderShipmentRequestParameters;
        $token    = $request->sessionToken;
        $orderId = $request->orderID;
        $courier  = $request->courier;
        $dateShipped = $request->dateShipped;
        $shipmentTrackingNumber = $request->shipmentTrackingNumber;

        try {
            //todo

            $message = 'ok';
            $status = true;
        } catch(Exception $e) {
            //todo
            $message = $e->getMessage();
            $status = false;
        }

        $obj = new StdClass();
        //todo
        $obj->message = $message;
        $obj->status = $status;
        return $obj;

    }

    /**
     * @return GH_Api_Model_Message
     */
    protected function getMessageModel() {
        return Mage::getModel('ghapi/message');
    }

    /**
     * @return GH_Api_Model_User
     */
    protected function getUserModel() {
        return Mage::getModel('ghapi/user');
    }

}