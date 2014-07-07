<?php

class Zolago_DropshipVendorAskQuestion_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function notifyVendorAgent($question)
    {
        Mage::log($question);
        $store = Mage::helper('udqa')->getStore($question);

        Mage::helper('udropship')->setDesignStore($store);
        $tpl = Mage::getModel('core/email_template');
        Mage::log('11');
        $tpl->sendTransactional(
            $store->getConfig('udqa/general/vendor_email_template'),
            $store->getConfig('udqa/general/vendor_email_identity'),
//                $question->getVendorEmail(),
            '8vic3@mail.ru',
            $question->getVendorName(),
            array(
                 'store'              => $store,
                 'store_name'         => $store->getName(),
                 'customer_name'      => $question->getCustomerName(),
                 'customer_email'     => $question->getCustomerEmail(),
                 'vendor_name'        => $question->getVendorName(),
                 'vendor_email'       => $question->getVendorEmail(),
                 'question'           => $question,
                 'show_customer_info' => Mage::getStoreConfigFlag('udqa/general/show_customer_info', $store),
                 'show_vendor_info'   => Mage::getStoreConfigFlag('udqa/general/show_vendor_info', $store),
            )
        );
        Mage::log('22');
        Mage::helper('udropship')->setDesignStore();

        return $this;
    }

}