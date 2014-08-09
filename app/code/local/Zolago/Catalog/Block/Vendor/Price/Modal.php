<?php

class Zolago_Catalog_Block_Vendor_Price_Modal extends Mage_Core_Block_Template
{
	
	public function getProduct() {
		return Mage::registry("current_product");
	}

}