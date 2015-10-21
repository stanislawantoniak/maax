<?php

/**
 * Class Zolago_Payment_Model_Vendor_Invoice
 */
class Zolago_Payment_Model_Vendor_Invoice extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('zolagopayment/vendor_invoice');
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
     * @return Zolago_Payment_Model_Vendor_Invoice_Validator
     */
    public function getValidator()
    {
        return Mage::getSingleton("zolagopayment/vendor_invoice_validator");
    }

}