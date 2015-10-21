<?php

/**
 * Class Zolago_Payment_Block_Adminhtml_Vendor_Invoice_Grid
 */
class Zolago_Payment_Block_Adminhtml_Vendor_Invoice_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('grid_id');
        $this->setDefaultSort('date');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('zolagopayment/vendor_invoice')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return Zolago_Payment_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper("zolagopayment");
    }

    protected function _prepareColumns()
    {
        $hlp = $this->_getHelper();

        $this->addColumn('vendor_invoice_id',
            array(
                'header' => $hlp->__('ID'),
                'width' => '50px',
                'index' => 'vendor_invoice_id'
            )
        );
        $this->addColumn('date',
            array(
                'header' => $hlp->__('Date'),
                'width' => '40px',
                'index' => 'date',
                "type" => "date"
            )
        );
        $this->addColumn('sale_date',
            array(
                'header' => $hlp->__('Sale Date'),
                'width' => '40px',
                'index' => 'sale_date',
                "type" => "date"
            )
        );

        $this->addColumn('wfirma_invoice_id',
            array(
                'header' => $hlp->__('wfirma invoice ID'),
                'width' => '50px',
                'index' => 'wfirma_invoice_id'
            )
        );
        $this->addColumn('wfirma_invoice_number',
            array(
                'header' => $hlp->__('wfirma invoice #'),
                'width' => '50px',
                'index' => 'wfirma_invoice_number'
            )
        );


        $this->addColumn('vendor_id',
            array(
                'header' => $this->__('Vendor'),
                'width' => '100px',
                "type" => "options",
                'index' => 'vendor_id',
                "options" => Mage::getSingleton('zolagodropship/source')->setPath('vendors')->toOptionHash()
            )
        );

        //Cost
        $costFields = array(
            //1. commission
            "commission_netto" => $hlp->__('Commission netto'),
            "commission_brutto" => $hlp->__('Commission brutto'),

            //2. transport
            "transport_netto" => $hlp->__('Transport netto'),
            "commission_brutto" => $hlp->__('Transport brutto'),

            //3. marketing
            "marketing_netto" => $hlp->__('Marketing netto'),
            "marketing_brutto" => $hlp->__('Marketing brutto'),

            //3. other
            "other_netto" => $hlp->__('Other netto'),
            "other_brutto" => $hlp->__('Other brutto')
        );
        foreach($costFields as $name => $label){
            $this->_addCostGridField($this, $name, $label);
        }
        //Cost


        $this->addColumn('delete',
            array(
                'header' => $hlp->__('Delete'),
                'width' => '50px',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => $hlp->__('Delete'),
                        'url' => array('base' => '*/*/delete'),
                        'field' => 'id',
                        'confirm'  => $hlp->__('Are you sure?')
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
     * @param $grid
     * @param $name
     * @param $label
     */
    private function _addCostGridField($grid, $name, $label)
    {
        $grid->addColumn($name,
            array(
                'header' => $label,
                'width' => '50px',
                'index' => $name,
                'type' => 'price',
                'currency' => 'base_currency_code',
                'currency_code' => Mage::getStoreConfig('currency/options/base')

            )
        );
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
