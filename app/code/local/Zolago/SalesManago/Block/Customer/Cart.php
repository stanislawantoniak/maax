<?php

class Zolago_SalesManago_Block_Customer_Cart extends Mage_Core_Block_Template
{

    public function getCustomerCart(){
        $products= array();
        $email = $this->getEmail();

        $customers =
            Mage::getModel("customer/customer")
                ->getCollection();
        $customers->addFieldToFilter("email", $email);
        $customer = $customers->getFirstItem();

        if($customer){
            $quote = Mage::getModel('sales/quote')
                ->setSharedStoreIds(array(Mage::app()->getStore()->getId()))
                ->loadByCustomer($customer);

            if ($quote) {
                $itemCollection = $quote->getItemsCollection(false);
            }
            else {
                $itemCollection = new Varien_Data_Collection();
            }


            foreach ($itemCollection as $item) {
                if($item->getData("parent_item_id") == NULL){
                    $vendorName = Mage::getModel('udropship/vendor')->load($item->getProduct()->getData("udropship_vendor"))->getVendorName();
                    $products[$vendorName][$item->getId()] = $item;
                }
            }

            unset($vendorName);
            unset($item);


        }


        return $products;
    }
}
