<?php
/**
 * orders integrator for one vendor
 */
class ZolagoOs_IAIShop_Model_Integrator_Order extends ZolagoOs_IAIShop_Model_Integrator_Ghapi {
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
     * process response from iaishop
     */
    public function processResponse($responseList,$orderId) {
        foreach ($responseList as $item) {
            if (empty($item->faultCode)) {
                if (!empty($item->order_sn)) {
                    $po = Mage::getModel('udpo/po')->loadByIncrementId($orderId);
                    if ($po) {
                        $po->setExternalOrderId($item->order_sn)
                        ->save();
                        $po->addComment(Mage::helper('zosiaishop')->__('Zamówienie %s zostało zaimportowane do IAI Shop pod numerem %s (%s)',$orderId,$item->order_sn,$item->order_id),false,true)
                            ->saveComments();
                        $this->addOrderToConfirmMessage($orderId);
                        $this->log($this->getHelper()->__('Zamówienie %s zostało zaimportowane do IAI Shop pod numerem %s (%s)',$orderId,$item->order_sn,$item->order_id));
                        return $item->order_sn;
                    } else {
                        $this->log($this->getHelper()->__('Zamówienie %s nie istnieje w systemie',$orderId));
                    }
                    break;
                } else {
                    $this->getHelper()->fileLog($item);
                    $this->log($this->getHelper()->__('Zamówienie %s nie otrzymało numeru w IAI Api',$orderId));
                }
            } else {
                $this->getHelper()->fileLog($item);
                $this->log($this->getHelper()->__('Błąd IAI Api nr %d dla zamówienia %s: %s',$item->faultCode,$orderId,$item->faultString));
            }
        }
        return null;
    }
    /**
     * sync orders
     */
    public function sync() {
        $orders = $this->getGhApiVendorOrders();
        $iaiConnector = Mage::getModel("zosiaishop/client_connector");
        $iaiConnector->setVendorId($this->getVendor()->getId());
        if ($orders->status) {
            foreach ($this->prepareOrderList($orders->list) as $item) {
                if (empty($item->external_order_id)) {
                    $response = $iaiConnector->addOrders(array($item));
                    if (!empty($response->result->orders)) {
                        $sn = $this->processResponse($response->result->orders,$item->order_id);
                        $iaiConnector->addComment($sn,$item->order_id);
                    }
                } else {
                    $this->addOrderToConfirmMessage($item->order_id);
                }
            }
            $this->confirmOrderMessages($orders->list);
        }

    }
    
}