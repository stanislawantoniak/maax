<?php

class Zolago_Catalog_AuthController
        extends Mage_Core_Controller_Front_Action {
    /**
     * Index
     */
    public function indexAction()
    {
        $host = 'http://admin.dev01.lorante.com';
        // $callbackUrl is a path to your file with OAuth authentication example for the Admin user
        $callbackUrl = $host . "/udprod/auth";
        $temporaryCredentialsRequestUrl = $host . "/index.php/oauth/initiate?oauth_callback=" . urlencode($callbackUrl);
        $adminAuthorizationUrl = $host . '/admin/oauth_authorize';
        $accessTokenRequestUrl = $host . '/oauth/token';
        $apiUrl = $host . '/api/rest';
        $consumerKey = 'a692397bbdce9093ce1952eb464087d6';
        $consumerSecret = '8fcfb8acecd55908193f9b523ebad833';
        if(!isset($_SESSION))
        {
            session_start();
        }
        if (!isset($_GET['oauth_token']) && isset($_SESSION['state']) && $_SESSION['state'] == 1) {
            $_SESSION['state'] = 0;
        }
        try {
            $authType = (isset($_SESSION['state']) && $_SESSION['state'] == 2) ? OAUTH_AUTH_TYPE_AUTHORIZATION : OAUTH_AUTH_TYPE_URI;
            $oauthClient = new OAuth($consumerKey, $consumerSecret, OAUTH_SIG_METHOD_HMACSHA1, $authType);
            $oauthClient->enableDebug();

            if (!isset($_GET['oauth_token']) && empty($_SESSION['state'])) {
                echo 'request';
                $requestToken = $oauthClient->getRequestToken($temporaryCredentialsRequestUrl);
                echo 'after';
                $_SESSION['secret'] = $requestToken['oauth_token_secret'];
                $_SESSION['state'] = 1;
                header('Location: ' . $adminAuthorizationUrl . '?oauth_token=' . $requestToken['oauth_token']);
                exit;
            } else if ($_SESSION['state'] == 1) {
                $oauthClient->setToken($_GET['oauth_token'], $_SESSION['secret']);
                $accessToken = $oauthClient->getAccessToken($accessTokenRequestUrl);
                $_SESSION['state'] = 2;
                $_SESSION['token'] = $accessToken['oauth_token'];
                $_SESSION['secret'] = $accessToken['oauth_token_secret'];
                header('Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8');
                header('Location: ' . $callbackUrl);
                exit;
            } else {
                $oauthClient->setToken($_SESSION['token'], $_SESSION['secret']);

                $resourceUrl = "$apiUrl/convertproduct";

                $data = self::emulateConverterTestData();


                $productData = json_encode($data);
                print_r($oauthClient->getLastResponse());
                $oauthClient->fetch($resourceUrl, $productData, OAUTH_HTTP_METHOD_PUT, array('Content-Type' => 'application/json'));

                print_r($oauthClient->getLastResponse());

            }
        }
        catch (Exception $e) {
            Mage::logException($e);
        }
//        catch (OAuthException $e) {
//            echo '--error<br />';
//            print_r($e->getMessage());
//            echo "<br/>";
//            print_r($e->lastResponse);
//        }

    }


    public function configurableAction()
    {

        Zolago_Catalog_Model_Observer::processConfigurableQueue();
        echo 'Done';
    }

    private function emulateConverterTestData()
    {
        $data = array();

        /*Load xml data*/
        $base_path = Mage::getBaseDir('base');
        $file = $base_path . '/var/log/price2-0.xml';
        //$file = $base_path . '/var/log/price2-1.xml';
        $xml = simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NOCDATA);
        $document = (array)$xml;

        $merchant = isset($document['merchant']) ? $document['merchant'] : FALSE;
        /*Load xml data*/
        if ($merchant) {
            $data['merchant'] = $merchant;
            $data['pos'] = array();
            $priceList = isset($document['priceList']) ? $document['priceList'] : array();

            if (!empty($priceList)) {
                //$priceList not empty, so we can start updating
//                $storeId = 0;
                $productsXML = isset($priceList->product) ? $priceList->product : array();

                if (!empty($productsXML)) {
                    $productsButch = array();
                    foreach ($productsXML as $productsXMLItem) {
                        $attributes = $productsXMLItem->attributes();
                        $skuXML = (string)$productsXMLItem;
                        $price = (string)$attributes->price;
                        $productsButch[] = array('sku' => $skuXML, 'price' => $price);
                    }
                    unset($productsXMLItem);
                    unset($price);

                    $data['pos'] = $productsButch;

                }

            }

        }
        return $data;
    }

    /*
     * emulate configurable file
     */
    public function emulateConfigurableAction()
    {
        $store = (int)Mage::app()->getRequest()->getParam('store');
        Zolago_Catalog_Helper_Log::emulateConfigurable(FALSE, $store);
    }




    //////////////////////////////////////////////
    public function queueAction(){

        $ids = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToFilter('type_id', array('eq' => 'simple'))
            ->getAllIds();

        Zolago_Catalog_Helper_Configurable::queue($ids);
    }



}



