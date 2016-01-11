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
					$helper->log($helper->__('Success: downloading list of changed orders return list (%s)', implode(',', $ret->list) ));
				}
			}
		}
        return $ret;
    }

	public function _getOrdersById(array $list) {
		/** @var Modago_Integrator_Helper_Api $helper */
		$helper = Mage::helper('modagointegrator/api');
		$client = $this->_getSoapClient();
		$key = $this->_getKey();
		$ret = $client->getOrdersByID($key, $list);

		if (empty($ret->status)) { // no answer or error
			$helper->log($helper->__('Error: no response from API server'));
		} else {
			if ($ret->message != 'ok') {
				$helper->log($helper->__('Error: getting order %s failed (%s)', implode(',', $list), $ret->message));
			} else {
				if (empty($ret->orderList)) {
					$helper->log($helper->__('Error: no info about order %s', implode(',', $list)));
				} else {
					$helper->log($helper->__('Success: getting info about order %s', implode(',', $list)));
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
		/** @var Modago_Integrator_Helper_Api $helper */
		$helper = Mage::helper('modagointegrator/api');
        $details = $this->_getOrdersById(array($orderId)); // one by one
        if (empty($details->status)) { // error
            return false;
        }
        if (empty($details->orderList)) {
            return false;
        }
        foreach ($details->orderList as $item) {
			try {
				/** @var Modago_Integrator_Model_Order $integratorOrders */
				$integratorOrders = Mage::getModel('modagointegrator/order');
				$orderId = $integratorOrders->createOrderFromApi($item);
				$helper->log($helper->__('Success: order %s (%s) was created', $orderId, $item->order_id));
			} catch (Exception $e) {
				$helper->log('Error: ' . $e->getMessage());
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
        $confirmMessages = array();
        $foreachMsgData = is_array($ret->list->message) ? $ret->list->message : $ret->list;
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