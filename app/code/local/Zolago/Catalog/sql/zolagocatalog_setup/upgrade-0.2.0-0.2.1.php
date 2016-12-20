<?php
/** @var Zolago_Catalog_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();


	$installer->addAttribute('catalog_product', 'update_stock_date', array(
		'group'             => 'Integrations',
		'type'              => Varien_Db_Ddl_Table::TYPE_DATETIME,
		'backend'           => '',
		'frontend'          => '',
		'label'             => "Update stock date",
		'input'             => 'text',
		'class'             => '',
		'source'            => '',
		'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
		'visible'           => true,
		'required'          => false,
		'user_defined'      => true,
		'searchable'        => false,
		'filterable'        => false,
		'comparable'        => false,
		'visible_on_front'  => false,
		'unique'            => false,
		'is_configurable'   => false
	));
	$installer->addAttribute('catalog_product', 'update_price_date', array(
		'group'             => 'Integrations',
		'type'              => Varien_Db_Ddl_Table::TYPE_DATETIME,
		'backend'           => '',
		'frontend'          => '',
		'label'             => "Update price date",
		'input'             => 'text',
		'class'             => '',
		'source'            => '',
		'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
		'visible'           => true,
		'required'          => false,
		'user_defined'      => true,
		'searchable'        => false,
		'filterable'        => false,
		'comparable'        => false,
		'visible_on_front'  => false,
		'unique'            => false,
		'is_configurable'   => false
	));


$installer->endSetup();




