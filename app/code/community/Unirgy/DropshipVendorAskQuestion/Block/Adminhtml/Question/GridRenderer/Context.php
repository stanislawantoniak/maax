<?php

class Unirgy_DropshipVendorAskQuestion_Block_Adminhtml_Question_GridRenderer_Context extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    public function render(Varien_Object $row)
    {
        $html = '';
        if ($row->getShipmentId()) {
            $html .= sprintf('<h5>SHIPMENT:</h5> <a onclick="this.target=\'blank\'" href="%sshipment_id/%s/">#%s</a> for order <a onclick="this.target=\'blank\'" href="%sorder_id/%s/">#%s</a>', $this->getUrl('adminhtml/sales_shipment/view'), $row->getShipmentId(), $row->getShipmentIncrementId(), $this->getUrl('adminhtml/sales_order/view'), $row->getOrderId(), $row->getOrderIncrementId());
        }
        if ($row->getProductId()) {
            $html .= sprintf('<h5>PRODUCT:</h5> SKU: %s<br /><a onclick="this.target=\'blank\'" href="%sid/%s/">%s</a>', $row->getProductSku(), $this->getUrl('adminhtml/catalog_product/edit'), $row->getProductId(), $row->getProductName());
        }
        return $html;
    }
}