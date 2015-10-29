<?php

class GH_Statements_Block_Dropship_Periodic_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct() {
        parent::__construct();
        $this->setId('ghstatements_dropship_periodic_grid_id');
        $this->setDefaultSort('event_date');
        $this->setDefaultDir('DESC');
        // Need
        $this->setGridClass('z-grid');
        $this->setTemplate("zolagoadminhtml/widget/grid.phtml");
    }

    protected function _prepareCollection(){
        /** @var GH_Statements_Model_Resource_Statement_Collection $collection */
        $collection = Mage::getResourceModel('ghstatements/statement_collection');
        $collection->addVendorFilter($this->getVendor());
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _afterLoadCollection() {
        /** @var GH_Statements_Model_Resource_Statement_Collection $collection */
        $collection = $this->getCollection();

        if ($collection->count()) {
            // Add from - to
            $this->_addSettlementPeriod();

        }
        return $this;
    }

    /**
     * Add Settlement period to collection
     *
     * @return $this
     */
    private function _addSettlementPeriod() {
        /** @var GH_Statements_Model_Resource_Statement_Collection $collection */
        $collection = $this->getCollection();
        $oneDay = strtotime("1 day", Mage::getModel('core/date')->timestamp(time())) - Mage::getModel('core/date')->timestamp(time());
        $from = strtotime(date("Y-m-d", strtotime($this->getVendor()->getRegulationAcceptDocumentDate()) - $oneDay));
        /** @var GH_Statements_Model_Statement $statement */
        foreach ($collection as $statement) {
            $to = strtotime(date("Y-m-d", strtotime($statement->getEventDate())));
            $statement->setData('settlement-period', date("Y-m-d", $from+$oneDay ) . ' - ' . date("Y-m-d", $to-$oneDay));
            $from = $to-$oneDay;
        }
        return $this;
    }

    protected function _prepareColumns() {
        /** @var GH_Statements_Helper_Data $helper */
        $helper = Mage::helper('ghstatements');
        $currency = (string)Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);

        $this->setFilterVisibility(false);

        // Rozliczenie na dzien
        $this->addColumn('event_date', array(
            "width"	    => "5%",
            'header'    => $helper->__('Settlement per day'),
            'index'     => 'event_date',
            'type'      => 'date',
            'format'    => 'Y-MM-d',
            'headings_css_class' => '' //remove .nobr
        ));

        // Okres rozliczenia
        $this->addColumn('settlement-period', array(
            "width"	    => "5%",
            'header'    => $helper->__("Settlement period"),
            'index'     => 'settlement-period',
            'headings_css_class' => ''
        ));

        // Rozliczenia płatnośći za zamówienia
        $this->addColumn('payment-settlement-for-orders', array(
            "width"	    => "11%",
            'header'    => $helper->__('Payment settlement for orders'),
            'index'     => 'payment-settlement-for-orders',
            'renderer'	=> Mage::getConfig()->getBlockClassName("ghstatements/dropship_periodic_grid_column_renderer_details"),
            'renderer_data' => array(
                array('text' => $helper->__("Payments for completed orders") , 'field' => 'order_value', 'css_class' => 'row-1'),
                array('text' => $helper->__("Correction for returned orders"), 'field' => 'order_value', 'css_class' => 'row-2'),
            ),
            'currency_code' => $currency,
            'headings_css_class' => ''
        ));
        // Prowizja Modago
        $this->addColumn('gallery-commission', array(
            "width"	    => "11%",
            'header'    => $helper->__('Gallery commission'),
            'index'     => 'gallery-commission',
            'renderer'	=> Mage::getConfig()->getBlockClassName("ghstatements/dropship_periodic_grid_column_renderer_details"),
            'renderer_data' => array(
                array('text' => $helper->__('Gallery commission') , 'field' => 'order_value', 'css_class' => 'row-1'),
                array('text' => $helper->__("Correction for discounts financed by the gallery"), 'field' => 'order_value', 'css_class' => 'row-2'),
                array('text' => $helper->__("Other corrections"), 'field' => 'order_value', 'css_class' => 'row-3'),
            ),
            'currency_code' => $currency,
            'headings_css_class' => ''
        ));
        // Koszty kurierów
        $this->addColumn('currier-costs', array(
            "width"	    => "11%",
            'header'    => $helper->__('Currier costs'),
            'index'     => 'currier-costs',
            'renderer'	=> Mage::getConfig()->getBlockClassName("ghstatements/dropship_periodic_grid_column_renderer_details"),
            'renderer_data' => array(
                array('text' => $helper->__('Currier costs') , 'field' => 'order_value', 'css_class' => 'row-1'),
                array('text' => $helper->__("Currier correction costs"), 'field' => 'order_value', 'css_class' => 'row-2'),
            ),
            'currency_code' => $currency,
            'headings_css_class' => ''
        ));
        // Koszty działań marketingowych
        $this->addColumn('marketing-costs', array(
            "width"	    => "11%",
            'header'    => $helper->__('Marketing costs'),
            'index'     => 'marketing-costs',
            'renderer'	=> Mage::getConfig()->getBlockClassName("ghstatements/dropship_periodic_grid_column_renderer_details"),
            'renderer_data' => array(
                array('text' => $helper->__('Marketing costs') , 'field' => 'order_value', 'css_class' => 'row-1'),
                array('text' => $helper->__("Marketing correction costs"), 'field' => 'order_value', 'css_class' => 'row-2'),
            ),
            'currency_code' => $currency,
            'headings_css_class' => ''
        ));
        // Do wypłaty
        $this->addColumn('to-payout', array(
            "width"		    => "5%",
            'header'        => $helper->__('To payout'),
            'index'         => 'to-payout',
            'type'          => 'currency',
            'currency_code' => $currency,
            'headings_css_class' => ''
        ));
        // [B] Saldo poprzedniego rozliczenia
        $this->addColumn('balance-of-the-previous-settlement', array(
            "width"		    => "5%",
            'header'        => $helper->__("Balance of the previous settlement"),
            'index'         => 'balance-of-the-previous-settlement',
            'type'          => 'currency',
            'currency_code' => $currency,
            'headings_css_class' => ''
        ));
        // payment to the seller
        $this->addColumn('payment-to-the-seller', array(
            "width"		    => "5%",
            'header'        => $helper->__("Payment to the seller"),
            'index'         => 'payment-to-the-seller',
            'type'          => 'currency',
            'currency_code' => $currency,
            'headings_css_class' => ''
        ));
        // Current statement
        $this->addColumn('current-balance-of-the-settlement', array(
            "width"		    => "5%",
            'header'        => $helper->__("Current balance of the settlement"),
            'index'         => 'current-balance-of-the-settlement',
            'type'          => 'currency',
            'currency_code' => $currency,
            'headings_css_class' => ''
        ));
//        // Download statement
////        $this->addColumn("actions", array(
////            'header'    => $helper->__('Action'),
////            'renderer'	=> Mage::getConfig()->getBlockClassName("zolagoadminhtml/widget_grid_column_renderer_link"),
////            'width'     => '100px',
////            'type'      => 'action',
////            'index'		=> 'id',
////            'link_action'=> "*/*/download",
////            'link_param' => 'id',
////            'link_label' => $helper->__('Download settlement'),
////            'link_target'=>'_self',
////            'filter'    => false,
////            'sortable'  => false
////        ));

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
