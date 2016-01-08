<?php
/**
 * connect to modago api
 */

class Modago_Integrator_Model_Api
    extends Varien_Object {


    protected $_soap;
    protected $_key;
        
    /**
     * @return Modago_Integrator_Helper_Api
     */
    protected function _getHelper() {
        return  Mage::helper('modagointegrator/api');
    }
    
    /**
     * prepare and return soap client 
     *
     * @return Modago_Integrator_Model_Soap_Client
     */

    protected function _getSoapClient() {
        if (!$this->_soap) {
            $this->_soap = Mage::getModel('modagointegrator/soap_client');
        }
        return $this->_soap;
    }
    
    /**
     * get token from soap
     * @param 
     * @return 
     */

    protected function _getKey() {
        if (!$this->_key) {
            $client = $this->_getSoapClient();
            $this->_key = Mage::helper('modagointegrator/api')->getKey($client);
        }
        return $this->_key;

    }
    
    /**
     * confirm messages
     *
     * @param array 
     */
     protected function _confirmMessages($list) {
         if (empty($list)) {
             return ;
         }
         $key = $this->_getKey();
         $client = $this->_getSoapClient();
         $ret = $client->setChangeOrderMessageConfirmation($key,$list);
         if (empty($ret->status)) { // no answer or error
             if (!empty($ret->message)) {
				 Mage::helper('modagointegrator/api')->log($ret->message);
             }
         }
     }
    
    /**
     * get list of changed orders
     * @param 
     * @return stdClass
     */
    protected function _getChangeOrderMessage() {
        $client = $this->_getSoapClient();
        $key = $this->_getKey();
        $size = $this->_getHelper()->getBatchSize();
        $ret = $client->getChangeOrderMessage($key,$size,'');
        return $ret;
    }
    
    /**
     * create new order
     *
     * @param string $orderId
     * @return bool
     */
    protected function _createNewOrder($orderId) {
        $key = $this->_getKey();
        $client = $this->_getSoapClient();
        $details = $client->getOrdersById($key,array($orderId)); // one by one
        if (empty($details->status)) { // error
            if (!empty($details->message)) {
				Mage::helper('modagointegrator/api')->log($details->message);
            }
            return false;
        }
        if (empty($details->orderList)) {
            $msg = Mage::helper('modagointegrator')->__('Empty order details (%s)',$orderId);
			Mage::helper('modagointegrator/api')->log($msg);
            return false;
        }
        foreach ($details->orderList as $item) {
            /** @var Modago_Integrator_Model_Order $integratorOrders */
            $integratorOrders = Mage::getModel('modagointegrator/order');
            $orderId = $integratorOrders->createOrderFromApi($item);
            if(!$orderId) {
                return false;
            }
        }
        return false; //todo: change to true - it's for debug
    }    
    
    

    /**
     * cancel order
     *
     * @param string $orderId
     * @return bool
     */
     protected function _cancelOrder($orderId) {
         // todo
        Mage::log('cancel '.$orderId);
        return true;
     }
    /**
     * end process
     *
     * @param string $msg 
     */
    protected function _finish($msg) {
        echo $msg.PHP_EOL;
    }
    /**
     * run process
     */
    public function run() {
		if (!$this->_getHelper()->isEnabled()) {
			$msg = Mage::helper('modagointegrator')->__('Configuration error. Integration is disabled');
			return $this->_finish($msg);
		}

        // login
        $key = $this->_getKey();
        if ($key == -1) {
            $msg = Mage::helper('modagointegrator')->__('Login error');
            return $this->_finish($msg);
        }
        $list = $this->_getChangeOrderMessage();
        if (empty($list->list) || empty($list->list->message)) { // no order list
            if (!empty($list->status) && (!empty($list->message))) { // no log if status == true                
				Mage::helper('modagointegrator/api')->log($list->message);
            }
            $msg = Mage::helper('modagointegrator')->__('Order list empty');
            return $this->_finish($msg);
        }
        $confirmMessages = array();
        $foreachMsgData = is_array($list->list->message) ? $list->list->message : $list->list;
        foreach ($foreachMsgData as $item) {
            switch ($item->messageType) {
                case Modago_Integrator_Model_System_Source_Message_Type::MESSAGE_NEW_ORDER:
                    if ($this->_createNewOrder($item->orderID)) {
                        $confirmMessages[] = $item->messageID;
                    }
                    break;
                case Modago_Integrator_Model_System_Source_Message_Type::MESSAGE_CANCELLED_ORDER:
                    if ($this->_cancelOrder($item->orderID)) {
                        $confirmMessages[] = $item->messageID;
                    }
                    break;
                default:
                    $confirmMessages[] = $item->messageID;
                    // ignore item
            }
        }
        $this->_confirmMessages($confirmMessages);
        $msg = Mage::helper('modagointegrator')->__('End process');
        $this->_finish($msg);
    }

}