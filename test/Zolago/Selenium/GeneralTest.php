<?php
/**
 * selenium general test
 */
class Zolago_Selenium_GeneralTest extends ZolagoSelenium_TestCase {
    public function setUp() {
        $this->setBrowser('*googlechrome');
        $this->setBrowserUrl('http://modago.dev/');
        
    }
    public function testTitle() {
    
        $this->open('http://modago.dev');
        $this->assertTitle('Modago');
    }
}