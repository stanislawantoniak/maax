<?php
/**
 * order shipments integrator for one vendor
 */
class ZolagoOs_IAIShop_Model_Integrator_Shipment extends ZolagoOs_IAIShop_Model_Integrator_Abstract {
    
    /**
     * save shipment in magento
     */
    protected function _setShipment($id,$courier,$trackNumber,$deliveryDate) {
       
        // collect order
        $connector = $this->getConnector();
        $params = new StdClass();
        $params->sessionToken = $this->_getToken();
        $params->orderID = new StdClass();
        $params->orderID->ID = array($id);
        $connector->setOrderAsCollected($params);
        
        $params = new StdClass();
        $params->orderID = $id;
        $params->sessionToken = $this->_getToken();
        $params->courier = $courier;
        $params->dateShipped = $deliveryDate;
        $params->shipmentTrackingNumber = $trackNumber;
        $response = $connector->setOrderShipment($params); 
        $helper = $this->getHelper();
        if (empty($response->status)) {
            if (!empty($response->message)) {
                $this->log($helper->__('Błąd API : %s',$response->message));
                $helper->fileLog($helper->__('Błąd API: %s [zamówienie %s, vendor %s, courier %s]',$response->message,$id,$this->getVendor()->getId(),$courier));
            } else {
                $this->log($helper->__('Błąd API: %s',$helper->__('Błędna odpowiedź z API')));                
            }
            return false;
        } else {
            $this->log($helper->__('Parametry dostawy dla zamówienia %s zapisane poprawnie',$id));                            
            return true;
        } 
    }

    /**
     * sync orders
     */
    public function sync() {
         $apiOrders = $this->_getOrdersExternalId();
         if (!count($apiOrders)) {
             return false;
         }
        $orders = $this->_getIAIOrders($apiOrders);
        
        foreach ($apiOrders as $incrementId => $snOrder) {
            if (isset($orders[$snOrder])) {
                $order = $orders[$snOrder];
                if (!empty($order->delivery_identifier) && !empty($order->deliverer_id)) {
                    $this->_setShipment(
                        $incrementId,
                        Mage::helper('zosiaishop')->getMappedCarrier($order->deliverer_id),
                        $order->delivery_identifier,
                        !empty($order->delivery_date)? $order->delivery_date: Mage::getModel('core/date')->date('Y-m-d H:i:s')
                    );
                }
            }
        }
    }
    
    /**
     * list of integrated orders without active shipment
     */
     protected function _getOrdersExternalId() {
         $collection = Mage::getModel('udpo/po')->getCollection();
         $status = Mage::getModel('zolagopo/po_status');
         $collection->getSelect()->joinLeft(
             array('ship' => $collection->getTable('sales/shipment')),
             'ship.udpo_id = main_table.entity_id and ship.udropship_status <> \''.ZolagoOs_OmniChannel_Model_Source::SHIPMENT_STATUS_CANCELED.'\'',
             array())
             ->where('ship.entity_id IS NULL')
             ->where('main_table.external_order_id IS NOT NULL')
             ->where('main_table.udropship_vendor = ?',$this->getVendor()->getId())
             ->where('main_table.udropship_status not in (?)',
                    $status::getFinishStatuses()
                );
        $list = array();
        foreach ($collection as $item) {
            // accept orders 
            if ($item->getUdropshipStatus() == ZolagoOs_OmniChannelPo_Model_Source::UDPO_STATUS_ACK) {             
                $po = Mage::getModel('udpo/po')->load($item->getId());
                $status->processConfirmRelease($po);
            }
            $list[$item->getIncrementId()] = $item->getExternalOrderId();
        }
        return $list;        
     }
    /**
     * list of non closed orders in iai
     */
     protected function _getIAIOrders($apiOrders) {
         $iaiConnector = Mage::getModel("zosiaishop/client_connector");
         $iaiConnector->setVendorId($this->getVendor()->getId());
         $orders = $iaiConnector->getOrders($apiOrders);
         $orderToProcess = array();
         if (isset($orders->orders)) {
             if (is_array($orders->orders)) {
                 foreach ($orders->orders as $order) {
                     if (isset($order->sn) &&
                         isset($order->order_details) &&
                         isset($order->order_details->dispatch)) {
                             $orderToProcess[$order->sn] = $order->order_details->dispatch;
                    } else {                                                
                         $this->getHelper()->fileLog($order);
                    }
                 }
             } else {
                 $this->log($this->getHelper()->__('Zamówienia nie istnieją w IAI Shop'));
                 $this->getHelper()->fileLog($orders->orders);
             }
         } else {
             $this->log($this->getHelper()->__('Zamówienia nie istnieją w IAI Shop'));
             $this->getHelper()->fileLog($orders);
         }
         return $orderToProcess;
     }


}