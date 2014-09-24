<?php
/**
 * @method Mage_Sales_Model_Resource_Order_Collection getOrders() 
 */
class Zolago_Modago_Block_Sales_Order_View extends Mage_Sales_Block_Order_View {
    
    //{{{ 
    /**
     * po items
     * @return 
     */
    public function getItems() {
        $order = $this->getOrder();
        foreach ($order->getShipmentsCollection() as $shipment) {
            var_dump($shipment);
        }
    }
    //}}}
}
