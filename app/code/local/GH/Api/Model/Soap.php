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
     * Show PO for given increment id (or ids)
     *
     * @param $getOrdersByIDParameters
     * @return StdClass
     */
    public function getOrdersByID($getOrdersByIDParameters) {
        $request  = $getOrdersByIDParameters;
        $token    = $request->sessionToken;
        $orderIds = $request->orderIds;

        $model    = Mage::getModel('zolagopo/po');

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
     * Set collected status
     *
     * @param $setOrderAsCollectedParameters
     * @return StdClass
     */
    public function setOrderAsCollected($setOrderAsCollectedParameters) {
        $request  = $setOrderAsCollectedParameters;
        $token    = $request->sessionToken;
        $orderIds = $request->orderIds;

        $model    = Mage::getModel('zolagopo/po');

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

    public function setOrderShipment($setOrderShipmentParameters) {
        $request  = $setOrderShipmentParameters;
        $token    = $request->sessionToken;
        $orderIds = $request->orderIds;
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