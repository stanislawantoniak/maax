<?php

class ZolagoOs_IAIShop_TestController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {

        error_reporting(E_ALL);
        ini_set("display_errors", 1);

        /** @var ZolagoOs_IAIShop_Helper_Data $helper */
        $helper = Mage::helper("zosiaishop");

        //dummy data
        $request = array();
        $request['getProducts']['params'] = array();
        $request['getProducts']['params']['available'] = "available";
        $request['getProducts']['params']['visible'] = "visible";

        $response = $helper->getProducts($request);
        Zend_Debug::dump($response);

        return;
    }
}