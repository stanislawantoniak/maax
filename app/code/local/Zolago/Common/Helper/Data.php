<?php
class Zolago_Common_Helper_Data extends Mage_Core_Helper_Abstract {

    const XML_PATH_DEFAULT_IDENTITY = "sales_email/order/identity";

    protected $_isGoogleBot;

    protected $_app;
    /**
     * Check is top customer data for varnish request
     * @return boolean
     */
    public function isUserDataRequest() {
        $request = Mage::app()->getRequest();
        if($request->getModuleName()=="orbacommon" &&
                $request->getControllerName()=="ajax_customer" &&
                $request->getActionName()=="get_account_information") {
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
        if(is_null($sender)) {
            $sender = Mage::getStoreConfig(self::XML_PATH_DEFAULT_IDENTITY, $storeId);
        }
        $templateParams['year'] = Mage::getModel('core/date')->date('Y');

        /* @var $mailer Zolago_Common_Model_Core_Email_Template_Mailer */
        $mailer = Mage::getModel('zolagocommon/core_email_template_mailer');
        /** @var Mage_Core_Model_Email_Info $emailInfo */
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($email, empty($name)? $email:$name);
        $emailInfo->addBcc($bcc);
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
        if(!($store instanceof Mage_Core_Model_Store)) {
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
        if($unsecure || $secure) {
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
        try {
            if(file_exists($imageUrl) && is_readable($imageUrl)) {
                return base64_encode(file_get_contents($imageUrl));
            }
        }  catch (Exception $e) {

        }

        return '';
    }

    /**
     * @return boolean
     */
    public function isGoogleBot() {
        if (is_null($this->_isGoogleBot)) {
            $userAgent = empty($_SERVER['HTTP_USER_AGENT'])? null:$_SERVER['HTTP_USER_AGENT'];
            if (empty($userAgent)) {
                $isCrawler = false;
            } else {
                $crawlers = 'Google|msnbot|Rambler|Yahoo|AbachoBOT|accoona|' .
                            'AcioRobot|ASPSeek|CocoCrawler|Dumbot|FAST-WebCrawler|bingbot|' .
                            'GeonaBot|Gigabot|Lycos|MSRBOT|Scooter|AltaVista|IDBot|eStyle|Scrubby';
                $isCrawler = (preg_match("/$crawlers/", $userAgent) > 0);
            }
            $this->_isGoogleBot = $isCrawler;
        }
        return $this->_isGoogleBot;;
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
    public function mergePdfs($pdf_array, $save_file = NULL) {

        $combined_pdf = new Zend_Pdf();
        $pdfs = array();

        foreach($pdf_array as $pdf) {

            // path provided
            if(is_string($pdf)) {
                $pdfs[]  = Zend_Pdf::load($pdf);
            }
            // Zend_Pdf provided
            elseif(is_a($pdf, 'Zend_Pdf')) {
                $pdfs[] = $pdf;
            }
        }

        if(sizeof($pdfs) == 0) {
            return NULL;
        }

        foreach($pdfs as $pdf) {

            $extractor = new Zend_Pdf_Resource_Extractor();
            foreach($pdf->pages as $page) {
                $pdf_extract = $extractor->clonePage($page);
                $combined_pdf->pages[] = $pdf_extract;
            }
        }

        if(!$save_file) {
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

    protected function _getApp() {
        if (!$this->_app) {
            $this->_app = Mage::app();
        }
        return $this->_app;
    }
    /**
     * cache helper function (uses lambda)
     *
     * @param string $key
     * @param string $group
     * @param string $function
     * @param array $params
     * @param int $ttl
     * @return mixed
     */
    public function getCache($key,$group,$function,$params,$ttl = Zolago_Common_Block_Page_Html_Head::BLOCK_CACHE_TTL) {
        if (!($out = $this->_getApp()->loadCache($key)) ||
                !$this->_getApp()->useCache($group)) {
            $out = $function($params);
            if ($this->_getApp()->useCache($group)) {
                $this->_getApp()->saveCache($out,$key,array($group),$ttl);
            }
        }
        return $out;

    }

    public function stringForJs($string,$quote='"',$includeQuotes=false) {
        $finalQuote = $includeQuotes ? $quote : "";
        return $finalQuote.str_replace(array($quote,"\n","\r"),array('\\'.$quote," "," "),$string).$finalQuote;
    }


    public function getSkuvFromSku($sku,$vendorId) {
        $toRemove = $vendorId."-";
        $toRemoveLen = strlen($toRemove);
        if(substr($sku,0,$toRemoveLen) == $toRemove) {
            return substr_replace($sku,"",0,$toRemoveLen);
        } else {
            return $sku;
        }
    }

    public function isOwnStore() {
        return Mage::app()->getWebsite()->getHaveSpecificDomain() ? true : false;
    }

    /**
     * @param $storeCode
     * @return false|Mage_Core_Model_Store
     */
    public function getStoreByCode($storeCode)
    {
        $stores = array_keys(Mage::app()->getStores());
        foreach($stores as $id) {
            $store = Mage::app()->getStore($id);
            if($store->getCode() == $storeCode) {
                return $store;
            }
        }
        return Mage::getModel('core/store'); // Empty model
    }

    private function getConfigIsGallery() {
        $flag = (string)Mage::getConfig()->getNode('global/is_gallery');
        return $flag;
    }

    /**
     * is_gallery flag tell us to use functionality from Modago(true) or not(false)
     * if false this will be hidden/blocked:
     *
     * portal vendora:
     * 		rozliczenia
     * 		warunki współpracy
     * 		na ekranie logowania - nowe konto
     *
     * admin: rejestracje sklepów
     * 		recenzje sklepów
     * 		koszty marketingu
     * 		rozliczenia
     * 		salda
     * 		faktury
     * 		wypłaty
     * 		konfiguracje z tym związane
     *
     * @return bool
     */
    public function useGalleryConfiguration() {
        if ($this->getConfigIsGallery() === "false") {
            return false;
        }
        return true;
    }

    /*
     * Retrieve information that vendor/operator can see/use Loyalty card section
     *
     * @return bool
     */
    public function useLoyaltyCardSection() {
        if (!$this->useGalleryConfiguration() // For now Modago don't use loyalty card
                && $this->hasDedicatedLoyaltyCardEditPhtml() // Section can be shown only if for vendor dedicated skin there is edit phtml
           ) {
            return true;
        }
        return false;
    }

    /**
     * Check if view file for edit card exist
     *
     * @return bool
     */
    protected function hasDedicatedLoyaltyCardEditPhtml() {
        /**
         * For more info:
         * @see ZolagoOs_LoyaltyCard_Block_Vendor_Card_Abstract::getTemplateFile()
         * @see layout/zosloyaltycard.xml
         * @see Mage_Core_Model_Design_Package::getBaseDir()
         */
        $area = 'frontend';
        $package = Mage::app()->getStore()->getConfig("design/package/name");
        $theme = Mage::app()->getStore()->getConfig("design/theme/skin");
        $type = 'template';
        $baseDir = Mage::getBaseDir('design') . DS . $area . DS . $package . DS . $theme . DS . $type;
        $templatePath = ZolagoOs_LoyaltyCard_Block_Vendor_Card_Edit::TEMPLATE_PATH;

        $file = $baseDir . DS . $templatePath;

        $exist = file_exists($file);
        return $exist;
    }

    /**
     * @param string $code
     * @return bool
     */
    public function isModuleActive($code)
    {
        return ('true' == (string)Mage::getConfig()->getNode('modules/'.$code.'/active'));
    }
    
    /**
     * list of available countries (for delivery)
     */
     public function getAvailableCountry() {
         /*
         $collection = Mage::getModel('udropship/vendor_shipping')->getCollection();
         $collection->joinShipping();
         $collection->getSelect()->columns('shipping.customer_ship_class');
         $tmp = array();
         foreach ($collection as $item) {
             $class = $item->getCustomerShipClass();
             foreach (explode(',',$class) as $c) {
                 $tmp[$c] = $c;
             }
         }
         
         */
         $collection = Mage::getModel('udshipclass/customer')->getCollection();
         $conn = $collection->getConnection();
         $table = $collection->getTable('udshipclass/customer_row');
         $select = $conn->select()->from($table);
         $tmp = array(
             'PL' => Mage::app()->getLocale()->getCountryTranslation('PL') // Polska zawsze
         );
         foreach ($conn->fetchAll($select) as $country) {           
             $tmp[$country['country_id']] = Mage::app()->getLocale()->getCountryTranslation($country['country_id']);
         }
         return array($tmp);
     }

}