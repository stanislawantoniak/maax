<?php

/**
 * Class Zolago_Rma_Block_Adminhtml_Rma_Grid
 */
class Zolago_Rma_Block_Adminhtml_Rma_Grid extends Unirgy_Rma_Block_Adminhtml_Rma_Grid
{

    protected function _prepareColumns()
    {
        $this->addColumn('increment_id', array(
            'header' => Mage::helper('urma')->__('Return #'),
            'index' => 'increment_id',
            'filter_index' => 'main_table.increment_id',
            'type' => 'text',
        ));

        $this->addColumn('created_at', array(
            'header' => Mage::helper('urma')->__('Return Created'),
            'index' => 'created_at',
            'filter_index' => 'main_table.created_at',
            'type' => 'datetime',
        ));

        $this->addColumn('order_increment_id', array(
            'header' => Mage::helper('sales')->__('Order #'),
            'index' => 'order_increment_id',
            'type' => 'number',
        ));

        $this->addColumn('order_created_at', array(
            'header' => Mage::helper('sales')->__('Order Date'),
            'index' => 'order_created_at',
            'type' => 'datetime',
        ));

        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('sales')->__('Shipper Name'),
            'index' => 'shipping_name',
        ));

        $this->addColumn('rma_status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'rma_status',
            'type' => 'options',
            'options' => array(
                'pending' => Mage::helper('urma')->__('Pending'),
                'approved' => Mage::helper('urma')->__('Approved'),
                'declined' => Mage::helper('urma')->__('Declined'),
            ),
        ));

        $this->addColumn('udropship_vendor', array(
            'header' => Mage::helper('udropship')->__('Vendor'),
            'index' => 'udropship_vendor',
            'type' => 'options',
            "options" => Mage::getSingleton('zolagodropship/source')->setPath('allvendorswithdisabled')->toOptionHash(),
            'filter' => 'udropship/vendor_gridColumnFilter'
        ));

        $this->addColumn('udropship_method_description', array(
            'header' => Mage::helper('udropship')->__('Method'),
            'index' => 'udropship_method_description',
        ));

        $this->addColumn('tracking_price', array(
            'header' => Mage::helper('udropship')->__('Tracking Price'),
            'index' => 'tracking_price',
            'filter_index' => $this->_getFlatExpressionColumn('tracking_price'),
            'type' => 'price',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));

        $this->addColumn('action',
            array(
                'header' => Mage::helper('sales')->__('Action'),
                'width' => '50px',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('sales')->__('View'),
                        'url' => array('base' => '*/rma/view'),
                        'field' => 'rma_id'
                    )
                ),
                'filter' => false,
                'sortable' => false,
                'is_system' => true
            ));

        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

        return Mage_Adminhtml_Block_Widget_Grid::_prepareColumns();
    }

    /**
     * Sets sorting order by some column
     *
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _setCollectionOrder($column)
    {
        $collection = $this->getCollection();

        if ($collection) {
            $columnIndex = $column->getFilterIndex() ?
                $column->getFilterIndex() : $column->getIndex();

            if ($columnIndex = "udropship_vendor") {
                $collection->getSelect()->join(
                    array("vendors" => $collection->getTable('udropship/vendor')), //$name
                    "t.udropship_vendor=vendors.vendor_id", //$cond
                    array("vendor_name" => "vendor_name")//$cols = '*'
                );
                $columnIndex = "vendor_name";
            }
            $collection->setOrder($columnIndex, strtoupper($column->getDir()));
        }
        return $this;
    }
}