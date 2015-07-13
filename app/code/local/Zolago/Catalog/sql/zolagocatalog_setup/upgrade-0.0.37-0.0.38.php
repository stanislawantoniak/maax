<?php
$installer = new Mage_Eav_Model_Entity_Setup('core_setup');;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$installer->updateAttribute('catalog_product', 'description_status', 'required', false);

$installer->endSetup();