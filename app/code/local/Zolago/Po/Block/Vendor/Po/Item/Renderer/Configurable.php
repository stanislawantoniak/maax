<?php

class Zolago_Po_Block_Vendor_Po_Item_Renderer_Configurable
	extends Zolago_Po_Block_Vendor_Po_Item_Renderer_Abstract
{
	
	protected static $_attributes = array();


	public function __construct(array $args = array()){
		parent::__construct($args);
		$this->setTemplate("zolagopo/vendor/po/item/renderer/configurable.phtml");
	}
	
	public function getConfigurableHtml(Mage_Sales_Model_Order_Item $item) {
		$request = $item->getProductOptionByCode("attributes_info");
		$out = array();
		if(is_array($request)){
			foreach($request as $item){
				$out[] = $this->__($item['label']).": ".$this->__($item['value']);
			}
		}
		if($out){
			return " <em class=\"text-muted\">(".implode(", ", $out).")</em>";
		}
		return "";
	}
	
//	/**
//	 * @param Mage_Sales_Model_Order_Item $item
//	 * @return string
//	 */
//	public function getSize(Mage_Sales_Model_Order_Item $item) {
//		$opts = $item->getProductOptionByCode("info_buyRequest");
//		if(is_array($opts) && isset($opts['super_attribute'])){
//			foreach($opts['super_attribute'] as $attributeId => $valueId){
//				$attribute=null;
//				if(!isset(self::$_attributes[$attributeId])){
//					$attribute = Mage::getSingleton('eav/config')
//						->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attributeId);
//				
//					self::$_attributes[$attributeId] = array();
//					
//					if($attribute && $attribute->getId()){
//						/* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
//						if($attribute->getSource()){
//							$source = $attribute->getSource();
//							$flat = array();
//							/* @var $source Mage_Eav_Model_Entity_Attribute_Source_Table */
//							foreach($source->getAllOptions() as $opt){
//								self::$_attributes[$attributeId][$opt['value']]=$opt['label'];
//							}
//						}
//					}
//				}
//				return isset(self::$_attributes[$attributeId][$valueId]) ? 
//					self::$_attributes[$attributeId][$valueId] : '';
//			}
//		}
//		return '';
//	}
	
}
