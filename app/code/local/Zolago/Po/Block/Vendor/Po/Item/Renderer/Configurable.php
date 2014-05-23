<?php

class Zolago_Po_Block_Vendor_Po_Item_Renderer_Configurable
	extends Zolago_Po_Block_Vendor_Po_Item_Renderer_Abstract
{
	
	protected static $_attributes = array();


	public function __construct(array $args = array()){
		parent::__construct($args);
		$this->setTemplate("zolagopo/vendor/po/item/renderer/configurable.phtml");
	}
	
	public function getConfigurableText(Zolago_Po_Model_Po_Item $item) {
		return $item->getConfigurableText();
	}
	
	public function getConfigurableFormattedText(Zolago_Po_Model_Po_Item $item) {
		$out = $this->getConfigurableText($item);
		if($out){
			return " ($out)";
		}
		return "";
	}
	
	public function getConfigurableHtml(Zolago_Po_Model_Po_Item $item) {
		$text = $this->getConfigurableFormattedText($item);
		if($text){
			return " <em class=\"text-muted\">".$text."</em>";
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
