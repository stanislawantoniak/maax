<?php
/**
  
 */

class ZolagoOs_OmniChannelPo_Block_OrderItemsRenderer_Bundle extends Mage_Bundle_Block_Sales_Order_Items_Renderer
{
    protected function _prepareItem(Mage_Core_Block_Abstract $renderer)
    {
        $renderer->getItem()->setOrder($this->getOrder());
        $renderer->getItem()->setSource($this->getPo());
    }
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
}