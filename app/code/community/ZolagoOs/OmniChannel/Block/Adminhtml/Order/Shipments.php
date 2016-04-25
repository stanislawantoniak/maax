<?php
/**
  
 */

class ZolagoOs_OmniChannel_Block_Adminhtml_Order_Shipments
    extends Mage_Adminhtml_Block_Sales_Order_View_Tab_Shipments
{

    protected function _prepareCollection()
    {
        //TODO: add full name logic
        $collection = Mage::getResourceModel('sales/order_shipment_collection')
            ->addAttributeToSelect('entity_id')
            ->addAttributeToSelect('increment_id')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('total_qty')
            ->addAttributeToSelect('udropship_status')
            ->addAttributeToSelect('udropship_vendor')
            ->addAttributeToSelect('udropship_method_description')
            ->addAttributeToSelect('base_shipping_amount')
            ->setOrderFilter($this->getOrder())
        ;

        $this->setCollection($collection);
        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('increment_id', array(
            'header' => Mage::helper('sales')->__('Shipment #'),
            'index' => 'increment_id',
        ));

        $this->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('Date Shipped'),
            'index' => 'created_at',
            'type' => 'datetime',
        ));

        $this->addColumn('total_qty', array(
            'header' => Mage::helper('sales')->__('Total Qty'),
            'index' => 'total_qty',
            'type'  => 'number',
        ));

        $this->addColumn('base_shipping_amount', array(
            'header' => Mage::helper('sales')->__('Shipping Price'),
            'index' => 'base_shipping_amount',
            'type'  => 'price',
            'currency_code' => $this->getOrder()->getBaseCurrencyCode(),
        ));

        $this->addColumn('udropship_status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'udropship_status',
            'type' => 'options',
            'options' => Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash(),
        ));

        $this->addColumn('udropship_vendor', array(
            'header' => Mage::helper('udropship')->__('Vendor'),
            'index' => 'udropship_vendor',
            'type' => 'options',
            'options' => Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionHash(),
            'filter' => 'udropship/vendor_gridColumnFilter'
        ));

        $this->addColumn('udropship_method_description', array(
            'header' => Mage::helper('udropship')->__('Method'),
            'index' => 'udropship_method_description',
        ));
        return Mage_Adminhtml_Block_Widget_Grid::_prepareColumns();
    }
}
