<?php

class Snowdog_Freshmail_Block_Adminhtml_Request_Grid_Renderer_Status
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Decorate request status
     */
    public function render(Varien_Object $row)
    {
        return Mage::helper('snowfreshmail')->decorateItemStatus($row);
    }
}
