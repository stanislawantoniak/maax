<?php

class GH_Statements_Block_Adminhtml_Vendor_Statements_Edit_Tab_Track
    extends GH_Statements_Block_Adminhtml_Vendor_Statements_Edit_Tab_Statement
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('statement_track');
        $this->setDefaultSort('id');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('ghstatements/track')
            ->getCollection()
            ->addFieldToFilter('statement_id', $this->getStatement()->getId());

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header' => Mage::helper('ghstatements')->__('ID'),
            'sortable' => true,
            'width' => '60',
            'index' => 'id'
        ));
        $this->addColumn('po_increment_id', array(
            'header' => Mage::helper('ghstatements')->__('Order number'),
            'sortable' => true,
            'width' => '60',
            'index' => 'po_increment_id'
        ));
        $this->addColumn('rma_increment_id', array(
            'header' => Mage::helper('ghstatements')->__('RMA number'),
            'sortable' => true,
            'width' => '60',
            'index' => 'rma_increment_id'
        ));
        $this->addColumn('shipped_date', array(
            'header' => Mage::helper('ghstatements')->__('Shipping Date'),
            'sortable' => true,
            'width' => '60',
            'index' => 'shipped_date',
            'type' => 'date',
        ));


        $this->addColumn('charge_subtotal', array(
            'header' => Mage::helper('ghstatements')->__('Total netto shipment charge'),
            'index' => 'charge_subtotal',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));
        $this->addColumn('charge_total', array(
            'header' => Mage::helper('ghstatements')->__('Total brutto shipment charge'),
            'index' => 'charge_total',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));

    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/trackGrid', array('_current'=>true));
    }

}
