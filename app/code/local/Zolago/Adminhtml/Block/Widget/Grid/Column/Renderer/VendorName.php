<?php

class Zolago_Adminhtml_Block_Widget_Grid_Column_Renderer_VendorName
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', "udropship_vendor");

        $options = array();
        foreach ($attribute->getSource()->getAllOptions(false, true) as $option) {
            $options[(int)$option['value']] = $option['label'];
        }
        return $options[(int)$row->getData("udropship_vendor")];
    }

}