<?php

class Zolago_SalesManago_Block_Customer_Cart extends Mage_Core_Block_Template
{

    public function getCustomerCart(){
        $products= array();

        $email = $this->getEmail();

        $customer = Mage::getModel("customer/customer");
        $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
        $customer->loadByEmail($email);

        $quote = Mage::getModel('sales/quote')
            ->load($email,"customer_email");


        $itemCollection = Mage::getModel('sales/quote_item')
            ->getCollection()
            //->addFieldToFilter('parent_item_id', array('null' => true))
        ;
        $itemCollection->setQuote($quote);
krumo($itemCollection);


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
                $item->setData("children", $children[$itemId]);

            }
        }
        return $products;
    }
}
