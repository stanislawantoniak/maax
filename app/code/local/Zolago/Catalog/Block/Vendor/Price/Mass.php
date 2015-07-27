<?php

class Zolago_Catalog_Block_Vendor_Price_Mass extends Zolago_Catalog_Block_Vendor_Price_Abstract
{
	
	public function getGlobal() {
		return $this->getRequest()->getParam("global")==1;
	}
	
	public function getSelected() {
		return $this->getRequest()->getParam("selected");
	}

    public function getStatus() {
        $status = $this->getRequest()->getParam("status");
        $value  = $status == 'enable'  ? Zolago_DropshipVendorProduct_Model_ProductStatus::STATUS_ENABLED : '';
        $value  = $status == 'invalid' ? Zolago_DropshipVendorProduct_Model_ProductStatus::STATUS_INVALID : $value;
        return $value;
    }
	
	public function getEncodedQuery() {
		return base64_encode(Mage::helper("core")->jsonEncode($this->getRequest()->getParam("query")));
	}
	
	public function getPriceSourceOptions() {

		$priceType = Mage::getSingleton('eav/config')->getAttribute(
			Mage_Catalog_Model_Product::ENTITY,
			Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_PRICE_TYPE_CODE
		);
		$priceType->setStoreId($this->getCurrentStoreId());

		return $priceType->getSource()->getAllOptions();
	}
	
	public function getConverterMsrpTypeOptions() {

		$priceType = Mage::getSingleton('eav/config')->getAttribute(
			Mage_Catalog_Model_Product::ENTITY,
			Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_MSRP_TYPE_CODE
		);
		$priceType->setStoreId($this->getCurrentStoreId());

		return $priceType->getSource()->getAllOptions();
	}

}