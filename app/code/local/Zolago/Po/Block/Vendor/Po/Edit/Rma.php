<?php
class Zolago_Po_Block_Vendor_Po_Edit_Rma
	extends Zolago_Po_Block_Vendor_Po_Edit_Abstract
{
	public function getRmaReasons(){
		$reasonsCollection = Mage::getModel('zolagorma/rma_reason')
			->getCollection();
		return $reasonsCollection;
	}
}
