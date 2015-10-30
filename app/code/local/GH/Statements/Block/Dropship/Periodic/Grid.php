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

        $oneDay = strtotime("1 day", Mage::getModel('core/date')->timestamp(time())) - Mage::getModel('core/date')->timestamp(time());
        $from = strtotime(date("Y-m-d", strtotime($this->getVendor()->getRegulationAcceptDocumentDate()) - $oneDay));
        /** @var GH_Statements_Model_Statement $statement */
        foreach ($sortedData as $statement) {
            $to = strtotime(date("Y-m-d", strtotime($statement->getEventDate())));
            $statement->setData('settlement_period', date("Y-m-d", $from+$oneDay ) . ' - ' . date("Y-m-d", $to-$oneDay));
            $from = $to-$oneDay;
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
        $B = 0;
        /** @var GH_Statements_Model_Statement $statement */
        foreach ($sortedData as $statement) {
            // Currency renderer need value like "0.0000" ( 0 is not rendering )
            $statement->setData('balance_of_the_previous_settlement', sprintf("%.4f", round($B, 2)));
            $A = $statement->getToPay();
            $C = $statement->getPaymentValue();
            $statement->setData('current_balance_of_the_settlement', sprintf("%.4f", round($BAC = $B + $A - $C, 2)));
            $B = $BAC;
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
            $statement->setData('gallery_discount_value', sprintf("%.4f", -1 * $statement->getData('gallery_discount_value')));
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
        $this->addColumn('settlement_period', array(
            "width"	    => "5%",
            'header'    => $helper->__("Settlement period"),
            'index'              => 'settlement_period',
            'headings_css_class' => ''
        ));
        // Rozliczenia płatnośći za zamówienia
        $this->addColumn('payment_settlement_for_orders', array(
            "width"	    => "11%",
            'header'    => $helper->__('Payment settlement for orders'),
            'renderer'	=> Mage::getConfig()->getBlockClassName("ghstatements/dropship_periodic_grid_column_renderer_details"),
            'renderer_data' => array(
                // Zapłaty za zrealizowane zamówienia
                array(
                    'text' => $helper->__("Payments for completed orders"),
                    'index' => 'order_value',
                    'css_class' => 'row-1'),
                // Korekta za zwrócone zamówienia
                array(
                    'text' => $helper->__("Correction for returned orders"),
                    'index' => 'rma_value',
                    'css_class' => 'row-2'
                ),
            ),
            'currency_code' => $currency,
            'headings_css_class' => ''
        ));
        // Prowizja Modago
        $this->addColumn('gallery-commission', array(
            "width"	    => "11%",
            'header'    => $helper->__('Gallery commission'),
            'renderer'	=> Mage::getConfig()->getBlockClassName("ghstatements/dropship_periodic_grid_column_renderer_details"),
            'renderer_data' => array(
                // Prowizja Modago
                array(
                    'text' => $helper->__('Gallery commission'),
                    'index' => 'order_commission_value',
                    'css_class' => 'row-1'
                ),
                // Korekta o rabaty finansowane przez Modago
                array(
                    'text' => $helper->__("Correction for discounts financed by the gallery"),
                    'index' => 'gallery_discount_value',
                    'css_class' => 'row-2'
                ),
                // Inne korekty
                array(
                    'text' => $helper->__("Other corrections"),
                    'index' => 'commission_correction',
                    'css_class' => 'row-3'
                ),
            ),
            'currency_code' => $currency,
            'headings_css_class' => ''
        ));
        // Koszty kurierów
        $this->addColumn('currier-costs', array(
            "width"	    => "11%",
            'header'    => $helper->__('Currier costs'),
            'renderer'	=> Mage::getConfig()->getBlockClassName("ghstatements/dropship_periodic_grid_column_renderer_details"),
            'renderer_data' => array(
                // Koszty kurierów
                array(
                    'text' => $helper->__('Currier costs'),
                    'index' => 'tracking_charge_total',
                    'css_class' => 'row-1'
                ),
                // Korekty kosztów kurierów
                array(
                    'text' => $helper->__("Currier correction costs"),
                    'index' => 'delivery_correction',
                    'css_class' => 'row-2'),
            ),
            'currency_code' => $currency,
            'headings_css_class' => ''
        ));
        // Koszty działań marketingowych
        $this->addColumn('marketing-costs', array(
            "width"	    => "11%",
            'header'    => $helper->__('Marketing costs'),
            'renderer'	=> Mage::getConfig()->getBlockClassName("ghstatements/dropship_periodic_grid_column_renderer_details"),
            'renderer_data' => array(
                // Koszty działań marketingowych
                array(
                    'text' => $helper->__('Marketing costs'),
                    'index' => 'marketing_value',
                    'css_class' => 'row-1'
                ),
                // Korekty kosztów marketingowych
                array(
                    'text' => $helper->__("Marketing correction costs"),
                    'index' => 'marketing_correction',
                    'css_class' => 'row-2'
                ),
            ),
            'currency_code' => $currency,
            'headings_css_class' => ''
        ));
        // [A] Do wypłaty
        $this->addColumn('to-payout', array(
            "width"		    => "5%",
            'header'        => $helper->__('To payout'),
            'index'         => 'to_pay',
            'type'          => 'currency',
            'currency_code' => $currency,
            'headings_css_class' => ''
        ));
        // [B] Saldo poprzedniego rozliczenia
        $this->addColumn('balance_of_the_previous_settlement', array(
            "width"		    => "5%",
            'header'        => $helper->__("Balance of the previous settlement"),
            'index'         => 'balance_of_the_previous_settlement',
            'type'          => 'currency',
            'currency_code' => $currency,
            'headings_css_class' => ''
        ));
        // [C] Wypłaty do sprzedawcy
        $this->addColumn('payment_value', array(
            "width"		    => "5%",
            'header'        => $helper->__("Payment to the seller"),
            'index'         => 'payment_value',
            'type'          => 'currency',
            'currency_code' => $currency,
            'headings_css_class' => ''
        ));
        // Bieżące saldo rozliczenia [B]+[A]-[C]:
        $this->addColumn('current_balance_of_the_settlement', array(
            "width"		    => "5%",
            'header'        => $helper->__("Current balance of the settlement"),
            'index'         => 'current_balance_of_the_settlement',
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
