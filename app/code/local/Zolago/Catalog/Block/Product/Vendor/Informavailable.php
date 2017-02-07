<?php
class Zolago_Catalog_Block_Product_Vendor_Informavailable
	extends Zolago_Catalog_Block_Product_Vendor_Abstract
{
	public function getFormAction() {
		return Mage::getUrl('informwhenavailable/entry/create'); 
	}
}
