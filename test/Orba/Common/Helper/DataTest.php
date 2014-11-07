<?php
/**
 *
 */
class Orba_Common_Helper_DataTest extends Zolago_TestCase
{
    public function testFormatOrders() {
        $helper = Mage::helper('orbacommon');
        $this->assertEquals($helper->formatOrdersText(5),'5 zamówień');
        $this->assertEquals($helper->formatOrdersText(33),'33 zamówienia');
        $this->assertEquals($helper->formatOrdersText(1),'1 zamówienie');
        $this->assertEquals($helper->formatOrdersText(44),'44 zamówienia');
        $this->assertEquals($helper->formatOrdersText(105),'105 zamówień');
        $this->assertEquals($helper->formatOrdersText(0),'0 zamówień');
        $this->assertEquals($helper->formatOrdersText(11),'11 zamówień');
        $this->assertEquals($helper->formatOrdersText(15),'15 zamówień');
    }
}
