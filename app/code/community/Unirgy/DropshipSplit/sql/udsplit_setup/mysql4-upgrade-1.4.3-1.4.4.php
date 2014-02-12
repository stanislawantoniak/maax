<?php

$this->startSetup();

$sales = new Mage_Sales_Model_Mysql4_Setup('sales_setup');

$sales->addAttribute('quote', 'udropship_shipping_details', array('type'=>'text'));

$this->endSetup();