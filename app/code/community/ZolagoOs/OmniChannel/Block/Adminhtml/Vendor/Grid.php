<?php
/**
  
 */

class ZolagoOs_OmniChannel_Block_Adminhtml_Vendor_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('vendorGrid');
        $this->setDefaultSort('vendor_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('vendor_filter');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('udropship/vendor')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

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
                        'url'     => array('base'=>'zolagoosadmin/adminhtml_vendor/edit'),
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

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('vendor');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'=> Mage::helper('udropship')->__('Delete'),
             'url'  => $this->getUrl('*/*/massDelete'),
             'confirm' => Mage::helper('udropship')->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('udropship')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'status' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('udropship')->__('Status'),
                         'values' => Mage::getSingleton('udropship/source')->setPath('vendor_statuses')->toOptionArray(true),
                     )
             )
        ));

        $this->getMassactionBlock()->addItem('carrier_code', array(
            'label'=> Mage::helper('udropship')->__('Change Preferred Carrier'),
            'url'  => $this->getUrl('*/*/massCarrierCode', array('_current'=>true)),
            'additional' => array(
                'carrier_code' => array(
                    'name' => 'carrier_code',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('udropship')->__('Preferred Carrier'),
                    'values' => Mage::getSingleton('udropship/source')->setPath('carriers')->toOptionHash(true),
                )
            )
        ));

        if (Mage::helper('udropship')->isUdsprofileActive()) {
            $this->getMassactionBlock()->addItem('shipping_profile', array(
                'label'=> Mage::helper('udropship')->__('Change Shipping Profile'),
                'url'  => $this->getUrl('*/*/massShippingProfile', array('_current'=>true)),
                'additional' => array(
                    'shipping_profile' => array(
                        'name' => 'shipping_profile',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => Mage::helper('udropship')->__('Shipping Profile'),
                        'values' => Mage::getSingleton('udsprofile/source')->setPath('profiles')->toOptionHash(true),
                    )
                )
            ));
        }

        Mage::dispatchEvent('udropship_adminhtml_vendor_grid_prepare_massaction', array('grid'=>$this));

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}
