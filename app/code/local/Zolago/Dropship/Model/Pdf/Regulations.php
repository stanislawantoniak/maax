<?php
class Zolago_Dropship_Model_Pdf_Regulations extends
    Zolago_Pdf_Model_Pdf {
    
    protected function _getFilePath() {
        return 'vendor';
    }
    protected function _getFilePrefix() {
        return 'vendor_';
    }
    public function getPdfFile($vendorId) {
        @$this->WriteHTML($this->_getHtml($vendorId));
        $path = $this->_getFileName($vendorId);
        $this->Output($path,'F');
        return $path;
    }
    protected function _getHtml($vendorId) {
        $content = Mage::app()->getLayout()
            ->createBlock('zolagodropship/vendor_pdf')
            ->setTemplate('zolagodropship/vendor/pdf.phtml')
            ->setVendorId($vendorId)
            ->toHtml();
        return $content;
    }
}