<?php

class Zolago_Catalog_TestController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        $rootId = 3;
        $product = Mage::getModel("catalog/product")->loadByAttribute("skuv","28409-BEZOWY");
        Zend_Debug::dump($product->getData());echo "----------------";

        /* @var $helper Zolago_Solrsearch_Helper_Data */
        $helper = Mage::helper("zolagosolrsearch");
        $categories = $helper->getDefaultCategory($product, $rootId);
        Zend_Debug::dump($categories);echo "----------------";
    }
}
