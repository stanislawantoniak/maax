<?php
/**
 * vendor operators
 */
class Zolago_Operator_Model_Operator extends Mage_Core_Model_Abstract {
    protected function _construct() {   
        $this->_init('zolagooperator/operator');
    }
    public function getVendor() {
        if (!$this->hasData('vendor')) {            
            $this->setData('vendor',Mage::getModel('udropship/vendor')->load($this->vendor_id));
        }
        return $this->getData('vendor');
    }
}