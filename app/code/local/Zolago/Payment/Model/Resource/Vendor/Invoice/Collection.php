<?php

/**
 * Class Zolago_Payment_Model_Resource_Vendor_Invoice_Collection
 */
class Zolago_Payment_Model_Resource_Vendor_Invoice_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('zolagopayment/vendor_invoice');
    }

    /**
     * @param Zolago_Dropship_Model_Vendor|int $vendor
     * @return $this
     */
    public function addVendorFilter($vendor) {
        if($vendor instanceof ZolagoOs_OmniChannel_Model_Vendor){
            $vendor = $vendor->getId();
        }
        $this->addFieldToFilter('main_table.vendor_id',(int)$vendor);
        return $this;
    }

    /**
     * Add filter for get invoices only connected with wFirma
     *
     * @return $this
     */
    public function addWFirmaConnectedFilter() {
        $this->addFieldToFilter('main_table.wfirma_invoice_id', array('gt' => 0));
        return $this;
    }

    /**
     * Add sum of all costs (netto and brutto sums)
     *
     * @return $this
     */
    public function addSum() {
        // Brutto
        $this->getSelect()->columns(
            new Zend_Db_Expr('main_table.commission_brutto + main_table.transport_brutto + main_table.marketing_brutto + main_table.other_brutto AS sum_brutto'));
        // Netto
        $this->getSelect()->columns(
            new Zend_Db_Expr('main_table.commission_netto + main_table.transport_netto + main_table.marketing_netto + main_table.other_netto AS sum_netto'));
        return $this;
    }
}