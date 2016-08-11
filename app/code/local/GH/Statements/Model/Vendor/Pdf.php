<?php
/**
 * pdf with rma details
 */
class GH_Statements_Model_Vendor_Pdf extends Zolago_Pdf_Model_Pdf {
    const PDF_PATH = 'statements';
    const PDF_PREFIX = 'statement_';
	const VENDOR_PDF_PAGE1_TEMPLATE = 'ghstatements/vendor/page1.phtml';
	const VENDOR_PDF_PAGE1_BLOCK = 'ghstatements/vendor_page1';
	const VENDOR_PDF_PAGE2_TEMPLATE = 'ghstatements/vendor/page2.phtml';
	const VENDOR_PDF_PAGE2_BLOCK = 'ghstatements/vendor_page2';
	const VENDOR_PDF_PAGE3_TEMPLATE = 'ghstatements/vendor/page3.phtml';
	const VENDOR_PDF_PAGE3_BLOCK = 'ghstatements/vendor_page3';
	const VENDOR_PDF_PAGE4_TEMPLATE = 'ghstatements/vendor/page4.phtml';
	const VENDOR_PDF_PAGE4_BLOCK = 'ghstatements/vendor_page4';
	const VENDOR_PDF_FOOTER_TEMPLATE = 'ghstatements/vendor/footer.phtml';
	const VENDOR_PDF_FOOTER_BLOCK = 'ghstatements/vendor_footer';

	protected $page1Html;
	protected $page2Html;
	protected $page3Html;
	protected $page4Html;
	protected $footer;

	protected $statementId;

    protected function _getFilePath() {
        return self::PDF_PATH;
    }
    protected function _getFilePrefix() {
        return self::PDF_PREFIX;
    }

	public function getPdfFile(&$statement) {
	    $this->setHtmlFooter($this->footer);
		@$this->WriteHTML($this->page1Html);
		$this->AddPage();
		@$this->WriteHTML($this->page2Html);
		$this->AddPage();
		@$this->WriteHTML($this->page3Html);
		$this->AddPage();
		@$this->WriteHTML($this->page4Html);

		$path = $this->_getFileName($this->statementId);
		$this->Output($path,'F');

		$nonAbsolutePath = explode('media',$path);

		$statement->setStatementPdf($nonAbsolutePath[1])->save();

		return $statement->getStatementPdf();
	}
	public function generateFooter($data) {
		$this->footer = Mage::app()->getLayout()
			->createBlock(self::VENDOR_PDF_FOOTER_BLOCK)
			->setTemplate(self::VENDOR_PDF_FOOTER_TEMPLATE)
			->setData('page_data',$data)
			->toHtml();
		return $this->page1Html;
	}
	public function generatePage1Html($data) {
		$this->page1Html = Mage::app()->getLayout()
			->createBlock(self::VENDOR_PDF_PAGE1_BLOCK)
			->setTemplate(self::VENDOR_PDF_PAGE1_TEMPLATE)
			->setData('page_data',$data)
			->toHtml();
		return $this->page1Html;
	}

	public function generatePage2Html($data) {
		$this->page2Html = Mage::app()->getLayout()
			->createBlock(self::VENDOR_PDF_PAGE2_BLOCK)
			->setTemplate(self::VENDOR_PDF_PAGE2_TEMPLATE)
			->setData('page_data',$data)
			->toHtml();
		return $this->page2Html;
	}

	public function generatePage3Html($data) {
		$this->page3Html = Mage::app()->getLayout()
			->createBlock(self::VENDOR_PDF_PAGE3_BLOCK)
			->setTemplate(self::VENDOR_PDF_PAGE3_TEMPLATE)
			->setData('page_data',$data)
			->toHtml();
		return $this->page3Html;
	}

	public function generatePage4Html($data) {
		$this->page4Html = Mage::app()->getLayout()
			->createBlock(self::VENDOR_PDF_PAGE4_BLOCK)
			->setTemplate(self::VENDOR_PDF_PAGE4_TEMPLATE)
			->setData('page_data',$data)
			->toHtml();
		return $this->page4Html;
	}

	public function setVariables($statement) {
		$this->statementId = $statement->getId();
	}
}