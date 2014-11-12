<?php
class Zolago_Rma_Model_Rma_Status
{
	/**
	 * Oczekuje na zamówienie kuriera
	 */
	const STATUS_PENDING_COURIER = "pending_courier";
	/**
	 * Oczekuje na nadanie przesyłki
	 */
	const STATUS_PENDING_PICKUP = "pending_pickup";
	/**
	 * Oczekuje na przesyłkę
	 */
	const STATUS_PENDING_DELIVERY = "pending_delivery";
	/**
	 * Nowe
	 */
	const STATUS_PENDING = "pending";
	/**
	 * Otrzymana przesyłka
	 */
	const STATUS_SHIPMENT_RECIVED = "shipment_recived";
	/**
	 * W trakcie wyjaśniania
	 */
	const STATUS_PROCESSING = "processing";
	/**
	 * Potwierdzona realizacja
	 */
	const STATUS_ACCEPTED = "acctepted";
	/**
	 * Odrzucona realizacja
	 */
	const STATUS_REJECTED = "rejected";
	/**
	 * Zamknięte - zrealizowane
	 */
	const STATUS_CLOSED_ACCEPTED = "closed_accepted";
	/**
	 * Zamknięte – niezrealizowane
	 */
	const STATUS_CLOSED_REJECTED = "closed_rejected";
	
	/**
	 * Xmla path to status object in db
	 */
	const XML_PATH_STATUES = "urma/general/statuses";
	
	/**
	 * @var array
	 */
	protected $_statues;

	/**
	 * @param Zolago_Rma_Model_Rma|string $status
	 * @return array
	 */
	public function getAvailableStatuses($status, $withCurrent=false, $asHash=true) {
		$ret = array();
		$obj = $this->getStatusObject($status);
		
		if($withCurrent){
			$ret[$obj->getCode()] = $obj->getTitle();
		}
		
		switch ($obj->getCode()){
			case self::STATUS_PENDING_COURIER:
			case self::STATUS_PENDING_PICKUP:
				$ret[self::STATUS_PROCESSING] = true;
				$ret[self::STATUS_CLOSED_REJECTED] = true;
			break;
			case self::STATUS_PENDING_DELIVERY:
				$ret[self::STATUS_SHIPMENT_RECIVED] = true;
				$ret[self::STATUS_PROCESSING] = true;
			break;
			case self::STATUS_PENDING:
				$ret[self::STATUS_PENDING_COURIER] = true;
			case self::STATUS_SHIPMENT_RECIVED:
				$ret[self::STATUS_PROCESSING] = true;
				$ret[self::STATUS_ACCEPTED] = true;
				$ret[self::STATUS_REJECTED] = true;
			break;
			case self::STATUS_PROCESSING:
				$ret[self::STATUS_PENDING_COURIER] = true;
				$ret[self::STATUS_PENDING_DELIVERY] = true;
				$ret[self::STATUS_PENDING_PICKUP] = true;
				$ret[self::STATUS_PENDING] = true;
				$ret[self::STATUS_SHIPMENT_RECIVED] = true;
				$ret[self::STATUS_ACCEPTED] = true;
				$ret[self::STATUS_REJECTED] = true;
			break;
			case self::STATUS_ACCEPTED:
				$ret[self::STATUS_PROCESSING] = true;
				$ret[self::STATUS_CLOSED_ACCEPTED] = true;
			break;
			case self::STATUS_REJECTED:
				$ret[self::STATUS_PROCESSING] = true;
				$ret[self::STATUS_CLOSED_REJECTED] = true;
			break;
		}
		
		if($asHash){
			foreach($ret as $status=>&$value){
				$value =  Mage::helper("zolagorma")->__($this->getStatusObject($status)->getTitle());
			}
		}else{
			$out = $ret;
			$ret = array();
			foreach($out as $status=>$value){
				$obj = $this->getStatusObject($status);
				$ret[] = $obj->getData() + array(
					"label" => Mage::helper("zolagorma")->__($obj->getTitle()),
					"value" => $obj->getCode()
				);
			}
		}
		
		return $ret;
	}


	/**
	 * @param string|Zolago_Rma_Model_Rma $status
	 * @return bool
	 */
	public function isEditingAddressAvailable($status) {
		return (bool)$this->getStatusObject($status)->getEditAddress();
	}
	
	/**
	 * @param string|Zolago_Rma_Model_Rma $status
	 * @return bool
	 */
	public function isPrintShippingLabelAvailable($status) {
		return (bool)$this->getStatusObject($status)->getPrintShippingLabel();
	}
	
	/**
	 * @param string|Zolago_Rma_Model_Rma $status
	 * @return bool
	 */
	public function isVendorCommentAvailable($status) {
		return (bool)$this->getStatusObject($status)->getVendorComment();
	}
	
	/**
	 * @param string|Zolago_Rma_Model_Rma $status
	 * @return bool
	 */
	public function isCustomerCommentAvailable($status) {
		return (bool)$this->getStatusObject($status)->getCustomerComment();
	}
	
	/**
	 * @param string|Zolago_Rma_Model_Rma $status
	 * @return bool
	 */
	public function isNotifyCustomerAvailable($status) {
		return (bool)$this->getStatusObject($status)->getNotifyCustomer();
	}

    /**
     * @param $status|Zolago_Rma_Model_Rma $status
     * @return bool
     */
    public function isNotifyEmailAvailable($status){
        return (bool)$this->getStatusObject($status)->getNotifyEmail();
    }
	

	/**
	 * @param Zolago_Rma_Model_Rma $rma
	 * @return Zolago_Rma_Model_Rma_Status
	 */
	public function processNewRmaStatus(Zolago_Rma_Model_Rma $rma) {
		$status = self::STATUS_PENDING;
		if($rma->hasCustomerTracking()){
			$status = self::STATUS_PENDING_PICKUP;
		}
		$rma->setRmaStatus($status);

        //Calculate response deadline
        if($response_deadline = Mage::helper('zolagoholidays/datecalculator')->calculateMaxRmaResponseDeadline($rma, $this->getStatusObject($status), true)){
            $rma->setResponseDeadline($response_deadline->toString('YYYY-MM-dd'));
        }

		return $this;
	}
	

	/**
	 * @param string $status
	 * @return Varien_Object
	 */
	public function getStatusObject($status) {
		if($status instanceof Zolago_Rma_Model_Rma){
			$status = $status->getRmaStatus();
		}
		
		$obejct = new Varien_Object();
		if($data = $this->_getStatus($status)){
			$obejct->setData($data);
		}
		return $obejct;
	}
	
	/**
	 * @return array
	 */
	protected function _getStatus($code=null) {
		if(is_null($this->_statues)){
			$stueses = Mage::helper('udropship')->unserialize(
				Mage::getStoreConfig(self::XML_PATH_STATUES));
			
			$this->_statues = is_array($stueses) ? $stueses : array();
		}
		if(!is_null($code)){
			foreach($this->_statues as $statusArray){
				if(isset($statusArray['code']) && $code==$statusArray['code']){
					return $statusArray;
				}
			}
			return null;
		}
		return $this->_statues;
	}
}