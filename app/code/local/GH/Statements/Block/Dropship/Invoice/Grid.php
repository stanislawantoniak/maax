<?php

/**
 * Grid for vendor invoices
 *
 * Class GH_Statements_Block_Dropship_Invoice_Grid
 */
class GH_Statements_Block_Dropship_Invoice_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('ghstatements_dropship_invoice_grid_id');
        $this->setDefaultSort('date');
        $this->setDefaultDir('DESC');
        // Need
        $this->setGridClass('z-grid');
        $this->setTemplate("zolagoadminhtml/widget/grid.phtml");
    }

    protected function _prepareCollection() {
        /** @var Zolago_Payment_Model_Resource_Vendor_Invoice_Collection $collection */
        $collection = Mage::getModel('zolagopayment/vendor_invoice')->getCollection();
        $collection->addVendorFilter($this->getVendor());
        $collection->addWFirmaConnectedFilter();
        $collection->addSum();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Pokazujemy tylko faktury które mają referencję z wFirma (czyli były wystawione)
     * data sprzedaży, data wystawienia, numer faktury, prowizja od sprzedaży, usługi transportowe, razem brutto, specyfikacja
     * w kolumnie specyfikacja tabelka która jest wysyłana z fakturą
     *
     * @return $this
     * @throws Exception
     */
    protected function _prepareColumns() {
        /** @var GH_Statements_Helper_Data $helper */
        $helper = Mage::helper('ghstatements');
        $currency = (string)Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);

        $this->setFilterVisibility(false);

        // Data sprzedaży
        $this->addColumn('sale_date', array(
            "width"     => "14%",
            'header'    => $helper->__("Sale date"), // TODO: "Sale date","Data sprzedaży"
            'index'     => 'sale_date',
            'type'      => 'date',
            'format'    => 'Y-MM-dd',
        ));
        // Data wystawienia
        $this->addColumn('date', array(
            "width"     => "14%",
            'header'    => $helper->__("Invoice date"), // TODO: "Invoice date","Data wystawienia"
            'index'     => 'date',
            'type'      => 'date',
            'format'    => 'Y-MM-dd',
        ));
        // Numer faktury
        $this->addColumn('wfirma_invoice_number', array(
            "width"     => "14%",
            'header'    => $helper->__("Invoice number"), // TODO: "Invoice number","Numer faktury"
            'index'     => 'wfirma_invoice_number',
            'sortable'  => false,
        ));
        // Prowizja od sprzedaży
        $this->addColumn('commission_brutto', array(
            "width"         => "14%",
            'header'        => $helper->__("Sale commision"), //TODO: "Sale commision","Prowizja od sprzedaży"
            'index'         => 'commission_brutto',
            'type'          => 'currency',
            'currency_code' => $currency,
            'sortable'      => false,
        ));
        // Usługi transportowe
        $this->addColumn('transport_brutto', array(
            "width"         => "14%",
            'header'        => $helper->__("Transport services"), //TODO: "Transport services","Usługi transportowe"
            'index'         => 'transport_brutto',
            'type'          => 'currency',
            'currency_code' => $currency,
            'sortable'      => false,
        ));
        // Usługi marketingowe
        if ($this->getVendor()->getData('marketing_charges_enabled')) {
            $this->addColumn('marketing_brutto', array(
                "width"         => "14%",
                'header'        => $helper->__("Marketing services"), //TODO: "Marketing services","Usługi marketingowe"
                'index'         => 'marketing_brutto',
                'type'          => 'currency',
                'currency_code' => $currency,
                'sortable'      => false,
            ));
        }
        // Razem brutto
        $this->addColumn('sum_brutto', array(
            'header'        => $helper->__("Total gross"), //TODO: "Total gross","Razem brutto"
            'index'         => 'sum_brutto',
            'type'          => 'currency',
            'currency_code' => $currency,
            'sortable'      => false,
        ));
        // Specyfikacja
        $this->addColumn('specification', array(
            'header'    => $helper->__("Specification"), //TODO: "specification","Specyfikacja"
            'renderer'  => Mage::getConfig()->getBlockClassName("ghstatements/dropship_invoice_grid_column_renderer_specification"),
            'sortable'  => false,
        ));

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
