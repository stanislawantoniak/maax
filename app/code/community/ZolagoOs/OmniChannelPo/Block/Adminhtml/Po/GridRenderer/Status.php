<?php
/**
  
 */
 
class ZolagoOs_OmniChannelPo_Block_Adminhtml_Po_GridRenderer_Status extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        return $row->getUdropshipStatusName();
    }

}