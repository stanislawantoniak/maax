<?php

/**
 * connect to modago api
 */
class Modago_Integrator_Model_Api
    extends Varien_Object {

    protected $_soap;
    protected $_key;
    protected $_connection;


    /**
     * get database connection for transactions
     *
     * @return Magento_Db_Adapter_Pdo_Mysql
     */
    protected function _getConnection() {
        if (!$this->_connection) {
            $this->_connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        }
        return $this->_connection;
    }
    /**
     * start mysql transaction
     */
    public function beginTransaction() {
        $this->_getConnection()->beginTransaction();
    }

    /**
     * commit transaction
     */
    public function commit() {
        $this->_getConnection()->commit();
    }

    /**
     * rollback transaction
     */
    public function rollback() {
        $this->_getConnection()->rollback();

    }
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
            if ($this->_key == -1) {
                Mage::throwException(Mage::helper('modagointegrator')->__('Token error'));
            }
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
            if (!empty($ret->message)) {
                $message = $helper->__('Error: confirming messages field (%s) for list (%s)', $helper->translate($ret->message), implode(',', $list) );
            } else {
                $message = $helper->__('Error: wrong response from API server');
            }
            $helper->log($message);
            Mage::throwException($message);
        } else {
            $helper->log($helper->__('Success: successfully confirmed list of messages (%s)', implode(',', $list) ));
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
            if (!empty($ret->message)) {
                $message = $helper->__('Error: downloading list of changed orders fail (%s)', $helper->translate($ret->message));
            } else {
                $message = $helper->__('Error: wrong response from API server');
            }
            $helper->log($message);
            Mage::throwException($message);
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
        return $ret;
    }

    public function _getOrdersById(array $list) {
        /** @var Modago_Integrator_Helper_Api $helper */
        $helper = Mage::helper('modagointegrator/api');
        $client = $this->_getSoapClient();
        $key = $this->_getKey();
        $ret = $client->getOrdersByID($key, $list);

        if (empty($ret->status)) { // no answer or error
            if (!empty($ret->message)) {
                $message = $helper->__('Error: getting order %s failed (%s)', implode(',', $list), $helper->translate($ret->message));
            } else {
                $message = $helper->__('Error: wrong response from API server');
            }
            $helper->log($message);
            Mage::throwException($message);
        } else {
            if (empty($ret->orderList)) {
                $message = $helper->__('Error: no info about order %s', implode(',', $list));
                $helper->log($message);
                Mage::throwException($message);
            } else {
                $helper->log($helper->__('Success: getting info about order %s', implode(',', $list)));
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
        $integratorOrders = Mage::getModel('modagointegrator/order');
        foreach ($details->orderList as $item) {
            try {
                $item = current($item);
                /** @var Modago_Integrator_Model_Order $integratorOrders */
                $orderId = $integratorOrders->createOrder($item);
                $helper->log($helper->__('Success: order %s (%s) was created', $orderId, $item->order_id));
            } catch (Exception $e) {
                Mage::logException($e);
                $helper->log($helper->__('Error: %s' , $e->getMessage()));
                return false;
            }
            try {
                if ($problems = $integratorOrders->getProductProblemList()) {
                    $message = sprintf('Products out of stocks (%s)',implode(',',$problems));
                    $this->_setOrderReservation($item->order_id,Modago_Integrator_Model_System_Source_Message_Type::MESSAGE_RESERVATION_STATUS_PROBLEM,$message);
                }
            } catch (Exception $xt) {
                Mage::logException($xt);
                $helper->log($helper->__('Error: %s', $xt->getMessage()));
                // no lock, only log
            }
        }

        return true;
    }


    /**
     * send order reservation message
     *
     * @param string $orderId
     * @param string $status
     * @param string $message
     */
    protected function _setOrderReservation($orderId,$status,$message) {
        $helper = Mage::helper('modagointegrator/api');
        $client = $this->_getSoapClient();
        $key = $this->_getKey();
        $ret = $client->setOrderReservation($key,$orderId,$status,$message) ;
        if (empty($ret->status)) {
            if (!empty($ret->message)) {
                $message = $helper->__('set order reservation problem failed: %s (%s)', $helper->translate($ret->message),$orderId);
            } else {
                $message = $helper->__('wrong response from API server');
            }
            Mage::throwException($message);
        } else {
            $helper->log($helper->__('Success: set order reservation successful (%s: status %s)',$orderId,$status));
        }
        return true;
    }

    /**
     * Change invoice address in the order
     *
     * @param $orderId
     * @return bool
     */
    protected function _changeOrderInvoiceAddress($orderId)
    {
        /** @var Modago_Integrator_Helper_Api $helper */
        $helper = Mage::helper('modagointegrator/api');

        /* @var $localOrder Mage_Sales_Model_Order */
        $localOrder = Mage::getModel("sales/order")->load($orderId, "modago_order_id");
        $localOrderId = $localOrder->getId();

        if (is_null($localOrderId)) {
            $helper->log($helper->__("Error: order %s not found.", $orderId));
            return false;
        }

        $details = $this->_getOrdersById(array($orderId));
        Mage::log($details, null, "api_invoice_b_as_sh.log");

        if (empty($details->status)) { // error
            return false;
        }
        if (empty($details->orderList)) {
            return false;
        }
        $orderList = $details->orderList;
        $orders = $orderList->order;

        foreach ($orders as $item) {
            $address = $item->invoice_data->invoice_address;

            /* @var $orderAddress Mage_Sales_Model_Order_Address */
            $orderAddress = $localOrder->getBillingAddress();

            if (!$orderAddress) {
                $helper->log($helper->__('Error: Invoice address not found, order %s (%s)', $localOrderId, $item->order_id));
                return false;
            }
            $orderAddress->setFirstname($address->invoice_first_name);
            $orderAddress->setLastname($address->invoice_last_name);

            $orderAddress->setCompany($address->invoice_company_name);

            $orderAddress->setStreet($address->invoice_street);
            $orderAddress->setCity($address->invoice_city);
            $orderAddress->setPostcode($address->invoice_zip_code);
            $orderAddress->setCountryId($address->invoice_country);
            $orderAddress->setTelephone($address->phone);
            $orderAddress->setData("vat_id", $address->invoice_tax_id);

            try {
                $orderAddress->save();
                $helper->log($helper->__('Success: Invoice address was updated. Order %s (%s)', $orderId, $item->order_id));
            } catch (Exception $e) {
                Mage::logException($e);
                $helper->log($helper->__('Error: %s', $e->getMessage()));
                return false;
            }

        }
        return true;
    }

    
    /**
     * set params for address
     *
     * @param Mage_Sales_Model_Order_Address $orderAddress
     * @param array $address
     */

    protected function _setOrderAddress($orderAddress,$address) {
        foreach ($address as $key=>$val) {
            $orderAddress->setData($key,$val);
        }
    }
    /**
     * Change delivery address in the order
     *
     * @param $orderId
     * @return bool
     */
    protected function _changeOrderDeliveryAddress($orderId)
    {
        /** @var Modago_Integrator_Helper_Api $helper */
        $helper = Mage::helper('modagointegrator/api');

        /* @var $localOrder Mage_Sales_Model_Order */
        $localOrder = Mage::getModel("sales/order")->load($orderId, "modago_order_id");
        $localOrderId = $localOrder->getId();

        if (is_null($localOrderId)) {
            $helper->log($helper->__("Error: order %s not found.", $orderId));
            return false;
        }

        $details = $this->_getOrdersById(array($orderId));
        Mage::log($details, null, "api_delivery_b_as_sh.log");

        if (empty($details->status)) { // error
            return false;
        }
        if (empty($details->orderList)) {
            return false;
        }
        $orderList = $details->orderList;
        $orders = $orderList->order;

        foreach ($orders as $item) {
            $address = $item->delivery_data->delivery_address;

            /* @var $orderAddress Mage_Sales_Model_Order_Address */
            $orderAddress = $localOrder->getShippingAddress();

            if (!$orderAddress) {
                $helper->log($helper->__('Error: Delivery address not found, order %s (%s)', $localOrderId, $item->order_id));
                return false;
            }

            $orderAddress->setFirstname($address->delivery_first_name);
            $orderAddress->setLastname($address->delivery_last_name);

            $orderAddress->setCompany($address->delivery_company_name);

            $orderAddress->setStreet($address->delivery_street);
            $orderAddress->setCity($address->delivery_city);
            $orderAddress->setPostcode($address->delivery_zip_code);
            $orderAddress->setCountryId($address->delivery_country);
            $orderAddress->setTelephone($address->phone);

            try {
                $orderAddress->save();
                $helper->log($helper->__('Success: Delivery address was updated. Order %s (%s)', $localOrderId, $item->order_id));
            } catch (Exception $e) {
                Mage::logException($e);
                $helper->log($helper->__('Error: %s', $e->getMessage()));
                return false;
            }

            //Change billing if invoice not required
            $invoiceRequired = $item->invoice_data->invoice_required;

            if(!$invoiceRequired) {
                /* @var $orderAddress Mage_Sales_Model_Order_Address */
                $orderAddress = $localOrder->getBillingAddress();

                if (!$orderAddress) {
                    $helper->log($helper->__('Error: Invoice address not found, order %s (%s)', $orderId, $item->order_id));
                    return false;
                }

                $orderAddress->setFirstname($address->delivery_first_name);
                $orderAddress->setLastname($address->delivery_last_name);

                $orderAddress->setCompany($address->delivery_company_name);

                $orderAddress->setStreet($address->delivery_street);
                $orderAddress->setCity($address->delivery_city);
                $orderAddress->setPostcode($address->delivery_zip_code);
                $orderAddress->setCountryId($address->delivery_country);
                $orderAddress->setTelephone($address->phone);

                try {
                    $orderAddress->save();
                    $helper->log($helper->__('Success: Invoice address was updated. Order %s (%s)', $orderId, $item->order_id));
                } catch (Exception $e) {
                    Mage::logException($e);
                    $helper->log($helper->__('Error: %s', $e->getMessage()));
                    return false;
                }
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

        $collection = Mage::getModel("sales/order")->getCollection();
        $collection->addFieldToFilter("modago_order_id", $orderId);

        if ($collection->getSize() <= 0) {
            $helper->log($helper->__("Error: order %s not found.", $orderId));
            return false;
        }

        $canceled = array();
        $errors = array();

        foreach ($collection as $collectionItem) {
            if ($collectionItem->getState() == Mage_Sales_Model_Order::STATE_CANCELED)
                continue;

            try {
                $order = Mage::getModel('sales/order');
                $order->load($collectionItem->getId());

                if ($order->canCancel()) {
                    $order->cancel();
                    $order->setStatus('canceled');
                    $order->save();

                    array_push($canceled, 1);
                } else {
                    //ERROR
                    $msg = $this->cantBeCanceledReason($order);

                    array_push($errors, $helper->__("Error: order %s can not be canceled.", $orderId) . $msg);
                    array_push($canceled, 0);
                }

            } catch (Exception $e) {
                Mage::logException($e);
                $helper->log('Error: ' . $e->getMessage());
            }
        }

        if (!in_array(0, $canceled)) {
            $helper->log($helper->__("Success: order (%s) was  canceled.", $orderId));
            return true;
        } else {
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $helper->log($error);
                }
            }
            return false;
        }
    }


    /**
     * @param $order
     * @return string
     */
    public function cantBeCanceledReason($order)
    {
        /** @var Modago_Integrator_Helper_Api $helper */
        $helper = Mage::helper('modagointegrator');

        $orderIncrementId = $order->getData("increment_id");
        $state = $order->getState();

        //1. Is payment is review
        if ($state === Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW)
            return $helper->__("%s Payment has review status", $orderIncrementId);


        //2. Items invoiced
        $allInvoiced = true;
        foreach ($order->getAllItems() as $item) {
            if ($item->getQtyToInvoice()) {
                $allInvoiced = false;
                break;
            }
        }
        if ($allInvoiced)
            return $helper->__("%s All order items are invoiced", $orderIncrementId);


        //3. State: canceled, completed or closed
        if (
            $state === Mage_Sales_Model_Order::STATE_COMPLETE
                       || $state === Mage_Sales_Model_Order::STATE_CLOSED
        ) {
            return $helper->__(" %s Order have status %s ", $orderIncrementId, $state);
        }

    }

    protected function _paymentOrder($orderId) {
        /** @var Modago_Integrator_Helper_Api $helper */
        $helper = Mage::helper('modagointegrator/api');

        $collection = Mage::getModel("sales/order")->getCollection();
        $collection->addFieldToFilter("modago_order_id", $orderId);
        $collection->addFieldToFilter("state",array(
                                          'nin'=>array(
                                              Mage_Sales_Model_Order::STATE_CANCELED,
                                              Mage_Sales_Model_Order::STATE_CLOSED,
                                              Mage_Sales_Model_Order::STATE_COMPLETE)
                                      ));

        if(!$collection->getSize()) {
            $helper->log($helper->__("Error: order %s not found.", $orderId));
            return false;
        }

        /** @var Mage_Sales_Model_Order $order */
        $order = $collection->getFirstItem();
        if($order->getId() && $order->getData('modago_order_id') == $orderId) {
            //get order from api
            $apiResponse = $this->_getOrdersById(array($orderId));
            if(isset($apiResponse->orderList->order[0])) {
                $apiOrder = $apiResponse->orderList->order[0];
                $total = floatval($apiOrder->order_total);
                $totalPaid = round(($total - floatval($apiOrder->order_due_amount)),2);

                $order->setTotalPaid($totalPaid);
                if($totalPaid >= $total) {
                    $order->setStatus(Mage_Sales_Model_Order::STATE_PROCESSING);
                } else {
                    $order->setStatus(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);
                }
                $order->save();
                $helper->log($helper->__("Success: payment in order has changed %s (%s)", $order->getIncrementId(), $orderId));
                return true;
            } else {
                $helper->log($helper->__("Error: order %s not found.", $orderId));
                return false;
            }
        } else {
            $helper->log($helper->__("Error: order %s not found.", $orderId));
            return false;
        }
        return false;
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
            case Modago_Integrator_Model_System_Source_Message_Type::MESSAGE_ITEMS_CHANGED:
                try {
                    $this->beginTransaction();
                    $cancel = $this->_cancelOrder($item->orderID);
                    $newOrder = $this->_createNewOrder($item->orderID);
                    if ($cancel && $newOrder) {
                        $confirmMessages[] = $item->messageID;
                        $this->commit();
                    } else {
                        $this->rollback();
                    }
                } catch (Exception $xt) {
                    $this->rollback();
                    throw $xt;
                }
            case Modago_Integrator_Model_System_Source_Message_Type::MESSAGE_DELIVERY_DATA_CHANGED:
                if ($this->_changeOrderDeliveryAddress($item->orderID)) {
                    $confirmMessages[] = $item->messageID;
                }
                break;
            case Modago_Integrator_Model_System_Source_Message_Type::MESSAGE_INVOICE_ADDRESS_CHANGED:
                if ($this->_changeOrderInvoiceAddress($item->orderID)) {
                    $confirmMessages[] = $item->messageID;
                }
                break;
            case Modago_Integrator_Model_System_Source_Message_Type::MESSAGE_PAYMENT_DATA_CHANGED:
                if($this->_paymentOrder($item->orderID)) {
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