<?php
//define('TEST_USER','zolago@wp.pl');
//define('TEST_PASSWORD','testtest123');
///**
// * selenium general test
// */
//class Automatic_Selenium_GeneralTest extends ZolagoSelenium_TestCase {
//    public function setUp() {
//        $host = $this->getHost();
//        $this->setBrowser('*chrome');
//        $this->setBrowserUrl($host);
//        $this->setSleep(1);
//
//    }
//    public function testTitle() {
//        $host = $this->getHost();
//        $this->open($host);
//        $this->assertTitle('Modago');
//    }
//    public function testLogin() {
//        $this->open("/");
//        $this->click("link=Twoje Konto");
//        $this->waitForPageToLoad("30000");
//        $this->type("id=email", TEST_USER);
//        $this->type("id=pass", TEST_PASSWORD);
//        $this->click("name=send");
//        $this->waitForPageToLoad("30000");
//        $this->assertTitle('Moje konto');
//
//    }
//    public function testRegister() {
//        $this->open("/");
//        $this->waitForPageToLoad(300000);
//        $this->click("css=#link_your_account > a > img.header_icon");
//        $this->waitForPageToLoad("30000000");
//        $this->click("link=Załóż konto");
//        $this->waitForPageToLoad("300000");
//        $this->type("id=account_email", "zolago@wp.pl");
//        $this->type("id=account_password", "testtest123");
//        $this->click("id=agreement");
//        $this->click("id=is_subscribed");
//        $this->click("//button[@type='submit']");
//        $this->waitForPageToLoad("30000");
//        $this->assertTitle('Moje konto');
//    }
//    public function addToBasketMatterhorn() {
//        $this->open("/acilia-32371.html");
//        $this->click("id=size_384");
//        $this->click("id=add-to-cart");
//        $this->click("css=div.modal-loaded.modal-header > button.close");
//    }
//    public function addToBasketEsotiq() {
//        $this->open("/stanik-norah.html");
//        $this->click("id=select-data-id-281");
//        $this->click("id=add-to-cart");
//        $this->click("id=popup-after-add-to-cart");
//
//    }
//    public function addToBasketJeansdom() {
//        $this->open("/levi-sr-511tm-jeans-slim-fit-wood-acre.html");
//        $this->click("id=add-to-cart");
//        $this->click("css=div.modal-loaded.modal-header > button.close");
//    }
//    protected function allowProduct($productId) {
//        $product = Mage::getModel('catalog/product')->load($productId);
//        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());
//        $data = $stockItem->getData();
//        if (($data['qty'] < 1) || $data['is_in_stock'] == 0) {
//            $stockItem->setData('qty',10);
//            $stockItem->setData('is_in_stock',1);
//            $stockItem->save();
//            $product->save();
//        }
//    }
//    protected function checkout($address) {
//        $this->click("css=#link_basket > a.dropdown-toggle");
//        $this->waitForPageToLoad("30000");
//        $this->clickAndWait("link=Kupuję");
//        $this->type("id=email", "zolago@wp.pl");
//        $this->type("id=pass", "testtest123");
//        $this->clickAndWait("name=send");
//        if (!$address) {
//            $this->assertEquals("Potwierdź dane dostawy",$this->getText("css=header.title-section > h2"));
//            $this->click("id=p_method_cashondelivery");
//        } else {
//            $this->verifyText("css=header.title-section > h2", "Potwierdź dane dostawy");
//            $this->type("id=account_firstname", "Jan");
//            $this->type("id=account_lastname", "Kowalski");
//            $this->type("id=account_telephone", "111222333");
//            $this->type("id=shipping_street", "Krótka");
//            $this->type("id=shipping_postcode", "18-400");
//            $this->type("id=shipping_city", "Chrząszczyrzewoszyce");
//            $this->click("id=agreement_tos");
//            $this->click("id=step-0-submit");
//            $this->click("id=p_method_cashondelivery");
//            $this->click("//div[@id='content-main']/section/fieldset[2]/div[2]/div/div[4]/div/div/label/span/strong");
//
//        }
//        $this->click("id=step-1-submit");
//        $this->click("id=step-2-submit");
//        $this->pause(5);
//        $this->assertEquals("Dziękujemy za złożenie zamówienia!", $this->getText("css=h2"));
//    }
//    public function testOrderWithNoAddress() {
//        $this->allowProduct(25758);
//        $this->addToBasketMatterhorn();
//        $this->checkout(true);
//    }
//    public function testOrder3Vendors() {
//        $this->allowProduct(25758);
//        $this->addToBasketMatterhorn();
//        $this->allowProduct(258);
//        $this->addToBasketEsotiq();
//        $this->allowProduct(33396);
//        $this->addToBasketJeansdom();
//        $this->checkout(false);
//
//    }
//}