<?php
/**
 * pdf with rma details
 */
class Zolago_Rma_Model_Pdf extends Zolago_Pdf_Model_Pdf {
    const PDF_PATH = 'rma';
    const PDF_PREFIX = 'rma_';
	const RMA_PDF_TEMPLATE = 'zolagorma/pdf_details.phtml';
	const RMA_PDF_BLOCK = 'zolagorma/pdf';

    protected function _getFilePath() {
        return self::PDF_PATH;
    }
    protected function _getFilePrefix() {
        return self::PDF_PREFIX;
    }

	public function getPdfFile($rmaid) {
		@$this->WriteHTML($this->getHtml());
		$path = $this->_getFileName($rmaid);
		$this->Output($path,'F');
		return $path;
	}

	protected function getHtml() {
		$content = Mage::app()->getLayout()
			->createBlock(self::RMA_PDF_BLOCK)
			->setTemplate(self::RMA_PDF_TEMPLATE)
			->toHtml();
		return $content;
	}
}