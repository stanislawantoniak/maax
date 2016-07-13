<?php

class ZolagoOs_IAIShop_TestController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {

        error_reporting(E_ALL);
        ini_set("display_errors", 1);

        /** @var ZolagoOs_IAIShop_Helper_Data $helper */
        $helper = Mage::helper("zosiaishop");

        $params = array();
        $response = $helper->addOrders($params);

        //dummy data
        $params = array();
        $params['available'] = "available";
        $params['visible'] = "visible";        

        $response = $helper->getProducts($params);
        Zend_Debug::dump($response);

        return;
    }
}