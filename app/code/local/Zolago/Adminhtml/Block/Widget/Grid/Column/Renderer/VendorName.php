<?php

class Zolago_Adminhtml_Block_Widget_Grid_Column_Renderer_VendorName
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        $options = $this->getColumn()->getData('options');
        if (isset($options[(int)$row->getData("udropship_vendor")])) {
            return $options[(int)$row->getData("udropship_vendor")];
        }
        return '';
    }

}