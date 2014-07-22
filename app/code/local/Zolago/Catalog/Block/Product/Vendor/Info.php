<?php
class Zolago_Catalog_Block_Product_Vendor_Info
	extends Zolago_Catalog_Block_Product_Vendor_Abstract
{
	const MIN_RATING = 0;
	const MAX_RATING = 5;
	
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
	 * @todo implement
	 * @return string|null
	 */
	public function getVendorInfoHtml() {
		return "Lorem ipsum [dev]";
	}
	
	/**
	 * @todo implement
	 * @return string|null
	 */
	public function getVendorDeliveryHtml() {
		return "Lorem ipsum [dev]";
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
		return round(($percent/100)*(self::MAX_RATING-self::MIN_RATING) + self::MIX_RATING, 1);
	}
}
