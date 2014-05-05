<?php

class Zolago_Po_Block_Vendor_Po_Item_Renderer_Configurable
	extends Zolago_Po_Block_Vendor_Po_Item_Renderer_Abstract
{
	public function __construct(array $args = array()){
		parent::__construct($args);
		$this->setTemplate("zolagopo/vendor/po/item/renderer/configurable.phtml");
	}
	
	/**
	 * @param Mage_Sales_Model_Order_Item $item
	 * @return string
	 */
	public function getSize(Mage_Sales_Model_Order_Item $item) {
		return "";
	}
	
}
