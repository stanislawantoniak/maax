<?php
/**
  
 */

class ZolagoOs_OmniChannel_Block_Adminhtml_Shipment_View
    extends Mage_Adminhtml_Block_Sales_Order_Shipment_View
{
    public function __construct()
    {
        parent::__construct();

        $shipment = $this->getShipment();
        if (($id = $shipment->getId()) && $shipment->getUdropshipStatus()!=ZolagoOs_OmniChannel_Model_Source::SHIPMENT_STATUS_SHIPPED) {
            $url = $this->getUrl('zolagoosadmin/adminhtml_shipment/ship', array(
                'id'=>$id,
                'order_id'=>$this->getRequest()->getParam('order_id')
            ));
            $this->_addButton('ship', array(
                'label'     => Mage::helper('sales')->__('Mark as shipped'),
                'class'     => 'save',
                'onclick'   => "setLocation('$url')"
            ));
        }
    }

    public function getHeaderText()
    {
        $header = parent::getHeaderText();
        $status = $this->getShipment()->getUdropshipStatus();
        if (is_numeric($status)) {
            $statuses = Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash();
            $header .= ' ['.$statuses[$this->getShipment()->getUdropshipStatus()].']';
        }
        return $header;
    }
}