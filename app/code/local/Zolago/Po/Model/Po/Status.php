<?php
class Zolago_Po_Model_Po_Status
{
	/**
	 * Dropship statuses
	 */

	/**
	 * czeka na spakowanie
	 */
    const STATUS_PENDING    = Zolago_Po_Model_Source::UDPO_STATUS_PENDING;// 0
	/**
	 * w trakcie pakowania
	 */
    const STATUS_EXPORTED   = Zolago_Po_Model_Source::UDPO_STATUS_EXPORTED;// 10
	/**
	 * czeka na potwierdzenie
	 */
    const STATUS_ACK        = Zolago_Po_Model_Source::UDPO_STATUS_ACK;// 9
	/**
	 * czeka na rezerwację
	 */
    const STATUS_BACKORDER  = Zolago_Po_Model_Source::UDPO_STATUS_BACKORDER;// 5
	/**
	 * problem
	 */
    const STATUS_ONHOLD     = Zolago_Po_Model_Source::UDPO_STATUS_ONHOLD;// 4
	/**
	 * spakowane
	 */
    const STATUS_READY      = Zolago_Po_Model_Source::UDPO_STATUS_READY;// 3
	/**
	 * N/O
	 */
    const STATUS_PARTIAL    = Zolago_Po_Model_Source::UDPO_STATUS_PARTIAL;// 2
	/**
	 * wysłane
	 */
    const STATUS_SHIPPED    = Zolago_Po_Model_Source::UDPO_STATUS_SHIPPED;// 1
	/**
	 * anulowane
	 */
    const STATUS_CANCELED   = Zolago_Po_Model_Source::UDPO_STATUS_CANCELED;// 6
	/**
	 * dostarczone
	 */
	const STATUS_DELIVERED  = Zolago_Po_Model_Source::UDPO_STATUS_DELIVERED;// 7
	/**
	 * zwrócone
	 */
    const STATUS_RETURNED   = Zolago_Po_Model_Source::UDPO_STATUS_RETURNED;// 11
	/**
	 * czeka na płatność
	 */
    const STATUS_PAYMENT    = Zolago_Po_Model_Source::UDPO_STATUS_PAYMENT;// 12

	static function getFinishStatuses() {
		return array(
			self::STATUS_CANCELED,
			self::STATUS_DELIVERED,
			self::STATUS_SHIPPED,
			self::STATUS_RETURNED
		);;
	}
    
    static function getOverpaymentStatuses() {
        return array (
			self::STATUS_CANCELED,
			self::STATUS_DELIVERED,
			self::STATUS_SHIPPED,
        );
    }	
	/**
	 * if PO is NEW
	 * set if ALERT is not null:
	 *   ACK
	 *	 else if PAYMENT IS GATEWAY
	 *	 BACKORDER
	 *	 else 
	 *	 PENDING
	 * @param Zolago_Po_Model_Po $po
	 */
	public function processNewStatus(Zolago_Po_Model_Po $po) {
		if($po->getId()){
			return;
		}
		if($po->getAlert()){
			$po->setUdropshipStatus(self::STATUS_ACK);
		}elseif(!$po->isCod()){
			$po->setUdropshipStatus(self::STATUS_BACKORDER);
		}else{
			$po->setUdropshipStatus(self::STATUS_PENDING);
		}
	}
	
	/**
	 * set if PAYMENT IS GATEWAY and IS NOT PAID:
	 *	 BACKORDER
	 *	 else 
	 *	 PENDING
	 * @param Zolago_Po_Model_Po $po
	 */
	public function processConfirmRelease(Zolago_Po_Model_Po $po) {
		if($this->isConfirmReleaseAvailable($po)){
			if(!$po->isCod() && !$po->isPaid()){
				$status = self::STATUS_BACKORDER;
			}else{
				$status = self::STATUS_PENDING;
			}
			$this->_processStatus($po, $status);
		}
	}
	
	/**
	 * set if PAYMENT IS GATEWAY and IS NOT PAID:
	 *	 PAYMENT
	 *	 else 
	 *	 PENDING
	 * @param Zolago_Po_Model_Po $po
	 */
	public function processConfirmStock(Zolago_Po_Model_Po $po) {
		if($this->isConfirmStockAvailable($po)){
			$po->setStockConfirm(1);
			$po->getResource()->saveAttribute($po, "stock_confirm");
			if(!$po->isCod() && !$po->isPaid()){
				$status = self::STATUS_PAYMENT;
			}else{
				$status = self::STATUS_PENDING;
			}
			$this->_processStatus($po, $status);
		}
	}
	
	/**
	 * @param Zolago_Po_Model_Po $po
	 */
	public function processPrintAggregated(Zolago_Po_Model_Po $po) {
		if($this->isPrintAggregatedAvailable($po)){
			
		}
	}
	
	/**
	 * @param Zolago_Po_Model_Po $po
	 */
	public function processConfirmSend(Zolago_Po_Model_Po $po) {
		if($this->isConfirmSendAvailable($po)){
			$this->_processStatus($po, self::STATUS_SHIPPED);
		}
	}
	
	/**
	 * set if PAYMENT IS GATEWAY and IS NOT PAID:
	 *	 BACKORDER
	 *	 else 
	 *	 PENDING
	 * @param Zolago_Po_Model_Po $po
	 */
	public function processDirectRealisation(Zolago_Po_Model_Po $po, $force=false) {
		if($this->isDirectRealisationAvailable($po) || $force){
			$po->setStockConfirm(0);
			$po->getResource()->saveAttribute($po, "stock_confirm");
			if(!$po->isPaymentCheckOnDelivery() && !$po->isPaid()){
				$status = self::STATUS_BACKORDER;
			}else{
				$status = self::STATUS_PENDING;
			}
			$this->_processStatus($po, $status);
		}
	}

    /**
     * @param Zolago_Po_Model_Po $po
     * @param bool $force
     * @param bool $throwError
     * @throws Mage_Core_Exception
     */
    public function processStartPacking(Zolago_Po_Model_Po $po, $force = false, $throwError = false) {
        if($this->isStartPackingAvailable($po) || $force){
            $this->_processStatus($po, self::STATUS_EXPORTED);
        } elseif($throwError) {
            Mage::throwException(Mage::helper('ghapi')->__('Invalid status for this operation.'));
        }
    }

	/**
	 * @param Zolago_Po_Model_Po $po
	 */
	public function confirmPickUp(Zolago_Po_Model_Po $po)
	{
		// Add shipment for RMA
		// @see Zolago_Rma_Model_ServicePo::prepareRmaForSave()
		/** @var Zolago_Po_Helper_Shipment $manager */
		$manager = Mage::helper('zolagopo/shipment');
		$po->setUdpoNoSplitPoFlag(true);
		$manager->setUdpo($po);
		$manager->getShipment(); // create shipment from po
		
		/** @var Zolago_Po_Helper_Data $hlp */
		$hlp = Mage::helper("zolagopo");
		$hlp->addConfirmPickUpComment($po);
		$this->_processStatus($po, self::STATUS_DELIVERED);
	}
	
	/**
	 * @param Zolago_Po_Model_Po $po
	 */
	public function processCancelShipment(Zolago_Po_Model_Po $po) {
		$this->_processStatus($po, self::STATUS_EXPORTED);
	}
	
	/**
	 * @param Zolago_Po_Model_Po $po
	 */
	public function processCancelAggregated(Zolago_Po_Model_Po $po, $force = false) {
		if($this->isCancelAggregatedAvailable($po) || $force){
			$po->setAggregatedId(null);
			$po->getResource()->saveAttribute($po, "aggregated_id");
			$this->_processStatus($po, self::STATUS_READY);
		}
	}
	
	
	/**
	 * @param Zolago_Po_Model_Po|int $po
	 * @return boolean
	 */
	public function isConfirmReleaseAvailable($po) {
		switch ($this->_getStatus($po)) {
			case self::STATUS_ACK:
				return true;
			break;
		}
		return false;
	}
	
	
	/**
	 * @param Zolago_Po_Model_Po|int $po
	 * @return boolean
	 */
	public function isConfirmStockAvailable($po) {
		switch ($this->_getStatus($po)) {
			case self::STATUS_BACKORDER:
				return true;
			break;
		}
		return false;
	}
	
	/**
	 * @param Zolago_Po_Model_Po|int $po
	 * @return boolean
	 */
	public function isPrintAggregatedAvailable($po) {
		switch ($this->_getStatus($po)) {
			case self::STATUS_READY:
				return true;
			break;
		}
		return false;
	}
	
	/**
	 * @param Zolago_Po_Model_Po|int $po
	 * @return boolean
	 */
	public function isCancelAggregatedAvailable($po) {
		return false;
	}
	
	/**
	 * @param Zolago_Po_Model_Po|int $po
	 * @return boolean
	 */
	public function isCancelShippingAvailable($po) {
		switch ($this->_getStatus($po)) {
			case self::STATUS_READY:
				if($po instanceof Zolago_Po_Model_Po){
					return !$po->getAggregated()->isConfirmed();
				}
				return true;
			break;
		}
		return false;
	}
	
	/**
	 * @param Zolago_Po_Model_Po|int $po
	 * @return boolean
	 */
	public function isConfirmSendAvailable($po) {
		switch ($this->_getStatus($po)) {
			case self::STATUS_READY:
				return true;
			break;
		}
		return false;
	}
	
	/**
	 * @param Zolago_Po_Model_Po|int $po
	 * @return boolean
	 */
	public function isStartPackingAvailable($po) {
		switch ($this->_getStatus($po)) {
			case self::STATUS_PENDING:
				return true;
			break;
		}
		return false;
	}
	
	/**
	 * @param Zolago_Po_Model_Po|int $po
	 * @return boolean
	 */
	public function isShippingAvailable($po) {
		switch ($this->_getStatus($po)) {
			case self::STATUS_EXPORTED:
				return true;
			break;
		}
		return false;
	}
	
	/**
	 * @param Zolago_Po_Model_Po|int $po
	 * @return boolean
	 */
	public function isEditingAvailable($po) {
		switch ($this->_getStatus($po)) {
			case self::STATUS_ACK:
			case self::STATUS_BACKORDER:
			case self::STATUS_PAYMENT:
			case self::STATUS_PENDING:
			case self::STATUS_EXPORTED:
				return true;
			break;
		}
		return false;
	}
	
	/**
	 * @param Zolago_Po_Model_Po|int $po
	 * @return boolean
	 */
	public function isManulaStatusAvailable($po) {
		return count($this->getAvailableStatuses($po));
	}
	
	/**
	 * @param Zolago_Po_Model_Po|int $po
	 * @return boolean
	 */
	public function isDirectRealisationAvailable($po) {
	    $order = $po->getOrder();
	    if ($order->getState() == Mage_Sales_Model_Order::STATE_CANCELED) {
	        return false;
	    }
		switch ($this->_getStatus($po)) {
			case self::STATUS_CANCELED:
			case self::STATUS_ONHOLD:
				return true;
			break;
		}
		return false;
	}
	
	/**
	 * @param Zolago_Po_Model_Po|int $status
	 * @return array
	 */
	public function getAvailableStatuses($status) {
		$statuses = array();
		/** @var Zolago_Po_Helper_Data $hlp */
		$hlp = Mage::helper("udpo");
		switch ($this->_getStatus($status)) {
			case self::STATUS_EXPORTED:
				$statuses[self::STATUS_PENDING] = $hlp->getPoStatusName(self::STATUS_PENDING);
			case self::STATUS_BACKORDER:
			case self::STATUS_PAYMENT:
			case self::STATUS_ACK:
			case self::STATUS_PENDING:
				$statuses[self::STATUS_ONHOLD] = $hlp->getPoStatusName(self::STATUS_ONHOLD);
				$statuses[self::STATUS_CANCELED] = $hlp->getPoStatusName(self::STATUS_CANCELED);
			break;
			case self::STATUS_ONHOLD:
				$statuses[self::STATUS_CANCELED] = $hlp->getPoStatusName(self::STATUS_CANCELED);
			break;
		}
		
		return $statuses;
	}
	
	/**
	 * @param Zolago_Po_Model_Po $po
	 * @param string $newStatus
	 */
	public function changeStatus(Zolago_Po_Model_Po $po, $newStatus) {
		$this->_processStatus($po, $newStatus);
	}

	public function updateStatusByAllocation(Zolago_Po_Model_Po $po) {
		$this->changeStatus($po,$po->getUdropshipStatus());
	}

	/**
	 * @param Zolago_Po_Model_Po $po
	 * @param string $newStatus
	 */
	protected function _processStatus(Zolago_Po_Model_Po $po, $newStatus) {

		$newStatus2 = $this->getPoStatusByPayment($po,$newStatus);
		/** @var Zolago_Po_Helper_Data $hlp */
		$hlp = Mage::helper("udpo");
		$po->setForceStatusChangeFlag(true);
		$hlp->processPoStatusSave($po, $newStatus2, true);

        if (in_array($newStatus2, $this->getOverpaymentStatuses())) {
            /** @var Zolago_Payment_Model_Allocation $allocModel */
            $allocModel = Mage::getModel("zolagopayment/allocation");
            $allocModel->createOverpayment($po);
        }

	}

	/**
	 * @param Zolago_Po_Model_Po|int $status
	 * @return int
	 */
	protected function _getStatus($status) {
		if($status instanceof Zolago_Po_Model_Po){
			return $status->getUdropshipStatus();
		}
		return $status;
	}

	/**
	 * Checks if status that you want to set on po is ok according to payment allocations
	 * @param Zolago_Po_Model_Po $po
	 * @param bool $status
	 * @return bool|int
	 */
	public function getPoStatusByPayment(Zolago_Po_Model_Po $po,$status=false) {
		if ($po->getId()) {
			$status = !is_null($status) && $status !== false ? $status : $po->getUdropshipStatus();

			if($status == Zolago_Po_Model_Po_Status::STATUS_PAYMENT
				|| $status == Zolago_Po_Model_Po_Status::STATUS_PENDING
				|| $status == Zolago_Po_Model_Po_Status::STATUS_BACKORDER)
			{
				$grandTotal = $po->getGrandTotalInclTax();
				$sumAmount  = $po->getPaymentAmount();

				//czeka na płatność lub czeka na rezerwacje
				if (($status == Zolago_Po_Model_Po_Status::STATUS_PAYMENT || $status == Zolago_Po_Model_Po_Status::STATUS_BACKORDER)
					&& $grandTotal <= $sumAmount)
				{
					return Zolago_Po_Model_Po_Status::STATUS_PENDING; //rowny albo nadplata
				} //czeka na spakowanie
				elseif ($status == Zolago_Po_Model_Po_Status::STATUS_PENDING && ($grandTotal > $sumAmount) && !$po->isCod()) {
					return Zolago_Po_Model_Po_Status::STATUS_PAYMENT; //jest mniej niz potrzeba
				}
			}
			return $status;
		}
		return false;
	}

    /**
     * Mapping statuses to GH API statuses
     *
     * @param $status string
     * @return string
     */
    public function ghapiOrderStatus($status) {
        switch ($status) {
            case Zolago_Po_Model_Source::UDPO_STATUS_ACK:
            case Zolago_Po_Model_Source::UDPO_STATUS_ONHOLD:
            case Zolago_Po_Model_Source::UDPO_STATUS_BACKORDER:
                return 'pending';
            case Zolago_Po_Model_Source::UDPO_STATUS_PAYMENT:
                return 'pending_payment';
            case Zolago_Po_Model_Source::UDPO_STATUS_PENDING:
            case Zolago_Po_Model_Source::UDPO_STATUS_EXPORTED:
            case Zolago_Po_Model_Source::UDPO_STATUS_READY:
                return 'ready';
            case Zolago_Po_Model_Source::UDPO_STATUS_SHIPPED:
                return 'shipped ';
            case Zolago_Po_Model_Source::UDPO_STATUS_DELIVERED:
                return 'delivered';
            case Zolago_Po_Model_Source::UDPO_STATUS_CANCELED:
                return 'cancelled';
            case Zolago_Po_Model_Source::UDPO_STATUS_RETURNED:
                return 'returned';
        }
        return '';
    }
}
