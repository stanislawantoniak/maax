<?php

class ZolagoOs_IAIShop_TestController extends Mage_Core_Controller_Front_Action
{

    public function testLogin()
    {
        $messagesCollection = Mage::getModel("ghapi/message")->getCollection();
        $messagesCollection
            ->addFieldToFilter("message", "newOrder")
            ->setOrder('po_increment_id', 'DESC')
            ->setOrder('message_id', 'ASC')
            ->setOrder('vendor_id', 'ASC');
        $messagesCollection->getSelect()->limit(10);

        $messages = $messagesCollection->getData();

        krumo($messages);

        die("test");
        $client = new ZolagoOs_IAIShop_Model_GHAPI_Connector();
        $vendorId = 1;
        $password = "testtest123";
        $apiKey = "dc893a25bdfe745862ec40ee08a5f047b1a6df547e71c74f3b771d587c99ae07";

        $response = $client->doLogin($vendorId, $password, $apiKey);

        if (!property_exists($response, 'token')) {
            return;
        }
        $token = $response->token;
        $batchSize = 10;
        $messageType = "newOrder";
        $orderId = "";
        krumo($response->token);

//        $getChangeOrderMessage = $client->getChangeOrderMessage(
//            $token,
//            $batchSize,
//            $messageType,
//            $orderId
//        );
//
//        krumo($getChangeOrderMessage);


    }

    public function indexAction()
    {

        error_reporting(E_ALL);
        ini_set("display_errors", 1);

        $this->testLogin();

        die("test");

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