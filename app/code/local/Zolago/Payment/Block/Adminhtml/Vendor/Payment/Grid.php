<?php

/**
 * Class Zolago_Payment_Block_Adminhtml_Vendor_Payment_Grid
 */
class Zolago_Payment_Block_Adminhtml_Vendor_Payment_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('grid_id');
        $this->setDefaultSort('date');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('zolagopayment/vendor_payment')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('vendor_payment_id',
            array(
                'header' => $this->__('ID'),
                'width' => '50px',
                'index' => 'vendor_payment_id'
            )
        );
        $this->addColumn('date',
            array(
                'header' => $this->__('Date'),
                'width' => '50px',
                'index' => 'date'
            )
        );
        $this->addColumn('vendor_id',
            array(
                'header' => $this->__('Vendor'),
                'width' => '50px',
                "type" => "options",
                'index' => 'vendor_id',
                "options" => Mage::getSingleton('zolagodropship/source')->setPath('vendors')->toOptionHash()
            )
        );
        $this->addColumn('cost',
            array(
                'header' => $this->__('Cost'),
                'width' => '50px',
                'index' => 'cost'
            )
        );
        $this->addColumn('comment',
            array(
                'header' => $this->__('Comment'),
                'width' => '50px',
                'index' => 'comment'
            )
        );


        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
