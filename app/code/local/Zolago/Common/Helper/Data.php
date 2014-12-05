<?php
class Zolago_Common_Helper_Data extends Mage_Core_Helper_Abstract {
	
	
	/**
	 * @param string $imageUrl
	 * @param Mage_Core_Model_Store | int $storeId
	 */
	public function getFileBase64ByUrl($imageUrl, $storeId= Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID) {
		$unsecure = strstr("http://", $imageUrl)==0;
		$secure = strstr("https://", $imageUrl)==0;
		if($unsecure || $secure){
			$storeUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, $secure);
			$imageUrl = str_replace($storeUrl, "", $imageUrl);
		}
		try{
			if(file_exists($imageUrl) && is_readable($imageUrl)){
				return base64_encode(file_get_contents($imageUrl));
			}
		}  catch (Exception $e){
		
		}
		
		return '';
	}
	
	/**
	 * @return boolean
	 */
	public function isGoogleBot(){
        $remoteAddr = $_SERVER['REMOTE_ADDR']; // Always available?
        $remoteName = gethostbyaddr($remoteAddr);
		// Test tmp address for localhost
        //$remoteName = "crawl-127-0-0-1.googlebot.com";
        if(preg_match("/^crawl-(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)-(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)-(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)-(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.googlebot\.com$/", $remoteName, $reg)){
			$segemnts = explode(".", $remoteAddr);
            if($segemnts[0]==$reg[1] && $segemnts[1]==$reg[2] && $segemnts[2]==$reg[3] && $segemnts[3]==$reg[4]){
				
                return true;
            }
        }
        return false;
    }
	
	/**
	 * @return array
	 */
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