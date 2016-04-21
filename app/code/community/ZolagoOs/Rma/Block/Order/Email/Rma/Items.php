<?php

/**
 * Sales Order Email Shipment items
 */
class ZolagoOs_Rma_Block_Order_Email_Rma_Items extends Mage_Sales_Block_Items_Abstract
{
    /**
     * Prepare item before output
     *
     * @param Mage_Core_Block_Abstract $renderer
     * @return Mage_Sales_Block_Items_Abstract
     */
    protected function _prepareItem(Mage_Core_Block_Abstract $renderer)
    {
        $renderer->getItem()->setOrder($this->getOrder());
        $renderer->getItem()->setSource($this->getRma());
    }
}
