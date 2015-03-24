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
	    $messageType = $request->messageType;

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
	    $messages = $request->messageIdList;

	    /** @var GH_Api_Model_Message $model */
	    $model = $this->getMessageModel();
		try {
			$status = $model->confirmMessages($token, $messages);
			$message = 'ok';
	    } catch(Exception $e) {
			$status = false;
			$message = $e->getMessage();
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
     * orders details
     * @param stdClass $getOrdersByIDRequestParameters
     * @return stdClass
     */
     public function getOrdersByID($getOrdersByIDRequestParameters) {
         $obj = new StdClass();
         $obj->status = true;
         $obj->message = print_R($getOrdersByIDRequestParameters,1);
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
         $invoiceAddress->invoice_zipcode = 'zipcode';
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
         $deliveryAddress->delivery_zipcode = 'zipcode';
         $deliveryAddress->delivery_country = 'polska';
         $deliveryAddress->phone = '88888888';
         $delivery->delivery_address = $deliveryAddress;
         $order->delivery_data = $delivery;
         $orderItem = new StdClass();
         $orderItem->is_delivery_item = 0;
         $orderItem->item_sku = 'skkku';
         $orderItem->item_name = 'name';
         $orderItem->item_qty = 555;
         $orderItem->item_value_before_discount = 234.32;
         $orderItem->item_discount = 24.32;
         $orderItem->item_value_after_discount = 11.11;
         $item = new StdClass();
         $item->item = $orderItem;
         $order->order_items = array ($item,$item,$item);
         $obj->orderList = array($order,$order);
         return $obj; 
     }
     
     
    /**
     * set orders as collected
     * @param stdClass $setOrderAsCollectedRequestParameters
     * @return stdClass
     */
     public function setOrderAsCollected($setOrderAsCollectedRequestParameters){
         $obj = new StdClass();
         $obj->status = true;
         $obj->message = print_R($setOrderAsCollectedRequestParameters,1);
         return $obj; 
     }
     
    /**
     * set order shipment
     * @param stdClass $setOrderShipmentRequestParameters
     * @return stdClass
     */
     public function setOrderShipment($setOrderShipmentRequestParameters) {
         $obj = new StdClass();
         $obj->status = true;
         $obj->message = print_R($setOrderShipmentRequestParameters,1);
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