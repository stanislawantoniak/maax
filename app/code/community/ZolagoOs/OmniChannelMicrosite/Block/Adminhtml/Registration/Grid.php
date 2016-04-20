<?php
/**
  
 */

class ZolagoOs_OmniChannelMicrosite_Block_Adminhtml_Registration_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('registrationGrid');
        $this->setDefaultSort('reg_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('reg_filter');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('umicrosite/registration')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $hlp = Mage::helper('umicrosite');
        $this->addColumn('reg_id', array(
            'header'    => $hlp->__('Registration ID'),
            'align'     => 'right',
            'width'     => '50px',
            'index'     => 'reg_id',
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

        $this->addColumn('carrier_code', array(
            'header'    => $hlp->__('Used Carrier'),
            'index'     => 'carrier_code',
            'type'      => 'options',
            'options'   => Mage::getSingleton('udropship/source')->setPath('carriers')->toOptionHash(),
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('adminhtml')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('adminhtml')->__('XML'));
        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('reg_id' => $row->getId()));
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

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}
