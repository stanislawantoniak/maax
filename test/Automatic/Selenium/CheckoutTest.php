<?php
/**
 * selenium checkout test
 */
class Automatic_Selenium_CheckoutTest extends ZolagoSelenium_TestCase {
    public function setUp() {
        $host = $this->getHost();
        $this->setBrowser('*chrome');
        $this->setBrowserUrl($host);
    }

    /**
     * Niezarejestrowany bez subskrypcji
     */
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

    /**
     * Niezarejestrowany z subskrybcją
     */
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

    /**
     * Zarejestrowany zakupy dla 3 vendorow z prostymi i konfigurowalnymi produktami
     * Platnosc przy odbiorze
     * @param string $payment
     * @param string $paymentType
     */
    public function testCheckoutRegistred3VendorsSimpleConfigurableProducts($payment = 'p_method_cashondelivery', $paymentType = '') {
        // Mhorn
        $this->addToBasketProductFromVendor(5,Mage_Catalog_Model_Product_Type::TYPE_SIMPLE);
        $this->addToBasketProductFromVendor(5,Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE);
        // Esotiq
        $this->addToBasketProductFromVendor(4,Mage_Catalog_Model_Product_Type::TYPE_SIMPLE);
        $this->addToBasketProductFromVendor(4,Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE);
        // Levi's
        $this->addToBasketProductFromVendor(2,Mage_Catalog_Model_Product_Type::TYPE_SIMPLE);
        $this->addToBasketProductFromVendor(2,Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE);

        $scenario = array(
            'login'      => true,
            'email'      => TEST_USER,
            'password'   => TEST_PASSWORD,
            'newsletter' => false,
            'payment'     => $payment,
            'payment_type' => $paymentType
        );
        $this->_checkout($scenario);
    }

    /**
     * Zarejestrowany zakupy dla 3 vendorow z prostymi i konfigurowalnymi produktami
     * Platnosc przy odbiorze
     * @param string $payment
     * @param string $paymentType
     */
    public function testCheckoutRegistred3VendorsSimpleConfigurableProductsPaymentCC($payment = 'p_method_zolagopayment_cc', $paymentType = 'todo') {
        $this->testCheckoutRegistred3VendorsSimpleConfigurableProducts($payment, $paymentType);
    }

    /**
     * Rejestracja po zakupach
     * Przy zakupach zapis do newslettera, przy rejestracji odznaczenie newslettera
     */
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

    /**
     * Rejestracja po zakupach
     * Przy zakupach zapis do newslettera, przy rejestracji zapis do newsletter
     */
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

    /**
     * Rejestracja po zakupach
     * Przy zakupach brak zapisu do newslettera, przy rejestracji brak zapisu do newsletter
     */
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

    /**
     * Rejestracja po zakupach
     * Przy zakupach brak zapisu do newslettera, przy rejestracji brak zapisu do newsletter
     */
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
        if ($product->getId()) {
            $this->_allowProduct($product->getId());
            $url = $product->getProductUrl();
            $this->open($url);
            $this->addToBasketSelectSize();
            $this->click("id=add-to-cart");
            $this->click("css=div.modal-loaded.modal-header > button.close");
        }
    }

    /**
     * Selecting any size of product
     * Works for square and select list and simple product (no size)
     */
    public function addToBasketSelectSize() {
        $this->pause(500);// wait for js to do stuff
        $isSelect = $this->getEval("win.jQuery('.size-box select').data('selectBox-selectBoxIt') ? 1 : 0;");
        $isSquare = $this->getEval("win.jQuery('.size-box .size label').length ? 1 : 0;");

        if ($isSelect) {
            $this->getEval("win.jQuery('.size-box select').data('selectBox-selectBoxIt').selectOption(0);");//todo nie dziala
        } elseif ($isSquare) {
            $this->click("css=.size-box .size label");
        } else {
            // Simple with no size
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