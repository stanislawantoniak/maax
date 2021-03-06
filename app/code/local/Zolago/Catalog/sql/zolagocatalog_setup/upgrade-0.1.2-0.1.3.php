<?php
/** @var Zolago_Catalog_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

$installer->removeAttribute('catalog_product', 'charge_lower_commission');
// percent_for_charge_lower_commission
$installer->addAttribute('catalog_product', 'charge_lower_commission', array(
	'group'             => 'General',
	'type'              => Varien_Db_Ddl_Table::TYPE_DECIMAL,
	'backend'           => '',
	'frontend'          => '',
	'label'             => 'Percent for charge lower commission',
	'input'             => 'text',
	'class'             => '',
	'source'            => '',
	'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
	'visible'           => false,
	'required'          => false,
	'user_defined'      => true,
	'default'           => '0',
	'searchable'        => false,
	'filterable'        => false,
	'comparable'        => false,
	'visible_on_front'  => false,
	'unique'            => false,
	'is_configurable'   => false
));

$installer->endSetup();




