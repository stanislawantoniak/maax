<?php
class Zolago_Common_Helper_Data extends Mage_Core_Helper_Abstract {
	
	const XML_PATH_DEFAULT_IDENTITY = "sales_email/order/identity";
	
	/**
	 * Check is top customer data for varnish request
	 * @return boolean
	 */
	public function isUserDataRequest() {
		$request = Mage::app()->getRequest();
		if($request->getModuleName()=="orbacommon" && 
		   $request->getControllerName()=="ajax_customer" && 
		   $request->getActionName()=="get_account_information"){
			return true;
		}
		return false;
	}
	
	/**
	 * @param string $email
	 * @param string $name
	 * @param string $template
	 * @param array $templateParams
	 * @param int | Mage_Core_Model_Store $storeId
	 */
	public function sendEmailTemplate($email, $name, $template, 
			array $templateParams = array(), $storeId=true, $sender=null, $bcc=null) {

            $templateParams['use_attachments'] = true;
		
			$storeId = Mage::app()->getStore($storeId)->getId();
			if(is_null($sender)){
				$sender = Mage::getStoreConfig(self::XML_PATH_DEFAULT_IDENTITY, $storeId);
			}

            /* @var $mailer Zolago_Common_Model_Core_Email_Template_Mailer */
			$mailer = Mage::getModel('zolagocommon/core_email_template_mailer');
            /** @var Mage_Core_Model_Email_Info $emailInfo */
			$emailInfo = Mage::getModel('core/email_info');
			$emailInfo->addTo($email, $name);
			$mailer->addEmailInfo($emailInfo);

			// Set all required params and send emails
			$mailer->setSender($sender);
			$mailer->setStoreId($storeId);
			$mailer->setTemplateId($template);
			$mailer->setTemplateParams($templateParams);
			return $mailer->send();
	}
	/**
	 * @param string $filename
	 * @param Mage_Core_Model_Store | int | string $store
	 * @param string $type
	 * @return string
	 */
	public function getDesignFileByStore($filename, $store, $type='skin') {
		if(!($store instanceof Mage_Core_Model_Store)){
			$store = Mage::app()->getStore($store);
		}
		$oldPack = Mage::getDesign()->getPackageName();
		$oldTheme = Mage::getDesign()->getTheme($type);
		
		Mage::getDesign()->
			setPackageName($store->getConfig("design/package/name"))->
			setTheme($store->getConfig("design/theme/" . $type));
		
		$return = Mage::getDesign()->getFilename($filename, array("_type"=>$type));
		
		Mage::getDesign()->
			setPackageName($oldPack)->
			setTheme($type, $oldTheme);
		
		return $return;
	}
	/**
	 * @param string $imageUrl
	 * @param Mage_Core_Model_Store | int $storeId
	 * @return string
	 */
	public function getRelativePath($url, $storeId= Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID) {
		$unsecure = strpos($url,"http://")===0;
		$secure = strpos($url,"https://")===0;
		if($unsecure || $secure){
			$storeUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, $secure);
			$url = str_replace($storeUrl, "", $url);
		}
		return $url;
	}
	
	/**
	 * @param string $imageUrl
	 * @param Mage_Core_Model_Store | int $storeId
	 */
	public function getFileBase64ByUrl($imageUrl, $storeId= Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID) {
		$imageUrl = $this->getRelativePath($imageUrl);
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

    /**
     * @param $text
     * @return string
     */
    public function nToBr($text)
    {
        if (empty($text)) {
            return '';
        }
        return strip_tags(nl2br(trim($text)), '<br>');
    }

	public function str2Url($string) {
		$chars = Array(
			//WIN
			"\xb9" => "a", "\xa5" => "A", "\xe6" => "c", "\xc6" => "C",
			"\xea" => "e", "\xca" => "E", "\xb3" => "l", "\xa3" => "L",
			"\xf3" => "o", "\xd3" => "O", "\x9c" => "s", "\x8c" => "S",
			"\x9f" => "z", "\xaf" => "Z", "\xbf" => "z", "\xac" => "Z",
			"\xf1" => "n", "\xd1" => "N",
			//UTF
			"\xc4\x85" => "a", "\xc4\x84" => "A", "\xc4\x87" => "c", "\xc4\x86" => "C",
			"\xc4\x99" => "e", "\xc4\x98" => "E", "\xc5\x82" => "l", "\xc5\x81" => "L",
			"\xc3\xb3" => "o", "\xc3\x93" => "O", "\xc5\x9b" => "s", "\xc5\x9a" => "S",
			"\xc5\xbc" => "z", "\xc5\xbb" => "Z", "\xc5\xba" => "z", "\xc5\xb9" => "Z",
			"\xc5\x84" => "n", "\xc5\x83" => "N",
			//ISO
			"\xb1" => "a", "\xa1" => "A", "\xe6" => "c", "\xc6" => "C",
			"\xea" => "e", "\xca" => "E", "\xb3" => "l", "\xa3" => "L",
			"\xf3" => "o", "\xd3" => "O", "\xb6" => "s", "\xa6" => "S",
			"\xbc" => "z", "\xac" => "Z", "\xbf" => "z", "\xaf" => "Z",
			"\xf1" => "n", "\xd1" => "N");

		return strtr($string,$chars);
	}
}