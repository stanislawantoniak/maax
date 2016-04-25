<?php
/**
  
 */

class ZolagoOs_OmniChannelPayout_Block_Adminhtml_Payout_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('payoutGrid');
        $this->setDefaultSort('payout_id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('payout_filter');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('udpayout/payout')->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $baseUrl = $this->getUrl();

        $this->addColumn('payout_id', array(
            'header'    => Mage::helper('udropship')->__('ID'),
            'index'     => 'payout_id',
            'width'     => 10,
            'type'      => 'number',

        ));
        
        $this->addColumn('statement_id', array(
            'header'    => Mage::helper('udropship')->__('Statement ID'),
            'index'     => 'statement_id',
        ));

        $this->addColumn('vendor_id', array(
            'header' => Mage::helper('udropship')->__('Vendor'),
            'index' => 'vendor_id',
            'type' => 'options',
            'options' => Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionHash(),
            'filter' => 'udropship/vendor_gridColumnFilter'
        ));

        $this->addColumn('payout_type', array(
            'header' => Mage::helper('udropship')->__('Payout Type'),
            'index' => 'payout_type',
            'type' => 'options',
            'options' => Mage::getSingleton('udpayout/source')->setPath('payout_type_internal')->toOptionHash(),
        ));

        $this->addColumn('payout_status', array(
            'header' => Mage::helper('udropship')->__('Payout Status'),
            'index' => 'payout_status',
            'type' => 'options',
            'options' => Mage::getSingleton('udpayout/source')->setPath('payout_status')->toOptionHash(),
        ));

        $this->addColumn('transaction_id', array(
            'header'    => Mage::helper('udropship')->__('Transaction ID'),
            'index'     => 'transaction_id',
        ));
        
        $this->addColumn('total_orders', array(
            'header'    => Mage::helper('udropship')->__('# of Orders'),
            'index'     => 'total_orders',
            'type'      => 'number',
        ));
        
        $this->addColumn('total_payout', array(
            'header' => Mage::helper('udpayout')->__('Total Payout'),
            'index' => 'total_payout',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));
        
        $this->addColumn('total_paid', array(
            'header' => Mage::helper('udpayout')->__('Total Paid'),
            'index' => 'total_paid',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));
        
        $this->addColumn('total_due', array(
            'header' => Mage::helper('udpayout')->__('Total Due'),
            'index' => 'total_due',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));

        $this->addColumn('created_at', array(
            'header'    => Mage::helper('udropship')->__('Created At'),
            'index'     => 'created_at',
            'type'      => 'datetime',
            'width'     => 150,
        ));

        $this->addColumn('updated_at', array(
            'header'    => Mage::helper('udropship')->__('Updated At'),
            'index'     => 'updated_at',
            'type'      => 'datetime',
            'width'     => 150,
        ));

        $this->addColumn('scheduled_at', array(
            'header'    => Mage::helper('udropship')->__('Scheduled At'),
            'index'     => 'scheduled_at',
            'type'      => 'datetime',
            'width'     => 150,
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('adminhtml')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('adminhtml')->__('XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('payout_id');
        $this->getMassactionBlock()->setFormFieldName('payout');

        $this->getMassactionBlock()->addItem('pay', array(
             'label'=> Mage::helper('udropship')->__('Pay'),
             'url'  => $this->getUrl('*/*/massPay'),
             'confirm' => Mage::helper('udropship')->__('Are you sure?')
        ));
        
        $this->getMassactionBlock()->addItem('delete', array(
             'label'=> Mage::helper('udropship')->__('Delete'),
             'url'  => $this->getUrl('*/*/massDelete'),
             'confirm' => Mage::helper('udropship')->__('Are you sure?')
        ));

        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}
