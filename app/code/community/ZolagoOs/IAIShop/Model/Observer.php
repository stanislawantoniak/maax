<?php

class ZolagoOs_IAIShop_Model_Observer
{
    const IAISHOP_SYNC_ORDERS_BATCH = 10;

    public function syncIAIShop()
    {
        //1. Get newOrder messages from GH_API
        $orderIncrementIdsByVendor = $this->getNewOrderMessages();

        if (empty($orderIncrementIdsByVendor))
            return;

        //2. Get order's info from GH_API
        foreach ($orderIncrementIdsByVendor as $vendorId => $orderIncrementIds) {
            $this->getGhApiVendorOrders($vendorId, $orderIncrementIds);
        }
        $client = new ZolagoOs_IAIShop_Model_GHAPI_Connector();
        $response = $client->doLogin($vendorId);


        //2. Add orders to IAI-Shop API
        /** @var ZolagoOs_IAIShop_Helper_Data $helper */
//        $helper = Mage::helper("zosiaishop");
//        $response = $helper->addOrders($orders);
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
        krumo($vendorId, $orderIncrementIds);

        $client = new ZolagoOs_IAIShop_Model_GHAPI_Connector();
        $vendorId = 1;
        $password = "testtest123";
        $apiKey = "dc893a25bdfe745862ec40ee08a5f047b1a6df547e71c74f3b771d587c99ae07";

        $response = $client->doLogin($vendorId);
//
//        if (!property_exists($response, 'token')) {
//            return;
//        }
//        $token = $response->token;
//        $batchSize = 10;
//        $messageType = "newOrder";
//        $orderId = "";
//        krumo($response->token);
//
//        $getChangeOrderMessage = $client->getChangeOrderMessage(
//            $token,
//            $batchSize,
//            $messageType,
//            $orderId
//        );
//
//        krumo($getChangeOrderMessage);
    }
}