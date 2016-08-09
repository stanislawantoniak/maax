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
    	$this->sendOrder();
		$this->addPayment();
		$this->getShipping();
    }

	private function sendOrder()
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


		//3. Add orders to IAI-Shop API
		/** @var ZolagoOs_IAIShop_Helper_Data $helper */
		$helper = Mage::helper("zosiaishop");
		$response = $helper->addOrders($orders);
    }

	private function addPayment()
	{
		echo "<br>addPayment()";
		echo "<br>" . 4;
		//1. Get paymentDataChanged messages from GH_API
		$paymentIncrementIdsByVendor = $this->getPaymentMessages();

		if (empty($paymentIncrementIdsByVendor))
			return;

		echo "<br>" . 5;
    }

	private function getShipping()
	{
		
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
			if ((bool) Mage::helper('udropship')->getVendor($message->getVendorId())->getIaishopId())
            	$orderIncrementIds[$message->getVendorId()][] = $message->getPoIncrementId();
        }

        return $orderIncrementIds;
    }


    public function getGhApiVendorOrders($vendorId, $orderIncrementIds)
    {
        //ini_set("soap.wsdl_cache_enabled", 0);
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

	public function getPaymentMessages()
	{
		$paymentIncrementIds = array();

		$messagesCollection = Mage::getModel("ghapi/message")
			->getCollection();

		$messagesCollection
			->addFieldToFilter("message", "paymentDataChanged")
			->setOrder('po_increment_id', 'DESC')
			->setOrder('message_id', 'ASC')
			->setOrder('vendor_id', 'ASC');

		$messagesCollection->getSelect()->limit(self::IAISHOP_SYNC_ORDERS_BATCH);

		if ($messagesCollection->count() <= 0)
			return $paymentIncrementIds; //nothing to update


		foreach ($messagesCollection as $message) {
			if ((bool) Mage::helper('udropship')->getVendor($message->getVendorId())->getIaishopId())
				$paymentIncrementIds[$message->getVendorId()][] = $message->getPoIncrementId();
		}

		return $paymentIncrementIds;
	}

	public function getGhApiNewPayment($vendorId)
	{
		//ini_set("soap.wsdl_cache_enabled", 0);
		$orders = array();
		$connector = $this->getGHAPIConnector();
		$doLoginResponse = $connector->doLoginRequest($vendorId);

//		paymentDataChanged
    }
}