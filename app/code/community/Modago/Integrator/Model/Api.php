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
	 * Get token from soap
	 * Return string token on success
	 * Return integer -1 on fail
	 *
	 * @return string|int
	 */
    protected function _getKey() {
        if (!$this->_key) {
            $client = $this->_getSoapClient();
            $this->_key = $this->_getHelper()->getKey($client);
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
			return;
		}
		/** @var Modago_Integrator_Helper_Api $helper */
		$helper = Mage::helper('modagointegrator/api');
		$key = $this->_getKey();
		$client = $this->_getSoapClient();
		$ret = $client->setChangeOrderMessageConfirmation($key, $list);
		if (empty($ret->status)) { // no answer or error
			$helper->log($helper->__('Error: no response from API server'));
		} else {
			if ($ret->message != 'ok') {
				$helper->log($helper->__('Error: confirming messages field (%s) for list (%s)', $ret->message, implode(',', $list) ));
			} else {
				$helper->log($helper->__('Success: successfully confirmed list of messages (%s)', implode(',', $list) ));
			}
		}
	}
    
    /**
     * get list of changed orders
     * @param 
     * @return stdClass
     */
    protected function _getChangeOrderMessage() {
		/** @var Modago_Integrator_Helper_Api $helper */
		$helper = Mage::helper('modagointegrator/api');
        $client = $this->_getSoapClient();
        $key = $this->_getKey();
        $size = $this->_getHelper()->getBatchSize();
        $ret = $client->getChangeOrderMessage($key,$size,'');
 		if (empty($ret->status)) { // no answer or error
			$helper->log($helper->__('Error: no response from API server'));
		} else {
			if ($ret->message != 'ok') {
				$helper->log($helper->__('Error: downloading list of changed orders fail (%s)', $ret->message));
			} else {
				if (empty($ret->list)) {
					$helper->log($helper->__('Success: downloading list of changed orders return empty list'));
				} else {
				    if (empty($ret->list->message)) {
				        $out = $ret->list;
				    } else {
				        $out = $ret->list->message;
				    }
				    $message = array();
				    foreach ($out as $item) {
				        $message[] = $item->orderID;
				    }
					$helper->log($helper->__('Success: downloading list of changed orders return list (%s)', implode(',', $message) ));
				}
			}
		}
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
     * process order list
     * 
     * @param array $foreachMsgData
     * @return 
     */

    public function processOrders($foreachMsgData) {
        $confirmMessages = array();
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
    }
    /**
     * run process
     */
    public function run() {
		/** @var Modago_Integrator_Helper_Data $helper */
		$helper = Mage::helper('modagointegrator');

		if (!$this->_getHelper()->isEnabled()) {
			$msg = $helper->__('Configuration error. Integration is disabled');
			return $this->_finish($msg);
		}

        // login
        $key = $this->_getKey();
        if ($key == -1) {
            $msg = $helper->__('Login error');
            return $this->_finish($msg);
        }
        $ret = $this->_getChangeOrderMessage();
        if (empty($ret->list) || empty($ret->list->message)) { // no order list
            $msg = $helper->__('Order list empty');
            return $this->_finish($msg);
        }
        $foreachMsgData = !empty($ret->list->message) ? $ret->list->message : $ret->list;
        $this->processOrders($foreachMsgData);
        $msg = Mage::helper('modagointegrator')->__('End process');
        $this->_finish($msg);
    }

}