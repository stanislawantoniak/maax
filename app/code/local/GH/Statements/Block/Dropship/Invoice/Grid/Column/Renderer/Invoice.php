<?php

class GH_Statements_Block_Dropship_Invoice_Grid_Column_Renderer_Invoice
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row) {
        /** @var GH_Wfirma_Helper_Data $helper */
        $helper = Mage::helper('ghwfirma');

        $href = $helper->getVendorInvoiceUrl($row->getData('vendor_invoice_id'));
        $fileName = Mage::helper('ghcommon')->cleanFileName($row->getData("wfirma_invoice_number"),'-').'.pdf';
        return  "<a href='{$href}'><i class='icon-file-text-alt'></i> {$fileName}</a>";
    }
}