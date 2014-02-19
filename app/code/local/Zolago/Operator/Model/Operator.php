<?php
/**
 * vendor operators
 */
class Zolago_Operator_Model_Operator extends Mage_Core_Model_Abstract {
    protected function _construct() {   
        $this->_init('zolagooperator/operator');
    }
    
    /**
     * return vendor object
     */
    public function getVendor() {
        if (!$this->hasData('vendor')) {            
            $this->setData('vendor',Mage::getModel('udropship/vendor')->load($this->vendor_id));
        }
        return $this->getData('vendor');
    }

    public function validate($data = null) {
        if($data===null){
            $data = $this->getData();
        }
        elseif($data instanceof Varien_Object){
            $data = $data->getData();
        }

        if(!is_array($data)){
            return false;
        }
        
        
        $errors = Mage::getSingleton("zolagooperator/operator_validator")->validate($data);

        if (empty($errors)) {
            return true;
        }
        return $errors;

    }
    
}