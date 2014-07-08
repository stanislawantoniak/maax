<?php

class Zolago_DropshipVendorAskQuestion_Helper_Data extends Mage_Core_Helper_Abstract
{



    public function notifyVendorAgent($question)
    {

            $emails = array();
            $tpl = Mage::getModel('core/email_template');

            $store = Mage::helper('udqa')->getStore($question);
            $vendorId = $question->getVendorId();

            Mage::helper('udropship')->setDesignStore($store);

            $template = $store->getConfig('udqa/general/vendor_email_template');
            $identity = $store->getConfig('udqa/general/vendor_email_identity');
            $vendorM = Mage::getResourceModel('zolagoudqa/question');
            $superVendorAgents = $vendorM->getSuperVendorHelpdeskAgentEmails($vendorId);
            $vendorAgents = $vendorM->getVendorHelpdeskAgentEmails($vendorId);

            $emails += $superVendorAgents;
            $emails += $vendorAgents;
            unset($superVendorAgents);unset($vendorAgents);

            if (!empty($emails)) {
                $data = array(
                    'store'              => $store,
                    'store_name'         => $store->getName(),
                    'customer_name'      => $question->getCustomerName(),
                    'customer_email'     => $question->getCustomerEmail(),
                    'vendor_name'        => $question->getVendorName(),
                    'vendor_email'       => $question->getVendorEmail(),
                    'question'           => $question,
                    'show_customer_info' => Mage::getStoreConfigFlag('udqa/general/show_customer_info', $store),
                    'show_vendor_info'   => Mage::getStoreConfigFlag('udqa/general/show_vendor_info', $store),
                );

                foreach ($emails as $email => $_) {
                    $data['vendor_name'] = implode(' ', array($_['firstname'], $_['lastname']));
                    $tpl
                        ->sendTransactional($template, $identity, $email, $question->getVendorName(), $data);

                }
                unset($email);
                unset($_);
            }

        return $this;
    }

}