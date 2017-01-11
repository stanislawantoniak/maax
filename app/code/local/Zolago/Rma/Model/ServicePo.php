<?php
// http://zolago01.dev/index.php/sales/po/saveRma/po_id/106
class Zolago_Rma_Model_ServicePo extends ZolagoOs_Rma_Model_ServiceOrder
{
    public function __construct($po)
    {
        $this->_po			= $po;
        $this->_order       = $po->getOrder();
        $this->_convertor   = Mage::getModel('zolagorma/convertPo');
    }
    public function prepareRma($qtys = array())
    {
        $totalQty = 0;
        $rma = $this->_convertor->toRma($this->_po);
        foreach ($this->_order->getAllItems() as $orderItem) {
            if (!$this->_canRmaItem($orderItem, $qtys)) {
                continue;
            }

            $item = $this->_convertor->itemToRmaItem($orderItem);
			
            if ($orderItem->isDummy(true)) {
                $qty = 1;
            } else {
                if (isset($qtys[$orderItem->getId()])) {
                    $qty = min($qtys[$orderItem->getId()], $orderItem->getQtyShipped());
                } elseif (!count($qtys)) {
                    $qty = $orderItem->getQtyShipped();
                } else {
                    continue;
                }
            }

            $totalQty += $qty;
            $item->setQty($qty);
            $rma->addItem($item);
        }
		
        $rma->setTotalQty($totalQty);
        return $rma;
    }
    public function prepareRmaForSave($data = array(), $conditions=array(), $shippingCost=true)
    {
		/** @var Zolago_Po_Model_Po_Item $poItem */
        $rmas = array();
        $items = $data['items_single'];
        $conditions = $data['items_condition_single'];
        $poItems = array();
        $rmaItems = array();
        foreach ($this->_po->getItemsCollection() as $poItem) {
            $poItems[$poItem->getId()] = $poItem;
        }        
        foreach ($items as $itemId=>$itemPack) {
            $poItem = $poItems[$itemId];
            foreach ($itemPack as $packId => $dummy) {
                if (empty($conditions[$itemId][$packId])) {
                    continue;
                }
                $item = $this->_convertor->itemToRmaItem($poItem);
                $item->setQty(1); // only 1 by line
                $item->setItemCondition($conditions[$itemId][$packId]);
	            $item->setCommissionPercent($poItems[$itemId]->getCommissionPercent());
                $vId = $poItem->getUdropshipVendor();                
                $rmaItems[$vId][] = $item;
                if (empty($totalQtys[$vId])) {
                    $totalQtys[$vId] = 0;
                }
                $totalQtys[$vId] ++;
            }
        }

        if (empty($rmaItems)) {
            Mage::throwException(
                Mage::getStoreConfig('urma/message/customer_no_items')
            );
        }

        foreach ($rmaItems as $vId=>$items) {
            if (empty($items)) continue;
            
            $shipment = $this->_po->getLastNotCanceledShipment();
			
            if (null == $shipment) continue;
			
            $rma = $this->_convertor->toRma($this->_po);
			$rma->setShipmentId($shipment->getId());
            $rma->setShipmentIncrementId($shipment->getIncrementId());
            $rma->setUdropshipVendor($vId);
            $rma->setUdropshipMethod($shipment->getUdropshipMethod());
            $rma->setUdropshipMethodDescription($shipment->getUdropshipMethodDescription());
            $rma->setTotalQty($totalQtys[$vId]);
			$rma->addData($data);
            $rmas[$vId] = $rma;
            foreach ($items as $item) {
                $rma->addItem($item);
            }
        }

        //add shipping costs
        foreach($rmas as $rma) {
            /** @var $rma Zolago_Rma_Model_Rma */
            /** @var Zolago_Rma_Model_Rma_Item $shippingRmaItem */
            $shippingRmaItem = Mage::getModel('zolagorma/rma_item');
            $po = $rma->getPo();
            $shippingCostsIncludingTax = ($shippingCost) ? $po->getShippingAmountIncl() : 0;
            $shippingCostsExcludingTax = ($shippingCost) ? $po->getShippingAmount() : 0;
            $shippingRmaItem->setData(array(
                'name' => 'Shipping costs',
                'price' => $shippingCostsIncludingTax,
                'base_cost' => $shippingCostsExcludingTax,
                'qty' => 1,
            ));
            $rma->addItem($shippingRmaItem);
        }
        
        return $rmas;
    }

    protected function _canRmaItem($poItem, $qtys=array())
    {
		/* @var $poItem Zolago_Po_Model_Po_Item */
		$item = $poItem->getOrderItem();
		/* @var $item Mage_Sales_Model_Order_Item */
		
        if ($item->isDummy(true)) {
            if ($item->getHasChildren()) {
                if ($item->isShipSeparately()) {
                    return true;
                }
                foreach ($item->getChildrenItems() as $child) {
                    if ($child->getIsVirtual()) {
                        continue;
                    }
                    if (empty($qtys)) {
                        if ($child->getQtyShipped() > 0) {
                            return true;
                        }
                    } else {
                        if (isset($qtys[$child->getId()]) && $qtys[$child->getId()] > 0) {
                            return true;
                        }
                    }
                }
                return false;
            } else if($item->getParentItem()) {
                $parent = $item->getParentItem();
                if (empty($qtys)) {
                    return $parent->getQtyShipped() > 0;
                } else {
                    return isset($qtys[$parent->getId()]) && $qtys[$parent->getId()] > 0;
                }
            }
        } else {
            return $poItem->getQtyShipped()>0;
        }
    }
}