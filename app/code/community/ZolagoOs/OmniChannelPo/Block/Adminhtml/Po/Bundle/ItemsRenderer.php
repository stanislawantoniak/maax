<?php
/**
  
 */

class ZolagoOs_OmniChannelPo_Block_Adminhtml_Po_Bundle_ItemsRenderer extends Mage_Bundle_Block_Adminhtml_Sales_Order_Items_Renderer
{
    public function getChilds($item)
    {
        $_itemsArray = array();
        $_items = $item->getPo()->getAllItems();

        if ($_items) {
            foreach ($_items as $_item) {
                if ($parentItem = $_item->getOrderItem()->getParentItem()) {
                    $_itemsArray[$parentItem->getId()][$_item->getOrderItemId()] = $_item;
                } else {
                    $_itemsArray[$_item->getOrderItem()->getId()][$_item->getOrderItemId()] = $_item;
                }
            }
        }

        if (isset($_itemsArray[$item->getOrderItem()->getId()])) {
            return $_itemsArray[$item->getOrderItem()->getId()];
        } else {
            return null;
        }
    }
    public function getOrderItem()
    {
        if ($this->getItem() instanceof Mage_Sales_Model_Order_Item) {
            return $this->getItem();
        } else {
            return $this->getItem()->getOrderItem();
        }
    }
}