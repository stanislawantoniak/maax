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
        $this->setSaveParametersInSession(true);
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
	    $helper = Mage::helper('ghstatements');

        $this->addColumn('id', array(
            'header' => $helper->__('ID'),
            'sortable' => true,
            'width' => '60',
            'index' => 'id'
        ));
        $this->addColumn('po_increment_id', array(
            'header' => $helper->__('Order number'),
            'sortable' => true,
            'width' => '60',
            'index' => 'po_increment_id'
        ));
        $this->addColumn('rma_increment_id', array(
            'header' => $helper->__('RMA number'),
            'sortable' => true,
            'width' => '60',
            'index' => 'rma_increment_id'
        ));
        $this->addColumn('shipped_date', array(
            'header' => $helper->__('Shipping Date'),
            'sortable' => true,
            'width' => '60',
            'index' => 'shipped_date',
            'type' => 'date',
        ));


        $this->addColumn('charge_subtotal', array(
            'header' => $helper->__('Total netto shipment charge'),
            'index' => 'charge_subtotal',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));
        $this->addColumn('charge_total', array(
            'header' => $helper->__('Total brutto shipment charge'),
            'index' => 'charge_total',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));
	    $this->addColumn('track_type', array(
		    'header' => $helper->__('Track type'),
		    'sortable' => true,
		    'index' => 'track_type',
		    'type' => 'options',
		    'options' => array(
			    GH_Statements_Model_Track::TRACK_TYPE_ORDER => $helper->__("Order shipment"),
			    GH_Statements_Model_Track::TRACK_TYPE_RMA_CLIENT => $helper->__("Items return"),
			    GH_Statements_Model_Track::TRACK_TYPE_RMA_VENDOR => $helper->__("RMA shipment"),
			    GH_Statements_Model_Track::TRACK_TYPE_UNDELIVERED => $helper->__("Undelivered order")
		    )
	    ));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/trackGrid', array('_current'=>true));
    }

}
