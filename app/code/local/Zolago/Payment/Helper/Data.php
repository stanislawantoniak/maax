<?php

/**
 * payment helper
 */
class Zolago_Payment_Helper_Data extends Mage_Core_Helper_Abstract
{

	/**
	 * @param Zolago_Po_Model_Po|int $poId
	 * @return Zolago_Payment_Model_Resource_Allocation_Collection
	 */
    public function getOverpaymentDetails($poId) {

        return $this->_addOverpaymentJoins($this->_getModel()->getPoOverpayments($poId));
    }


	/**
	 * @param Zolago_Po_Model_Po|int $poId
	 * @return Zolago_Payment_Model_Resource_Allocation_Collection
	 */
    public function getPaymentDetails($poId)  {
	    
        return $this->_addPaymentsJoins($this->_getModel()->getPoPayments($poId));
    }

	/**
	 * @return Zolago_Payment_Model_Allocation
	 */
	private function _getModel() {
		return Mage::getModel('zolagopayment/allocation');
	}

	/**
	 * @param Zolago_Payment_Model_Resource_Allocation_Collection $collection
	 * @return Zolago_Payment_Model_Resource_Allocation_Collection
	 */
	private function _addPaymentsJoins(Zolago_Payment_Model_Resource_Allocation_Collection $collection) {
		$collection
			->joinTransactions()
			->joinOperators()
			->joinPos()
            ->joinVendors();

		return $collection;
	}

	private function _addOverpaymentJoins(Zolago_Payment_Model_Resource_Allocation_Collection $collection) {
		$collection
			->joinTransactions()
			->getSelect()->columns('udropship_po.increment_id AS increment_id');

		return $collection;
	}

}