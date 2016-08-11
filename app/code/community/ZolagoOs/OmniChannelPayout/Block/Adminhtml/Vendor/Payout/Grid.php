<?php
/**
  
 */

class ZolagoOs_OmniChannelPayout_Block_Adminhtml_Vendor_Payout_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('udpayout_vendor_payouts');
        $this->setDefaultSort('payout_id');
        $this->setUseAjax(true);
    }

    public function getVendor()
    {
        $vendor = Mage::registry('vendor_data');
        if (!$vendor) {
            $vendor = Mage::getModel('udropship/vendor')->load($this->getVendorId());
            Mage::register('vendor_data', $vendor);
        }
        return $vendor;
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('udpayout/payout')->getCollection()
            ->addFieldToFilter('vendor_id', $this->getVendor()->getId());
        ;

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('pt_grid_payout_id', array(
            'header'    => Mage::helper('udropship')->__('ID'),
            'index'     => 'payout_id',
            'width'     => 10,
            'type'      => 'number',
        ));

        $this->addColumn('pt_grid_payout_type', array(
            'header' => Mage::helper('udropship')->__('Payout Type'),
            'index' => 'payout_type',
            'type' => 'options',
            'options' => Mage::getSingleton('udpayout/source')->setPath('payout_type')->toOptionHash(),
        ));

        $this->addColumn('pt_grid_payout_status', array(
            'header' => Mage::helper('udropship')->__('Payout Status'),
            'index' => 'payout_status',
            'type' => 'options',
            'options' => Mage::getSingleton('udpayout/source')->setPath('payout_status')->toOptionHash(),
        ));

        $this->addColumn('pt_grid_total_orders', array(
            'header'    => Mage::helper('udropship')->__('# of Orders'),
            'index'     => 'total_orders',
            'type'      => 'number',
        ));
        
        $this->addColumn('pt_grid_total_payout', array(
            'header' => Mage::helper('udpayout')->__('Total Payout'),
            'index' => 'total_payout',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));

        $this->addColumn('pt_grid_created_at', array(
            'header'    => Mage::helper('udropship')->__('Created At'),
            'index'     => 'created_at',
            'type'      => 'datetime',
            'width'     => 150,
        ));
        
        $this->addColumn('pt_grid_scheduled_at', array(
            'header'    => Mage::helper('udropship')->__('Scheduled At'),
            'index'     => 'scheduled_at',
            'type'      => 'datetime',
            'width'     => 150,
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('zospayoutadmin/payout/edit', array('id' => $row->getId()));
    }

    public function getGridUrl()
    {
        return $this->getUrl('zospayoutadmin/payout/vendorPayoutsGrid', array('_current'=>true));
    }
}
