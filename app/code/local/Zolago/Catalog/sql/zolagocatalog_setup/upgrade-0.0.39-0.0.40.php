<?php
$installer = new Mage_Eav_Model_Entity_Setup('core_setup');;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$installer->updateAttribute('catalog_product', 'description_status', 'default_value', Zolago_Catalog_Model_Product_Source_Description::DESCRIPTION_NOT_ACCEPTED);

$installer->endSetup();