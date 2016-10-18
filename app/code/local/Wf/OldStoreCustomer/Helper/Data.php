<?php

/**
 * Class Wf_OldStoreCustomer_Helper_Data
 */
class Wf_OldStoreCustomer_Helper_Data extends Mage_Core_Helper_Abstract
{


    /**
     * @param $email
     * @param $websiteId
     * @return bool
     */
    public function showOldStoreMessage($email, $websiteId)
    {
        $email = trim($email);
        /** @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId($websiteId);
        $customer->loadByEmail($email);

        if (!$customer->getId()) {
            $oldStoreCustomersCollection = Mage::getModel('wfoldstorecustomer/customer')->getCollection();
            $oldStoreCustomersCollection->addFieldToFilter("email", $email);
            $oldStoreCustomersCollection->addFieldToFilter("has_account_in_old_store", 1);
            if ($oldStoreCustomersCollection->getFirstItem()->getId()) {
                return TRUE;
            }
        }
        return FALSE;

    }


    /**
     * @param $isCheckout
     * @return string
     */
    public function getOldStoreAccountMessage($isCheckout)
    {
        if ($isCheckout)
            return $this->__("SORRY BUT YOU NO LONGER HAVE AN ACCOUNT IN OUR ONLINE STORE<br><br>We have launched a completely new and improved online store and unfortunately customer accounts created in the old version are no longer active. When placing your order you can create a customer account or purchase as a guest, without registering.");

        return $this->__("SORRY BUT YOU NO LONGER HAVE AN ACCOUNT IN OUR ONLINE STORE<br><br>We have launched a completely new and improved online store and unfortunately customer accounts created in the old version are no longer active. Create a new account and enjoy all of its advantages. Registering will take just a moment.");
    }

}