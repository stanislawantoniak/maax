<?php
$installer = new Mage_Eav_Model_Entity_Setup('core_setup');

$installer->startSetup();

$installer->updateAttribute('catalog_product', 'status', 'grid_permission', Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::DO_NOT_USE);

$installer->endSetup();