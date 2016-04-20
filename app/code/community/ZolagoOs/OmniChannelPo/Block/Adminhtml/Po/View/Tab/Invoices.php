<?php
/**
  
 */
 
class ZolagoOs_OmniChannelPo_Block_Adminhtml_Po_View_Tab_Invoices extends Mage_Adminhtml_Block_Sales_Order_View_Tab_Invoices
{
    public function setCollection($collection)
    {
        $collection->addAttributeToFilter('udpo_id', $this->getPo()->getId());
        return parent::setCollection($collection);
    }
    public function getPo()
    {
        return Mage::registry('current_udpo');
    }
    
    public function getRowUrl($row)
    {
        return $this->getUrl('adminhtml/sales_order_invoice/view',
            array(
                'invoice_id'=> $row->getId(),
                'order_id'  => $row->getOrderId()
            )
        );
    }

}