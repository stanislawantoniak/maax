<?php

/**
 * Class Zolago_Payment_Model_Resource_Vendor_Invoice
 */
class Zolago_Payment_Model_Resource_Vendor_Invoice extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('zolagopayment/vendor_invoice', 'vendor_invoice_id');
    }


    /**
     * Perform actions after object save
     *
     * @param Varien_Object $object
     * @return Zolago_Payment_Model_Resource_Vendor_Invoice
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        echo "<pre>";
        //var_dump($object->getData("vendor_id"));
        //var_dump($object->getOrigData("vendor_id"));
        //echo "</pre>";
       //die("test");
        $wfirmaInvoiceNumber = $object->getWfirmaInvoiceNumber();

        if (empty($wfirmaInvoiceNumber))
            return parent::_afterSave($object);


        $isInvoiceCorrection = $object->getData("is_invoice_correction");

        //faktury wg daty sprzedaży (a nie daty wystawienia)
        $date = $object->getSaleDate();

        if ($isInvoiceCorrection == Zolago_Payment_Model_Vendor_Invoice::INVOICE_TYPE_CORRECTION) {
            //korekty wg daty wystawienia
            $date = $object->getDate();
        }

        Mage::helper("ghstatements/vendor_balance")
            ->updateVendorBalanceData(
                $object->getVendorId(),
                "vendor_invoice_cost",
                ($object->getCommissionBrutto() + $object->getTransportBrutto() + $object->getMarketingBrutto() + $object->getOtherBrutto()),
                $date
            );
        if($object->getData("vendor_id") !== $object->getOrigData("vendor_id")){
            Mage::helper("ghstatements/vendor_balance")
                ->updateVendorBalanceData(
                    $object->getOrigData("vendor_id"),
                    "vendor_invoice_cost",
                    (-($object->getCommissionBrutto() + $object->getTransportBrutto() + $object->getMarketingBrutto() + $object->getOtherBrutto())),
                    $date
                );
        }
        return parent::_afterSave($object);
    }

}