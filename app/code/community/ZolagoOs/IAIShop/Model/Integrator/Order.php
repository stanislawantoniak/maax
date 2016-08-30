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
     * process response from getProductsStock
     */
    protected function _processStockResponse($result) {	
        $vendor = $this->getVendor();
        $posList = $vendor->getActivePos();
        $externalList = array();
        foreach ($posList as $pos) {
            if ($extId = $pos['external_id']) {
                $externalList[$extId] = $extId;
            }
        }
        $list = array();
        if (empty($result->results)) {
            $this->getHelper()->fileLog($result);
            $this->log($this->getHelper()->__('Brak stanów magazynowych w IAI Shop'));
            return $list;
        }
        foreach ($result->results as $object) {
            if (isset($object->quantities->stocks)) { 
                foreach ($object->quantities->stocks as $stock) {
                    if (isset($stock->stock_id)
                        && !empty($externalList[$stock->stock_id])
                        && isset($stock->sizes)) {
                        foreach ($stock->sizes as $size) {
                            if (isset($size->product_sizecode)) {
                                $list[$size->product_sizecode][$stock->stock_id] = empty($size->quantity)? 0:$size->quantity;
                            }
                        }
                    }
                }
            }
        }
        return $list;
        
    }
    
    /**
     * find quantity
     */
    protected function _findPos($item,$warehouse,$defaultId) {
        if (!isset($warehouse[$item->item_sku])) {
            return $defaultId;
        }
        $qty = $item->item_qty;
        $stock = $warehouse[$item->item_sku];
        $return = $defaultId;
        foreach ($stock as $id=>$stockQty) {            
            if ($id == $defaultId 
                && $stockQty >= $qty) {
                return $id; // default has highest priority
            }
            if ((float)$stockQty >= (float)$qty) {
                $return = $id;
            }
        }
        return $return;
    }
    /**
     * assign stock_id for products from IAI shop
     */
    protected function _getProductsStock(&$list,$defaultPos) {
        $iaiConnector = $this->getIaiConnector();
        $params = array();
        $params['products'] = array();
        foreach ($list as $item) {
            if (empty($item->is_delivery_item)) {
                $params['products'][] = array(
                    'identType' => 'codeExtern',
                    'identValue' => $item->item_sku,
                );
            }
        }        
        $result = $iaiConnector->getProductsStocks($params);
        $warehouse = $this->_processStockResponse($result);        
        foreach ($list as &$item) {
            if (empty($item->is_delivery_item)) {
                $item->pos_id = $this->_findPos($item,$warehouse,$defaultPos);
            }
        }
    }
    /**
     * sync orders
     */
    public function sync() {
        $orders = $this->getGhApiVendorOrders();
        $iaiConnector = $this->getIaiConnector();
        if ($orders->status) {
            foreach ($this->prepareOrderList($orders->list) as $item) {
                if (empty($item->external_order_id)) {
                    if ($email = $this->getVendor()->getData('zosiaishop_vendor_gallery_email')) {
                        $item->email = sprintf($email,$item->order_id);
                    }
                    $this->_getProductsStock($item->order_items,$item->pos_id);                    
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