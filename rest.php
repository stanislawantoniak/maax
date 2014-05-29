<?php
/**
 * Example of retrieving the products list using Admin account via Magento REST API. OAuth authorization is used
 * Preconditions:
 * 1. Install php oauth extension
 * 2. If you were authorized as a Customer before this step, clear browser cookies for 'yourhost'
 * 3. Create at least one product in Magento
 * 4. Configure resource permissions for Admin REST user for retrieving all product data for Admin
 * 5. Create a Consumer
 */
include_once('developer/class.krumo.php');
define('MAGENTO_ROOT', getcwd());

$compilerConfig = MAGENTO_ROOT . '/includes/config.php';

$mageFilename = MAGENTO_ROOT . '/app/Mage.php';
require_once $mageFilename;

$host = $_SERVER['HTTP_HOST'];


// $callbackUrl is a path to your file with OAuth authentication example for the Admin user
$callbackUrl = "http://modago.local/rest.php";
$temporaryCredentialsRequestUrl = "http://modago.local/index.php/oauth/initiate?oauth_callback=" . urlencode($callbackUrl);
$adminAuthorizationUrl = 'http://modago.local/admin/oauth_authorize';
$accessTokenRequestUrl = 'http://modago.local/oauth/token';
$apiUrl = 'http://modago.local/api/rest';
$consumerKey = '76cc2b14a5bfd31f13245303baaa321a';
$consumerSecret = '71bed0926360cd79cbecbb14fbbc01b2';
//die('test');
session_start();
if (!isset($_GET['oauth_token']) && isset($_SESSION['state']) && $_SESSION['state'] == 1) {
    $_SESSION['state'] = 0;
}
try {
    $authType = ($_SESSION['state'] == 2) ? OAUTH_AUTH_TYPE_AUTHORIZATION : OAUTH_AUTH_TYPE_URI;
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
        $data = array(
            'cmd' => 'saba',
            'merchant' => 'zolago',
            'sku' => '4-18796-00X-XL',
            'data' => array(
                'pos_id' => 1,
                'price' => 3.6)
        );

        echo "fetch\n";

        $storeId =  Mage::app()->getStore()->getId();
        //echo $storeId."<br />";
        //echo "Start reading XML ".date('h:i:s')." ".microtime() . "<br />";
        /*Load xml data*/

        $base_path = Mage::getBaseDir('base');
        //$file = $base_path . '/var/log/price2-0.xml';
        $file = $base_path . '/var/log/price2-1.xml';

        $data = array();

        $xml = simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NOCDATA);
        $document = (array) $xml;
//krumo($document);
        //echo "XML loaded ".date('h:i:s')." ".microtime() . "<br />";

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

                $productAction = Mage::getSingleton('catalog/product_action');
                if (!empty($productsXML)) {

                    $productsButch = array();
                    foreach ($productsXML as $num => $productsXMLItem) {
                        $attributes = $productsXMLItem->attributes();
                        $skuXML = (string)$productsXMLItem;
                        $price = (string)$attributes->price;
                        $productsButch[] = array('sku' => $skuXML, 'price' => $price);
                    }
                    unset($productsXMLItem);
                    unset($price);
                    //echo "productsButch retrieved from XML ".date('h:i:s')." ".microtime() . "<br />";
                    $data['pos'] = $productsButch;

                }

            }

        }

        $productData = json_encode($data);
        //print_r($oauthClient->getLastResponse());
        $oauthClient->fetch($resourceUrl, $productData,  OAUTH_HTTP_METHOD_PUT , array('Content-Type' => 'application/json'));

        //print_r($oauthClient->getLastResponse());




    }
} catch (OAuthException $e) {
    echo '--error<br />';
    print_r($e->getMessage());
    echo "<br/>";
    print_r($e->lastResponse);
}
