<?php

/**
 * Class ZolagoOs_OrdersExport_Model_Integrator_Order
 */
class ZolagoOs_OrdersExport_Model_Integrator_Order
    extends ZolagoOs_OrdersExport_Model_Integrator_Ghapi
{

    /**
     * @return mixed
     */
    public function getExportConnector()
    {
        return Mage::getModel("zosordersexport/export_order");
    }

    /**
     * prepare new orders list
     *
     * @return array
     */

    public function getGhApiVendorOrders()
    {
        return $this->getGhApiVendorMessages(GH_Api_Model_System_Source_Message_Type::GH_API_MESSAGE_NEW_ORDER);
    }


    /**
     * sync orders
     */
    public function sync()
    {
        $orders = $this->getGhApiVendorOrders();

        /* @var $exportConnector ZolagoOs_OrdersExport_Model_Export_Order */
        $exportConnector = $this->getExportConnector();

        if (!$orders->status)
            return $this;

        $ordersList = $orders->list;

        if (empty($ordersList))
            return $this;

        foreach ($this->prepareOrderList($ordersList) as $item) {
            $response = $exportConnector->addOrders((array)$item);

            if ($response->status){
                $this->addOrderToConfirmMessage($item->order_id);
                $this->fileLog("ORDER " . $item->order_id. " WAS EXPORTED", Zend_Log::INFO);
            }


        }

        //$this->confirmOrderMessages($ordersList);

    }


}