<?php

/**
 * Class Zolago_Payment_Model_Allocation
 * @method float getAllocationAmount()
 * @method Zolago_Payment_Model_Allocation setAllocationAmount(float $amount)
 * @method int getTransactionId()
 * @method Zolago_Payment_Model_Allocation setTransactionId(int $id)
 * @method int getPrimary()
 */
class Zolago_Payment_Model_Allocation extends Mage_Core_Model_Abstract {
    const ZOLAGOPAYMENT_ALLOCATION_TYPE_PAYMENT   = 'payment';
    const ZOLAGOPAYMENT_ALLOCATION_TYPE_OVERPAY   = 'overpay'; // nadplata
	const ZOLAGOPAYMENT_ALLOCATION_TYPE_REFUND    = 'refund';

	protected $currentLocale;
	protected $session;

    protected function _construct() {
	    $this->currentLocale = Mage::app()->getLocale()->getLocaleCode();
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
	                $helper = Mage::helper("zolagopayment");
                    $payment = Mage::getModel("sales/order_payment")->load($transaction->getPaymentId());
                    $comment = $helper->__($payment->getMethod());
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
     *    'vendor_id'         => $po->getVendor()->getId(),
     *    'is_automat'        => $isAutomat
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
     *    'vendor_id'         => $po->getVendor()->getId(),
     *    'is_automat'        => $isAutomat
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
	 * @return float
	 */
    public function getSumOfAllocations($poId) {
	    $poId = $this->getPoId($poId);
        return $poId ? $this->getResource()->getSumOfAllocations($poId) : 0.0;
    }

    public function allocateOverpayment($newPo, $transactionId) {
        $newPo = $this->getPo($newPo);
        $coll = null;


        if ($newPo->getId()) { //check if po exists and
            $debtAmount = $newPo->getDebtAmount();


            if ( $debtAmount < 0) { //jezeli jest niedoplata

                if (($coll = $this->getPoOverpayments($newPo)) === false) {
                    return false;
                }

                $data = $coll->addFieldToFilter("transaction_id", $transactionId)->getFirstItem();//nadplata dla danej transakcji
                $alocAmount = $data['allocation_amount'];
                $oldPo = $this->getPo($data['po_id']);

	            $this->setLocaleByPo($newPo);
	            $helper = Mage::helper("zolagopayment");

                if ($alocAmount >= abs($debtAmount)) {
                    $endAmountAllocation = $debtAmount;
                } else {
                    $endAmountAllocation = (-1 * $alocAmount);
                }

                $allocations[] = array(
                    'transaction_id'    => $transactionId,
                    'po_id'             => $oldPo->getId(),
                    'allocation_amount' => $endAmountAllocation,
                    'allocation_type'   => self::ZOLAGOPAYMENT_ALLOCATION_TYPE_OVERPAY,
                    'operator_id'       => $this->getOperatorId(),
                    'created_at'        => Mage::getSingleton('core/date')->gmtDate(),
                    'comment'           => $helper->__("Overpayment moved to %s",$newPo->getIncrementId()),
                    'customer_id'       => $oldPo->getCustomerId(),
                    'vendor_id'         => $oldPo->getVendor()->getId(),
                    'is_automat'        => $this->isAutomat()
                );

                $allocations[] = array(
                    'transaction_id'    => $transactionId,
                    'po_id'             => $newPo->getId(),
                    'allocation_amount' => -1 * $endAmountAllocation,
                    'allocation_type'   => self::ZOLAGOPAYMENT_ALLOCATION_TYPE_PAYMENT,
                    'operator_id'       => $this->getOperatorId(),
                    'created_at'        => Mage::getSingleton('core/date')->gmtDate(),
                    'comment'           => $helper->__("Overpayment moved from %s",$oldPo->getIncrementId()),
                    'customer_id'       => $newPo->getCustomerId(),
                    'vendor_id'         => $newPo->getVendor()->getId(),
                    'is_automat'        => $this->isAutomat()
                );
				$this->restoreLocale();
                $r = $this->appendAllocations($allocations);
                if ($r) {
                    Mage::dispatchEvent("zolagopayment_allocate_overpayment_save_after",
                        array(
                            'oldPo' => $oldPo,
                            'newPo' => $newPo,
                            "operator_id" => $this->getOperatorId(),
                            "amount" => abs($endAmountAllocation)
                        ));
                }
                return $r;
            }
        }
        return false;
    }

	public function createOverpayment($po,$commentMoved="Moved to overpayment",$commentCreated="Created overpayment",$rma_id=null) {

		$po = $this->getPo($po);
		$helper = Mage::helper("zolagopayment");
		if($po->getId()) { //check if po exists and

			//rma returned value getting:
			/** @var Zolago_Rma_Model_Rma $rmaModel */
			$rmaModel = Mage::getModel('zolagorma/rma');
			$rmas = $rmaModel->loadByPoId($po->getId());
			$rmaReturnedValue = 0;
			foreach($rmas as $rma) {
				$rmaReturnedValue += $rma->getReturnedValue();
			}

			if($rmaReturnedValue) {
				$poGrandTotal = $po->getGrandTotalInclTax() - $rmaReturnedValue;
				if($poGrandTotal < 0) {
					return false;
				}
			} elseif (in_array($po->getUdropshipStatus(), array(Zolago_Po_Model_Po_Status::STATUS_CANCELED, Zolago_Po_Model_Po_Status::STATUS_RETURNED))) {
                $poGrandTotal = 0;
            } else {
	            $poGrandTotal = $po->getGrandTotalInclTax();
            }
			$poAllocationSum = $this->getSumOfAllocations($po->getId());
			if($poGrandTotal < $poAllocationSum) { //if there is overpayment
				$operatorId = $this->getOperatorId();

				$overpaymentAmount = $finalOverpaymentAmount = $poAllocationSum - $poGrandTotal;

				$payments = $this->getPoPayments($po,true); //get all po payments
				$allocations = array();
				if($payments) { //if there are any then
					$createdAt = Mage::getSingleton('core/date')->gmtDate();
					$this->setLocaleByPo($po);

					foreach($payments as $payment) {
						if($overpaymentAmount > 0) { //if there is any overpayment then try to allocate it from payment
							if($payment->getAllocationAmount() >= $overpaymentAmount) {//check if currently selected payment has enough cash to create overpayment from it
								$paymentDecreaseAmount = $overpaymentAmount;
								$overpaymentAmount = 0;
							} else { //if not allocate as much as possible and leave rest to be taken from next payment
								$paymentDecreaseAmount = $payment->getAllocationAmount();
								$overpaymentAmount -= $paymentDecreaseAmount;
							}
                            if (!$paymentDecreaseAmount) {
                                break;
                            }

							//create payment decrease
							$allocations[] = array(
                                'transaction_id'    => $payment->getTransactionId(),
								'po_id'             => $po->getId(),
								'allocation_amount' => -1 * $paymentDecreaseAmount,
								'allocation_type'   => self::ZOLAGOPAYMENT_ALLOCATION_TYPE_PAYMENT,
								'operator_id'       => $operatorId,
								'created_at'        => $createdAt,
								'comment'           => $helper->__($commentMoved),
								'customer_id'       => $po->getCustomerId(),
                                'vendor_id'         => $po->getVendor()->getId(),
                                'is_automat'        => $this->isAutomat(),
								'rma_id'            => $rma_id
							);

							//create overpayment
							$allocations[] = array(
								'transaction_id'    => $payment->getTransactionId(),
								'po_id'             => $po->getId(),
								'allocation_amount' => $paymentDecreaseAmount,
								'allocation_type'   => self::ZOLAGOPAYMENT_ALLOCATION_TYPE_OVERPAY,
								'operator_id'       => $operatorId,
								'created_at'        => $createdAt,
								'comment'           => $helper->__($commentCreated),
								'customer_id'       => $po->getCustomerId(),
                                'vendor_id'         => $po->getVendor()->getId(),
                                'is_automat'        => $this->isAutomat(),
								'rma_id'            => $rma_id
							);
						} else {
							break;
						}
					}
					$this->restoreLocale();
                    $r = false;
                    if (!empty($allocations)) {
                        $r = $this->appendMultipleAllocations($allocations);
                    }
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
     * @return int
     */
    public function isAutomat() {
        return (!$this->getSession()->isLoggedIn()) ? 1 : 0;
    }

	/**
	 * @param int|Zolago_Po_Model_Po $po
	 * @param bool $orderByAmount
	 * @return bool|Zolago_Payment_Model_Resource_Allocation_Collection
	 */
	public function getPoPayments($po,$orderByAmount=false) {
		/** @var Zolago_Payment_Model_Resource_Allocation_Collection $collection */
        $po_id = $this->getPoId($po);

		$collection = $this->getPoAllocations($po);
		if($collection) {
			$collection->addPoIdFilter($po_id);
			$collection->getSelect()->where("main_table.allocation_type = ?",self::ZOLAGOPAYMENT_ALLOCATION_TYPE_PAYMENT);
			if($orderByAmount) {
				$collection->addOrder("main_table.allocation_amount");//desc
			}
			return $collection;
		}
		return false;
	}

	/**
	 * @param int|Zolago_Po_Model_Po $po
	 * @return bool|Zolago_Payment_Model_Resource_Allocation_Collection
	 */
	public function getPoRefunds($po) {
		/** @var Zolago_Payment_Model_Resource_Allocation_Collection $collection */
		$po_id = $this->getPoId($po);

		$collection = $this->getPoAllocations($po_id);
		if($collection) {
			$collection->addPoIdFilter($po_id);
			$collection->getSelect()->where("main_table.allocation_type = ?",self::ZOLAGOPAYMENT_ALLOCATION_TYPE_REFUND);
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
		$po = $this->getPo($po_id);
		if($po instanceof Zolago_Po_Model_Po) {
			/** @var Zolago_Payment_Model_Resource_Allocation_Collection $collection */
			$collection = $this->getCollection();
			if(!$byCustomer) {
				$collection->joinPos();
				$collection->getSelect()->where("udropship_po.order_id = ?", $po->getOrderId());
				$collection->getSelect()->where("udropship_po.udropship_vendor = ?", $po->getUdropshipVendor());
			} else {
				$collection->getSelect()->where("main_table.customer_id = ?", $po->getCustomerId());
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
			$collection->joinPos();
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
					"main_table.comment",
                    "main_table.vendor_id",
                    "main_table.is_automat",
					"udropship_po.increment_id"
				))
				->where("main_table.allocation_type = ?",self::ZOLAGOPAYMENT_ALLOCATION_TYPE_OVERPAY)
				->where("main_table.vendor_id = ?" , $udpoVendorId)
				->having("allocation_amount > 0")
				->group("main_table.transaction_id")
				->order("main_table.created_at",Zend_Db_Select::SQL_DESC);

			return $collection;
		}
		return false;
	}

	/**
	 * gets all allocations for single transaction
	 * @param int|Mage_Sales_Model_Order_Payment_Transaction $transaction_id
	 * @return Zolago_Payment_Model_Resource_Allocation_Collection
	 */
	public function getTransactionAllocations($transaction_id) {
		$transaction_id = $this->getRealTransactionId($transaction_id);

		/** @var Zolago_Payment_Model_Resource_Allocation_Collection $collection */
		$collection = $this->getCollection();

		$collection
			->joinOperators()
			->joinPos()
			->joinRmas()
			->joinTransactions()
			->joinRefundTransactions()
			->joinVendors()
			->joinCustomers()
			->getSelect()
				->order('main_table.created_at',Zend_Db_Select::SQL_ASC);

		$collection->getSelect()->where("main_table.transaction_id = ?", $transaction_id);

		return $collection;
	}

	/**
	 * @return bool
	 */
	protected function isOperatorMode() {
		return $this->getSession()->isOperatorMode();
	}

    protected function isVendorMode() {
        return $this->getSession()->isVendorMode();
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
	 * @param int|Mage_Sales_Model_Order_Payment_Transaction $transaction
	 * @return int
	 */
	protected function getRealTransactionId($transaction) {
		if($transaction instanceof Mage_Sales_Model_Order_Payment_Transaction) {
			return $transaction->getId();
		}
		return $transaction;
	}

	/**
	 * @return Zolago_Payment_Model_Resource_Allocation
	 */
	public function getResource() {
		return parent::getResource();
	}

	protected function setLocaleByPo($po) {
		$po = $this->getPo($po);
		if($po instanceof Zolago_Po_Model_Po) {
			$locale = Mage::getModel('core/store')->load($po->getOrder()->getStoreId())->getLocaleCode();
			Mage::app()->getLocale()->setLocaleCode($locale);
			Mage::getSingleton('core/translate')->setLocale($locale)->init('frontend', true);
		}
	}

	protected function  restoreLocale() {
		Mage::app()->getLocale()->setLocaleCode($this->currentLocale);
		Mage::getSingleton('core/translate')->setLocale($this->currentLocale)->init('frontend', true);
	}
}