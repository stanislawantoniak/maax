<?php
/**
 * connect to modago api
 */

class Modago_Integrator_Model_Api
    extends Varien_Object {


    protected $_soap;
    protected $_key;
        
    /**
     * get parameters from config by name
     * @param string $name
     * @return string
     */

    protected function _getConfig($name) {
        return  Mage::helper('modagointegrator/api')->getConfig($name);        
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
                 Modago_Integrator_Model_Log::log($ret->message);
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
        $size = $this->_getConfig('batchSize');
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
                Modago_Integrator_Model_Log::log($details->message);                
            }
            return false;
        }
        if (empty($details->orderList)) {
            $msg = Mage::helper('modagointegrator')->__('Empty order details (%s)',$orderId); 
            Modago_Integrator_Model_Log::log($msg);
            return false;
        }
        foreach ($details->orderList as $item) {
            // todo - create order from item (stdClass);
        }
        return true;
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
        // login
        $key = $this->_getKey();
        if ($key == -1) {
            $msg = Mage::helper('modagointegrator')->__('Login error');
            return $this->_finish($msg);
        }
        $list = $this->_getChangeOrderMessage();
        if (empty($list->list) || empty($list->list->message)) { // no order list
            if (!empty($list->status) && (!empty($list->message))) { // no log if status == true                
                Modago_Integrator_Model_Log::log($list->message);
            }
            $msg = Mage::helper('modagointegrator')->__('Order list empty');
            return $this->_finish($msg);
        }
        $confirmMessages = array();
        foreach ($list->list->message as $item) {
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