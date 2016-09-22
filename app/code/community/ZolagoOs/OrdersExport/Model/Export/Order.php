<?php
/**
 *
 */
class ZolagoOs_OrdersExport_Model_Export_Order extends ZolagoOs_OrdersExport_Model_Export_Ghapi {


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
    public function sync() {
        $orders = $this->getGhApiVendorOrders();
        

    }
    


}