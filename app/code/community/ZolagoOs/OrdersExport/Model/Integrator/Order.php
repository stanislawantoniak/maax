<?php

/**
 * Class ZolagoOs_OrdersExport_Model_Integrator_Order
 */
class ZolagoOs_OrdersExport_Model_Integrator_Order
    extends ZolagoOs_OrdersExport_Model_Integrator_Ghapi
{


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


        if (!$orders->status)
            return;

        $ordersList = $orders->list;


        if (empty($ordersList))
            return;

        foreach ($this->prepareOrderList($ordersList) as $item) {

        }

       

    }


}