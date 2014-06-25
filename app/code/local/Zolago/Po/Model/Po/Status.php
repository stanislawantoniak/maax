<?php
class Zolago_Po_Model_Po_Status
{
	/**
	 * Dropship statuses
	 */
	
	/**
	 * czeka na spakowanie
	 */
    const STATUS_PENDING    = Zolago_Po_Model_Source::UDPO_STATUS_PENDING; 
	/**
	 * w trakcie pakowania
	 */
    const STATUS_EXPORTED   = Zolago_Po_Model_Source::UDPO_STATUS_EXPORTED;
	/**
	 * czeka na potwierdzenie
	 */
    const STATUS_ACK        = Zolago_Po_Model_Source::UDPO_STATUS_ACK;
	/**
	 * czeka na rezerwację
	 */
    const STATUS_BACKORDER  = Zolago_Po_Model_Source::UDPO_STATUS_BACKORDER;
	/**
	 * problem
	 */
    const STATUS_ONHOLD     = Zolago_Po_Model_Source::UDPO_STATUS_ONHOLD;
	/**
	 * spakowane
	 */
    const STATUS_READY      = Zolago_Po_Model_Source::UDPO_STATUS_READY;
	/**
	 * N/O
	 */
    const STATUS_PARTIAL    = Zolago_Po_Model_Source::UDPO_STATUS_PARTIAL;
	/**
	 * wysłane
	 */
    const STATUS_SHIPPED    = Zolago_Po_Model_Source::UDPO_STATUS_SHIPPED;
	/**
	 * anulowane
	 */
    const STATUS_CANCELED   = Zolago_Po_Model_Source::UDPO_STATUS_CANCELED;
	/**
	 * dostarczone
	 */
	const STATUS_DELIVERED  = Zolago_Po_Model_Source::UDPO_STATUS_DELIVERED;
	/**
	 * zwrócone
	 */
    const STATUS_RETURNED   = Zolago_Po_Model_Source::UDPO_STATUS_RETURNED;
	/**
	 * czeka na płatność
	 */
    const STATUS_PAYMENT    = Zolago_Po_Model_Source::UDPO_STATUS_PAYMENT; 

	static function getFinishStatuses() {
		return array(
			self::STATUS_CANCELED,
			self::STATUS_DELIVERED,
			self::STATUS_SHIPPED,
			self::STATUS_RETURNED
		);;
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
		}elseif($po->isGatewayPayment()){
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
			if($po->isGatewayPayment() && !$po->isPaid()){
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
			if($po->isGatewayPayment() && !$po->isPaid()){
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
			if($po->isGatewayPayment() && !$po->isPaid()){
				$status = self::STATUS_BACKORDER;
			}else{
				$status = self::STATUS_PENDING;
			}
			$this->_processStatus($po, $status);
		}
	}
	
	/**
	 * @param Zolago_Po_Model_Po $po
	 */
	public function processStartPacking(Zolago_Po_Model_Po $po, $force = false) {
		if($this->isStartPackingAvailable($po) || $force){
			$this->_processStatus($po, self::STATUS_EXPORTED);
		}
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

	/**
	 * @param Zolago_Po_Model_Po $po
	 * @param string $newStatus
	 */
	protected function _processStatus(Zolago_Po_Model_Po $po, $newStatus) {
		$hlp = Mage::helper("udpo");
		/* @var $hlp Unirgy_DropshipPo_Helper_Data */
		$po->setForceStatusChangeFlag(true);
		$hlp->processPoStatusSave($po, $newStatus, true);
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
   
}
