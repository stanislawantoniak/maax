<?php

/**
 * Class Zolago_Payment_Model_Vendor_Payment
 *
 * @method int getVendorPaymentId()
 * @method Zolago_Payment_Model_Vendor_Payment setVendorPaymentId()
 * @method string getDate()
 * @method Zolago_Payment_Model_Vendor_Payment setDate()
 * @method float getCost()
 * @method Zolago_Payment_Model_Vendor_Payment setCost()
 * @method int getVendorId()
 * @method Zolago_Payment_Model_Vendor_Payment setVendorId()
 * @method string getComment()
 * @method Zolago_Payment_Model_Vendor_Payment setComment()
 * @method int getStatementId()
 * @method Zolago_Payment_Model_Vendor_Payment setStatementId()
 *
 */
class Zolago_Payment_Model_Vendor_Payment extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('zolagopayment/vendor_payment');
    }

    public function getVendor()
    {
        return Mage::getModel("zolagodropship/vendor")->load($this->getVendorId());
    }


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
     * @return Zolago_Payment_Model_Vendor_Payment_Validator
     */
    public function getValidator()
    {
        return Mage::getSingleton("zolagopayment/vendor_payment_validator");
    }
    
    /**
     * lock delete if payment is in statement
     */
    public function delete() {
        if ($this->getData('statement_id'))  {
            Mage::throwException("Can't delete. Payment in statement");
        }
        parent::delete();
    }

}