<?php

/**
 * helper nadpisujacy oryginalny udropship'owy helper
 * dlaczego taki balagan?
 * w helperze zolagodropshipmicrosite nie ma dziedziczenie z oryginalnego helpera
 * a sa roznice w generowaniu url'i w porownaniu z oryginalem
 */

class Zolago_DropshipMicrosite_Helper_Datarewrite extends Unirgy_DropshipMicrosite_Helper_Data {

    public function sendVendorSignupEmail($registration)
    {
        $store = Mage::app()->getDefaultStoreView();
        Mage::helper('udropship')->setDesignStore($store);

        /** @var Zolago_Common_Helper_Data $mailer */
        $mailer = Mage::helper('zolagocommon');
        $mailer->sendEmailTemplate(
            $registration->getEmail(),
            $registration->getVendorName(),
            $store->getConfig('udropship/microsite/signup_template'),
            array(
                'store_name' => $store->getName(),
                'vendor' => $registration,
                'use_attachments' => true
            ),
            $store->getId(),
            $store->getConfig('udropship/vendor/vendor_email_identity')
        );
        Mage::helper('udropship')->setDesignStore();

        return $this;
    }

    public function sendVendorWelcomeEmail($vendor)
    {
        $store = Mage::app()->getDefaultStoreView();
        Mage::helper('udropship')->setDesignStore($store);

        /** @var Zolago_Common_Helper_Data $mailer */
        $mailer = Mage::helper('zolagocommon');
        $confirmation_email_send_copy_to = Mage::getStoreConfig('udropship/microsite/confirmation_email_send_copy_to');
        $mailer->sendEmailTemplate(
            $vendor->getEmail(),
            $vendor->getVendorName(),
            $store->getConfig('udropship/microsite/welcome_template'),
            array(
                'store_name' => $store->getName(),
                'vendor' => $vendor,
                'use_attachments' => true
            ),
            $store->getId(),
            $store->getConfig('udropship/vendor/vendor_email_identity'),
            null,
            $confirmation_email_send_copy_to
        );
        Mage::helper('udropship')->setDesignStore();

        return $this;
    }
}