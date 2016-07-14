<?php

class ZolagoOs_IAIShop_Model_Observer
{
    const IAISHOP_SYNC_ORDERS_BATCH = 100;

    private $_connector = false;

    private function getGHAPIConnector()
    {
        if (!$this->_connector) {
            $this->_connector = new ZolagoOs_IAIShop_Model_GHAPI_Connector();
        }
        return $this->_connector;
    }

    public function syncIAIShop()
    {
        //1. Get newOrder messages from GH_API
        $orderIncrementIdsByVendor = $this->getNewOrderMessages();

        if (empty($orderIncrementIdsByVendor))
            return;

        //2. Get order's info from GH_API
        $orders = array();
        foreach ($orderIncrementIdsByVendor as $vendorId => $orderIncrementIds) {

            if (!empty($vendorOrders = $this->getGhApiVendorOrders($vendorId, $orderIncrementIds)))
                $orders[$vendorId] = $vendorOrders;
        }       


        //2. Add orders to IAI-Shop API
        /** @var ZolagoOs_IAIShop_Helper_Data $helper */
        $helper = Mage::helper("zosiaishop");
        $response = $helper->addOrders($orders);
    }


    /**
     * @return array
     */
    public function getNewOrderMessages()
    {
        $orderIncrementIds = array();

        $messagesCollection = Mage::getModel("ghapi/message")
            ->getCollection();
        $messagesCollection
            ->addFieldToFilter("message", "newOrder")
            ->setOrder('po_increment_id', 'DESC')
            ->setOrder('message_id', 'ASC')
            ->setOrder('vendor_id', 'ASC');
        $messagesCollection->getSelect()->limit(self::IAISHOP_SYNC_ORDERS_BATCH);

        if ($messagesCollection->count() <= 0)
            return $orderIncrementIds; //nothing to update


        foreach ($messagesCollection as $message) {
            $orderIncrementIds[$message->getVendorId()][] = $message->getPoIncrementId();
        }

        return $orderIncrementIds;
    }


    public function getGhApiVendorOrders($vendorId, $orderIncrementIds)
    {
        $orders = array();
        $connector = $this->getGHAPIConnector();
        $doLoginResponse = $connector->doLoginRequest($vendorId);

        if (!$doLoginResponse->status)
            return $orders; //Can't login

        $token = $doLoginResponse->token;

        $getOrdersByIDResponse = $connector->getOrdersByIDRequest($token, $orderIncrementIds);

        if (!$getOrdersByIDResponse->status)
            return $orders; //Can't get orders info

        $orderList = $getOrdersByIDResponse->orderList->order;

        if (is_array($orderList)) {
            $orders = $orderList;
        }
        if (is_object($orderList)) {
            $orders[] = $orderList;
        }

        return $orders;
    }
}