<?php
/**
  
 */
 
class ZolagoOs_OmniChannelPo_Block_Adminhtml_Po_GridRenderer_VendorName extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        $vId = $row->getData($this->getColumn()->getIndex());
        $v = Mage::helper('udropship')->getVendor($vId);
        return $v->getId() == $vId ? $v->getVendorName() : $vId;
    }

}