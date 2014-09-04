<?php
/**
 * Author: Paweł Chyl <pawel.chyl@orba.pl>
 * Date: 04.09.2014
 */

class Zolago_Customer_Block_Account_Forgotpasswordmessage extends Mage_Core_Block_Template
{
    const MESSAGE_CMS_STATIC_BLOCK_ID = "customer_forgotpassword_message";

    public function getCustomerEmail()
    {
        return Mage::getSingleton("customer/session")->getData("forgotpassword_customer_email", true);
    }

    public function getMessageHtml()
    {
        // get block
        $block = Mage::getModel("cms/block")
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load(self::MESSAGE_CMS_STATIC_BLOCK_ID);

        $data = array(
            "customer_email" => $this->getCustomerEmail()
        );

        $filter = Mage::getModel("cms/template_filter")
            ->setVariables($data);

        return $filter->filter($block->getContent());
    }
} 