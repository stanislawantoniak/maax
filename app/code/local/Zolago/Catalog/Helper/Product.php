<?php
class Zolago_Catalog_Helper_Product extends Mage_Catalog_Helper_Product {
	
	const SIZE_PRESENTATION_IMAGES = "images";
	const SIZE_PRESENTATION_LIST = "list";
	
	const FLAG_EMPTY		= null;			// brak
	const FLAG_PROMOTION	= 'promotion';	// promotion (percent)
	const FLAG_SALE			= 'sale';		// wyprzedaz
	const FLAG_BESTSELLER	= 'bestseller'; // bsesseller (hit)
	const FLAG_NEW			= 'new';		// nowosc
	
	public function getSizePresentationType(Mage_Catalog_Model_Product $product) {
		if(!$product->getName()){
			return null;
		}
		return strlen($product->getName())%2 ? 
			self::SIZE_PRESENTATION_IMAGES : self::SIZE_PRESENTATION_LIST;
	}
	
	/**
	 * @param Mage_Catalog_Model_Product $product
	 * @return string
	 */
	public function getProductBestFlag(Mage_Catalog_Model_Product $product) {
		switch($product->getProductFlag()){
			case Zolago_Catalog_Model_Product_Source_Flag::FLAG_PROMOTION:
				return self::FLAG_PROMOTION;
			break;
			case Zolago_Catalog_Model_Product_Source_Flag::FLAG_SALE:
				return self::FLAG_SALE;
			break;
		}
		if((int)$product->getIsBestseller()){
			return self::FLAG_BESTSELLER;
		}
		if((int)$product->getIsNew()){
			return self::FLAG_NEW;
		}
		return self::FLAG_EMPTY;
	}

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param int $width
     * @return string|empty_string
     */
    public function getResizedImageUrl(Mage_Catalog_Model_Product $product, $width = 300) {

        /** @var $product Zolago_Catalog_Model_Product*/

        if(!$product->hasData("listing_resized_image_url")){

            $return = null;
            try{
                $return = Mage::helper('catalog/image')->
                init($product, 'thumbnail')->
                keepAspectRatio(true)->
                constrainOnly(true)->
                keepFrame(false)->
                resize($width, null);
            } catch (Exception $ex) {
                Mage::logException($ex);
            }

            $product->setData("listing_resized_image_url", $return . ""); // Cast to string
        }

        return $product->getData("listing_resized_image_url");
    }

    /**
     * @param Mage_Catalog_Model_Product $model
     * @return array|null
     */
    public function getResizedImageInfo(Mage_Catalog_Model_Product $model) {

        $urlPath = $this->getResizedImageUrl($model);
        // Extract cached image URI
        if($urlPath){
            $filePath = substr($urlPath, strpos($urlPath, "//")+2);
            $filePath = substr($filePath, strpos($filePath, "/")+1);
            $filePath = str_replace("/", DS, $filePath);
            if($info=@getimagesize($filePath)){
                return array("width"=>$info[0], "height"=>$info[1]);
            }
        }
        return null;
    }

    /**
     * @param Mage_Catalog_Model_Product $model
     * @return null|string
     */
    public function getManufacturerLogoUrl(Mage_Catalog_Model_Product $model) {
        if($model->getData("manufacturer_logo")){            
            return Mage::getBaseUrl('media') . $model->getData("manufacturer_logo");
        }
        return null;
    }

    /**
     * Return the strikeout price if exist else return final price
     *
     * @param $product Zolago_Catalog_Model_Product|Varien_Object
     * @param null $qty
     * @return float
     */
    public function getStrikeoutPrice($product, $qty=null) {
        $campaignRegularId = (int)$product->getData('campaign_regular_id');
        $strikeoutType = $product->getData('campaign_strikeout_price_type');
        $price = (float)$product->getPrice();
        $specialPrice = (float)$product->getSpecialPrice();
        $finalPrice = (float)$product->getFinalPrice($qty);
        $msrp = (float)$product->getData('msrp');

        //When previous price is chosen then standard price striked out (if it is bigger than special price)
        //When MSRP price is chosen - then MSRP field is displayed as striked out (if it is bigger than special price)
        if ($campaignRegularId && Zolago_Campaign_Model_Campaign_Strikeout::STRIKEOUT_TYPE_PREVIOUS_PRICE == $strikeoutType) {
            return $price > $specialPrice ? $price : $finalPrice;
        } elseif ($campaignRegularId && Zolago_Campaign_Model_Campaign_Strikeout::STRIKEOUT_TYPE_MSRP_PRICE == $strikeoutType) {
            $returnPrice = $msrp > $specialPrice ? $msrp : $finalPrice;
            return $returnPrice > $finalPrice ? $returnPrice : $finalPrice;
        }
        elseif (empty($campaignRegularId)) {
            $returnPrice = $msrp > $price ? $msrp : $finalPrice;
            return $returnPrice > $finalPrice ? $returnPrice : $finalPrice;
        }
        else {
            return $finalPrice;
        }
    }
}