<?php
/**
 * selenium checkout test
 */
class Automatic_Selenium_CheckoutTest extends ZolagoSelenium_TestCase {
    public function setUp() {
        $host = $this->getHost();
        $this->setBrowser('*chrome');
        $this->setBrowserUrl($host);
        $this->setSleep(1);

    }
    public function testCheckoutUnregisterNoSubscribe() {
        $this->allowProduct(25758);
        $this->addToBasketMatterhorn();
        $email = $this->_getNewEmail();
        $scenario = array(
            'login' => false,
            'email' => $email,
            'password' => false,
            'newsletter' => false,
            'payment' => 'p_method_cashondelivery',
        );
        $this->_checkout($scenario);
    }
    public function testCheckoutUnregisterSubscribe() {
        $this->allowProduct(25758);
        $this->addToBasketMatterhorn();
        $email = $this->_getNewEmail();
        $scenario = array(
            'login' => false,
            'email' => $email,
            'password' => false,
            'newsletter' => true,
            'payment' => 'p_method_cashondelivery',
        );
        $this->_checkout($scenario);
    }
    public function testRegisterAfterCheckoutSubscribeUnsubscribe() {
        $this->allowProduct(25758);
        $this->addToBasketMatterhorn();
        $email = $this->_getNewEmail();
        $scenario = array(
            'login' => false,
            'email' => $email,
            'password' => false,
            'newsletter' => true,
            'payment' => 'p_method_cashondelivery',
        );
        $this->_checkout($scenario);
        $this->waitForPageToLoad("30000");
        $this->click("link=Twoje Konto");
        $this->waitForPageToLoad("30000");
        $this->_register($email,false);
    }
    public function testRegisterAfterCheckoutSubscribeSubscribe() {
        $this->allowProduct(25758);
        $this->addToBasketMatterhorn();
        $email = $this->_getNewEmail();
        $scenario = array(
            'login' => false,
            'email' => $email,
            'password' => false,
            'newsletter' => true,
            'payment' => 'p_method_cashondelivery',
        );
        $this->_checkout($scenario);
        $this->waitForPageToLoad("30000");
        $this->click("link=Twoje Konto");
        $this->waitForPageToLoad("30000");
        $this->_register($email,true);
    }
    public function testRegisterAfterCheckoutUnsubscribeUnsubscribe() {
        $this->allowProduct(25758);
        $this->addToBasketMatterhorn();
        $email = $this->_getNewEmail();
        $scenario = array(
            'login' => false,
            'email' => $email,
            'password' => false,
            'newsletter' => false,
            'payment' => 'p_method_cashondelivery',
        );
        $this->_checkout($scenario);
        $this->waitForPageToLoad("30000");
        $this->click("link=Twoje Konto");
        $this->waitForPageToLoad("30000");
        $this->_register($email,false);
    }
    public function testRegisterAfterCheckoutUnsubscribeSubscribe() {
        $this->allowProduct(25758);
        $this->addToBasketMatterhorn();
        $email = $this->_getNewEmail();
        $scenario = array(
            'login' => false,
            'email' => $email,
            'password' => false,
            'newsletter' => false,
            'payment' => 'p_method_cashondelivery',
        );
        $this->_checkout($scenario);
        $this->waitForPageToLoad("30000");
        $this->click("link=Twoje Konto");
        $this->waitForPageToLoad("30000");
        $this->_register($email,true);
    }
    public function testAddToBasketMatterhorn() {    
        $this->_getProductUrlByVendor(5);
        die();
        $this->open("/acilia-32371.html");
        $this->click("id=size_384");
        $this->click("id=add-to-cart");
        $this->click("css=div.modal-loaded.modal-header > button.close");
    }
    public function addToBasketEsotiq() {
        $this->open("/stanik-norah.html");
        $this->click("id=select-data-id-281");
        $this->click("id=add-to-cart");
        $this->click("id=popup-after-add-to-cart");

    }
    public function addToBasketJeansdom() {
        $this->open("/levi-sr-511tm-jeans-slim-fit-wood-acre.html");
        $this->click("id=add-to-cart");
        $this->click("css=div.modal-loaded.modal-header > button.close");
    }
    protected function _allowProduct($productId) {
        $product = Mage::getModel('catalog/product')->load($productId);
        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());
        $data = $stockItem->getData();
        if (($data['qty'] < 1) || $data['is_in_stock'] == 0) {
            $stockItem->setData('qty',10);
            $stockItem->setData('is_in_stock',1);
            $stockItem->save();
            $product->save();
        }
    }
    /*
    public function testOrderWithNoAddress() {
        $this->allowProduct(25758);
        $this->addToBasketMatterhorn();
        $this->checkout(true);
    }
    public function testOrder3Vendors() {
        $this->allowProduct(25758);
        $this->addToBasketMatterhorn();
        $this->allowProduct(258);
        $this->addToBasketEsotiq();
        $this->allowProduct(33396);
        $this->addToBasketJeansdom();
        $this->checkout(false);

    }
    */
}