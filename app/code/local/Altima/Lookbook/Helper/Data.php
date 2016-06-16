<?php
/**
 * Altima Lookbook Free Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Altima
 * @package    Altima_LookbookFree
 * @author     Altima Web Systems http://altimawebsystems.com/
 * @email      support@altima.net.au
 * @copyright  Copyright (c) 2012 Altima Web Systems (http://altimawebsystems.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Altima_Lookbook_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getEnabled()
	{
		return Mage::getStoreConfig('lookbook/general/enabled');
	}
    
    public function getMaxImageWidth()
	{
		return intval(Mage::getStoreConfig('lookbook/general/max_image_width'));
	}

    public function getMaxImageHeight()
	{
		return intval(Mage::getStoreConfig('lookbook/general/max_image_height'));
	}

    public function getMinImageWidth()
	{
		return intval(Mage::getStoreConfig('lookbook/general/min_image_width'));
	}

    public function getMinImageHeight()
	{
		return intval(Mage::getStoreConfig('lookbook/general/min_image_height'));
	}
 
    public function getMaxUploadFilesize()
	{
		return intval(Mage::getStoreConfig('lookbook/general/max_upload_filesize'));
	}
  
    public function getAllowedExtensions()
	{
		return Mage::getStoreConfig('lookbook/general/allowed_extensions');
	} 

    public function getEffects()
	{
		return Mage::getStoreConfig('lookbook/general/effects');
	}
    public function getNavigation()
	{
		return Mage::getStoreConfig('lookbook/general/navigation');
	}   
    public function getNavigationHover()
	{
		return Mage::getStoreConfig('lookbook/general/navigation_hover');
	}
    public function getThumbnails()
	{
		return Mage::getStoreConfig('lookbook/general/thumbnails');
	} 
    public function getPause()
	{
		return Mage::getStoreConfig('lookbook/general/pause');
	} 
    public function getTransitionDuration()
	{
		return Mage::getStoreConfig('lookbook/general/transition_duration');
	}            
	/**
	* Returns the resized Image URL
	*
	* @param string $imgUrl - This is relative to the the media folder (custom/module/images/example.jpg)
	* @param int $x Width
	* @param int $y Height
	*Remember your base image or big image must be in Root/media/lookbook/example.jpg
	*
	* echo Mage::helper('lookbook')->getResizedUrl("lookbook/example.jpg",101,65)
	*
	*By doing this new image will be created in Root/media/lookbook/101X65/example.jpg
	*/

    public function getResizedUrl($imgUrl, $x, $y = NULL) {

        $imgPath = $this->splitImageValue($imgUrl, "path");
        $imgName = $this->splitImageValue($imgUrl, "name");

        /**
         * Path with Directory Seperator
         */
        $imgPath = str_replace("/", DS, $imgPath);

        /**
         * Absolute full path of Image
         */
        $imgPathFull = Mage::getBaseDir("media") . DS . $imgPath . DS . $imgName;

        /**
         * If Y is not set set it to as X
         */
        $width = $x;
        $y ? $height = $y : $height = $x;

        /**
         * Resize folder is widthXheight
         */
        $resizeFolder = $width . "X" . $height;

        /**
         * Image resized path will then be
         */
        $imageResizedPath = Mage::getBaseDir("media") . DS . $imgPath . DS . $resizeFolder . DS . $imgName;

        /**
         * First check in cache i.e image resized path
         * If not in cache then create image of the width=X and height = Y
         */
        if (!file_exists($imageResizedPath) && file_exists($imgPathFull)) :
            $imageObj = new Varien_Image($imgPathFull);
            $imageObj->constrainOnly(FALSE);
            $imageObj->keepAspectRatio(TRUE);
            $imageObj->keepTransparency(TRUE);
            $imageObj->keepFrame(FALSE);
            if (($width / $height) > ($imageObj->getOriginalWidth() / $imageObj->getOriginalHeight())) {
                $imageObj->resize($width, null);
            } else {
                $imageObj->resize(null, $height);
            }
            $cropX = 0;
            $cropY = 0;
            if ($imageObj->getOriginalWidth() > $width) {
                $cropX = intval(($imageObj->getOriginalWidth() - $width) / 2);
            }
            if ($imageObj->getOriginalHeight() > $height) {
                $cropY = intval(($imageObj->getOriginalHeight() - $height) / 2);
            }

            $imageObj->crop($cropY, $cropX, $cropX, $cropY);
            $imageObj->save($imageResizedPath);
        endif;

        /**
         * Else image is in cache replace the Image Path with / for http path.
         */
        $imgUrl = str_replace(DS, "/", $imgPath);

        /**
         * Return full http path of the image
         */
        return Mage::getBaseUrl("media") . $imgUrl . "/" . $resizeFolder . "/" . $imgName;
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
    public function splitImageValue($imageValue,$attr="name"){
        $imArray=explode("/",$imageValue);
 
        $name=$imArray[count($imArray)-1];
        $path=implode("/",array_diff($imArray,array($name)));
        if($attr=="path"){
            return $path;
        }
        else
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
    public function getImageDimensions($img_path){
            $imageObj = new Varien_Image($img_path);
            $width = $imageObj->getOriginalWidth();
            $height = $imageObj->getOriginalHeight();
            $result = array('width'=>$width, 'height'=>$height);
        return $result;
    }

	 /**
     * Change SKU to product information into Json array
     *
     * img_path=lookbook/example.jpg
     *
     * @param json array $array
     * @return json array('width'=>$width, 'height'=>$height)
     */ 
    public function getHotspotsWithProductDetails($hotspots_json){
        if ($hotspots_json=='') return '';
		$decoded_array = json_decode($hotspots_json,true);
        $img_width = intval(Mage::getStoreConfig('lookbook/general/max_image_width'));
        $hotspot_icon  = Mage::getBaseUrl('media').'lookbook/icons/default/hotspot-icon.png';
        $hotspot_icon_path  = Mage::getBaseDir('media').DS.'lookbook'.DS.'icons'.DS.'default'.DS.'hotspot-icon.png';
		$icon_dimensions = $this->getImageDimensions($hotspot_icon_path);
        $_coreHelper = Mage::helper('core');
        foreach($decoded_array as $key => $value){
		      $product_details = Mage::getModel('catalog/product')->loadByAttribute('sku',$decoded_array[$key]['text']);

            		$html_content = '<img class="hotspot-icon" src="'.$hotspot_icon.'" alt="" style="
                    left:'. (round($value['width']/2)-round($icon_dimensions['width']/2)) .'px; 
                    top:'. (round($value['height']/2)-round($icon_dimensions['height']/2)) .'px;
                    "/><div class="product-info" style="';           
                    $html_content .=  'left:'.round($value['width']/2).'px;';
                    $html_content .=  'top:'.round($value['height']/2).'px;';
                   
                if ($product_details) {
                    $_p_name = $product_details->getName();
                    $html_content .=  'width: '. strlen($_p_name)*8 .'px;';
                }
                else
                {
                    $html_content .=  'width: 200px;';
                }
                
                    $html_content .=  '">';
                if ($product_details) {
        			$_p_price = $_coreHelper->currency($product_details->getFinalPrice(),true,false);
                    if($product_details->isAvailable())
                    {
                        $_p_url = $product_details->getProductUrl();                                                                                    
            			$html_content .= '<div><a href=\''.$_p_url.'\'>'.$_p_name.'</a></div>';
                    }
                    else
                    {
                        $html_content .= '<div>'.$_p_name.'</div>';
                        $html_content .= '<div class="out-of-stock"><span>'. $this->__('Out of stock') .'</span></div>';                        
                    }

                    if($product_details->getFinalPrice()){
                            if ($product_details->getPrice()>$product_details->getFinalPrice()){
                                    $regular_price = $_coreHelper->currency($product_details->getPrice(),true,false);
                                    $_p_price = '<div class="old-price">'.$regular_price.'</div>'.$_p_price;
                            }
            				$html_content .= '<div class="price">'.$_p_price.'</div>';
            		}  
                }
                else
                {
                    $html_content .= '<div>Product with SKU "'.$decoded_array[$key]['text'].'" doesn\'t exists.</div>';
                }
			$html_content .= '	
			</div>
			';
			
			$decoded_array[$key]['text'] = $html_content;
		}
        $result = $decoded_array;
        return $result;
    }
}