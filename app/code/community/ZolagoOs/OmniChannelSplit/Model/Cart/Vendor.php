<?php
/**
  
 */

class ZolagoOs_OmniChannelSplit_Model_Cart_Vendor extends Mage_Sales_Model_Quote_Item
{
    public function getProduct()
    {
        $product = parent::getProduct();
        if (!$product->getTypeId()) {
            $product->setTypeId('simple');
        }
        return $product;
    }
}