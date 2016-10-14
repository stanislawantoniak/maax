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
        /** @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId($websiteId);
        $customer->loadByEmail($email);

        if (!$customer->getId()) {
            $oldStoreCustomersCollection = Mage::getModel('wfoldstorecustomer/customer')->getCollection();
            $oldStoreCustomersCollection->addFieldToFilter("has_account_in_new_store", 1);
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
            return $this->__("PRZEPRASZAMY, NIE MASZ JUŻ KONTA W NASZYM SKLEPIE<br><br>Stworzyliśmy zupełnie nowy, wygodniejszy sklep internetowy i niestety konta założone w poprzedniej wersji sklepu nie działają w obecnym. Podczas składania zamówienia możesz założyć nowe konto lub zrobić zakupy jako gość. ");

        return $this->__("PRZEPRASZAMY, NIE MASZ JUŻ KONTA W NASZYM SKLEPIE <br><br>Stworzyliśmy zupełnie nowy, wygodniejszy sklep internetowy i niestety konta założone w poprzedniej wersji sklepu nie działają w obecnym. Załóż konto w nowym sklepie, aby mieć dostęp do wszystkich nowych funkcji. Rejestracja zajmie Ci zaledwie chwilkę. ");
    }

}