<?php

class GH_Statements_Block_Adminhtml_Vendor_Balance_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('vendor_balance_grid_id');
        $this->setDefaultSort('date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        /** @var GH_Statements_Model_Resource_Vendor_Balance_Collection $collection */
        $collection = Mage::getResourceModel('ghstatements/vendor_balance_collection');
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        /** @var GH_Statements_Helper_Data $helper */
        $helper = Mage::helper('ghstatements');
        $currency = (string)Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);

        // Vendor balance id
        $this->addColumn('id', array(
            'header' => $helper->__('ID #'),
            'index'  => 'id'
        ));
        // Status miesiąca
        $this->addColumn('status', array(
            'header'  => $helper->__('Status of monthly balance'),
            'index'   => 'status',
            'type'    => 'options',
            'options' => array(0 => $helper->__("Open"), 1 => $helper->__("Close"))
        ));
        // Statement month (Miesiąc rozliczeniowy)
        $this->addColumn('date', array(
            'header' => $helper->__("Statement month"),
            'type'   => 'date',
            'format' => 'Y-M',
            'index'  => 'date'
        ));
        // Sprzedawca
        $this->addColumn('vendor_id', array(
            'header'  => $helper->__('Vendor'),
            'width'   => '50px',
            "type"    => "options",
            'index'   => 'vendor_id',
            "options" => Mage::getSingleton('zolagodropship/source')->setPath('vendors')->toOptionHash()
            )
        );
        // Płatności od klientów
        $this->addColumn('payment_from_client', array(
            'header'        => $helper->__('Payment from client'),
            'index'         => 'payment_from_client',
            'type'          => 'currency',
            'currency_code' => $currency
        ));
        // Zwroty płatności do klientów
        $this->addColumn('payment_return_to_client', array(
            'header'        => $helper->__('Payment return to client'),
            'index'         => 'payment_return_to_client',
            'type'          => 'currency',
            'currency_code' => $currency
        ));
        // Wypłaty
        $this->addColumn('vendor_payment_cost', array(
            'header'        => $helper->__("Vendor payment cost"),
            'index'         => 'vendor_payment_cost',
            'type'          => 'currency',
            'currency_code' => $currency
        ));
        // Faktury i korekty faktur
        $this->addColumn('vendor_invoice_cost', array(
            'header'        => $helper->__("Vendor invoice cost"),
            'index'         => 'vendor_invoice_cost',
            'type'          => 'currency',
            'currency_code' => $currency
        ));
        // Bilans miesiąca
        $this->addColumn('balance_per_month', array(
            'header'        => $helper->__("Balance per month"),
            'index'         => 'balance_per_month',
            'type'          => 'currency',
            'currency_code' => $currency
        ));
        // Saldo narastająco
        $this->addColumn('balance_cumulative', array(
            'header'        => $helper->__("Balance cumulative"),
            'index'         => 'balance_cumulative',
            'type'          => 'currency',
            'currency_code' => $currency
        ));
        // Saldo wymagalne
        $this->addColumn('balance_due', array(
            'header'        => $helper->__("Balance due"),
            'index'         => 'balance_due',
            'type'          => 'currency',
            'currency_code' => $currency
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}