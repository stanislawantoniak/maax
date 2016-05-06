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

    public function updateProductsPricesStocks($params) {
        $this->_begin();
        $obj = parent::updateProductsPricesStocks($params);
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
     * @param $setOrderAsCollectedRequestParameters
     * @return StdClass
     */
    public function setOrderAsCollected($setOrderAsCollectedRequestParameters) {

        $this->_begin();
        $obj = parent::setOrderAsCollected($setOrderAsCollectedRequestParameters);
        $this->_rollback();    
        return $obj;
    }

    /**
     * Set order shipment
     *
     * @param $setOrderShipmentRequestParameters
     * @return StdClass
     */
    public function setOrderShipment($setOrderShipmentRequestParameters) {
        $this->_begin();
        $obj = parent::setOrderShipment($setOrderShipmentRequestParameters);
        $this->_rollback();
        return $obj;
    }

	public function setOrderReservation($setOrderReservationRequestParameters) {
		$this->_begin();
		$obj = parent::setOrderReservation($setOrderReservationRequestParameters);
		$this->_rollback();
		return $obj;
	}

    /**
     * Method to export all attributes sets (with create products = yes)
     * Export id and name
     *
     * @param $getCategoriesParameters
     * @return StdClass
     */
    public function getCategories($getCategoriesParameters) {
        $this->_begin();
        $obj = parent::getCategories($getCategoriesParameters);
        $this->_rollback();
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