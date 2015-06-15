<?php

class Zolago_SalesManago_Block_Customer_Cart extends Mage_Core_Block_Template
{

    public function getCustomerCart(){
        $products= array();

        $email = $this->getEmail();

        $customer = Mage::getModel("customer/customer");
        $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
        $customer->loadByEmail($email);


        $customerId = (int)$customer->getId();
        //krumo($customerId);
        $quote = Mage::getModel('sales/quote')
            ->load($email,"customer_email");
        //krumo($quote->getData());



        $itemCollection = Mage::getModel('sales/quote_item')
            ->getCollection()
            //->addFieldToFilter('parent_item_id', array('null' => true))
        ;
        $itemCollection->setQuote($quote);



        $children = array();
        foreach ($itemCollection as $item) {
            //krumo($item->getProduct()->getData(), "-----------------");
            $id = $item->getProduct()->getId();
            if($item->getData("parent_item_id") == NULL){
                $vendorName = Mage::getModel('udropship/vendor')->load($item->getProduct()->getData("udropship_vendor"))->getVendorName();
                $products[$vendorName][$item->getId()] = $item;
                echo $item->getId() . ": ".$item->getProduct()->getName() . " - " . $item->getProduct()->getSkuv() . " " . $item->getQty() . "<br />";
            } else {
                $children[$item->getData("parent_item_id")] = $item;
            }

        }
        unset($vendorName);

        //krumo($products,$children);
        return $products;
    }
}
