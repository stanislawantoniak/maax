<?php
class Zolago_Catalog_Block_Product_Vendor_Info
	extends Zolago_Catalog_Block_Product_Vendor_Abstract
{
	/**
	 * @return string|null
	 */
	public function getLogoUrl() {
		return Mage::getBaseUrl('media') . '/' . 'vendor' . '/' . $this->getVendor()->getLogo();
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
	 * @todo implement
	 * @return int
	 */
	public function getAvarangeRating() {
		return 80;
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
}
