<?php

/**
 * Class Zolago_Payment_Block_Adminhtml_Vendor_Invoice_Grid
 */
class Zolago_Payment_Block_Adminhtml_Vendor_Invoice_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('vendor_invoice_grid_id');
        $this->setDefaultSort('date');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {

        $model = Mage::getModel('zolagopayment/vendor_payment');

        /* @var $collection Zolago_Payment_Model_Resource_Vendor_Invoice_Collection */
        $collection = Mage::getModel('zolagopayment/vendor_invoice')->getCollection();
        $this->setCollection($collection);

        $collection->getSelect()->join(
            array("vendors" => $model->getResource()->getTable('udropship/vendor')), //$name
            "main_table.vendor_id=vendors.vendor_id", //$cond
            array("vendor_name" => "vendor_name")//$cols = '*'
        );

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
        $this->addColumn('is_invoice_correction',
            array(
                'header' => $hlp->__('Invoice correction'),
                'width' => '100px',
                "type" => "options",
                'index' => 'is_invoice_correction',
                "options" => array(
                    Zolago_Payment_Model_Vendor_Invoice::INVOICE_TYPE_ORIGINAL => $hlp->__("No"),
                    Zolago_Payment_Model_Vendor_Invoice::INVOICE_TYPE_CORRECTION => $hlp->__("Yes")
                )
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

        /*        $this->addColumn('Wfirma_invoice_id',
                    array(
                        'header' => "wfirma " . $hlp->__('Invoice ID'),
                        'width' => '50px',
                        'index' => 'wfirma_invoice_id'
                    )
                );*/
        $this->addColumn('wfirma_invoice_number',
            array(
                'header' => "Wfirma " . $hlp->__('Invoice #'),
                'width' => '50px',
                'index' => 'wfirma_invoice_number'
            )
        );


        $this->addColumn('vendor_id',
            array(
                'header' => $hlp->__('Vendor'),
                'width' => '100px',
                "type" => "options",
                'index' => 'vendor_id',
                'filter_index' => 'vendor_name',
                "options" => Mage::getSingleton('zolagodropship/source')->setPath('allvendorswithdisabled')->toOptionHash(),
                'filter_condition_callback' => array($this, '_sortByVendorName'),
            )
        );

        //Cost
        $costFields = array(
            //1. commission
            //"commission_netto" => $hlp->__('Commission netto'),
            "commission_brutto" => $hlp->__('Commission brutto'),

            //2. transport
            //"transport_netto" => $hlp->__('Transport netto'),
            "transport_brutto" => $hlp->__('Transport brutto'),

            //3. marketing
            //"marketing_netto" => $hlp->__('Marketing netto'),
            "marketing_brutto" => $hlp->__('Marketing brutto'),

            //3. other
            //"other_netto" => $hlp->__('Other netto'),
            "other_brutto" => $hlp->__('Other brutto')
        );
        foreach ($costFields as $name => $label) {
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
                        'confirm' => $hlp->__('Are you sure?')
                    ),
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
            ));

        $this->addColumn('generate',
            array(
                'header' => $hlp->__('Generate'),
                'width' => '50px',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => $hlp->__('Generate'),
                        'url' => array('base' => '*/*/generate'),
                        'field' => 'id',
                        'confirm' => $hlp->__('Generate invoice in wFirma system?')
                    ),
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
            ));

        $this->addColumn('download',
            array(
                'header' => $hlp->__('Download'),
                'width' => '50px',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => $hlp->__('Download'),
                        'url' => array('base' => '*/*/download'),
                        'field' => 'id'
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
