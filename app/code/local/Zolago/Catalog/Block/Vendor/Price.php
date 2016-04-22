<?php

class Zolago_Catalog_Block_Vendor_Price extends Mage_Core_Block_Template
{
	/**
	 * 
	 * @return string
	 */
	public function getAttributeSourceJson() {
		
		$campaign =  Mage::getResourceModel("zolagocampaign/campaign_collection");
		/* @var $campaign Zolago_Campaign_Model_Resource_Campaign_Collection */
		$campaign->addVendorFilter($this->getVendor());
        $campaign->addFieldToFilter("status", array("neq" => Zolago_Campaign_Model_Campaign_Status::TYPE_ARCHIVE));
        $campaign->addFieldToFilter("type", array("neq" => Zolago_Campaign_Model_Campaign_Type::TYPE_INFO));
		
		$priceType = Mage::getSingleton('eav/config')->getAttribute(
			Mage_Catalog_Model_Product::ENTITY,
			Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_PRICE_TYPE_CODE
		);
		$priceType->setStoreId($this->getCurrentStoreId());
		/* @var $priceType Mage_Catalog_Model_Resource_Eav_Attribute */
		$priceTypes = array_merge(
				array(array("value"=>0, "label"=>"Manual")), 
				$this->_clearEmpty($priceType->getSource()->getAllOptions(false))
		);
		
		$msrpType = Mage::getSingleton('eav/config')->getAttribute(
			Mage_Catalog_Model_Product::ENTITY,
			Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_MSRP_TYPE_CODE
		);
		/* @var $msrpTypes Mage_Catalog_Model_Resource_Eav_Attribute */
		
		$status = Mage::getSingleton('eav/config')->getAttribute(
			Mage_Catalog_Model_Product::ENTITY,
			"status"
		);
		$status->setStoreId($this->getCurrentStoreId());

		$typeModel = Mage::getSingleton('catalog/product_type');
		
		
		$flags = Mage::getSingleton('eav/config')->getAttribute(
			Mage_Catalog_Model_Product::ENTITY,
			"product_flag"
		);
		$flags->setStoreId($this->getCurrentStoreId());
		
		$bool = Mage::getSingleton("eav/entity_attribute_source_boolean");
		/* @var $bool Mage_Eav_Model_Entity_Attribute_Source_Boolean */

		$descriptionStatusSrc = Mage::getSingleton("zolagocatalog/product_source_description");

		$x = array_merge(
			array(array("value" => 0, "label" => "Standard")),
			$campaign->toOptionArray()
		);
		$source = array(
			"converter_price_type" => $priceTypes,
			"converter_msrp_type" => $this->_clearEmpty($msrpType->getSource()->getAllOptions(false)),
			"campaign_regular_id" => $this->_clearEmpty($x),
			"status" => $this->_clearEmpty($status->getSource()->getAllOptions(false)),
			"description_status" => $this->_clearEmpty($descriptionStatusSrc->getAllOptions(false, false, true)),
			"type_id" => $this->_clearEmpty($typeModel::getAllOptions()),
			"product_flag" => $this->_clearEmpty($flags->getSource()->getAllOptions(false)),
			"bool" => $this->_clearEmpty($bool->getAllOptions())
		);
		
		
		return Mage::helper("core")->jsonEncode($source);
	}

	/**
	 * @param array $array
	 * @return array
	 */
	protected function _clearEmpty($array) {
		foreach($array as $key=>$item){
			if($item['value']===""){
				unset($array[$key]);
			}
		}
		return array_values($array);
	}


	/**
	 * @return array
	 */
	public function getAllowedStores() {
		return Mage::helper("zolagodropship")->getAllowedStores($this->getVendor());
	}
	
	/**
	 * @return int
	 */
	public function getCurrentStoreId() {
		return Mage::app()->getRequest()->getParam('store_id', $this->getDefaultStoreId());
	}
	
	/**
	 * @return id
	 */
	public function getDefaultStoreId() {
		if($this->getVendor()->getLabelStore()){
			return $this->getVendor()->getLabelStore();
		}
		$allowed = $this->getAllowedStores();
		if($allowed){
			return $allowed[0]["id"];
		}
		throw new Mage_Core_Exception("No store defined");
	}
	
	/**
	 * @return ZolagoOs_OmniChannel_Model_Vendor
	 */
	public function getVendor() {
		return $this->_getSession()->getVendor();
	}
	
	/**
	 * @return ZolagoOs_OmniChannel_Model_Session
	 */
	protected function _getSession() {
		return Mage::getSingleton('udropship/session');
	}
	
	/**
	 * @return Mage_Catalog_Model_Resource_Product_Collection
	 */
	public function getCollection() {
		if(!$this->getData("collection")){
			$collection = Mage::getResourceModel("catalog/product_collection");
			/* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
			$collection->addAttributeToFilter("udropship_vendor", $this->getVendor()->getId());
			$collection->addAttributeToFilter("type_id", "configurable");
			$collection->setPageSize(1000);
			$this->setData("collection", $collection);
		}
		return $this->getData("collection");
	}
	
	public function getJsonCollection() {
		$out = array();
		foreach($this->getCollection() as $product){
			$product->setCollapsed(false);
			$out[] = $product->getData();
		}
		return Mage::helper("core")->jsonEncode($out);
	}
	

}