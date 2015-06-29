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
        $this->addToBasketProductFromVendor();
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
        $this->addToBasketProductFromVendor();
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
        $this->addToBasketProductFromVendor();
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
        $this->addToBasketProductFromVendor();
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
        $this->addToBasketProductFromVendor();
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
        $this->addToBasketProductFromVendor();
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


    /**
     * Add product to basket for specific vendor (default Matterhorn)
     * @param int $vendorId
     * @param string $typeId
     */
    public function addToBasketProductFromVendor($vendorId = 5, $typeId = Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
        /** @var Zolago_Catalog_Model_Product $product */
        $product = $this->_getProductByVendor($vendorId, $typeId);
        $this->_allowProduct($product->getId());
        $url = $product->getProductUrl();
        $this->open($url);
        $this->addToBasketSelectSize();
        $this->click("id=add-to-cart");
        $this->click("css=div.modal-loaded.modal-header > button.close");
    }

    /**
     * Selecting some size of product
     * Works for square and select list
     */
    public function addToBasketSelectSize() {
        // Handling simple select of sizes
        $this->waitForElementPresent("css=.size-box .size label");
        $this->click("css=.size-box .size label");

        // Handling select (with selectboxit) and jQuery
        $this->getEval("
            var win = (this.page().getCurrentWindow().wrappedJSObject) ? this.page().getCurrentWindow().wrappedJSObject : this.page().getCurrentWindow();
            var sm = win.jQuery('.size-box select').data('selectBox-selectBoxIt');
            if(sm) { sm.open().selectOption(0); }");
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