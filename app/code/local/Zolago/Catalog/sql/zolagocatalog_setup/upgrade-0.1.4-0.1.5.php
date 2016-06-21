<?php
/** @var Zolago_Catalog_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

$priceLabels = array("A", "B", "C", "Z", "salePriceBefore");
foreach ($priceLabels as $priceLabel) {

	$installer->addAttribute('catalog_product', "external_price_{$priceLabel}", array(
		'group'             => 'Prices',
		'type'              => Varien_Db_Ddl_Table::TYPE_DECIMAL,
		'backend'           => '',
		'frontend'          => '',
		'label'             => "Converter price {$priceLabel}",
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

}

$installer->endSetup();




