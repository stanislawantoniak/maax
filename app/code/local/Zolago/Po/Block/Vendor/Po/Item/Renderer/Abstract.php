<?php

class Zolago_Po_Block_Vendor_Po_Item_Renderer_Abstract extends Mage_Core_Block_Template
{
    public function __construct(array $args = array()){
		parent::__construct($args);
		$this->setTemplate("zolagopo/vendor/po/item/renderer/simple.phtml");
	}
}
