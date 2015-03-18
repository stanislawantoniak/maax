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
        $obj = new StdClass();
        $obj->message = $changeOrderMessageParameters->sessionToken.' '.$changeOrderMessageParameters->messageBatchSize.' '.$changeOrderMessageParameters->messageType;;
        $obj->status = 1;
        $list = array();
        $t = new StdClass();
        $t->orderId = 1;
        $t->messageType = 'type1';
        $t->messageId = 11;
        $list[] = $t;
        $t = new StdClass();
        $t->orderId = 2;
        $t->messageType = 'type2';
        $t->messageId = 22;
        $list[] = $t;
        $t = new StdClass();
        $t->orderId = 3;
        $t->messageType = 'type3';
        $t->messageId = 33;
        $list[] = $t;
        $obj->list = $list;
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
        $obj = new StdClass();
        $obj->message = $request->sessionToken;
        $list = $request->messageIdList;
        foreach ($list as $item) {
            $obj->message .= $item.' ';
        }
        $obj->status = 1;
        return $obj;
    }
    /**
     * login handler
     * @param stdClass $loginParameters
     * @return stdClass
     */

    public function doLogin($loginParameters) {
        $obj = new StdClass();
        $obj->status = 1;
        $obj->message = 'ok';
        $obj->token = $loginParameters->password.' '.$loginParameters->webApiKey.' '.$loginParameters->vendorId;
        return $obj;
    }


}