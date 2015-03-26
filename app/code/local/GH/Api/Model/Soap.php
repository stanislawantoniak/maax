<?php
/**
 * soap methods handler
 */
class GH_Api_Model_Soap extends Mage_Core_Model_Abstract {


    /**
     * message list
     *
     * @param stdClass $changeOrderMessageParameters
     * @return stdClass
     */
    public function getChangeOrderMessage($changeOrderMessageParameters) {
        $request = $changeOrderMessageParameters;
        $token = $request->sessionToken;
        $batchSize = $request->messageBatchSize;
        $messageType = empty($request->messageType)? null:$request->messageType;

        $model = $this->getMessageModel();

        try {
            $messages = $model->getMessages($token,$batchSize,$messageType);

            $list = array();
            foreach($messages as $msg) {
                $m = new StdClass();
                $m->messageID = $msg['messageID'];
                $m->messageType = $msg['messageType'];
                $m->orderID = $msg['orderID'];
                $list[] = $m;
            }

            $message = 'ok';
            $status = true;
        } catch(Exception $e) {
            $list = array();
            $message = $e->getMessage();
            $status = false;
        }
        $obj = new StdClass();
        $obj->list = $list;
        $obj->message = $message;
        $obj->status = $status;
        return $obj;
    }

    /**
     * confirm messages
     *
     * @param stdClass $setChangeOrderMessageConfirmationParameters
     * @return stdClass
     */
    public function setChangeOrderMessageConfirmation($setChangeOrderMessageConfirmationParameters) {
        $request = $setChangeOrderMessageConfirmationParameters;

        $token = $request->sessionToken;
        if (!isset($request->messageID->ID)) {            
            $message = Mage::helper('ghapi')->__('Message ID list empty');
            $status = false;
        } else {
            $messages = $request->messageID->ID;
            if (!is_array($messages)) {
                $messages = array($messages);
            }
            /** @var GH_Api_Model_Message $model */
            $model = $this->getMessageModel();
            try {
                $status = $model->confirmMessages($token, $messages);
                $message = 'ok';
            } catch(Exception $e) {
                $status = false;
                $message = $e->getMessage();
            }
        }
        $obj = new StdClass();
        $obj->message = $message;
        $obj->status = $status;
        return $obj;
    }

    /**
     * login handler
     * @param stdClass $loginParameters
     * @return stdClass
     */
    public function doLogin($loginParameters) {
        $vendorId = $loginParameters->vendorId;
        $password = $loginParameters->password;
        $apiKey = $loginParameters->webApiKey;

        $model = $this->getUserModel();

        $obj = new StdClass();
        try {
            $model->loginUser($vendorId,$password,$apiKey);
            $token = $model->getSession()->getToken();
            $obj->status = true;
            $obj->message = 'ok';
            $obj->token = $token;
        } catch (Exception $ex) {
            $obj->status = false;
            $obj->message = $ex->getMessage();
            $obj->token = '';
        }
        return $obj;
    }


    /**
     * Show PO for given increment id (or ids)
     *
     * @param stdClass $getOrdersByIDRequestParameters
     * @return StdClass
     */
    public function getOrdersByID($getOrdersByIDRequestParameters) {
        $request  = $getOrdersByIDRequestParameters;
        $token    = $request->sessionToken;
        $obj = new StdClass();
        
        try {
            if (!isset($request->orderID->ID)) {
                Mage::throwException('Order ID list empty');
            }
            $orderIds = $request->orderID->ID;
            if (!is_array($orderIds)) {
                $orderIds = array($orderIds);
            }
            /** @var Zolago_Po_Model_Po $model */
            $model    = Mage::getModel('zolagopo/po');
            $user = $this->getUserByToken($token);
            $vendor = Mage::getModel('udropship/vendor')->load($user->getVendorId());
            $allData = $model->ghapiGetOrdersByIncrementIds($orderIds, $vendor);

            // Checking if ids are correct
            $allDataIds = array();
            foreach ($allData as $po) {
                $allDataIds[] = $po['order_id'];
            }
            if (count($orderIds) != count($allData)) {
                $idsCheck = array_diff($orderIds, $allDataIds);
                $this->throwOrderIdWrongError($idsCheck);
            }

            // Collecting orderList
            $poList = array();
            foreach ($allData as $data) {

                $order = new StdClass();
                $order->vendor_id = $data['vendor_id'];
                $order->vendor_name = $data['vendor_name'];
                $order->order_id = $data['order_id'];
                $order->order_date = $data['order_date'];
                $order->order_max_shipping_date = $data['order_max_shipping_date'];
                $order->order_status = $data['order_status'];
                $order->order_total = $data['order_total'];
                $order->payment_method = $data['payment_method'];
                $order->order_due_amount = $data['order_due_amount'];
                $order->delivery_method = $data['delivery_method'];
                $order->shipment_tracking_number = $data['shipment_tracking_number'];
                $order->pos_id = $data['pos_id'];

                $invoice = new StdClass();
                $invoice->invoice_required = $data['invoice_data']['invoice_required'];
                if ($invoice->invoice_required) {
                    $invoiceAddress = new StdClass();
                    $invoiceAddress->invoice_first_name = $data['invoice_required']['invoice_address']['invoice_first_name'];
                    $invoiceAddress->invoice_last_name = $data['invoice_required']['invoice_address']['invoice_last_name'];
                    $invoiceAddress->invoice_company_name = $data['invoice_required']['invoice_address']['invoice_company_name'];
                    $invoiceAddress->invoice_street = $data['invoice_required']['invoice_address']['invoice_street'];
                    $invoiceAddress->invoice_city = $data['invoice_required']['invoice_address']['invoice_city'];
                    $invoiceAddress->invoice_zip_code = $data['invoice_required']['invoice_address']['invoice_zip_code'];
                    $invoiceAddress->invoice_country = $data['invoice_required']['invoice_address']['invoice_country'];
                    $invoiceAddress->invoice_tax_id = $data['invoice_required']['invoice_address']['invoice_tax_id'];
                    $invoice->invoice_address = $invoiceAddress;
                }
                $order->invoice_data = $invoice;

                $delivery = new StdClass();
                $delivery->inpost_locker_id = $data['delivery_data']['inpost_locker_id'];
                $deliveryAddress = new StdClass();
                $deliveryAddress->delivery_first_name = $data['delivery_data']['delivery_address']['delivery_first_name'];
                $deliveryAddress->delivery_last_name = $data['delivery_data']['delivery_address']['delivery_last_name'];
                $deliveryAddress->delivery_company_name = $data['delivery_data']['delivery_address']['delivery_company_name'];
                $deliveryAddress->delivery_street = $data['delivery_data']['delivery_address']['delivery_street'];
                $deliveryAddress->delivery_city = $data['delivery_data']['delivery_address']['delivery_city'];
                $deliveryAddress->delivery_zip_code = $data['delivery_data']['delivery_address']['delivery_zip_code'];
                $deliveryAddress->delivery_country = $data['delivery_data']['delivery_address']['delivery_country'];
                $deliveryAddress->phone = $data['delivery_data']['delivery_address']['phone'];
                $delivery->delivery_address = $deliveryAddress;
                $order->delivery_data = $delivery;

                $items = array();
                foreach ($data['order_items'] as $item) {
                    $orderItem = new StdClass();
                    $orderItem->is_delivery_item = $item['is_delivery_item'];
                    $orderItem->item_sku = $item['item_sku'];
                    $orderItem->item_name = $item['item_name'];
                    $orderItem->item_qty = $item['item_qty'];
                    $orderItem->item_value_before_discount = $item['item_value_before_discount'];
                    $orderItem->item_discount = $item['item_discount'];
                    $orderItem->item_value_after_discount = $item['item_value_after_discount'];
                    $items[] = $orderItem;
                }

                $order->order_items = $items;
                $poList[] = $order;
            }
            $obj->orderList = $poList;

            $message = 'ok';
            $status = true;
        } catch(Exception $e) {
            $obj->orderList = array();
            $message = $e->getMessage();
            $status = false;
        }

        $obj->message = $message;
        $obj->status = $status;
        return $obj;
    }

    /**
     * Set collected status
     *
     * @param $setOrderAsCollectedRequestParameters
     * @return StdClass
     */
    public function setOrderAsCollected($setOrderAsCollectedRequestParameters) {
        /** @var Zolago_Po_Model_Po $model */
        /** @var Zolago_Po_Helper_Data $hlpPo */
        $request  = $setOrderAsCollectedRequestParameters;
        $token    = $request->sessionToken;

        try {
            if (!isset($request->orderID->ID)) {
                Mage::throwException(Mage::helper('ghapi')->__('Order ID list empty'));
            }            
            $orderIds = $request->orderID->ID;
            if (!is_array($orderIds)) {
                $orderIds = array($orderIds);
            }
            $user = $this->getUserByToken($token);
            $vendor = Mage::getModel('udropship/vendor')->load($user->getVendorId());
            $model = Mage::getModel('zolagopo/po');
            $hlpPo = Mage::helper('zolagopo');
            $coll = $model->getVendorPoCollectionByIncrementId($orderIds, $vendor);

            // START Checking if ids are correct
            $checkList = $hlpPo->massCheckIsStartPackingAvailable($coll);
            if (count($checkList)) {
                $this->throwOrderInvalidStatusError($checkList);
            }
            $allDataIds = array();
            foreach ($coll as $po) {
                /** @var Zolago_Po_Model_Po $po */
                $allDataIds[] = $po->getIncrementId();
            }
            if (count($orderIds) != $coll->count()) {
                $idsCheck = array_diff($orderIds, $allDataIds);
                $this->throwOrderIdWrongError($idsCheck);
            }
            // END Checking if ids are correct

            // Finally if no errors start masss processing
            $hlpPo->massProcessStartPacking($coll);

            $message = 'ok';
            $status = true;
        } catch(Exception $e) {
            $message = $e->getMessage();
            $status = false;
        }

        $obj = new StdClass();
        $obj->message = $message;
        $obj->status = $status;
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

    /**
     * @param string $token
     * @return GH_Api_Model_User
     */
    protected function getUserByToken($token) {
        return $this->getHelper()->getUserByToken($token);
    }

    /**
     * Gets main GH Api helper
     * @return GH_Api_Helper_Data
     */
    protected function getHelper() {
        return Mage::helper('ghapi');
    }

    /**
     * @param array $ids
     * @throws Mage_Core_Exception
     */
    protected function throwOrderIdWrongError(array $ids = array()) {
        $ids = count($ids) ? ' ('.implode(',',$ids).')' : '';
        Mage::throwException('error_order_id_wrong'.$ids);
    }

    /**
     * @param array $ids
     * @throws Mage_Core_Exception
     */
    protected function throwOrderInvalidStatusError(array $ids = array()) {
        $ids = count($ids) ? ' ('.implode(',',$ids).')' : '';
        Mage::throwException('error_order_invalid_status'.$ids);
    }
}