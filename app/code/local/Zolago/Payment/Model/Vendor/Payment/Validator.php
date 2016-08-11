<?php

/**
 * Class Zolago_Payment_Model_Vendor_Payment_Validator
 */
class Zolago_Payment_Model_Vendor_Payment_Validator extends Zolago_Common_Model_Validator_Abstract
{

    protected function _getHelper()
    {
        return Mage::helper('zolagopayment');
    }

    public function validate($data)
    {

        $this->_errors = array();
        $this->_data = $data;

        $this->_notEmpty('vendor_id', 'Vendor');

        return $this->_errors;
    }

}
