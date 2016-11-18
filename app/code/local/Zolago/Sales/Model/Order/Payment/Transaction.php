<?php

/**
 * Class Zolago_Sales_Model_Order_Payment_Transaction
 */
class Zolago_Sales_Model_Order_Payment_Transaction extends Mage_Sales_Model_Order_Payment_Transaction {
    
    const TYPE_DELIVERY_CHARGE = 'delivery_charge';

    /**
     * @param array $data
     * @return array
     */
    public function validate($data = null)
    {
        if ($data === null) {
            $data = $this->getData();
        } elseif ($data instanceof Varien_Object) {
            $data = $data->getData();
        }

        if (!is_array($data)) {
            return false;
        }

        $errors = $this->getValidator()->validate($data);

        if (empty($errors)) {
            return true;
        }
        return $errors;

    }

    /**
     * Set order instance for transaction depends on transaction behavior
     * If $order equals to true, method isn't loading new order instance.
     *
     * @param Mage_Sales_Model_Order|null|boolean $order
     * @return Mage_Sales_Model_Order_Payment_Transaction
     */
    public function setOrder($order = null)
    {
        if (null === $order || $order === true) {
            if (null !== $this->_paymentObject && $this->_paymentObject->getOrder()) {
                $this->_order = $this->_paymentObject->getOrder();
            } elseif ($this->getOrderId() && $order === null) {
                $this->_order = Mage::getModel('sales/order')->load($this->getOrderId());
            } else {
                $this->_order = false;
            }
        } elseif (!$this->getId() || ($this->getOrderId() == $order->getId())) {
            $this->_order = $order;
        } elseif($this->_paymentObject->getOrder()->getPayment()->getMethod() == Zolago_Adminhtml_Block_Sales_Transactions::PAYMENT_TYPE_BANK_TRANSFER && Mage::app()->getRequest()->getParam('allow_order') == Zolago_Adminhtml_Block_Sales_Transactions::ALLOW_SET_ORDER_FOR_EXISTING_TRANSACTIONS){
            $this->_order = $order;
        } else {
            Mage::throwException(Mage::helper('sales')->__('Set order for existing transactions not allowed'));
        }

        return $this;
    }
    
    
    /**
     * override (new transaction type)
     * @return array
     */
     public function getTransactionTypes() {
         $out = parent::getTransactionTypes();
         $out[self::TYPE_DELIVERY_CHARGE] = Mage::helper('sales')->__('Return delivery charge');
         return $out;
     }
     
     
    /**
     * override 
     * @param 
     * @return 
     */
    protected function _verifyTxnType($txnType = null)
    {
        if (null === $txnType) {
            $txnType = $this->getTxnType();
        }
        if ($txnType === self::TYPE_DELIVERY_CHARGE) {
            return; // new type, OK
        }
        parent::_verifyTxnType($txnType);
    }

}