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
}