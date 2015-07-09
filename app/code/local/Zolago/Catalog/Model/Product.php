<?php
/**
 * Catalog product model
 *
 *
 * @category    Zolago
 * @package     Zolago_Catalog
 */
class Zolago_Catalog_Model_Product extends Mage_Catalog_Model_Product
{
    const ZOLAGO_CATALOG_CONVERTER_PRICE_TYPE_CODE = 'converter_price_type';
    const ZOLAGO_CATALOG_CONVERTER_MSRP_TYPE_CODE = 'converter_msrp_type';
    const ZOLAGO_CATALOG_MSRP_CODE = 'msrp';
    const ZOLAGO_CATALOG_PRICE_MARGIN_CODE = 'price_margin';
    const ZOLAGO_CATALOG_BRANDSHOP_CODE = 'brandshop';


    const ZOLAGO_CATALOG_CONVERTER_MSRP_SOURCE = 'salePriceBefore';

	/**
	 * @return string
	 */
	public function getNoVendorContextUrl() {
		if(!$this->hasData("no_vendor_context_url")){
			$this->setData(
				"no_vendor_context_url",
				Mage::helper("zolagodropshipmicrosite")->convertToNonVendorContext($this->getProductUrl())
			);
		}
		return $this->getData("no_vendor_context_url");
	}

    /**
     * Get converter price type
     *
     * @param $sku
     *
     * @return array
     */
    public function getConverterPriceTypeBySku($sku)
    {
        return $this->getResource()->getConverterPriceTypeBySku($sku);
    }

    /**
     * Get converter price type
     *
     * @param $sku
     *
     * @return array
     */
    public function getConverterPriceType($skus)
    {
        return $this->getResource()->getConverterPriceType($skus);
    }

	/**
	 * @return string|null
	 */
	public function getManufacturerLogoUrl() {
        /** @var $_helper Zolago_Catalog_Helper_Product */
        $_helper = Mage::helper("zolagocatalog/product");
        return $_helper->getManufacturerLogoUrl($this);
	}
	
    /**
     * Get product final price
     *
     * @param double $qty
     * @return double
     */
    public function getFinalPrice($qty=null) {

        $price = $this->getCalculatedFinalPrice();

        if ($price !== null) {
            return $price;
        }
        return parent::getFinalPrice($qty, $this);
    }

    /**
     * Return the strikeout price if exist else return final price
     *
     * @param null $qty
     * @return float
     */
    public function getStrikeoutPrice($qty=null) {
        /** @var $helper Zolago_Catalog_Helper_Product */
        $helper = Mage::helper("zolagocatalog/product");
        return $helper->getStrikeoutPrice($this, $qty);
    }

    /**
     * Checks whether product has enabled status
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
    }
    /**
     * Retrive media gallery images
     *
     * @return Varien_Data_Collection
     */
    public function getFullMediaGalleryImages()
    {
        if(!$this->hasData('media_gallery_images') && is_array($this->getMediaGallery('images'))) {
            $images = new Varien_Data_Collection();
            foreach ($this->getMediaGallery('images') as $image) {
                if ($image['disabled']) {
                    //continue;
                }
                $image['url'] = $this->getMediaConfig()->getMediaUrl($image['file']);
                $image['id'] = isset($image['value_id']) ? $image['value_id'] : null;
                $image['path'] = $this->getMediaConfig()->getMediaPath($image['file']);
                $images->addItem(new Varien_Object($image));
            }
            $this->setData('media_gallery_images', $images);
        }

        return $this->getData('media_gallery_images');
    }

    /**
     * Product can be enabled when have accepted description and any price
     * @return bool
     */
    public function getIsProductCanBeEnabled() {
        $descAccepted = $this->getData('description_status') == Zolago_Catalog_Model_Product_Source_Description::DESCRIPTION_ACCEPTED;
        $isValidPrice = $this->getPrice() > 0 ? true : false;
        // Check if description is accepted and
        // Check if price is not zero
        return $descAccepted && $isValidPrice;
    }
}