<?php
/**
 * selenium customer test
 */
class Automatic_Selenium_CustomerTest extends ZolagoSelenium_TestCase {
    public function setUp() {
        $host = $this->getHost();
        $this->setBrowser('*chrome');
        $this->setBrowserUrl($host);
        $this->setSleep(1);

    }
    public function testLogin() {
        $this->open("/");
        $this->click("link=Twoje Konto");
        $this->waitForPageToLoad("30000");
        $this->_login(TEST_USER);
        $this->waitForPageToLoad("30000");
        $this->assertTitle('Moje konto');

    }
    public function testRegisterNoSubscribe() {
        $email = $this->_getNewEmail();
        $this->_register($email,false);
    }
    public function testRegisterSubscribe() {
        $email = $this->_getNewEmail();
        $this->_register($email,true);
    }
}