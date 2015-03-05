<?php

class Zolago_Dropship_Block_Adminhtml_Vendor_Grid extends Unirgy_Dropship_Block_Adminhtml_Vendor_Grid {
    protected function _prepareColumns()
    {
        $hlp = Mage::helper('udropship');
        $this->addColumn('vendor_id', array(
            'header'    => $hlp->__('Vendor ID'),
            'align'     => 'right',
            'width'     => '50px',
            'index'     => 'vendor_id',
            'type'      => 'number',
        ));

        $this->addColumn('vendor_name', array(
            'header'    => $hlp->__('Vendor Name'),
            'index'     => 'vendor_name',
        ));

        $this->addColumn('email', array(
            'header'    => $hlp->__('Email'),
            'index'     => 'email',
        ));

        if ($hlp->isModuleActive('ustockpo')) {
            $this->addColumn('distributor_id', array(
                'header' => Mage::helper('ustockpo')->__('Distributor'),
                'index' => 'distributor_id',
                'type' => 'options',
                'options' => Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionHash(),
            ));
        }

        $this->addColumn('carrier_code', array(
            'header'    => $hlp->__('Used Carrier'),
            'index'     => 'carrier_code',
            'type'      => 'options',
            'options'   => Mage::getSingleton('udropship/source')->setPath('carriers')->toOptionHash(),
        ));
        $this->addColumn('vendor_type', array(
            'header'    => $hlp->__('Vendor type'),
            'align'     => 'right',
            'width'     => '50px',
            'index'     => 'vendor_type',
            'type'      => 'options',
            'options'=> Mage::getSingleton('zolagodropship/source')->setPath('vendorstype')->toOptionHash()
        ));

        $this->addColumn('url_key', array(
            'header'    => $hlp->__('URL key'),
            'align'     => 'right',
            'width'     => '50px',
            'index'     => 'url_key',
            'type'      => 'text',
        ));
        $this->addColumn('sequence', array(
            'header'    => $hlp->__('Sequence'),
            'align'     => 'right',
            'width'     => '50px',
            'index'     => 'sequence',
            'type'      => 'number',
        ));
        if (Mage::helper('udropship')->isUdsprofileActive()) {
            $this->addColumn('shipping_profile', array(
                'header'    => $hlp->__('Shipping Profile'),
                'index'     => 'shipping_profile',
                'type'      => 'options',
                'options'   => Mage::getSingleton('udsprofile/source')->setPath('profiles')->toOptionHash(),
            ));
        }

        $this->addColumn('status', array(
            'header'    => $hlp->__('Status'),
            'index'     => 'status',
            'type'      => 'options',
            'options'   => Mage::getSingleton('udropship/source')->setPath('vendor_statuses')->toOptionHash(),
        ));

        $this->addColumn('action',
            array(
                'header'    => Mage::helper('sales')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('sales')->__('View'),
                        'url'     => array('base'=>'udropshipadmin/adminhtml_vendor/edit'),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true
            ));

        Mage::dispatchEvent('udropship_adminhtml_vendor_grid_prepare_columns', array('grid'=>$this));

        $this->addExportType('*/*/exportCsv', Mage::helper('adminhtml')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('adminhtml')->__('XML'));
        return parent::_prepareColumns();
    }
}