<?php

/**
 * Class Zolago_Payment_Model_Vendor_Payment
 *
 * @method int getVendorPaymentId()
 * @method string getDate()
 * @method string getCost()
 * @method int getVendorId()
 * @method text getComment()
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

}