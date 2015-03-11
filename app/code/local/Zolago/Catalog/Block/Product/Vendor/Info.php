<?php
class Zolago_Catalog_Block_Product_Vendor_Info
	extends Zolago_Catalog_Block_Product_Vendor_Abstract
{
	const MIN_RATING = 0;
	const MAX_RATING = 5;
	
	/**
	 * @return string | null
	 */
	public function getTermsSellerInformation() {
		return $this->getVendor()->getTermsSellerInformation();
	}
	
	/**
	 * @return string|null
	 */
	public function getLogoUrl() {
		return Mage::getBaseUrl('media') . $this->getVendor()->getLogo();
	}
	
	/**
	 * @return string
	 */
	public function getVendorName() {
		return $this->getVendor()->getVendorName();
	}
	
	/**
	 * @return string
	 */
	public function getVendorUrl() {
		return Mage::helper("umicrosite")->getVendorUrl($this->getVendor());
	}
    /**
     * @return string
     */
    public function getVendorTypeLabel() {
        return $this->getVendor()->getVendorTypeLabel();
    }
    /**
     * @return string
     */
    public function getBrandshopVendorUrl() {
        return Mage::helper("umicrosite")->getVendorUrl($this->getBrandshopVendor());
    }

	/**
	 * @return string|null
	 */
	public function getVendorInfoHtml() {
	    $vendor = $this->getVendor();
	    $info = $vendor->getTermsSellerInformation();
		return $info;
	}

    /**
     * @return string|null
     */
    public function getMarketingInfo() {
        $vendor = $this->getVendor();


        $info = new stdClass;
        $info->store_information_title = $vendor->getData('marketing_store_information_title');
        $info->store_information = $vendor->getData('marketing_store_information');

        $info->brand_information_title = $vendor->getData('marketing_brand_information_title');
        $info->brand_information = $vendor->getData('marketing_brand_information');

        $info->delivery_information = $vendor->getTermsDeliveryInformation();
        $info->return_information = $vendor->getTermsReturnInformation();

        return $info;
    }
	
	/**
	 * @return string|null
	 */
	public function getVendorDeliveryHtml() {
		$vendor = $this->getVendor();
        $info = '<div id="termsDeliveryInformation">' . $vendor->getTermsDeliveryInformation() . '</div>';
        $info .= '<div id="termsReturnInformation">' . $vendor->getTermsReturnInformation() . '</div>';
        return  $info;
	}

	/**
	 * @return int
	 */
	public function getAvarangeRating() {
		$c = 0;
		$i = 0;
		foreach($this->getRatingsSummary() as $rating){
			$c += $rating['vote_percent'];
			$i++;
		}
		if($i>0){
			$c = round($c/$i);
		}
		return $c;
	}
	
	/**
	 * @todo implement
	 * @return int
	 */
	public function getRatingCount() {
		return 1;
	}
	
	/**
	 * @return array(array("rating_id"=>id, "rating_title"=>string, "vote_percent"=>int),...);
	 */
	public function getRatingsSummary() {
		return array(
			array("rating_id"=>"1", "rating_title"=>"Zgodność towarów", "vote_percent"=>80),
			array("rating_id"=>"2", "rating_title"=>"Realizacja zamówień", "vote_percent"=>60),
			array("rating_id"=>"3", "rating_title"=>"Kontakt", "vote_percent"=>40)
		);
	}
	
	/**
	 * @param int $percent
	 * @return float
	 */
	public function percentToNumber($percent) {
		return round(($percent/100)*(self::MAX_RATING-self::MIN_RATING) + self::MIN_RATING, 1);
	}
}
