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

	public function createOverpayment($po) {

		$po = $this->getPo($po);
//        Mage::log($po->getId(), null, "op.log");
		if($po->getId() && $this->isOperatorMode()) { //check if po exists and
			$poGrandTotal = $po->getGrandTotalInclTax();
			$poAllocationSum = $this->getSumOfAllocations($po->getId());
//            Mage::log("poGrandTotal $poGrandTotal || poAllocationSum $poAllocationSum", null, "op.log");
			if($poGrandTotal < $poAllocationSum) { //if there is overpayment
//                Mage::log("grandtotoal jest mniejszy od sumy", null, "op.log");
				$operatorId = $this->getOperatorId();
				$overpaymentAmount = $finalOverpaymentAmount = $poAllocationSum - $poGrandTotal;
				$payments = $this->getPoPayments($po); //get all po payments
				$allocations = array();
//                Mage::log("operatorid: $operatorId", null, "op.log");
//                Mage::log("overpaymentAmount $overpaymentAmount", null, "op.log");
				if($payments) { //if there are any then
					$createdAt = Mage::getSingleton('core/date')->gmtDate();
					$helper = Mage::helper("zolagopayment");
//                    Mage::log($payments->getSize(), null, "op.log");
					foreach($payments as $payment) {
						if($overpaymentAmount > 0) { //if there is any overpayment then try to allocate it from payment
							if($payment->getAllocationAmount() >= $overpaymentAmount) {//check if currently selected payment has enough cash to create overpayment from it
//                                Mage::log("tuttaj 1", null, "op.log");
								$paymentDecreaseAmount = $overpaymentAmount;
								$overpaymentAmount = 0;
							} else { //if not allocate as much as possible and leave rest to be taken from next payment
//                                Mage::log("tuttaj 2", null, "op.log");
								$paymentDecreaseAmount = $payment->getAllocationAmount();
								$overpaymentAmount -= $paymentDecreaseAmount;
							}
//                            Mage::log("paymentDecreaseAmount $paymentDecreaseAmount", null, "op.log");
//                            Mage::log("overpaymentAmount $overpaymentAmount", null, "op.log");
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
//                    Mage::log("allocations:", null, "op.log");
//                    Mage::log($allocations, null, "op.log");
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
	 * @return bool|Zolago_Payment_Model_Resource_Allocation_Collection
	 */
	public function getPoPayments($po) {
		/** @var Zolago_Payment_Model_Resource_Allocation_Collection $collection */
        $po_id = $this->getPoId($po);

		$collection = $this->getPoAllocations($po_id);
		if($collection) {
			$collection->addPoIdFilter($po_id);
			$collection->addAllocationTypeFilter(self::ZOLAGOPAYMENT_ALLOCATION_TYPE_PAYMENT);
            $collection->addOrder("allocation_amount");//desc
            $collection->getSelect()->where("allocation_amount > 0");
			return $collection;
		}
		return false;
	}

	/**
	 * @param int|Zolago_Po_Model_Po $po_id
	 * @return bool|Zolago_Payment_Model_Resource_Allocation_Collection
	 */
	protected function getPoAllocations($po_id) {
		$po_id = $this->getPoId($po_id);
		if($po_id) {
			/** @var Zolago_Payment_Model_Resource_Allocation_Collection $collection */
			$collection = $this->getCollection();
			$collection->addPoIdFilter($po_id);
			return $collection;
		}
		return false;
	}

	/**
	 * @param int|Zolago_Po_Model_Po $po_id
	 * @return bool|Zolago_Payment_Model_Resource_Allocation_Collection
	 */
	public function getPoOverpayments($po_id) {
		$po_id = $this->getPoId($po_id);
		if($po_id) {
			$collection = $this->getPoAllocations($po_id);
			$collection->addAllocationTypeFilter(self::ZOLAGOPAYMENT_ALLOCATION_TYPE_OVERPAY);
			$collection->getSelect()
				->reset(Zend_Db_Select::COLUMNS)
				->columns(array(
					"allocation_id" => "MAX(main_table.allocation_id)",
					"transaction_id" => "main_table.transaction_id",
					"po_id" => "main_table.po_id",
					"allocation_amount" => "SUM(main_table.allocation_amount)",
					"allocation_type" => "main_table.allocation_type",
					"created_at" => "MAX(main_table.created_at)",
					"customer_id" => "main_table.customer_id"
				))
				->joinLeft(
					array("main_table_again" => "zolago_payment_allocation"),
					"MAX(main_table.allocation_id) = main_table_again.allocation_id",
					array(
						"operator_id"=>"main_table_again.operator_id",
						"comment"=>"main_table_again.comment"
					));
			$collection->getSelect()->group("main_table.transaction_id");
			$collection->getSelect()->having("allocation_amount_sum > 0");
			return $collection;
		}
		return false;
	}

	/**
	 * @return bool
	 */
	protected function isOperatorMode() {
//		return $this->getSession()->isOperatorMode();
        return true; //temporary for testing
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
			$po = Mage::getModel("zolago/po")->load($po);
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