<?php

class Zolago_DropshipMicrositePro_Helper_Data extends ZolagoOs_OmniChannelMicrositePro_Helper_Data {

    public function sendVendorConfirmationEmail($vendor)
    {
        $store = Mage::app()->getDefaultStoreView();
        Mage::helper('udropship')->setDesignStore($store);
        $bcc = Mage::getStoreConfig('zolagoos/microsite/invitation_email_send_copy_to');
        $bccList = explode(',',$bcc);
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
            $store->getConfig('udropship/vendor/vendor_email_identity'),
            $bccList
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