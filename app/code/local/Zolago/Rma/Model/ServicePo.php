<?php
// http://zolago01.dev/index.php/sales/po/saveRma/po_id/106
class Zolago_Rma_Model_ServicePo extends Unirgy_Rma_Model_ServiceOrder
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
    public function prepareRmaForSave($qtys = array(), $conditions=array())
    {
        $totalQtys = array();
        $rmaItems = array();
		

        foreach ($this->_po->getItemsCollection() as $poItem) {
			/* @var $poItem Zolago_Po_Model_Po_Item */
			$orderItem = $poItem->getOrderItem();
            if (!$this->_canRmaItem($poItem, $qtys)) {
                continue;
            }
			
            $item = $this->_convertor->itemToRmaItem($poItem);
			
            if ($orderItem->isDummy(true)) {
                $qty = 1;
            } else {
                if (isset($qtys[$poItem->getId()])) {
                    $qty = min($qtys[$poItem->getId()], $poItem->getQtyShipped());
                } elseif (!count($qtys)) {
                    $qty = $poItem->getQtyShipped();
                } else {
                    continue;
                }
            }
			
            if ($qty<=0) continue;
			
            $vId = $poItem->getUdropshipVendor();

            $rmaItems[$vId][] = $item;

            if (empty($totalQtys[$vId])) {
                $totalQtys[$vId] = 0;
            }
            $totalQtys[$vId] += $qty;

            $item->setQty($qty);

            $item->setItemCondition(@$conditions[$poItem->getId()]);

        }
        if (empty($rmaItems)) {
            Mage::throwException(
                Mage::getStoreConfig('urma/message/customer_no_items')
            );
        }
        foreach ($rmaItems as $vId=>$items) {
            if (empty($items)) continue;
            $shipment = null;
            foreach ($this->_order->getShipmentsCollection() as $_shipment) {
                if ($_shipment->getUdropshipVendor()==$vId) {
                    $shipment = $_shipment;
                    break;
                }
            }
            if (null == $shipment) continue;
            $rma = $this->_convertor->toRma($this->_po);
            $rma->setUdropshipVendor($vId);
            $rma->setUdropshipMethod($shipment->getUdropshipMethod());
            $rma->setUdropshipMethodDescription($shipment->getUdropshipMethodDescription());
            $rma->setTotalQty($totalQtys[$vId]);
            $rmas[$vId] = $rma;
            foreach ($items as $item) {
                $rma->addItem($item);
            }

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