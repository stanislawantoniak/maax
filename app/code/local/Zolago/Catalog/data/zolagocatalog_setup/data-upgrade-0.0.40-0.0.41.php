<?php
/**
 * Product name now can be editable on grid
 */

$installer = new Mage_Eav_Model_Entity_Setup('core_setup');;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$installer->updateAttribute('catalog_product', 'name', 'grid_permission', Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::EDITION);

$installer->endSetup();