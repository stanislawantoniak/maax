<?php

/**
 * Class ZolagoOs_OrdersExport_Model_Integrator_Ghapi
 *
 * connection between ghapi and ZolagoOs_OrdersExport
 */
abstract class ZolagoOs_OrdersExport_Model_Integrator_Ghapi
    extends ZolagoOs_OrdersExport_Model_Integrator_Abstract
{
    protected $_confirmOrderList;
    
    const EXPORT_ORDERS_BATCH = 100;


    /**
     * list of api messages from queue
     *
     * @param $messageType
     * @return mixed
     */
    public function getGhApiVendorMessages($messageType)
    {
        $token = $this->_getToken();
        $connector = $this->getApiConnector();
        $params = new StdClass();
        $params->sessionToken = $token;
        $params->messageBatchSize = self::EXPORT_ORDERS_BATCH;
        $params->messageType = $messageType;
        return $connector->getChangeOrderMessage($params);
    }

    /**
     * confirm messages from list
     *
     * @param $list
     */
    public function confirmOrderMessages($list)
    {
        $toConfirm = array();
        foreach ($list as $msg) {
            if (($msg->orderID) && isset($this->_confirmOrderList[$msg->orderID])) {
                $toConfirm[] = $msg->messageID;
            }
        }
        $this->confirmMessages($toConfirm);
    }


    /**
     * complete message list to confirm
     *
     * @param $orderId
     */
    public function addOrderToConfirmMessage($orderId)
    {
        $this->_confirmOrderList[$orderId] = $orderId;
    }

    /**
     * prepare orders details
     *
     * @param $list
     * @return array
     */
    public function prepareOrderList($list)
    {
        $ids = array();
        foreach ($list as $item) {
            $ids[$item->orderID] = $item->orderID;
        }
        if (!count($ids)) {
            return array();
        }
        $token = $this->_getToken();
        $params = new StdClass();
        $params->sessionToken = $token;
        $params->orderID = new StdClass();
        $params->orderID->ID = $ids;
        $connector = $this->getApiConnector();
        $list = $connector->getOrdersById($params);
        if ($list->status) {
            return $list->orderList;
        }
        return array();
    }


}