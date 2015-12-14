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

    /**
     * Add custom data to collection
     *
     * @return $this
     */
    protected function _afterLoadCollection() {
        /** @var GH_Statements_Model_Resource_Statement_Collection $collection */
        $collection = $this->getCollection();

        if ($collection->count()) {
            // Add from - to
            $this->_addSettlementPeriod();
            // [B] and [B]+[A]-[C]
            $this->_calculateBalance();
            // set corrent sign for corrections
            $this->_processValuesSignForGrid();

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

        // Temporary sorting ASC for chronological periods calculating
        // because user can change sorting manually on event_date column
        $sortedData = array();
        /** @var GH_Statements_Model_Statement $statement */
        foreach ($collection as $statement) {
            $sortedData[$statement->getEventDate()] = $statement;
        }
        sort($sortedData);

        $oneDay = 24 * 60 * 60;
        /** @var GH_Statements_Model_Statement $statement */
        foreach ($sortedData as $statement) {
            $eventDate = strtotime(date("Y-m-d", strtotime($statement->getEventDate())));
            $statement->setData('event_date',date("Y-m-d",$eventDate+$oneDay));
            $statement->setData('statement_period_from',$statement->getDateFrom());
            $statement->setData('statement_period_to',date("Y-m-d",$eventDate));
        }
        return $this;
    }

    /**
     * Add to the collection:
     * - balance of the previous settlement
     * - current balance of the settlement
     *
     * [A] Do wypłaty -> to_pay (to_payout)
     * [B] Saldo poprzedniego rozliczenia -> balance_of_the_previous_settlement
     * [C] Wypłaty do sprzedawcy -> payment_value
     * [BAC] Bieżące saldo rozliczenia [B]+[A]-[C]: -> current_balance_of_the_settlement
     *
     * @return $this
     */
    private function _calculateBalance() {
        /** @var GH_Statements_Model_Resource_Statement_Collection $collection */
        $collection = $this->getCollection();

        // Temporary sorting ASC for chronological periods calculating
        // because user can change sorting manually on event_date column
        $sortedData = array();
        /** @var GH_Statements_Model_Statement $statement */
        foreach ($collection as $statement) {
            $sortedData[$statement->getEventDate()] = $statement;
        }
        sort($sortedData);
        /** @var GH_Statements_Model_Statement $statement */
        foreach ($sortedData as $statement) {
            // Currency renderer need value like "0.0000" ( 0 is not rendering )
            $B = $statement->getLastStatementBalance();
            $A = $statement->getToPay();
            $C = $statement->getPaymentValue();
            $statement->setData('current_balance_of_the_settlement', sprintf("%.4f", round($B + $A - $C, 2)));
        }
        return $this;
    }

    /*
     * Set correct sign for correction values
     * Because statements are calculated for admin point of view
     * Like in ex: 'pay' needs to be transformed to 'payout'
     */
    private function _processValuesSignForGrid() {
        /** @var GH_Statements_Model_Resource_Statement_Collection $collection */
        $collection = $this->getCollection();

        /** @var GH_Statements_Model_Statement $statement */
        foreach ($collection as $statement) {
            // Korekta za zwrócone zamówienia
            $statement->setData('rma_value', sprintf("%.4f", -1 * $statement->getData('rma_value')));
            // Korekta o rabaty finansowane przez Modago
//            $statement->setData('gallery_discount_value', sprintf("%.4f", -1 * $statement->getData('gallery_discount_value')));
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
            'header'    => $helper->__('Statement date'),
            'index'     => 'event_date',
            'type'      => 'date',
            'format'    => 'Y-MM-dd',
            'headings_css_class' => '', //remove .nobr
            'column_css_class'   => 'event-date-big'
        ));
        // Okres rozliczenia
        $this->addColumn('statement_period', array(
            "width"	    => "5%",
            'header'    => $helper->__("Statement period"),
            'index'     => 'statement_period',
            'headings_css_class' => '',
            'renderer'	=> Mage::getConfig()->getBlockClassName("ghstatements/dropship_periodic_grid_column_renderer_dateFromTo"),
            'renderer_data' => array(
                'from' => array(
                    'index' => 'statement_period_from'
                ),
                'to' => array(
                    'index' => 'statement_period_to'
                )
            ),
            'format'    => 'Y-MM-dd',
            'sortable'  => false,
        ));
        // Rozliczenia płatnośći za zamówienia
        $this->addColumn('payment_settlement_for_orders', array(
            "width"	    => "11%",
            'header'    => $helper->__('Customer payments balance'),
            'renderer'	=> Mage::getConfig()->getBlockClassName("ghstatements/dropship_periodic_grid_column_renderer_details"),
            'renderer_data' => array(
                // Zapłaty za zrealizowane zamówienia
                array(
                    'text' => $helper->__("Completed orders balance"),
                    'index' => 'order_value',
                    'css_class' => 'row-1'),
                // Korekta za zwrócone zamówienia
                array(
                    'text' => $helper->__("Refunds balance"),
                    'index' => 'refund_value',
                    'css_class' => 'row-2'
                ),
            ),
            'currency_code' => $currency,
            'headings_css_class' => '',
            'sortable' => false,
            
        ));
        // Prowizja Modago
        $this->addColumn('gallery-commission', array(
            "width"	    => "11%",
            'header'    => $helper->__("Modago commission"),
            'renderer'	=> Mage::getConfig()->getBlockClassName("ghstatements/dropship_periodic_grid_column_renderer_details"),
            'renderer_data' => array(
                // Prowizja Modago
                array(
                    'text' => $helper->__("Modago commission"),
                    'index' => 'total_commission',
                    'css_class' => 'row-1'
                ),
                // Korekta o rabaty finansowane przez Modago
                array(
                    'text' => $helper->__("Discounts financed by Modago"),
                    'index' => 'gallery_discount_value',
                    'css_class' => 'row-2'
                ),
                // Inne korekty
                array(
                    'text' => $helper->__("Other adjustments"),
                    'index' => 'commission_correction',
                    'css_class' => 'row-3'
                ),
            ),
            'currency_code' => $currency,
            'headings_css_class' => '',
            'sortable'  => false,
        ));
        // Koszty kurierów
        $this->addColumn('currier-costs', array(
            "width"	    => "11%",
            'header'    => $helper->__('Carrier costs'),
            'renderer'	=> Mage::getConfig()->getBlockClassName("ghstatements/dropship_periodic_grid_column_renderer_details"),
            'renderer_data' => array(
                // Koszty kurierów
                array(
                    'text' => $helper->__('Carrier cost'),
                    'index' => 'tracking_charge_total',
                    'css_class' => 'row-1'
                ),
                // Korekty kosztów kurierów
                array(
                    'text' => $helper->__("Credit notes for carrier costs"),
                    'index' => 'delivery_correction',
                    'css_class' => 'row-2'),
            ),
            'currency_code' => $currency,
            'headings_css_class' => '',
            'sortable'  => false,
            
        ));
        if ($this->getVendor()->getData('marketing_charges_enabled')) {
            // Koszty działań marketingowych
            $this->addColumn('marketing-costs', array(
                "width" => "11%",
                'header' => $helper->__('Marketing costs'),
                'renderer' => Mage::getConfig()->getBlockClassName("ghstatements/dropship_periodic_grid_column_renderer_details"),
                'renderer_data' => array(
                    // Koszty działań marketingowych
                    array(
                        'text' => $helper->__("Marketing cost"),
                        'index' => 'marketing_value',
                        'css_class' => 'row-1'
                    ),
                    // Korekty kosztów marketingowych
                    array(
                        'text' => $helper->__("Credit notes for marketing costs"),
                        'index' => 'marketing_correction',
                        'css_class' => 'row-2'
                    ),
                ),
                'currency_code' => $currency,
                'headings_css_class' => '',
                'sortable'  => false,
            ));
        }
        // [A] Do wypłaty
        $this->addColumn('to-payout', array(
            "width"		    => "5%",
            'header'        => $helper->__('[A] Statement total'),
            'index'         => 'to_pay',
            'type'          => 'currency',
            'currency_code' => $currency,
            'headings_css_class' => '',
            'column_css_class' => 'important-cell',
            'sortable'  => false,
            
        ));
        // [B] Saldo poprzedniego rozliczenia
        $this->addColumn('last_statement_balance', array(
            "width"		    => "5%",
            'header'        => $helper->__("[B] Previous balance"),
            'index'         => 'last_statement_balance',
            'type'          => 'currency',
            'currency_code' => $currency,
            'headings_css_class' => '',
            'column_css_class' => 'important-cell',
            'sortable'  => false,
            
        ));
        // [C] Wypłaty do sprzedawcy
        $this->addColumn('payment_value', array(
            "width"		    => "5%",
            'header'        => $helper->__("[C] Payouts in statement period"),
            'index'         => 'payment_value',
            'type'          => 'currency',
            'currency_code' => $currency,
            'headings_css_class' => '',
            'column_css_class' => 'important-cell',
            'sortable'  => false,
            
        ));
        // Bieżące saldo rozliczenia [B]+[A]-[C]:
        $this->addColumn('current_balance_of_the_settlement', array(
            "width"		    => "5%",
            'header'        => $helper->__("Current due balance [B]+[A]-[C]"),
            'index'         => 'current_balance_of_the_settlement',
            'type'          => 'currency',
            'currency_code' => $currency,
            'headings_css_class' => '',
            'column_css_class' => 'important-cell',
            'sortable'  => false,
            
        ));
        //Download statement
        $this->addColumn("actions", array(
            'header'    => $helper->__('PDF'),
            'renderer'	=> Mage::getConfig()->getBlockClassName("zolagoadminhtml/widget_grid_column_renderer_link"),
            'width'     => '3%',
            'type'      => 'action',
            'index'		=> 'id',
            'link_action'=> "*/*/downloadStatement",
            'link_param' => 'id',
            'link_label' => $helper->__('Download'),
            'link_target'=>'_self',
            'filter'    => false,
            'sortable'  => false
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
