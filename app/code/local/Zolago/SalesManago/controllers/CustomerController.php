<?php



class Zolago_SalesManago_CustomerController extends Mage_Core_Controller_Front_Action {

    public function cartAction(){
        $email = $this->getRequest()->getParam('email');

        echo "<h3>In Your cart:</h3>";
        $customer = Mage::getModel("customer/customer");
        $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
        $customer->loadByEmail($email);


        $customerId = (int)$customer->getId();
        //krumo($customerId);
        $quote = Mage::getModel('sales/quote')
            ->load($email,"customer_email");
        //krumo($quote->getData());



        $itemCollection = Mage::getModel('sales/quote_item')->getCollection();
        $itemCollection->getSelect()
            ->joinLeft(
                array('cp' => Mage::getSingleton('core/resource')
                    ->getTableName('catalog/product')),
                'cp.entity_id = main_table.product_id',
                array('cp.attribute_set_id'))
        ;
        $itemCollection->setQuote($quote);


        foreach($itemCollection as $item) {
            //krumo($item->getData(),"-----------------");
            if($item->getProductType() == "configurable"){
                echo $item->getName() . " - " . $item->getSku(). " ". $item->getQty(). "<br />";
            }

        }


    }
}