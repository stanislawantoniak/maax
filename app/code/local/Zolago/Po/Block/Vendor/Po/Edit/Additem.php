<?php
class Zolago_Po_Block_Vendor_Po_Edit_Additem
	extends Zolago_Po_Block_Vendor_Po_Edit_Abstract
{

	public function getLoadCollectionUrl() {
		return $this->getPoUrl("loadCollection");
	}
	
}
