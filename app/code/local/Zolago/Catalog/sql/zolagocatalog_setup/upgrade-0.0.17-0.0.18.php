<?php

$installer = new Mage_Eav_Model_Entity_Setup('core_setup');;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('catalog/eav_attribute'), "column_width", "VARCHAR( 255 ) NULL");

$installer->endSetup();