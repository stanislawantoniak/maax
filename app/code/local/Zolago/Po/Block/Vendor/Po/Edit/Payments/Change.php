<?php
class Zolago_Po_Block_Vendor_Po_Edit_Payments_Change
    extends Zolago_Po_Block_Vendor_Po_Edit_Abstract
{
    protected $_po;


    protected function _getPo() {
        if (!$this->_po) {
            $this->_po = Mage::getModel('udpo/po')->load($this->getRequest()->getParam('id'));
        }
        if (!$this->_po->getId()) {
            Mage::throwException(Mage::helper('udpo')->__('Purchase order does not exists'));
        }
        return $this->_po;
    }
    public function getPaymentMethods() {
        $po = $this->_getPo();
        return Mage::helper('zolagopayment')->getChangePaymentMethods($po);
    }
    public function isMethodChecked($code) {
        
        $po = $this->_getPo();
        $method = $po->getOrder()->getPayment()->getMethod();
        return $method == $code;
    }
    public function getChangePaymentFormAction() {
        return $this->getPoUrl('savePayment');
    }
}