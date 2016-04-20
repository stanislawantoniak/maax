<?php

class Zolago_Dropship_Block_Adminhtml_Vendor_Grid extends ZolagoOs_OmniChannel_Block_Adminhtml_Vendor_Grid {
    protected function _prepareColumns()
    {
        $hlp = Mage::helper('udropship');

        $this->addColumnAfter('sequence', array(
            'header'    => $hlp->__('Sequence'),
            'align'     => 'right',
            'width'     => '50px',
            'index'     => 'sequence',
            'type'      => 'number',
        ),'carrier_code');
        $this->addColumnAfter('url_key', array(
            'header'    => $hlp->__('URL key'),
            'align'     => 'right',
            'width'     => '180px',
            'index'     => 'url_key',
            'type'      => 'text',
        ),'carrier_code');
        $this->addColumnAfter('vendor_type', array(
            'header'    => $hlp->__('Vendor type'),
            'align'     => 'right',
            'width'     => '20px',
            'index'     => 'vendor_type',
            'type'      => 'options',
            'options'=> Mage::getSingleton('zolagodropship/source')->setPath('vendorstype')->toOptionHash()
        ),'carrier_code');

        return parent::_prepareColumns();
    }
}