<?php
class Zolago_Catalog_Block_Product_Vendor_Deliveryreturn
	extends Zolago_Catalog_Block_Product_Vendor_Abstract
{
	/**
	 * @return string
	 */
	public function getDeliveryreturnHtml() {
	    return $this->getVendor()->getTermsDeliveryInformation().
               $this->getVendor()->getTermsReturnInformation();
//		return $this->getVendor()->getProductShortInformation();
	}
}
