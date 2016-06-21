<?php
/** @var Zolago_Catalog_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

$installer->addAttribute('catalog_product', 'external_price_a', array(
	'group'             => 'Prices',
	'type'              => Varien_Db_Ddl_Table::TYPE_DECIMAL,
	'backend'           => '',
	'frontend'          => '',
	'label'             => 'Converter price A',
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

$installer->addAttribute('catalog_product', 'external_price_b', array(
	'group'             => 'Prices',
	'type'              => Varien_Db_Ddl_Table::TYPE_DECIMAL,
	'backend'           => '',
	'frontend'          => '',
	'label'             => 'Converter price B',
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

$installer->addAttribute('catalog_product', 'external_price_c', array(
	'group'             => 'Prices',
	'type'              => Varien_Db_Ddl_Table::TYPE_DECIMAL,
	'backend'           => '',
	'frontend'          => '',
	'label'             => 'Converter price C',
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
$installer->addAttribute('catalog_product', 'external_price_z', array(
	'group'             => 'Prices',
	'type'              => Varien_Db_Ddl_Table::TYPE_DECIMAL,
	'backend'           => '',
	'frontend'          => '',
	'label'             => 'Converter price Z',
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

$installer->addAttribute('catalog_product', 'external_price_salePriceBefore', array(
	'group'             => 'Prices',
	'type'              => Varien_Db_Ddl_Table::TYPE_DECIMAL,
	'backend'           => '',
	'frontend'          => '',
	'label'             => 'Converter price salePriceBefore',
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




