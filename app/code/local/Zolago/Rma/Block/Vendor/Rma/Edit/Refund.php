<?php

class Zolago_Rma_Block_Vendor_Rma_Edit_Refund extends Zolago_Rma_Block_New_Abstract
{
    public function getFormAction($id)
    {
        return $this->getUrl('*/*/makeRefund',array('id' => $id));
    }

	public function getPriceValue($price) {
		return str_replace(',','.',Mage::getModel('directory/currency')->format(
			$price,
			array('display'=>Zend_Currency::NO_SYMBOL),
			false
		));
	}
}