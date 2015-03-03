<?php

class Zolago_DropshipMicrositePro_Helper_Data extends Unirgy_DropshipMicrositePro_Helper_Data {

    public function sendVendorConfirmationEmail($vendor)
    {
        $store = Mage::app()->getDefaultStoreView();
        Mage::helper('udropship')->setDesignStore($store);

        /** @var Zolago_Common_Helper_Data $mailer */
        $mailer = Mage::helper('zolagocommon');
        $mailer->sendEmailTemplate(
            $vendor->getEmail(),
            $vendor->getVendorName(),
            $store->getConfig('udropship/microsite/confirmation_template'),
            array(
                'store_name' => $store->getName(),
                'vendor' => $vendor,
                'use_attachments' => true
            ),
            $store->getId(),
            $store->getConfig('udropship/vendor/vendor_email_identity')
        );

        Mage::helper('udropship')->setDesignStore();

        return $this;
    }

    public function sendVendorRejectEmail($vendor)
    {
        $store = Mage::app()->getDefaultStoreView();
        Mage::helper('udropship')->setDesignStore($store);

        /** @var Zolago_Common_Helper_Data $mailer */
        $mailer = Mage::helper('zolagocommon');
        $mailer->sendEmailTemplate(
            $vendor->getEmail(),
            $vendor->getVendorName(),
            $store->getConfig('udropship/microsite/reject_template'),
            array(
                'store_name' => $store->getName(),
                'vendor' => $vendor,
                'use_attachments' => true
            ),
            $store->getId(),
            $store->getConfig('udropship/vendor/vendor_email_identity')
        );

        Mage::helper('udropship')->setDesignStore();

        return $this;
    }
}