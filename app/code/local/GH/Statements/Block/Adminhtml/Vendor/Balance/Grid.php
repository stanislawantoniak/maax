<?php

/**
 * Class GH_Statements_Block_Adminhtml_Vendor_Balance_Grid
 */
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
        $model = Mage::getModel('ghstatements/vendor_balance');
        /** @var GH_Statements_Model_Vendor_Balance_Collection $collection */
        $collection = $model->getCollection();
        $this->setCollection($collection);

        $collection->getSelect()->join(
            array("vendors" => $model->getResource()->getTable('udropship/vendor')), //$name
            "main_table.vendor_id=vendors.vendor_id", //$cond
            array("vendor_name" => "vendor_name")//$cols = '*'
        );

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
            'index' => 'id',
            'width' => '5px',
        ));
        // Status miesiąca
        $this->addColumn('status', array(
            'header' => $helper->__('Status'),
            'index' => 'status',
            //'width' => '100px',
            'type' => 'options',
            'options' => array(
                GH_Statements_Model_Vendor_Balance::GH_VENDOR_BALANCE_STATUS_OPENED => $helper->__("Open"),
                GH_Statements_Model_Vendor_Balance::GH_VENDOR_BALANCE_STATUS_CLOSED => $helper->__("Close")
            ),
            "renderer" => Mage::getConfig()->getBlockClassName("ghstatements/adminhtml_vendor_balance_grid_column_renderer_status")
        ));
        // Statement month (Miesiąc rozliczeniowy)
        $this->addColumn('date', array(
            'header' => $helper->__('Month'),
            //'type'   => 'date',
            //'format' => 'Y-M',
            'index' => 'date'
        ));
        // Sprzedawca
        $this->addColumn('vendor_id', array(
                'header' => $helper->__('Vendor'),
                'width' => '50px',
                "type" => "options",
                'index' => 'vendor_id',
                "options" => Mage::getSingleton('zolagodropship/source')->setPath('allvendorswithdisabled')->toOptionHash(),
                'filter_index' => 'vendor_name',
                'filter_condition_callback' => array($this, '_sortByVendorName')
            )
        );
        // Płatności od klientów
        $this->addColumn('payment_from_client', array(
            'header' => $helper->__('Customer payments'),
            'index' => 'payment_from_client',
            'type' => 'currency',
            'currency_code' => $currency
        ));
        // Zwroty płatności do klientów
        $this->addColumn('payment_return_to_client', array(
            'header' => $helper->__('Customer refunds'),
            'index' => 'payment_return_to_client',
            'type' => 'currency',
            'currency_code' => $currency
        ));
        // Wypłaty
        $this->addColumn('vendor_payment_cost', array(
            'header' => $helper->__('Payouts to vendor'),
            'index' => 'vendor_payment_cost',
            'type' => 'currency',
            'currency_code' => $currency
        ));
        // Faktury i korekty faktur
        $this->addColumn('vendor_invoice_cost', array(
            'header' => $helper->__('Invoices and credit notes'),
            'index' => 'vendor_invoice_cost',
            'type' => 'currency',
            'currency_code' => $currency
        ));
        // Bilans miesiąca
        $this->addColumn('balance_per_month', array(
            'header' => $helper->__('Monthly balance'),
            'index' => 'balance_per_month',
            'type' => 'currency',
            'currency_code' => $currency
        ));
        // Saldo narastająco
        $this->addColumn('balance_cumulative', array(
            'header' => $helper->__('Cumulative balance'),
            'index' => 'balance_cumulative',
            'type' => 'currency',
            'currency_code' => $currency
        ));
        // Saldo wymagalne
        $this->addColumn('balance_due', array(
            'header' => $helper->__('Due balance'),
            'index' => 'balance_due',
            'type' => 'currency',
            'currency_code' => $currency
        ));

        $this->addColumn('close_month',
            array(
                'header' => $helper->__('Close month'),
                'width' => '50px',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => $helper->__('Close month'),
                        'url' => array('base' => '*/*/closeMonth'),
                        'field' => 'id',
                        'confirm' => $helper->__('Are you sure?')
                    ),
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
            ));

        return parent::_prepareColumns();
    }

    /**
     * @param $collection
     * @param $column
     * @return $this
     */
    function _sortByVendorName($collection, $column)
    {
        /* @var $collection Zolago_Payment_Model_Resource_Vendor_Payment_Collection */
        $direction = strtoupper($column->getDir());

        if ($direction) {
            $collection->getSelect()->order($column->getFilterIndex(), $direction);
        }
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        $index = $column->getIndex();
        $collection->getSelect()->where("main_table.{$index} = ?", $value);

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
}