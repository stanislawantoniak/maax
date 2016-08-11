<?php
class Zolago_Po_Block_Adminhtml_Po_Grid extends ZolagoOs_OmniChannelPo_Block_Adminhtml_Po_Grid
{

    protected function _prepareColumns()
    {
        $this->addColumn('increment_id', array(
            'header'    => Mage::helper('udpo')->__('Purchase Order #'),
            'index'     => 'increment_id',
            'type'      => 'text',
        ));

        $this->addColumn('created_at', array(
            'header'    => Mage::helper('udpo')->__('Purchase Order Created'),
            'index'     => 'created_at',
            'type'      => 'datetime',
        ));

        $this->addColumn('order_increment_id', array(
            'header'    => Mage::helper('sales')->__('Order #'),
            'index'     => 'order_increment_id',
            'type'      => 'number',
        ));

        $this->addColumn('order_created_at', array(
            'header'    => Mage::helper('sales')->__('Order Date'),
            'index'     => 'order_created_at',
            'type'      => 'datetime',
        ));

        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('sales')->__('Ship to Name'),
            'index' => 'shipping_name',
        ));

        $this->addColumn('udropship_vendor', array(
            'header' => Mage::helper('udpo')->__('Vendor'),
            'index' => 'udropship_vendor',
            'type' => 'options',
            'options' => Mage::getSingleton('zolagodropship/source')->setPath('allvendorswithdisabled')->toOptionHash(),
            'filter' => 'udropship/vendor_gridColumnFilter'
        ));

        if (Mage::helper('udropship')->isModuleActive('ustockpo')) {
            $this->addColumn('ustock_vendor', array(
                'header' => Mage::helper('udpo')->__('Stock Vendor'),
                'index' => 'ustock_vendor',
                'type' => 'options',
                'options' => Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionHash(),
                'filter' => 'udropship/vendor_gridColumnFilter'
            ));
        }

        $this->addColumn('udropship_method_description', array(
            'header' => Mage::helper('udropship')->__('Method'),
            'index' => 'udropship_method_description',
        ));

        $this->addColumn('base_shipping_amount', array(
            'header' => Mage::helper('sales')->__('Shipping Price'),
            'index' => 'base_shipping_amount',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/udpo_view_cost')) {
            $this->addColumn('total_cost', array(
                'header' => Mage::helper('sales')->__('Total Cost'),
                'index' => 'total_cost',
                'type'  => 'price',
                'currency' => 'base_currency_code',
                'currency_code' => Mage::getStoreConfig('currency/options/base'),
            ));
        }

        $this->addColumn('total_qty', array(
            'header' => Mage::helper('sales')->__('Total Qty'),
            'index' => 'total_qty',
            'type'  => 'number',
        ));

        $this->addColumn('statement_date', array(
            'header' => Mage::helper('udropship')->__('Statement Ready At'),
            'index' => 'statement_date',
            'type'  => 'date',
        ));

        $this->addColumn('udropship_status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'udropship_status',
            'type' => 'options',
            //'renderer' => 'udpo/adminhtml_po_gridRenderer_status',
            'options' => Mage::getSingleton('udpo/source')->setPath('po_statuses')->toOptionHash(),
        ));

        $this->addColumn('action',
            array(
                'header'    => Mage::helper('sales')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('sales')->__('View'),
                        'url'     => array('base'=>'*/po/view'),
                        'field'   => 'udpo_id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true
            ));
        $this->addColumnAfter('default_pos_name', array(
            'header'    => Mage::helper('zolagopos')->__('POS'),
            'index'     => 'default_pos_name',
            'type'      => 'text',
        ), "order_increment_id");

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
                    "main_table.udropship_vendor=vendors.vendor_id", //$cond
                    array("vendor_name" => "vendor_name")//$cols = '*'
                );
                $columnIndex = "vendor_name";
            }
            $collection->setOrder($columnIndex, strtoupper($column->getDir()));
        }
        return $this;
    }

}
