<?php
/**
 * Altima Lookbook Professional Extension
 *
 * Altima web systems.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://blog.altima.net.au/lookbook-magento-extension/lookbook-professional-licence/
 *
 * @category   Altima
 * @package    Altima_LookbookProfessional
 * @author     Altima Web Systems http://altimawebsystems.com/
 * @license    http://blog.altima.net.au/lookbook-magento-extension/lookbook-professional-licence/
 * @email      support@altima.net.au
 * @copyright  Copyright (c) 2012 Altima Web Systems (http://altimawebsystems.com/)
 */
class Altima_Lookbookslider_Helper_Data extends Mage_Core_Helper_Abstract {

    function __construct() {
        $this->temp = Mage::getStoreConfig('lookbookslider/general/' . base64_decode('c2VyaWFs'));
    }

    /**
     * Encode the mixed $valueToEncode into the JSON format
     *
     * @param mixed $valueToEncode
     * @param  boolean $cycleCheck Optional; whether or not to check for object recursion; off by default
     * @param  array $options Additional options used during encoding
     * @return string
     */
    public function jsonEncode($valueToEncode, $cycleCheck = false, $options = array()) {
        $json = Zend_Json::encode($valueToEncode, $cycleCheck, $options);
        /* @var $inline Mage_Core_Model_Translate_Inline */
        $inline = Mage::getSingleton('core/translate_inline');
        if ($inline->isAllowed()) {
            $inline->setIsJson(true);
            $inline->processResponseBody($json);
            $inline->setIsJson(false);
        }

        return $json;
    }

    public function getEnabled() {
        return Mage::getStoreConfig('lookbookslider/general/enabled');
    }

    public function getEnableJquery() {
        return Mage::getStoreConfig('lookbookslider/general/enable_jquery');
    }

    public function getUseFullProdUrl() {
        return Mage::getStoreConfig('lookbookslider/general/cat_path_in_prod_url');
    }

    public function getInterdictOverlap() {
        $value = Mage::getStoreConfig('lookbookslider/general/interdict_areas_overlap');
        if ($value == 1) {
            return 'true';
        } else {
            return 'false';
        }
    }

    public function getMaxUploadFilesize() {
        return intval(Mage::getStoreConfig('lookbookslider/general/max_upload_filesize'));
    }

    public function getAllowedExtensions() {
        return Mage::getStoreConfig('lookbookslider/general/allowed_extensions');
    }

    public function canShowProductDescr() {
        return Mage::getStoreConfig('lookbookslider/general/show_product_desc');
    }

    public function canShowAddToCart() {
        return Mage::getStoreConfig('lookbookslider/general/show_add_to_cart');
    }

    public function canShowPinitButton() {
        return Mage::getStoreConfig('lookbookslider/general/show_pinit_button');
    }

    public function getHotspotIcon() {
        $config_icon_path = Mage::getStoreConfig('lookbookslider/general/hotspot_icon');
        if ($config_icon_path == '')
            $config_icon_path = 'default/hotspot-icon.png';
        return Mage::getBaseUrl('media') . 'lookbookslider/icons/' . $config_icon_path;
    }

    public function getHotspotIconPath() {
        $config_icon_path = Mage::getStoreConfig('lookbookslider/general/hotspot_icon');
        if ($config_icon_path == '')
            $config_icon_path = 'default/hotspot-icon.png';
        return Mage::getBaseDir('media') . DS . 'lookbookslider' . DS . 'icons' . DS . str_replace('/', DS, $config_icon_path);
    }

    /**
     * Returns the resized Image URL
     *
     * @param string $imgUrl - This is relative to the the media folder (custom/module/images/example.jpg)
     * @param int $x Width
     * @param int $y Height
     * Remember your base image or big image must be in Root/media/lookbookslider/example.jpg
     *
     * echo Mage::helper('lookbookslider')->getResizedUrl("lookbookslider/example.jpg",101,65)
     *
     * By doing this new image will be created in Root/media/lookbookslider/101X65/example.jpg
     */
    public function getResizedUrl($imgUrl, $x, $y = NULL, $noresize = FALSE) {

        $config_no_resample = Mage::getStoreConfig('lookbookslider/general/no_resample');
        $path_parts = pathinfo($imgUrl);
        $imgPath = $path_parts['dirname'];
        $imgName = $path_parts['basename'];
        $imgNameExt =  $path_parts['filename'];
        $imgExt = $path_parts['extension'];

        $imgPath = str_replace("/", DS, $imgPath);
        $imgPathFull = Mage::getBaseDir("media") . DS . $imgPath . DS . $imgName;

        if ($noresize) {
            $imgUrl = str_replace(DS, "/", $imgPath);
            return Mage::getBaseUrl("media") . $imgUrl . "/" . $imgName;
        }
        
        if($config_no_resample):
            $orig_dimensions = getimagesize($imgPathFull);
            if($orig_dimensions[0] == $x && $orig_dimensions[1] == $y):
                $imgUrl = str_replace(DS, "/", $imgPath);
                return Mage::getBaseUrl("media") . $imgUrl . "/" . $imgName;
            endif;
        endif;

        $width = $x;
        $y ? $height = $y : $height = $x;
        $resizeFolder = $width . "X" . $height;
        $imageResizedPath = Mage::getBaseDir("media") . DS . $imgPath . DS . $resizeFolder . DS . $imgName;
        $imageResizedPathPng = Mage::getBaseDir("media") . DS . $imgPath . DS . $resizeFolder . DS . $imgNameExt . '.png';
        if (file_exists($imageResizedPathPng)) {
            $imageResizedPath = $imageResizedPathPng;
            $imgName = $imgNameExt . '.png';
        }
        if (!file_exists($imageResizedPath) && file_exists($imgPathFull)) :
            $dimensions = getimagesize($imgPathFull);
            if ($dimensions[0] < $dimensions[1]):
                $this->copyTransparent($imgPathFull, $width, $height, $imageResizedPathPng, $imgExt);
                $imgUrl = str_replace(DS, "/", $imgPath);
                return Mage::getBaseUrl("media") . $imgUrl . "/" . $resizeFolder . "/" . $imgNameExt . '.png';
            endif;
            $imageObj = new Varien_Image($imgPathFull);
            $imageObj->constrainOnly(FALSE);
            $imageObj->keepAspectRatio(TRUE);
            $imageObj->keepTransparency(TRUE);
            $imageObj->keepFrame(FALSE);
            $imageObj->quality(100);
            if ($imageObj->getOriginalWidth() < $imageObj->getOriginalHeight()) {
                $imageObj->keepFrame(TRUE);
                $imageObj->backgroundColor(array(255, 255, 255));
                $imageObj->resize($width, $height);
            } elseif (($width / $height) > ($imageObj->getOriginalWidth() / $imageObj->getOriginalHeight())) {
                $imageObj->resize($width, null);
            } else {
                $imageObj->resize(null, $height);
            }
            $cropX = 0;
            $cropY = 0;
            if ($imageObj->getOriginalWidth() > $width) {
                $cropX = intval(($imageObj->getOriginalWidth() - $width) / 2);
            } elseif ($imageObj->getOriginalHeight() > $height) {
                $cropY = intval(($imageObj->getOriginalHeight() - $height) / 2);
            }
            $imageObj->crop($cropY, $cropX, $cropX, $cropY);
            $imageObj->save($imageResizedPath);
        endif;
        $imgUrl = str_replace(DS, "/", $imgPath);
        return Mage::getBaseUrl("media") . $imgUrl . "/" . $resizeFolder . "/" . $imgName;
    }

    function copyTransparent($src, $x, $y, $output, $imgExt) {
        /* check and create dir */
        $imgPath = Mage::helper('lookbookslider')->splitImageValue($output, "path");
        $io = new Varien_Io_File();
        $io->checkAndCreateFolder($imgPath);

        $dimensions = getimagesize($src);
        $x_src = $dimensions[0];
        $y_src = $dimensions[1];
        $im = @imagecreatetruecolor($x, $y) or die('Cannot Initialize new GD image stream');
        // Save transparency
        imagealphablending($im, true);         
        if ($imgExt == 'png') {
            $src_ = imagecreatefrompng($src) or die('Cannot load original PNG');;
        } elseif ($imgExt == 'gif') {
            $src_ = imagecreatefromgif($src) or die('Cannot load original GIF');;
        } else {
            $src_ = imagecreatefromjpeg($src) or die('Cannot load original JPEG');;
        }
        // Prepare alpha channel for transparent background
        $alpha_channel = imagecolorallocatealpha($im, 0, 0, 0, 127);
        // Fill image
        imagefill($im, 0, 0, $alpha_channel);

        // Scale image
        $ratio_orig = $x_src / $y_src;
        if ($x / $y >= $ratio_orig) {
            $x_new = $y * $ratio_orig;
            $y_new = $y;
        } else {
            $y_new = $x / $ratio_orig;
            $x_new = $x;
        }

        $des_x = ($x - $x_new) / 2;
        $des_y = ($y - $y_new) / 2;
        // Copy from other
        imagecopyresampled($im, $src_, $des_x, $des_y, 0, 0, $x_new, $y_new, $x_src, $y_src);
        imagepng($im, $output);

        // Save PNG
        imagealphablending($im, false); 
        imagesavealpha($im,true); 
        imagepng($im, $output, 9);
        imagedestroy($im);
    }

    /**
     * Splits images Path and Name
     *
     * Path=lookbook/
     * Name=example.jpg
     *
     * @param string $imageValue
     * @param string $attr
     * @return string
     */
    public function splitImageValue($imageValue, $attr = "name") {
        $imArray = explode("/", $imageValue);

        $name = $imArray[count($imArray) - 1];
        $path = implode("/", array_diff($imArray, array($name)));
        if ($attr == "path") {
            return $path;
        } else
            return $name;
    }

    /**
     * Splits images Path and Name
     *
     * img_path=lookbook/example.jpg
     *
     * @param string $img_path
     * @return array('width'=>$width, 'height'=>$height)
     */
    public function getImageDimensions($img_path) {
        if (file_exists($img_path)) {
            $imageObj = new Varien_Image($img_path);
            $width = $imageObj->getOriginalWidth();
            $height = $imageObj->getOriginalHeight();
            $result = array('width' => $width, 'height' => $height);
        } else {
            $result = array('error' => "$img_path does not exists");
        }
        return $result;
    }

  /*  public function getFullProductUrl($product) {
        if (is_object($product) && $product->getSku()) {
            $allCategoryIds = $product->getCategoryIds();
            $lastCategoryId = end($allCategoryIds);
            $lastCategory = Mage::getModel('catalog/category')->load($lastCategoryId);
            $lastCategoryUrl = $lastCategory->getUrl();
            $url = str_replace(Mage::getStoreConfig('catalog/seo/category_url_suffix'), '/', $lastCategoryUrl) . basename($product->getUrlKey()) . Mage::getStoreConfig('catalog/seo/product_url_suffix');
            
            //$url = $product->getProductUrl();
            $url = $product->getAttributeRawValue($productId, 'url_key', Mage::app()->getStore());
        } else {
            $url = '';
        }
        return $url;
    }
    */
    /***************************************/
public static function getFullProductUrl (Mage_Catalog_Model_Product $product , 
        Mage_Catalog_Model_Category $category = null , 
        $mustBeIncludedInNavigation = true ){

    // Try to find url matching provided category
    if( $category != null){
        // Category is no match then we'll try to find some other category later
        if( !in_array($product->getId() , $category->getProductCollection()->getAllIds() ) 
                ||  !self::isCategoryAcceptable($category , $mustBeIncludedInNavigation )){
            $category = null;
        }
    }
    if ($category == null) {
        if( is_null($product->getCategoryIds() )){
            return $product->getProductUrl();
        }
        $catCount = 0;
        $productCategories = $product->getCategoryIds();
        // Go through all product's categories
        while( $catCount < count($productCategories) && $category == null ) {
            $tmpCategory = Mage::getModel('catalog/category')->load($productCategories[$catCount]);
            // See if category fits (active, url key, included in menu)
            if ( !self::isCategoryAcceptable($tmpCategory , $mustBeIncludedInNavigation ) ) {
                $catCount++;
            }else{
                $category = Mage::getModel('catalog/category')->load($productCategories[$catCount]);
            }
        }
    }
    $url = (!is_null( $product->getUrlPath($category))) ?  Mage::getBaseUrl() . $product->getUrlPath($category) : $product->getProductUrl();
    return $url;
}

/**
 * Checks if a category matches criteria: active && url_key not null && included in menu if it has to
 */
protected static function isCategoryAcceptable(Mage_Catalog_Model_Category $category = null, $mustBeIncludedInNavigation = true){
    if( !$category->getIsActive() || is_null( $category->getUrlKey() )
        || ( $mustBeIncludedInNavigation && !$category->getIncludeInMenu()) ){
        return false;
    }
    return true;
}
/*********************************************************/
    function checkEntry($domain, $ser) {
        if ($this->isEnterpr()) {
            $key = sha1(base64_decode('bG9va2Jvb2tzbGlkZXJfZW50ZXJwcmlzZQ=='));
        } else {
            $key = sha1(base64_decode('YWx0aW1hbG9va2Jvb2tzbGlkZXI='));
        }

        $domain = str_replace('www.', '', $domain);
        $www_domain = 'www.' . $domain;

        if (sha1($key . $domain) == $ser || sha1($key . $www_domain) == $ser) {
            return true;
        }

        return false;
    }

    function checkEntryDev($domain, $ser) {
        $key = sha1(base64_decode('YWx0aW1hbG9va2Jvb2tzbGlkZXJfZGV2'));

        $domain = str_replace('www.', '', $domain);
        $www_domain = 'www.' . $domain;
        if (sha1($key . $domain) == $ser || sha1($key . $www_domain) == $ser) {
            return true;
        }

        return false;
    }

    public function canRun($dev = false) {
        $temp = trim($this->temp);
        $m = empty($temp[0])? '': $temp[0];
        $temp = substr($temp, 1);
        if ($m) {
            $base_url = parse_url(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB));
            $base_url = $base_url['host'];
            
        } else {
            $base_url = $_SERVER['SERVER_NAME'];
        }

        if (!$dev) {
            $original = $this->checkEntry($base_url, $temp);
        } else {
            $original = $this->checkEntryDev($base_url, $temp);
        }

        if (!$original) {
            return false;
        }

        return true;
    }

    function isEnterpr() {
        return Mage::getConfig()->getModuleConfig('Enterprise_Enterprise') && Mage::getConfig()->getModuleConfig('Enterprise_AdminGws') && Mage::getConfig()->getModuleConfig('Enterprise_Checkout') && Mage::getConfig()->getModuleConfig('Enterprise_Customer');
    }

}
