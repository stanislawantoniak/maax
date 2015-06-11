<?php

class Zolago_SalesManago_Block_Customer_Cart extends Mage_Core_Block_Template
{

    public function getCustomerCart(){
        $products= array();
        $smcid = $this->getSmcid();

        $customers =
            Mage::getModel("customer/customer")
                ->getCollection();
        $customers->addFieldToFilter("salesmanago_contact_id", $smcid);
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

            $children = array();
            foreach ($itemCollection as $item) {
                if($item->getData("parent_item_id") == NULL){
                    $vendorName = Mage::getModel('udropship/vendor')->load($item->getProduct()->getData("udropship_vendor"))->getVendorName();
                    $products[$vendorName][$item->getId()] = $item;
                } else {
                    $children[$item->getData("parent_item_id")] = $item->getProduct()->getSize();
                }
            }

            unset($vendorName);
            unset($item);

            foreach($products as $vendor => $items){
                foreach($items as $itemId => $item){
                    if(isset($children[$itemId])){ //for configurable products
                        $item->setData("children_size", $children[$itemId]);
                    }
                }
            }
        }


        return $products;
    }
}
