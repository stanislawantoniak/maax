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
                if (empty($ret->list) || empty($ret->list->message)) {
                    $helper->log($helper->__('Success: downloading list of changed orders return empty list'));
                } else {
                    $message = array();
                    foreach ($ret->list->message as $item) {
                        $message[] = $item->orderID;
                    }
                    $helper->log($helper->__('Success: downloading list of changed orders return list (%s)', implode(',', $message) ));
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
                $item = current($item);
                /** @var Modago_Integrator_Model_Order $integratorOrders */
                $integratorOrders = Mage::getModel('modagointegrator/order');
                $orderId = $integratorOrders->createOrderFromApi($item);
                $helper->log($helper->__('Success: order %s (%s) was created', $orderId, $item->order_id));
            } catch (Exception $e) {
                Mage::logException($e);
                $helper->log('Error: ' . $e->getMessage());
                return false;
            }
        }
        return true;
    }



    /**
     * cancel order
     *
     * @param string $orderId
     * @return bool
     */
    protected function _cancelOrder($orderId)
    {
        /** @var Modago_Integrator_Helper_Api $helper */
        $helper = Mage::helper('modagointegrator/api');

        $ordersCollection = Mage::getModel("sales/order")->getCollection();
        $ordersCollection->addFieldToFilter("modago_order_id", $orderId);
        $modagoOrder = $ordersCollection->getFirstItem();
        if (!$modagoOrder->getId()) {
            $helper->log($helper->__("Error: order %s not found.", $orderId));
            return false;
        }

        try {
            $order = Mage::getModel('sales/order');
            $order->load($modagoOrder->getId());
            if ($order->canCancel()) {
                $order->cancel();
                $order->setStatus('canceled');
                $order->save();
                $helper->log($helper->__("Success: order (%s) was  canceled.", $orderId));
                return true;
            } else {
                //ERROR
                $msg = $this->cantBeCanceledReason($order);
                $helper->log($helper->__("Error: order %s can not be canceled. %s", $orderId, $msg));

                return false;
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $helper->log('Error: ' . $e->getMessage());
        }
        return false;
    }

    /**
     * @param $order
     * @return string
     */
    public function cantBeCanceledReason($order)
    {
        /** @var Modago_Integrator_Helper_Api $helper */
        $helper = Mage::helper('modagointegrator');

        $state = $order->getState();

        //1. Is payment is review
        if ($state === Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW)
            return $helper->__("Payment has review status");


        //2. Items invoiced
        $allInvoiced = true;
        foreach ($order->getAllItems() as $item) {
            if ($item->getQtyToInvoice()) {
                $allInvoiced = false;
                break;
            }
        }
        if ($allInvoiced)
            return $helper->__("All order items are invoiced");


        //3. State: canceled, completed or closed
        if ($order->isCanceled() || $state === Mage_Sales_Model_Order::STATE_COMPLETE || $state === Mage_Sales_Model_Order::STATE_CLOSED)
            return $helper->__("Order have status %s",$state);

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
        try {
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
            $foreachMsgData = $ret->list->message;
            $this->processOrders($foreachMsgData);
            $msg = Mage::helper('modagointegrator')->__('End process');
            $this->_finish($msg);
        } catch (Exception $e) {
            Mage::logException($e);
            $msg = $helper->__('Process error: see exception log');
            return $this->_finish($msg);
        }
    }

}