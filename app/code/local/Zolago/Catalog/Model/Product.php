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
     * Return the strikeout price if exist
     * else return final price
     */
    public function getStrikeoutPrice($qty=null) {

        $campaignModel = Mage::getModel("zolagocampaign/campaign"); /** @var Zolago_Campaign_Model_Campaign $campaignModel */
        $id = $this->getData('campaign_regular_id');

        //Strike out price can appear only when product has promo or sale flag
        //which means when a product is included in campaign.
        if (empty($id)) {
            return (float)$this->getFinalPrice($qty);
        }
        $productCampaignType = $campaignModel->getCampaignType($id);
        if (Zolago_Campaign_Model_Campaign_Type::TYPE_INFO == $productCampaignType || is_null($productCampaignType)) {
            return (float)$this->getFinalPrice($qty);
        }

        $strikeoutType = $campaignModel->getCampaignStrikeoutType($id);//int or null
        $price = (float)$this->getPrice();
        $specialPrice = (float)$this->getSpecialPrice();
        $finalPrice = (float)$this->getFinalPrice($qty);
        $msrp = (float)$this->getData('msrp');

        //When previous price is chosen then standard price striked out (if it is bigger than special price)
        //When MSRP price is chosen - then MSRP field is displayed as striked out (if it is bigger than special price)
        if (Zolago_Campaign_Model_Campaign_Strikeout::STRIKEOUT_TYPE_PREVIOUS_PRICE == $strikeoutType) {
            return $price > $specialPrice ? $price : $finalPrice;
        } elseif (Zolago_Campaign_Model_Campaign_Strikeout::STRIKEOUT_TYPE_MSRP_PRICE == $strikeoutType) {
            return $msrp > $specialPrice ? $msrp : $finalPrice;
        } else {
            return $finalPrice;
        }
    }
}