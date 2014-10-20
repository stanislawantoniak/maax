<?php
class Zolago_Rma_Block_Success extends Zolago_Rma_Block_Abstract
{
	/**
	 * @param Zolago_Rma_Model_Rma $rma
	 * @return string
	 */
	public function getSuccessMessage(Zolago_Rma_Model_Rma $rma) {
		return Mage::getStoreConfig('urma/message/customer_success');
	}
}
