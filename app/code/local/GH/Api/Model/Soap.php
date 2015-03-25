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

            $model    = Mage::getModel('zolagopo/po');

            //todo
            $order = new StdClass();
            $order->vendor_id = 5;
            $order->vendor_name = 'vendor jakiś';
            $order->order_id = '34334';
            $order->order_date = '13213123232123';
            $order->order_max_shipping_date = 'ddd';
            $order->order_status = 'pending';
            $order->order_total = 15.3;
            $order->payment_method = 'cash_on_delivery';
            $order->order_due_amount = 3234.23;
            $order->delivery_method = 'standard_courier';
            $order->shipment_tracking_number = 'track 323423423423';
            $order->pos_id = 'posid --adf';
            $invoice = new StdClass();
            $invoice->invoice_required = 1;
            $invoiceAddress = new StdClass();
            $invoiceAddress->invoice_first_name = 'firstname';
            $invoiceAddress->invoice_last_name = 'lastname';
            $invoiceAddress->invoice_company_name = 'companyname';
            $invoiceAddress->invoice_street = 'street';
            $invoiceAddress->invoice_city = 'city';
            $invoiceAddress->invoice_zip_code = 'zipcode';
            $invoiceAddress->invoice_country = 'polska';
            $invoiceAddress->invoice_tax_id = '34534434353';
            $invoice->invoice_address = $invoiceAddress;
            $order->invoice_data = $invoice;
            $delivery = new StdClass();
            $delivery->inpost_locker_id = 'inpost dadfadfadsf';
            $deliveryAddress = new StdClass();
            $deliveryAddress->delivery_first_name = 'firstname';
            $deliveryAddress->delivery_last_name = 'lastname';
            $deliveryAddress->delivery_company_name = 'companyname';
            $deliveryAddress->delivery_street = 'street';
            $deliveryAddress->delivery_city = 'city';
            $deliveryAddress->delivery_zip_code = 'zipcode';
            $deliveryAddress->delivery_country = 'polska';
            $deliveryAddress->phone = '88888888';
            $delivery->delivery_address = $deliveryAddress;
            $order->delivery_data = $delivery;
            $orderItem = new StdClass();
            $orderItem->is_delivery_item = 1;
            $orderItem->item_sku = 'skkku';
            $orderItem->item_name = 'name';
            $orderItem->item_qty = 555;
            $orderItem->item_value_before_discount = 234.32;
            $orderItem->item_discount = 24.32;
            $orderItem->item_value_after_discount = 11.11;
            $order->order_items = array ($orderItem,clone($orderItem),clone($orderItem));
            $obj->orderList = array($order,clone($order));

            $message = 'ok';
            $status = true;
        } catch(Exception $e) {
            //todo
            $message = $e->getMessage();
            $status = false;
        }

        //todo
        $obj->message = $message;
        $obj->status = $status;
        return $obj;
    }

    /**
     * Set collected status
     *
     * @param $setOrderAsCollectedParameters
     * @return StdClass
     */
    public function setOrderAsCollected($setOrderAsCollectedRequestParameters) {
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
            $model    = Mage::getModel('zolagopo/po');
            
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