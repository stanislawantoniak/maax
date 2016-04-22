<?php

class ZolagoOs_OmniChannelTierCommission_Block_Adminhtml_PayoutEditTabRows extends ZolagoOs_OmniChannelPayout_Block_Adminhtml_Payout_Edit_Tab_Rows
{
    protected function _prepareColumns()
    {
        $this->addColumn('sku', array(
            'header'    => Mage::helper('udropship')->__('SKU'),
            'index'     => 'sku'
        ));
        $this->addColumn('product', array(
            'header'    => Mage::helper('udropship')->__('Product'),
            'index'     => 'product'
        ));
        $this->addColumnsOrder('sku', 'po_increment_id');
        $this->addColumnsOrder('product', 'sku');
        return parent::_prepareColumns();
    }
}