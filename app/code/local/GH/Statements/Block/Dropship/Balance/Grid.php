<?php

class GH_Statements_Block_Dropship_Balance_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct() {
        parent::__construct();
        $this->setId('ghstatements_dropship_balance_grid_id');
        $this->setDefaultSort('date');
        $this->setDefaultDir('DESC');
        // Need
        $this->setGridClass('z-grid');
        $this->setTemplate("zolagoadminhtml/widget/grid.phtml");
    }

    protected function _prepareCollection(){
        /** @var GH_Statements_Model_Resource_Vendor_Balance_Collection $collection */
        $collection = Mage::getResourceModel('ghstatements/vendor_balance_collection');
        $collection->addVendorFilter($this->getVendor());
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns() {
        /** @var GH_Statements_Helper_Data $helper */
        $helper = Mage::helper('ghstatements');
        $currency = (string)Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);

        $this->setFilterVisibility(false);

        // Status miesiąca
        $this->addColumn('status', array(
            'header'    => $helper->__("Status"),
            'index'     => 'status',
            'type'      => 'options',
            'renderer'	=> Mage::getConfig()->getBlockClassName("ghstatements/dropship_balance_grid_column_renderer_balanceStatusIcon"),
            "width"	    => "11%",
        ));
        // Miesiąc rozliczeniowy
        $this->addColumn('date', array(
            'header'    => $helper->__("Month"),
            'index'     => 'date',
            "width"	    => "11%",
        ));
        // Płatności od klientów
        $this->addColumn('payment_from_client', array(
            'header'        => $helper->__('Customer payments'),
            'index'         => 'payment_from_client',
            'type'          => 'currency',
            'currency_code' => $currency,
            "width"		    => "11%",
        ));
        // Zwroty płatności do klientów
        $this->addColumn('payment_return_to_client', array(
            'header'        => $helper->__('Customer refunds'),
            'index'         => 'payment_return_to_client',
            'type'          => 'currency',
            'currency_code' => $currency,
            "width"		    => "11%",
        ));
        // Wypłaty
        $this->addColumn('vendor_payment_cost', array(
            'header'        => $helper->__("Payouts to vendor"),
            'index'         => 'vendor_payment_cost',
            'type'          => 'currency',
            'currency_code' => $currency,
            "width"		    => "11%",
        ));
        // Faktury i korekty faktur
        $this->addColumn('vendor_invoice_cost', array(
            'header'        => $helper->__("Invoices and credit notes"),
            'index'         => 'vendor_invoice_cost',
            'type'          => 'currency',
            'currency_code' => $currency,
            "width"		    => "11%",
        ));
        // Bilans miesiąca
        $this->addColumn('balance_per_month', array(
            'header'            => $helper->__("Monthly balance"),
            'index'             => 'balance_per_month',
            'type'              => 'currency',
            'currency_code'     => $currency,
            'header_css_class'  => "use-hint"
        ));
        // Saldo narastająco
        $this->addColumn('balance_cumulative', array(
            'header'            => $helper->__("Cumulative balance"),
            'index'             => 'balance_cumulative',
            'type'              => 'currency',
            'currency_code'     => $currency,
            'header_css_class'  => "use-hint",
            "width"		        => "11%",
        ));
        // Saldo wymagalne
        $this->addColumn('balance_due', array(
            'header'            => $helper->__("Due balance"),
            'index'             => 'balance_due',
            'type'              => 'currency',
            'currency_code'     => $currency,
            'header_css_class'  => "use-hint",
            "width"		        => "11%",
        ));
        // zobacz szczegoly / see details
//        $this->addColumn("actions", array(
//            'header'    => $helper->__('Action'),
//            'renderer'	=> Mage::getConfig()->getBlockClassName("zolagoadminhtml/widget_grid_column_renderer_link"),
//            'width'     => '100px',
//            'type'      => 'action',
//            'index'		=> 'id',
//            'link_action'=> "*/*/details",
//            'link_param' => 'id',
//            'link_label' => $helper->__('See details'),
//            'link_target'=>'_self',
//            'filter'    => false,
//            'sortable'  => false
//        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($item) {
        return null;
    }

    /**
     * @return Zolago_Dropship_Model_Vendor
     */
    public function getVendor() {
        return Mage::getSingleton('udropship/session')->getVendor();
    }
}
