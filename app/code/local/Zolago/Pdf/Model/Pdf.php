<?php
include_once(Mage::getBaseDir("lib")."/mPDF/mpdf.php");
class Zolago_Pdf_Model_Pdf extends mPDF {
	const PDF_PATH = 'pdf';
	const PDF_PREFIX = 'pdf_';

	protected function _getFilePath() {
		return self::PDF_PATH;
	}
	protected function _getFilePrefix() {
		return self::PDF_PREFIX;
	}

	protected function _getFileName($id) {
		$sfx = $id % 100;
		$a = floor($sfx / 10);
		$b = $sfx % 10;
		$path = Mage::getBaseDir('media').DS.$this->_getFilePath().DS.$b.DS.$a.DS;
		if(!file_exists($path)) {
			mkdir($path,0755,true);
		}
		$filename = $path.$this->_getFilePrefix().$id.'.pdf';
		return $filename;
	}
}