<?php

$installer = $this;
$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

// Remove Customer Attribute
$setup->removeAttribute('customer', 'phone');
$setup->removeAttribute('customer', 'sms_agreement');

$installer->endSetup();