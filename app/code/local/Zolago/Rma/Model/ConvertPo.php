<?php

class Zolago_Rma_Model_ConvertPo extends Mage_Sales_Model_Convert_Order
{
    public function toRma(Zolago_Po_Model_Po $po)
    {
		$order = $po->getOrder();
        $rma = Mage::getModel('urma/rma');
        $rma->setOrder($order)
			->setPo($po)
            ->setStoreId($po->getStoreId())
            ->setCustomerId($po->getCustomerId())
            ->setBillingAddressId($po->getBillingAddressId())
            ->setShippingAddressId($po->getShippingAddressId());

        Mage::helper('core')->copyFieldset('sales_convert_order', 'to_urma', $order, $rma);
        Mage::helper('core')->copyFieldset('zolagopo_convert_po', 'to_urma', $po, $rma);
        return $rma;
    }
    public function itemToRmaItem(Zolago_Po_Model_Po_Item $poItem)
    {
		$item = $poItem->getOrderItem();
        $rmaItem = Mage::getModel('urma/rma_item');
        $rmaItem->setOrderItem($item)
			->setPoItem($poItem)
            ->setProductId($item->getProductId());
        Mage::helper('core')->copyFieldset('sales_convert_order_item', 'to_urma_item', $item, $rmaItem);
        Mage::helper('core')->copyFieldset('zolagopo_convert_po_item', 'to_urma_item', $poItem, $rmaItem);
        $rmaItem->setName($poItem->getName());
        $rmaItem->setPrice($poItem->getPriceInclTax());
        return $rmaItem;
    }
}