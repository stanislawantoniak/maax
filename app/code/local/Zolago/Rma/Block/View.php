<?php
class Zolago_Rma_Block_View extends Zolago_Rma_Block_Abstract
{
	/**
	 * @param Zolago_Rma_Model_Rma $rma
	 * @return string
	 */
	public function getSuccessMessage(Zolago_Rma_Model_Rma $rma) {
		return Mage::getStoreConfig('urma/message/customer_success');
	}
	
	/**
	 * Is 'success' emulation
	 * @return type
	 */
	public function getIsSuccessPage() {
		return $this->getRma()->getJustCreated();
	}
}
