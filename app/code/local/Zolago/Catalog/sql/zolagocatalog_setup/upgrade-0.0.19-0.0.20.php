<?php

$installer = new Mage_Eav_Model_Entity_Setup('core_setup');;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('catalog/eav_attribute'), "column_attribute_order", "TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT '0'");
$installer->endSetup();




