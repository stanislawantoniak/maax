<?php

/**
 * payment helper
 */
class  Zolago_Payment_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Temporary live time cache
     *
     * @var array
     */
    protected $_cache = array();

    /**
     * @param Zolago_Po_Model_Po $po
     * @return Zolago_Payment_Model_Resource_Allocation_Collection
     */
    public function getAllocationOverpaymentDetails(Zolago_Po_Model_Po $po) {
        $key = 'allocation_overpayment_details_' . $po->getId();
        if (!isset($this->_cache[$key])) {
            $this->_cache[$key] = $this->_addOverpaymentJoins($this->_getAllocationModel()->getPoOverpayments($po));
        }
        return $this->_cache[$key];
    }

    /**
     * @param Zolago_Po_Model_Po $po
     * @return Zolago_Payment_Model_Resource_Allocation_Collection
     */
    public function getAllocationPaymentDetails(Zolago_Po_Model_Po $po) {
        $key = 'allocation_payment_details_'. $po->getId();
        if (!isset($this->_cache[$key])) {
            $this->_cache[$key] = $this->_addPaymentsJoins($this->_getAllocationModel()->getPoPayments($po));
        }
        return $this->_cache[$key];
    }

    public function getSimpleTransactionsDetails(Zolago_Po_Model_Po $po) {
        $key = 'simple_transactions_details_'. $po->getId();
        if (!isset($this->_cache[$key])) {
            $coll = $this->getTransactionCollection($po);
            $coll->addTxnTypeFilter(Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER);
            $this->_cache[$key] = $coll;
        }
        return $this->_cache[$key];
    }

    public function getSimpleRefundsDetails(Zolago_Po_Model_Po $po) {
        $key = 'simple_refunds_details_'. $po->getId();
        if (!isset($this->_cache[$key])) {
            $coll = $this->getTransactionCollection($po);
            $coll->addTxnTypeFilter(Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND);
            $this->_cache[$key] = $coll;
        }
        return $this->_cache[$key];
    }

    /**
     * @return Zolago_Payment_Model_Allocation
     */
    private function _getAllocationModel() {
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
        ->joinTransactions();

        return $collection;
    }

    public function RandomStringForRefund()
    {
        return MD5(strrev(microtime()));
    }

    /**
     * @param string $email
     * @param Zolago_Rma_Model_Rma $rma
     * @param float $refundAmount
     * @param bool|string $paymentType
     * @return bool
     */
    public function sendRmaRefundEmail($email,$rma,$refundAmount,$paymentType=false) {
        if (!$templateId = $this->_getRmaRefundEmailTemplateId()) {
            return false;
        }
        /** @var Zolago_Common_Helper_Data $helper */
        $helper = Mage::helper("zolagocommon");

        Mage::helper('udropship')->setDesignStore($rma->getPo()->getOrder()->getStore());

        $return =  $helper->sendEmailTemplate(
                       $email,
                       '',
                       $templateId,
                       $this->_getRmaRefundEmailVars($rma->getIncrementId(),$refundAmount,$paymentType),
                       true,
                       $this->_getRefundEmailSender()
                   );

        Mage::helper('udropship')->setDesignStore();

        return $return;
    }


    /**
     * @param string $email
     * @param Mage_Sales_Model_Order $order
     * @param float $refundAmount
     * @param bool|string $paymentType
     * @return Mage_Core_Model_Email_Template_Mailer
     */
    public function sendRefundEmail($email,$order,$refundAmount,$paymentType=false) {
        if (!$templateId = $this->_getRefundEmailTemplateId()) {
            return false;
        }
        /** @var Zolago_Common_Helper_Data $helper */
        $helper = Mage::helper("zolagocommon");

        Mage::helper('udropship')->setDesignStore($order->getStore());

        $return = $helper->sendEmailTemplate(
                      $email,
                      '',
                      $templateId,
                      $this->_getRefundEmailVars($order->getIncrementId(),$refundAmount,$paymentType),
                      true,
                      $this->_getRefundEmailSender()
                  );

        Mage::helper('udropship')->setDesignStore();

        return $return;
    }

    protected function _getRefundEmailTemplateId() {
        return Mage::getStoreConfig("payment_refunds/payment_refunds_emails/refund_email_template");
    }

    protected function _getRmaRefundEmailTemplateId() {
        return Mage::getStoreConfig("payment_refunds/payment_refunds_emails/refund_rma_email_template");
    }

    protected function _getRmaRefundEmailVars($rmaId,$refundAmount,$paymentType) {
        return array(
                   'store_name' => Mage::app()->getStore()->getName(),
                   'rma_id' => $rmaId,
                   'return_amount' => $refundAmount,
                   'payment_type' => $paymentType,
                   'use_attachments' => true
               );
    }

    protected function _getRefundEmailVars($orderId,$refundAmount,$paymentType) {
        return array(
                   'store_name' => Mage::app()->getStore()->getName(),
                   'order_id' => $orderId,
                   'return_amount' => $refundAmount,
                   'payment_type' => $paymentType,
                   'use_attachments' => true
               );
    }

    protected function _getRefundEmailSender() {
        return Mage::getStoreConfig("payment_refunds/payment_refunds_emails/refund_email_identity");
    }

    /**
     * @param Mage_Sales_Model_Order_Payment_Transaction $transaction
     * @return bool|Zolago_Rma_Model_Rma
     */
    public function getTransactionRma($transaction) {
        /** @var Zolago_Payment_Model_Allocation $allocationModel */
        $allocationModel = Mage::getModel('zolagopayment/allocation');
        $collection =
            $allocationModel
            ->getCollection()
            ->addFieldToFilter('refund_transaction_id',$transaction->getId())
            ->addFieldToFilter('allocation_type',Zolago_Payment_Model_Allocation::ZOLAGOPAYMENT_ALLOCATION_TYPE_REFUND);

        if($collection->getSize()) {
            foreach($collection as $allocation) {
                if($allocation->getRmaId()) {
                    /** @var Zolago_Rma_Model_Rma $rma */
                    $rma = Mage::getModel('zolagorma/rma');
                    $rma->load($allocation->getRmaId());
                    if($rma->getId()) {
                        return $rma;
                    }
                }
            }
        }
        return false;
    }

    /**
     * @param Mage_Sales_Model_Order_Payment_Transaction $transaction
     * @return bool|Zolago_Po_Model_Po
     */
    public function getTransactionPo($transaction) {
        /** @var Zolago_Payment_Model_Allocation $allocationModel */
        $allocationModel = Mage::getModel('zolagopayment/allocation');
        $collection =
            $allocationModel
            ->getCollection()
            ->addFieldToFilter('refund_transaction_id',$transaction->getId())
            ->addFieldToFilter('allocation_type',Zolago_Payment_Model_Allocation::ZOLAGOPAYMENT_ALLOCATION_TYPE_REFUND);
        if($collection->getSize()) {
            $poRefundSum = 0;
            $poId = false;
            foreach($collection as $allocation) {
                $poRefundSum += abs($allocation->getAllocationAmount());
                if(!$poId) {
                    $poId = $allocation->getPoId();
                }
            }
            if($poRefundSum == abs($transaction->getTxnAmount())) {
                /** @var Zolago_Po_Model_Po $rma */
                $po = Mage::getModel('zolagopo/po');
                $po->load($poId);
                if($po->getId()) {
                    return $po;
                }
            }
        }
        return false;
    }

    public function getCurrencyFormattedAmount($amount) {
        return Mage::helper('core')->currency(
                   $amount,
                   true,
                   false
               );
    }

    /**
     * @param null $store
     * @return bool
     */
    public function getConfigUseAllocation($store = null) {
        $config = (bool)(!Mage::helper('core')->isModuleEnabled('ZolagoOs_OutsideStore')) || (bool)Mage::getStoreConfig('payment/config/use_allocation', $store);
        return (bool)$config;
    }

    /**
     * @param $po
     * @return Mage_Sales_Model_Resource_Order_Payment_Transaction_Collection
     */
    public function getTransactionCollection(Zolago_Po_Model_Po $po) {
        /** @var Mage_Sales_Model_Resource_Order_Payment_Transaction_Collection $coll */
        $coll = Mage::getResourceModel('sales/order_payment_transaction_collection');
        $coll->addOrderIdFilter($po->getOrder()->getId());
        $coll->addFieldToFilter('txn_status', array('eq' => Zolago_Payment_Model_Client::TRANSACTION_STATUS_COMPLETED));
        return $coll;
    }
    /**
     * @param $po
     * @return Mage_Sales_Model_Resource_Order_Payment_Transaction_Collection
     */
    public function getRefundTransactionCollection(Zolago_Po_Model_Po $po) {
        /** @var Mage_Sales_Model_Resource_Order_Payment_Transaction_Collection $coll */
        $coll = Mage::getResourceModel('sales/order_payment_transaction_collection');
        $coll->addOrderIdFilter($po->getOrder()->getId());
        $coll->addFieldToFilter('txn_status', array('in' => array(Zolago_Payment_Model_Client::TRANSACTION_STATUS_COMPLETED,Zolago_Payment_Model_Client::TRANSACTION_STATUS_NEW)));
        return $coll;
    }

    /**
     * @param $po
     * @return float
     */
    public function getSimplePaymentAmount(Zolago_Po_Model_Po $po) {
        $key = 'simple_payment_amount_' . $po->getId();
        if (!isset($this->_cache[$key])) {
            $sum = 0;
            $coll = $this->getTransactionCollection($po);
            $coll->addTxnTypeFilter(Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER);
            /** @var Mage_Sales_Model_Order_Payment_Transaction $transaction */
            foreach ($coll as $transaction) {
                $sum += $transaction->getTxnAmount();
            }
            $coll = $this->getRefundTransactionCollection($po);
            $coll->addTxnTypeFilter(Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND);
            foreach ($coll as $transaction) {
                $sum += $transaction->getTxnAmount();
            }            
            $this->_cache[$key] = $sum;
        }
        return $this->_cache[$key];
    }

    /**
     * list of methods for change payments type
     * @return array
     */

    public function getChangePaymentMethods($po) {
        $out = array();
        $storeId = $po->getStoreId();
        $store = Mage::app()->getStore($storeId);
        foreach (Mage::helper('payment')->getStoreMethods($store,null) as $method) {
            if ($method->getConfigData('visible') &&
                    $method->canUseCheckout()) {
                // wyłączamy dotpay
                switch ($method->getCode()) {
                case Zolago_Payment_Model_Cc::PAYMENT_METHOD_CODE:
                case Zolago_Payment_Model_Gateway::PAYMENT_METHOD_CODE:
                    continue 2;
                default:
                    ;
                }
                $out[] = $method;
            }
        }
        return $out;
    }

}