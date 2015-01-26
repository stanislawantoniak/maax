<?php

class Zolago_Payment_Model_Allocation extends Mage_Core_Model_Abstract {
    const ZOLAGOPAYMENT_ALLOCATION_TYPE_PAYMENT   = 'payment';
    const ZOLAGOPAYMENT_ALLOCATION_TYPE_OVERPAY   = 'overpay'; // nadplata
    const ZOLAGOPAYMENT_ALLOCATION_TYPE_UNDERPAID = 'underpaid'; // niedoplata

	protected $session;

    protected function _construct() {
        $this->_init('zolagopayment/allocation');
    }

    /**
     * @param $transaction Mage_Sales_Model_Order_Payment_Transaction
     * @param $allocation_type
     * @param $operator_id
     * @param $comment
     */
    public function importDataFromTransaction($transaction, $allocation_type, $operator_id = null, $comment = '') {

        if($transaction instanceof Mage_Sales_Model_Order_Payment_Transaction) {
            if ($transaction->getId() && $transaction->getTxnStatus() == Zolago_Payment_Model_Client::TRANSACTION_STATUS_COMPLETED) {
                if (empty($comment)) {
                    /** @var Mage_Sales_Model_Order_Payment $payment */
                    $payment = Mage::getModel("sales/order_payment")->load($transaction->getPaymentId());
                    $comment = $payment->getMethod();
                }
                $data = $this->getResource()->getDataAllocationForTransaction($transaction, $allocation_type, $operator_id, $comment);
                $this->appendAllocations($data);
            }
        }
    }

    /**
     * params data as:
     * array(
     *    'transaction_id'    => $transaction_id,
     *    'po_id'             => $po_id,
     *    'allocation_amount' => $allocation_amount,
     *    'allocation_type'   => $allocation_type,
     *    'operator_id'       => $operator_id,
     *    'created_at'        => Mage::getSingleton('core/date')->gmtDate(),
     *    'comment'           => $comment
     *    'customer_id'       => $po['customer_id']));
     *
     * @param array $data
     * @return bool
     */
    public function appendAllocations($data) {
	    try {
		    foreach ($data as $allocationData) {
			    $allocation = Mage::getModel("zolagopayment/allocation");
			    $allocation->setData($allocationData);
			    $allocation->save();

			    Mage::dispatchEvent("zolagopayment_allocation_save_after", array('po' => Mage::getModel("zolagopo/po")->load($allocationData['po_id'])));
		    }
		    return true;
	    } catch(Exception $e) {
		    Mage::logException($e);
	    }
	    return false;
    }

	/**
	 * params data as:
	 * array(
	 *    'transaction_id'    => $transaction_id,
	 *    'po_id'             => $po_id,
	 *    'allocation_amount' => $allocation_amount,
	 *    'allocation_type'   => $allocation_type,
	 *    'operator_id'       => $operator_id,
	 *    'created_at'        => Mage::getSingleton('core/date')->gmtDate(),
	 *    'comment'           => $comment
	 *    'customer_id'       => $po['customer_id']));
	 *
	 * @param array $data
	 * @return bool
	 */
	public function appendMultipleAllocations($data) {
		try {
			$this->getResource()->appendAllocations($data);
			return true;
		} catch(Exception $e) {
			Mage::logException($e);
		}
		return false;
	}

	/**
	 * @param int|Zolago_Po_Model_Po $poId
	 * @return int|bool
	 */
    public function getSumOfAllocations($poId) {
	    $poId = $this->getPoId($poId);
        return $poId ? $this->getResource()->getSumOfAllocations($poId) : false;
    }

    public function allocateOverpayments($po) {
        $po = $this->getPo($po);
    }

	public function createOverpayment($po) {

		$po = $this->getPo($po);
		if($po->getId()) { //check if po exists and
			$poGrandTotal = $po->getGrandTotalInclTax();
			$poAllocationSum = $this->getSumOfAllocations($po->getId());
			if($poGrandTotal < $poAllocationSum) { //if there is overpayment
				$operatorId = $this->getOperatorId();
				$overpaymentAmount = $finalOverpaymentAmount = $poAllocationSum - $poGrandTotal;
				$payments = $this->getPoPayments($po,true); //get all po payments
				$allocations = array();
				if($payments) { //if there are any then
					$createdAt = Mage::getSingleton('core/date')->gmtDate();
					$helper = Mage::helper("zolagopayment");
					foreach($payments as $payment) {
						if($overpaymentAmount > 0) { //if there is any overpayment then try to allocate it from payment
							if($payment->getAllocationAmount() >= $overpaymentAmount) {//check if currently selected payment has enough cash to create overpayment from it
								$paymentDecreaseAmount = $overpaymentAmount;
								$overpaymentAmount = 0;
							} else { //if not allocate as much as possible and leave rest to be taken from next payment
								$paymentDecreaseAmount = $payment->getAllocationAmount();
								$overpaymentAmount -= $paymentDecreaseAmount;
							}
							//create payment decrease
							$allocations[] = array(
                                'transaction_id' => $payment->getTransactionId(),
								'po_id' => $po->getId(),
								'allocation_amount' => -1 * $paymentDecreaseAmount,
								'allocation_type' => self::ZOLAGOPAYMENT_ALLOCATION_TYPE_PAYMENT,
								'operator_id' => $operatorId,
								'created_at' => $createdAt,
								'comment' => $helper->__("Moved to overpayment"),
								'customer_id' =>  $po->getCustomerId()
							);

							//create overpayment
							$allocations[] = array(
								'transaction_id' => $payment->getTransactionId(),
								'po_id' => $po->getId(),
								'allocation_amount' => $paymentDecreaseAmount,
								'allocation_type' => self::ZOLAGOPAYMENT_ALLOCATION_TYPE_OVERPAY,
								'operator_id' => $operatorId,
								'created_at' => $createdAt,
								'comment' => $helper->__("Created overpayment"),
								'customer_id' =>  $po->getCustomerId()
							);
						} else {
							break;
						}
					}
					$r = $this->appendMultipleAllocations($allocations);
                    if ($r) {
                        Mage::dispatchEvent("zolagopayment_create_overpayment_save_after",
                            array(
                                'po' => $po,
                                "operator_id" => $operatorId,
                                "amount" => $finalOverpaymentAmount
                            ));
                    }
                    return $r;
				}
			}
		}
		return false;
	}

	/**
	 * @param int|Zolago_Po_Model_Po $po
	 * @param bool $orderByAmount
	 * @return bool|Zolago_Payment_Model_Resource_Allocation_Collection
	 */
	public function getPoPayments($po,$orderByAmount=false) {
		/** @var Zolago_Payment_Model_Resource_Allocation_Collection $collection */
        $po_id = $this->getPoId($po);

		$collection = $this->getPoAllocations($po_id);
		if($collection) {
			$collection->addPoIdFilter($po_id);
			$collection->getSelect()->where("main_table.allocation_type = ?",self::ZOLAGOPAYMENT_ALLOCATION_TYPE_PAYMENT);
			if($orderByAmount) {
				$collection->addOrder("main_table.allocation_amount");//desc
			}
            $collection->getSelect()->where("main_table.allocation_amount > 0");
			return $collection;
		}
		return false;
	}

	/**
	 * @param int|Zolago_Po_Model_Po $po_id
	 * @param bool $byCustomer
	 * @return bool|Zolago_Payment_Model_Resource_Allocation_Collection
	 */
	protected function getPoAllocations($po_id,$byCustomer=false) {
		$po_id = $this->getPoId($po_id);
		if($po_id) {
			/** @var Zolago_Payment_Model_Resource_Allocation_Collection $collection */
			$collection = $this->getCollection();
			if(!$byCustomer) {
				$collection->getSelect()->where("main_table.po_id = ?", $po_id);
			} else {
				$collection->getSelect()->where("main_table.customer_id = ?", $this->getPo($po_id)->getCustomerId());
			}
			return $collection;
		}
		return false;
	}

	/**
	 * @param int|Zolago_Po_Model_Po $po_id
	 * @return bool|Zolago_Payment_Model_Resource_Allocation_Collection
	 */
	public function getPoOverpayments($po_id) {

        $po = $this->getPo($po_id);
        $udpoVendorId = $po->getUdropshipVendor();

		$po_id = $this->getPoId($po_id);
		if($po_id) {
			$customer = $po->getCustomerId();
			$byCustomer = $customer ? true : false;
			$collection = $this->getPoAllocations($po_id,$byCustomer);
			$collection->getSelect()
				->reset(Zend_Db_Select::COLUMNS)
				->columns(array(
					"main_table.allocation_id",
					"main_table.transaction_id",
					"main_table.po_id",
					"allocation_amount" => "SUM(main_table.allocation_amount)",
					"main_table.allocation_type",
					"main_table.created_at",
					"main_table.customer_id",
					"main_table.operator_id",
					"main_table.comment"
				))
                ->joinLeft(
                    array("udpo" => Mage::getSingleton('core/resource')->getTableName('udpo/po')),
                    "udpo.entity_id = main_table.po_id",
                    "udpo.udropship_vendor")
				->where("main_table.allocation_type = ?",self::ZOLAGOPAYMENT_ALLOCATION_TYPE_OVERPAY)
				->where("udpo.udropship_vendor = ?" , $udpoVendorId)
				->having("allocation_amount > 0")
				->group("main_table.transaction_id")
				->order("main_table.created_at",Zend_Db_Select::SQL_DESC);
//				->limit(1);
			Mage::log((string)$collection->getSelect(),null,"sql.log");
			return $collection;
		}
		return false;
	}

	/**
	 * @return bool
	 */
	protected function isOperatorMode() {
		return $this->getSession()->isOperatorMode();
	}

	/**
	 * @return int
	 */
	protected function getOperatorId() {
		return $this->getSession()->getOperator()->getId();
	}

	/**
	 * @return Zolago_Dropship_Model_Session
	 */
	protected function getSession() {
		if(!$this->session) {
			/** @var Zolago_Dropship_Model_Session session */
			$this->session = Mage::getSingleton("zolagodropship/session");
		}
		return $this->session;
	}

	/**
	 * @param int|Zolago_Po_Model_Po $po
	 * @return Zolago_Po_Model_Po
	 */
	protected function getPo($po) {
		if(!$po instanceof Zolago_Po_Model_Po) {
			$po = Mage::getModel("zolagopo/po")->load($po);
		}
		return $po;
	}

	/**
	 * @param int|Zolago_Po_Model_Po $po
	 * @return int
	 */
	protected function getPoId($po) {
		if($po instanceof Zolago_Po_Model_Po) {
			return $po->getId();
		}
		return $po;
	}

	/**
	 * @return Zolago_Payment_Model_Resource_Allocation
	 */
	public function getResource() {
		return parent::getResource();
	}
}