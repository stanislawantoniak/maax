<?php
/**
 * po item block
 */
class Zolago_Modago_Block_Sales_Order_Item extends ZolagoOs_Rma_Block_Order_Rma_Items {
    //{{{ 
    /**
     * name of renderer
     * @return string
     */
    public function getRendererType() {
        return 'default';
    }
    //}}}

    //{{{ 
    /**
     * 
     * @param Zolago_Po_Model_Po
     * @return 
     */
    public function getItemHtml(Varien_Object $item)
    {
        $renderer = $this->getItemRenderer($item->getRendererType())->setItem($item);
        return $renderer->toHtml();
    }
    //}}}
}