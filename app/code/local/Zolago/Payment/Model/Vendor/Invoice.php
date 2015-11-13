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

    protected $_vendor = false;

    protected function _construct()
    {
        $this->_init('zolagopayment/vendor_invoice');
    }

    /**
     * @return Zolago_Dropship_Model_Vendor
     */
    public function getVendor()
    {
        if(!$this->_vendor) {
            $this->_vendor = Mage::getModel("zolagodropship/vendor")->load($this->getVendorId());
        }
        return $this->_vendor;
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
    
    /**
     * check if invoice is not empty 
     *
     * @return false
     */
     public function checkNotEmpty() {
         $data = $this->getData();
         if (empty($data['transport_brutto'])
            && empty($data['marketing_brutto'])
            && empty($data['commission_brutto']) 
            && empty($data['other_brutto'])) 
        { 
            return false;
        }
        return true;

     }

}