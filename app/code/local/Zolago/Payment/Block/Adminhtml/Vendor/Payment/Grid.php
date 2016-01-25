<?php

/**
 * Class Zolago_Payment_Block_Adminhtml_Vendor_Payment_Grid
 */
class Zolago_Payment_Block_Adminhtml_Vendor_Payment_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('vendor_payment_grid_id');
        $this->setDefaultSort('date');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $model = Mage::getModel('zolagopayment/vendor_payment');

        /* @var $collection Zolago_Payment_Model_Resource_Vendor_Payment_Collection */
        $collection = $model->getCollection();
        $this->setCollection($collection);

        $collection->getSelect()->join(
            array("vendors" => $model->getResource()->getTable('udropship/vendor')), //$name
            "main_table.vendor_id=vendors.vendor_id", //$cond
            array("vendor_name" => "vendor_name")//$cols = '*'
        );

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('vendor_payment_id',
            array(
                'header' => Mage::helper('zolagopayment')->__('ID'),
                'width' => '50px',
                'index' => 'vendor_payment_id'
            )
        );
        $this->addColumn('date',
            array(
                'header' => Mage::helper('zolagopayment')->__('Date'),
                'width' => '50px',
                'index' => 'date',
                "type" => "date"
            )
        );
        $this->addColumn('vendor_id',
            array(
                'header' => $this->__('Vendor'),
                'width' => '50px',
                "type" => "options",
                'index' => 'vendor_id',
                'filter_index' => 'vendor_name',
                "options" => Mage::getSingleton('zolagodropship/source')
                    ->setPath('allvendorswithdisabled')
                    ->toOptionHash(),
                'filter_condition_callback' => array($this, '_sortByVendorName'),
            )
        );
        $this->addColumn('cost',
            array(
                'header' => $this->__('Cost'),
                'width' => '50px',
                'index' => 'cost',
                'type' => 'price',
                'currency' => 'base_currency_code',
                'currency_code' => Mage::getStoreConfig('currency/options/base')

            )
        );
        $this->addColumn('comment',
            array(
                'header' => Mage::helper('zolagopayment')->__('Comment'),
                'width' => '50px',
                'index' => 'comment'
            )
        );

        $this->addColumn('delete',
            array(
                'header' => Mage::helper('zolagopayment')->__('Delete'),
                'width' => '50px',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('zolagopayment')->__('Delete'),
                        'url' => array('base' => '*/*/delete'),
                        'field' => 'id',
                        'confirm' => Mage::helper('zolagopayment')->__('Are you sure?')
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
     * Sets sorting order by some column
     *
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _setCollectionOrder($column)
    {
        if ($column->getOrderConditionCallback()) {
            call_user_func($column->getOrderConditionCallback(), $this->getCollection(), $column);
            return $this;
        }

        return parent::_setCollectionOrder($column);
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
