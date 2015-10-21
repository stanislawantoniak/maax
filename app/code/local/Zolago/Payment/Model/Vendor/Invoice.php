<?php

/**
 * Class Zolago_Payment_Model_Vendor_Invoice
 *
 * @method int getVendorId()
 * @method string getDate()
 * @method string getSaleDate()
 *
 * @method int getWfirmaInvoiceId()
 * @method string getWfirmaInvoiceNumber()
 *
 * @method string getCommissionNetto()
 * @method string getCommissionBrutto()
 *
 * @method string getTransportNetto()
 * @method string getTransportBrutto()
 *
 * @method string getMarketingNetto()
 * @method string getMarketingBrutto()
 *
 * @method string getOtherNetto()
 * @method string getOtherBrutto()
 *
 * @method int getIsInvoiceCorrection()
 *
 */
class Zolago_Payment_Model_Vendor_Invoice extends Mage_Core_Model_Abstract
{
    const INVOICE_TYPE_ORIGINAL = 0;
    const INVOICE_TYPE_CORRECTION = 1;

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