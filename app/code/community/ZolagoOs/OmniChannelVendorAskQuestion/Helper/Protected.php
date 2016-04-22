<?php

class ZolagoOs_OmniChannelVendorAskQuestion_Helper_Protected
{
    public function notifyAdminVendor($question)
    {
        ZolagoOs_OmniChannel_Helper_Protected::validateLicense('ZolagoOs_OmniChannelVendorAskQuestion');
        $store = Mage::helper('udqa')->getStore($question);
        if (Mage::helper('udqa')->isNotifyAdminVendor($question)) {
            Mage::helper('udropship')->setDesignStore($store);
            $tpl = Mage::getModel('core/email_template');
            $adminIdent = $store->getConfig('udqa/general/admin_email_identity');
            $tpl->sendTransactional(
                $store->getConfig('udqa/general/admin_vendor_email_template'),
                $store->getConfig('udqa/general/vendor_email_identity'),
                Mage::getStoreConfig('trans_email/ident_' . $adminIdent . '/email', $store),
                Mage::getStoreConfig('trans_email/ident_' . $adminIdent . '/name', $store),
                array(
                    'store' => $store,
                    'store_name' => $store->getName(),
                    'customer_name' => $question->getCustomerName(),
                    'customer_email' => $question->getCustomerEmail(),
                    'vendor_name' => $question->getVendorName(),
                    'vendor_email' => $question->getVendorEmail(),
                    'question' => $question,
                    'show_customer_info' => Mage::getStoreConfigFlag('udqa/general/show_customer_info', $store),
                    'show_vendor_info' => Mage::getStoreConfigFlag('udqa/general/show_vendor_info', $store),
                )
            );
            if ($tpl->getSentSuccess()) {
                $question->setIsAdminQuestionNotified(1);
                Mage::getResourceSingleton('udropship/helper')->updateModelFields($question, array('is_admin_question_notified'));
            }
            Mage::helper('udropship')->setDesignStore();
        }
    }

    public function notifyAdminCustomer($question)
    {
        ZolagoOs_OmniChannel_Helper_Protected::validateLicense('ZolagoOs_OmniChannelVendorAskQuestion');
        $store = Mage::helper('udqa')->getStore($question);
        if (Mage::helper('udqa')->isNotifyAdminCustomer($question)) {
            Mage::helper('udropship')->setDesignStore($store);
            $tpl = Mage::getModel('core/email_template');
            $adminIdent = $store->getConfig('udqa/general/admin_email_identity');
            $tpl->sendTransactional(
                $store->getConfig('udqa/general/admin_customer_email_template'),
                $store->getConfig('udqa/general/vendor_email_identity'),
                Mage::getStoreConfig('trans_email/ident_' . $adminIdent . '/email', $store),
                Mage::getStoreConfig('trans_email/ident_' . $adminIdent . '/name', $store),
                array(
                    'store' => $store,
                    'store_name' => $store->getName(),
                    'customer_name' => $question->getCustomerName(),
                    'customer_email' => $question->getCustomerEmail(),
                    'vendor_name' => $question->getVendorName(),
                    'vendor_email' => $question->getVendorEmail(),
                    'question' => $question,
                    'show_customer_info' => Mage::getStoreConfigFlag('udqa/general/show_customer_info', $store),
                    'show_vendor_info' => Mage::getStoreConfigFlag('udqa/general/show_vendor_info', $store),
                )
            );
            if ($tpl->getSentSuccess()) {
                $question->setIsAdminAnswerNotified(1);
                Mage::getResourceSingleton('udropship/helper')->updateModelFields($question, array('is_admin_answer_notified'));
            }
            Mage::helper('udropship')->setDesignStore();
        }
    }

    public function notifyCustomer($question)
    {
        ZolagoOs_OmniChannel_Helper_Protected::validateLicense('ZolagoOs_OmniChannelVendorAskQuestion');
        $store = Mage::helper('udqa')->getStore($question);
        if (Mage::helper('udqa')->isNotifyCustomer($question)) {
            Mage::helper('udropship')->setDesignStore($store);
            $tpl = Mage::getModel('core/email_template');
            if (Mage::getStoreConfigFlag('udqa/general/send_admin_notifications_copy', $store)) {
                $adminIdent = $store->getConfig('udqa/general/admin_email_identity');
                $tpl->addBcc(Mage::getStoreConfig('trans_email/ident_' . $adminIdent . '/email', $store));
            }
            $tpl->sendTransactional(
                $store->getConfig('udqa/general/customer_email_template'),
                $store->getConfig('udqa/general/customer_email_identity'),
                $question->getCustomerEmail(),
                $question->getCustomerName(),
                array(
                    'store' => $store,
                    'store_name' => $store->getName(),
                    'customer_name' => $question->getCustomerName(),
                    'customer_email' => $question->getCustomerEmail(),
                    'vendor_name' => $question->getVendorName(),
                    'vendor_email' => $question->getVendorEmail(),
                    'question' => $question,
                    'show_customer_info' => Mage::getStoreConfigFlag('udqa/general/show_customer_info', $store),
                    'show_vendor_info' => Mage::getStoreConfigFlag('udqa/general/show_vendor_info', $store),
                )
            );
            if ($tpl->getSentSuccess()) {
                $question->setIsCustomerNotified(1);
                Mage::getResourceSingleton('udropship/helper')->updateModelFields($question, array('is_customer_notified'));
            }
            Mage::helper('udropship')->setDesignStore();
        }
    }

    public function notifyVendor($question)
    {
        ZolagoOs_OmniChannel_Helper_Protected::validateLicense('ZolagoOs_OmniChannelVendorAskQuestion');
        $store = Mage::helper('udqa')->getStore($question);
        if (Mage::helper('udqa')->isNotifyVendor($question)) {
            Mage::helper('udropship')->setDesignStore($store);
            $tpl = Mage::getModel('core/email_template');
            if (Mage::getStoreConfigFlag('udqa/general/send_admin_notifications_copy', $store)) {
                $adminIdent = $store->getConfig('udqa/general/admin_email_identity');
                $tpl->addBcc(Mage::getStoreConfig('trans_email/ident_' . $adminIdent . '/email', $store));
            }
            $tpl->sendTransactional(
                $store->getConfig('udqa/general/vendor_email_template'),
                $store->getConfig('udqa/general/vendor_email_identity'),
                $question->getVendorEmail(),
                $question->getVendorName(),
                array(
                    'store' => $store,
                    'store_name' => $store->getName(),
                    'customer_name' => $question->getCustomerName(),
                    'customer_email' => $question->getCustomerEmail(),
                    'vendor_name' => $question->getVendorName(),
                    'vendor_email' => $question->getVendorEmail(),
                    'question' => $question,
                    'show_customer_info' => Mage::getStoreConfigFlag('udqa/general/show_customer_info', $store),
                    'show_vendor_info' => Mage::getStoreConfigFlag('udqa/general/show_vendor_info', $store),
                )
            );
            if ($tpl->getSentSuccess()) {
                $question->setIsVendorNotified(1);
                Mage::getResourceSingleton('udropship/helper')->updateModelFields($question, array('is_vendor_notified'));
            }
            Mage::helper('udropship')->setDesignStore();
        }
    }
}