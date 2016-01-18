<?php
/**
 * New global attribute EAN
 */

$installer = new Mage_Catalog_Model_Resource_Setup('core_setup');
/**@var$installerMage_Catalog_Model_Resource_Setup */

$installer->startSetup();

$installer->addAttribute('catalog_product', 'ean', array(
	'backend' => null,
	'type' => 'text',
	'table' => null,
	'frontend' => null,
	'input' => 'text',
	'label' => 'EAN',
	'frontend_class' => null,
	'source' => null,
	'required' => 0,
	'user_defined' => 1,
	'default' => '',
	'unique' => 0,
	'note' => null,
	'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
	'group' => 'General',

	'is_visible' => 0,
	'grid_permission' => Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::DO_NOT_USE
));

$installer->endSetup();