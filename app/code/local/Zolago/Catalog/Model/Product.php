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
    const ZOLAGO_CATALOG_PRICE_MARGIN_CODE = 'price_margin';
    const ZOLAGO_CATALOG_BRANDSHOP_CODE = 'brandshop';	

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
}