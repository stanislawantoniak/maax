<?php
class Zolago_Po_Block_Adminhtml_Po_Grid extends Unirgy_DropshipPo_Block_Adminhtml_Po_Grid
{
	
    protected function _prepareColumns()
    {
        $this->addColumnAfter('default_pos_name', array(
            'header'    => Mage::helper('zolagopos')->__('POS'),
            'index'     => 'default_pos_name',
            'type'      => 'text',
        ), "order_increment_id");

        return parent::_prepareColumns();
    }


}
