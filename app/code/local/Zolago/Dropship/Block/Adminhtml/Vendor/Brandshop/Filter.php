<?php
/**
 * filter renderer for yes/no columns
 */


class Zolago_Dropship_Block_Adminhtml_Vendor_Brandshop_Filter extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{

    protected function _getOptions()
    {
        return array(
            array(
                'value' =>  '',
                'label' =>  ''
            ),
            array(
                'value' =>  1,
                'label' =>  Mage::helper('zolagodropship')->__('Yes')
            ),
            array(
                'value' =>  0,
                'label' =>  Mage::helper('zolagodropship')->__('No')
            )
        );
    }

}
