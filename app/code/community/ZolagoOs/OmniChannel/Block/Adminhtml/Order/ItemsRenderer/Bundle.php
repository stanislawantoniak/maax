<?php
/**
  
 */

class ZolagoOs_OmniChannel_Block_Adminhtml_Order_ItemsRenderer_Bundle
    extends Mage_Bundle_Block_Adminhtml_Sales_Order_View_Items_Renderer
{
    public function getItemOrderOptions($item)
    {
        $result = array();
        if ($item instanceof Mage_Sales_Model_Order_Item) {
            $options = $item->getProductOptions();
        } else {
            $options = $item->getOrderItem()->getProductOptions();
        }
        if ($options) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (!empty($options['attributes_info'])) {
                $result = array_merge($options['attributes_info'], $result);
            }
        }
        return $result;
    }
}