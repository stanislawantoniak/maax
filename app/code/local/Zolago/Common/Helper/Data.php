<?php
class Zolago_Common_Helper_Data extends Mage_Core_Helper_Abstract {
	public function getCarriersForVendor() {
		return Mage::helper("zolagodropship")->getAllowedCarriers();
	}
	
	/**
	 * Mereges pdf files
	 * @param array
	 * @return Zend_Pdf
	 */
	public function mergePdfs($pdf_array, $save_file = NULL){
		
		$combined_pdf = new Zend_Pdf();
		$pdfs = array();
		
		foreach($pdf_array as $pdf){
			
			// path provided
			if(is_string($pdf)){
				$pdfs[]  = Zend_Pdf::load($pdf);
			}
			// Zend_Pdf provided
			elseif(is_a($pdf, 'Zend_Pdf')){
				$pdfs[] = $pdf;
			}
		}
		
		if(sizeof($pdfs) == 0){
			return NULL;
		}
		
		foreach($pdfs as $pdf){
			
			$extractor = new Zend_Pdf_Resource_Extractor();
			foreach($pdf->pages as $page){
				$pdf_extract = $extractor->clonePage($page);
				$combined_pdf->pages[] = $pdf_extract;
			}
		}
		
		if(!$save_file){
			$save_file ="mergefile.pdf";
		}
		
		$combined_pdf->save($save_file);
	}
}