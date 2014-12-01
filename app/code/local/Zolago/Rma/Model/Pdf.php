<?php
/**
 * pdf with rma details
 */
class Zolago_Rma_Model_Pdf extends Orba_Common_Model_Pdf {	
    const RMA_PDF_PATH = 'rma';
    const RMA_PDF_PREFIX = 'rma_';
    
    protected function _getFilePath() {
        return self::RMA_PDF_PATH;
    }
    protected function _getFilePrefix() {
        return self::RMA_PDF_PREFIX;
    }
    protected function _preparePages($id) {
        $page = $this->_newPage();
        $this->_setFont($page,12,'b');
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $page->drawText('Dummy document - RMA Details',200,250,'UTF-8');
    }
}